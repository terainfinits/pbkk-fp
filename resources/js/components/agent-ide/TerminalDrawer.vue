<script setup lang="ts">
import { computed, inject, nextTick, onMounted, onUnmounted, ref, watch } from 'vue';
import { Terminal } from '@xterm/xterm';
import { FitAddon } from '@xterm/addon-fit';
import '@xterm/xterm/css/xterm.css';
import type { IdeApi, TerminalSessionResponse } from '@/composables/useIdeApi';
import type { useIdeState } from '@/composables/useIdeState';

const { api, state } = inject<{ api: IdeApi; state: ReturnType<typeof useIdeState> }>('ide')!;

/* ─── Terminal state ─── */
const terminalContainer = ref<HTMLDivElement | null>(null);
const outputRef = ref<HTMLDivElement | null>(null);

const activeTab = ref<'shell' | 'kernel'>('shell');
const isMinimized = ref(false);
const execTime = ref<number | null>(null);
const connectionStatus = ref('Connecting');

let term: Terminal | null = null;
let fitAddon: FitAddon | null = null;
let socket: WebSocket | null = null;
let resizeObserver: ResizeObserver | null = null;
let isUnmounting = false;
let isAuthenticated = false;

interface LogItem {
  id: number;
  type: 'command' | 'output' | 'error' | 'system' | 'meta';
  text: string;
  prompt?: string;
}

const kernelLogs = ref<LogItem[]>([]);
let logSeq = 0;
const nextLogId = () => ++logSeq;

const visibleLogs = computed(() => kernelLogs.value);

const scrollToBottom = () => {
  nextTick(() => {
    if (outputRef.value) {
      outputRef.value.scrollTop = outputRef.value.scrollHeight;
    }
  });
};

/* ─── WebSocket Docker terminal ─── */

function sendResize() {
  if (
    !isAuthenticated ||
    !term ||
    !socket ||
    socket.readyState !== WebSocket.OPEN
  ) {
    return;
  }

  socket.send(JSON.stringify({
    type: 'resize',
    cols: term.cols,
    rows: term.rows,
  }));
}

async function connectTerminal() {
  if (!term) return;

  try {
    connectionStatus.value = 'Requesting session';

    const session: TerminalSessionResponse = await api.createTerminalSession();

    console.log('Terminal session response:', session);

    if (isUnmounting) return;

    if (!session.ticket || !session.ws_url) {
      throw new Error('Invalid terminal session response.');
    }

    connectionStatus.value = 'Connecting';

    socket = new WebSocket(session.ws_url);
    socket.binaryType = 'arraybuffer';

    socket.onopen = () => {
      connectionStatus.value = 'Authenticating';

      socket?.send(JSON.stringify({
        type: 'auth',
        ticket: session.ticket,
      }));
    };

    socket.onmessage = async (event: MessageEvent) => {
      if (typeof event.data === 'string') {
        let message: {
          type: string;
          message?: string;
        };

        try {
          message = JSON.parse(event.data);
        } catch {
          term?.writeln('\r\n\x1b[31mInvalid gateway response.\x1b[0m');
          return;
        }

        switch (message.type) {
          case 'attached':
            isAuthenticated = true;
            connectionStatus.value = 'Connected';

            term?.writeln('\r\n\x1b[90mConnected to Docker terminal.\x1b[0m');

            await nextTick();

            fitAddon?.fit();
            sendResize();
            term?.focus();
            break;

          case 'error':
            isAuthenticated = false;
            connectionStatus.value = 'Error';
            term?.writeln(`\r\n\x1b[31m${message.message ?? 'Terminal error'}\x1b[0m`);
            break;

          case 'exit':
            connectionStatus.value = 'Exited';
            term?.writeln('\r\n\x1b[90mTerminal process exited.\x1b[0m');
            break;

          case 'terminated':
            isAuthenticated = false;
            connectionStatus.value = 'Terminated';
            break;
        }

        return;
      }

      if (event.data instanceof ArrayBuffer) {
        term?.write(new Uint8Array(event.data));
        return;
      }

      if (event.data instanceof Blob) {
        const buffer = await event.data.arrayBuffer();
        term?.write(new Uint8Array(buffer));
      }
    };

    socket.onerror = () => {
      connectionStatus.value = 'Connection error';
    };

    socket.onclose = () => {
      isAuthenticated = false;

      if (!isUnmounting) {
        connectionStatus.value = 'Disconnected';
      }
    };
  } catch (error) {
    isAuthenticated = false;
    connectionStatus.value = 'Error';

    const message = error instanceof Error
      ? error.message
      : 'Failed to create terminal session.';

    term?.writeln(`\r\n\x1b[31m${message}\x1b[0m`);
  }
}

/* ─── Kernel output ─── */

interface KernelRunDetail {
  code: string;
  language: string;
  path?: string;
}

async function runKernel(detail: KernelRunDetail) {
  activeTab.value = 'kernel';

  if (isMinimized.value) {
    isMinimized.value = false;
  }

  const lang = detail.language
    || (detail.path ? detail.path.split('.').pop() : 'php')
    || 'php';

  kernelLogs.value.push({
    id: nextLogId(),
    type: 'command',
    prompt: '➜',
    text: `Executing ${lang.toUpperCase()} kernel${detail.path ? ` (${detail.path})` : ''}...`,
  });

  scrollToBottom();

  const data = await api.runCode(detail.code, lang, detail.path || '');

  execTime.value = data.executionTimeMs ?? null;

  if (data.success) {
    if (data.stdout) {
      kernelLogs.value.push({
        id: nextLogId(),
        type: 'output',
        text: data.stdout,
      });
    }

    if (data.stderr) {
      kernelLogs.value.push({
        id: nextLogId(),
        type: 'error',
        text: data.stderr,
      });
    }

    if (!data.stdout && !data.stderr) {
      kernelLogs.value.push({
        id: nextLogId(),
        type: 'meta',
        text: `[Process exited with code ${data.exitCode} (No output)]`,
      });
    }

    kernelLogs.value.push({
      id: nextLogId(),
      type: 'meta',
      text: `Exit Code: ${data.exitCode} | Time: ${data.executionTimeMs}ms`,
    });

    state.pushToast(`Code executed in ${data.kernel} (${data.executionTimeMs}ms)`);
  } else {
    kernelLogs.value.push({
      id: nextLogId(),
      type: 'error',
      text: `Error: ${data.error || 'Execution failed'}`,
    });
  }

  scrollToBottom();
}

function handleKernelRun(event: Event) {
  const customEvent = event as CustomEvent<KernelRunDetail>;
  void runKernel(customEvent.detail);
}

/* ─── Terminal controls ─── */

function clearActive() {
  if (activeTab.value === 'shell') {
    term?.clear();
  } else {
    kernelLogs.value = [];
  }
}

function handleTerminalInput(data: string) {
  if (activeTab.value !== 'shell') return;
  if (!isAuthenticated || socket?.readyState !== WebSocket.OPEN) return;

  socket.send(new TextEncoder().encode(data));
}

/* ─── Lifecycle ─── */

onMounted(() => {
  term = new Terminal({
    cursorBlink: true,
    fontFamily: 'monospace',
    fontSize: 11,
    theme: {
      background: '#0c101c',
      foreground: '#cccccc',
      cursor: '#ffffff',
    },
  });

  fitAddon = new FitAddon();
  term.loadAddon(fitAddon);

  if (terminalContainer.value) {
    term.open(terminalContainer.value);
    fitAddon.fit();
  }

  term.writeln('\x1b[90mConnecting to Docker terminal...\x1b[0m');
  term.onData(handleTerminalInput);

  void connectTerminal();

  if (terminalContainer.value) {
    resizeObserver = new ResizeObserver(() => {
      if (activeTab.value === 'shell') {
        fitAddon?.fit();
        sendResize();
      }
    });

    resizeObserver.observe(terminalContainer.value);
  }

  window.addEventListener('ide:kernel-run', handleKernelRun);
});

onUnmounted(() => {
  isUnmounting = true;
  isAuthenticated = false;

  window.removeEventListener('ide:kernel-run', handleKernelRun);

  resizeObserver?.disconnect();

  if (socket?.readyState === WebSocket.OPEN) {
    socket.send(JSON.stringify({ type: 'detach' }));
    socket.close();
  } else {
    socket?.close();
  }

  term?.dispose();
});

watch(isMinimized, () => {
  nextTick(() => {
    if (activeTab.value === 'shell') {
      fitAddon?.fit();
      sendResize();
    }
  });
});

watch(activeTab, () => {
  nextTick(() => {
    if (activeTab.value === 'shell') {
      fitAddon?.fit();
      sendResize();
      term?.focus();
    } else {
      scrollToBottom();
    }
  });
});
</script>

<template>
  <div
    class="bg-[#080c14] border-t border-slate-800 flex flex-col shrink-0 transition-all duration-200"
    :style="{ height: isMinimized ? '32px' : '220px' }"
  >
    <!-- Tab bar -->
    <div class="h-8 bg-[#0d1322] px-3 flex items-center justify-between border-b border-slate-800 text-xs select-none shrink-0">
      <div class="flex items-center gap-1">
        <button
          class="terminal-tab px-3 py-1 rounded-t flex items-center gap-1.5 font-semibold transition"
          :class="activeTab === 'shell'
            ? 'text-slate-200 bg-[#080c14] border border-slate-700 border-b-0 -mb-px relative z-10'
            : 'text-slate-500 hover:text-slate-300 bg-transparent border border-transparent'"
          @click="activeTab = 'shell'"
        >
          <i class="fa-solid fa-terminal text-cyan-400 text-[10px]"></i>
          <span>Terminal</span>
        </button>

        <button
          class="terminal-tab px-3 py-1 rounded-t flex items-center gap-1.5 transition"
          :class="activeTab === 'kernel'
            ? 'text-slate-200 bg-[#080c14] border border-slate-700 border-b-0 -mb-px relative z-10'
            : 'text-slate-500 hover:text-slate-300 bg-transparent border border-transparent'"
          @click="activeTab = 'kernel'"
        >
          <i class="fa-solid fa-microchip text-indigo-400 text-[10px]"></i>
          <span>Kernel</span>
        </button>

        <span
          v-if="activeTab === 'shell'"
          class="ml-2 px-2 py-0.5 rounded bg-indigo-500/20 text-indigo-300 border border-indigo-500/30 text-[10px] font-mono"
        >
          {{ connectionStatus }}
        </span>

        <span
          v-else
          class="ml-2 px-2 py-0.5 rounded bg-indigo-500/20 text-indigo-300 border border-indigo-500/30 text-[10px] font-mono"
        >
          System Kernel Ready
        </span>

        <span v-if="execTime !== null" class="text-[10px] text-slate-500 font-mono">
          {{ execTime }}ms
        </span>
      </div>

      <div class="flex items-center gap-2 text-[11px]">
        <button
          class="px-2 py-0.5 rounded hover:bg-slate-800 text-slate-400 hover:text-slate-200 transition"
          @click="clearActive"
        >
          <i class="fa-solid fa-trash-can mr-1 text-[10px]"></i> Clear
        </button>

        <button
          class="px-2 py-0.5 rounded hover:bg-slate-800 text-slate-400 hover:text-slate-200 transition"
          @click="isMinimized = !isMinimized"
        >
          <i
            class="fa-solid text-[10px]"
            :class="isMinimized ? 'fa-chevron-up' : 'fa-chevron-down'"
          ></i>
        </button>
      </div>
    </div>

    <!-- Docker terminal: shell tab only -->
    <div
      v-show="!isMinimized && activeTab === 'shell'"
      ref="terminalContainer"
      class="flex-1 p-1 bg-[#0c101c] overflow-hidden select-text"
    ></div>

    <!-- Original independent kernel output -->
    <div
      v-show="!isMinimized && activeTab === 'kernel'"
      ref="outputRef"
      class="flex-1 p-3 overflow-y-auto font-mono text-[11px] leading-5 text-slate-300 bg-[#060910] space-y-1 select-text"
    >
      <div v-for="log in visibleLogs" :key="log.id">
        <div
          v-if="log.type === 'command'"
          class="flex items-center gap-2 text-slate-200 font-semibold mt-1"
        >
          <span class="text-cyan-400 font-bold">{{ log.prompt }}</span>
          <span>{{ log.text }}</span>
        </div>

        <pre
          v-else-if="log.type === 'output'"
          class="text-slate-300 whitespace-pre-wrap pl-4"
        >{{ log.text }}</pre>

        <pre
          v-else-if="log.type === 'error'"
          class="text-rose-400 whitespace-pre-wrap pl-4"
        >{{ log.text }}</pre>

        <div
          v-else-if="log.type === 'meta'"
          class="text-[10px] text-slate-500 pl-4"
        >
          {{ log.text }}
        </div>
      </div>
    </div>
  </div>
</template>
