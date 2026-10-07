<script setup lang="ts">
import { computed, inject, nextTick, onMounted, ref, watch } from 'vue';
import type { IdeApi } from '@/composables/useIdeApi';
import type { useIdeState } from '@/composables/useIdeState';

const { api, state } = inject<{ api: IdeApi; state: ReturnType<typeof useIdeState> }>('ide')!;

/* ─── Terminal state ─── */
const outputRef = ref<HTMLDivElement | null>(null);
const inputRef = ref<HTMLInputElement | null>(null);

const activeTab = ref<'shell' | 'kernel'>('shell');
const isMinimized = ref(false);
const commandInput = ref('');
const isExecuting = ref(false);
const execTime = ref<number | null>(null);
const cwd = ref('');
const isWindows = ref(false);

const history = ref<string[]>([]);
const historyIndex = ref(-1);

interface LogItem {
  id: number;
  type: 'command' | 'output' | 'error' | 'system' | 'meta';
  text: string;
  prompt?: string;
}
const logs = ref<LogItem[]>([]);
const kernelLogs = ref<LogItem[]>([]);
let logSeq = 0;
const nextLogId = () => ++logSeq;

const promptSign = computed(() => (isWindows.value ? 'PS>' : '$'));
const promptCwd = computed(() => {
  if (isWindows.value) return cwd.value ? `PS ~/${cwd.value}>` : 'PS ~>';
  return cwd.value ? `~/${cwd.value} $` : '~ $';
});
const examples = computed(() =>
  isWindows.value ? 'python script.py, dir, cd app' : 'python3 script.py, ls, cd app'
);
const shellDesc = computed(() =>
  isWindows.value
    ? 'Windows PowerShell — type commands below. Supports python, php, node, dir, cd, and all PowerShell commands.'
    : 'Unix Shell — type commands below. Supports python3, php, node, ls, cd, and standard shell commands.'
);

const visibleLogs = computed(() => (activeTab.value === 'shell' ? logs.value : kernelLogs.value));

const scrollToBottom = () => {
  nextTick(() => {
    if (outputRef.value) outputRef.value.scrollTop = outputRef.value.scrollHeight;
  });
};

/* ─── Shell command execution ─── */
async function execute(command: string) {
  isExecuting.value = true;
  const start = performance.now();

  logs.value.push({
    id: nextLogId(),
    type: 'command',
    prompt: promptCwd.value,
    text: command,
  });
  scrollToBottom();

  if (['cls', 'clear'].includes(command.trim().toLowerCase())) {
    logs.value = [];
    isExecuting.value = false;
    return;
  }

  const data = await api.terminalExecute(command, cwd.value);
  execTime.value = Math.round(performance.now() - start);

  if (data.success) {
    if (data.cwd !== undefined) cwd.value = data.cwd || '';
    if (data.stdout) {
      logs.value.push({ id: nextLogId(), type: 'output', text: data.stdout });
    }
    if (data.stderr) {
      logs.value.push({ id: nextLogId(), type: 'error', text: data.stderr });
    }
    if (data.exitCode !== 0) {
      logs.value.push({
        id: nextLogId(),
        type: 'meta',
        text: `Exit code: ${data.exitCode} (${data.executionTimeMs}ms)`,
      });
    }
  } else {
    logs.value.push({
      id: nextLogId(),
      type: 'error',
      text: data.error || 'Command execution failed.',
    });
  }

  isExecuting.value = false;
  scrollToBottom();
  nextTick(() => inputRef.value?.focus());
}

function onKeydown(e: KeyboardEvent) {
  if (e.key === 'Enter') {
    e.preventDefault();
    const cmd = commandInput.value.trim();
    if (!cmd || isExecuting.value) return;
    if (history.value[history.value.length - 1] !== cmd) history.value.push(cmd);
    historyIndex.value = history.value.length;
    commandInput.value = '';
    execute(cmd);
    return;
  }
  if (e.key === 'ArrowUp') {
    e.preventDefault();
    if (history.value.length && historyIndex.value > 0) {
      historyIndex.value--;
      commandInput.value = history.value[historyIndex.value] || '';
    }
    return;
  }
  if (e.key === 'ArrowDown') {
    e.preventDefault();
    if (historyIndex.value < history.value.length - 1) {
      historyIndex.value++;
      commandInput.value = history.value[historyIndex.value] || '';
    } else {
      historyIndex.value = history.value.length;
      commandInput.value = '';
    }
    return;
  }
  if (e.key === 'l' && e.ctrlKey) {
    e.preventDefault();
    logs.value = [];
    return;
  }
  if (e.key === 'c' && e.ctrlKey) {
    e.preventDefault();
    if (commandInput.value) {
      logs.value.push({
        id: nextLogId(),
        type: 'command',
        prompt: promptCwd.value,
        text: commandInput.value + '^C',
      });
      commandInput.value = '';
    }
  }
}

/* ─── Kernel run handler (listens to window event from Editor/Chat) ─── */
interface KernelRunDetail {
  code: string;
  language: string;
  path?: string;
}
async function runKernel(detail: KernelRunDetail) {
  activeTab.value = 'kernel';
  if (isMinimized.value) isMinimized.value = false;

  const lang = detail.language || (detail.path ? detail.path.split('.').pop() : 'php') || 'php';
  kernelLogs.value.push({
    id: nextLogId(),
    type: 'command',
    prompt: '➜',
    text: `Executing ${lang.toUpperCase()} kernel${detail.path ? ' (' + detail.path + ')' : ''}...`,
  });
  scrollToBottom();

  const data = await api.runCode(detail.code, lang, detail.path || '');
  execTime.value = data.executionTimeMs ?? null;

  if (data.success) {
    if (data.stdout) kernelLogs.value.push({ id: nextLogId(), type: 'output', text: data.stdout });
    if (data.stderr) kernelLogs.value.push({ id: nextLogId(), type: 'error', text: data.stderr });
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

function clearActive() {
  if (activeTab.value === 'shell') logs.value = [];
  else kernelLogs.value = [];
}

watch(activeTab, () => nextTick(() => inputRef.value?.focus()));

onMounted(() => {
  if (typeof navigator !== 'undefined') {
    isWindows.value = navigator.userAgent.toLowerCase().includes('win');
  }
  logs.value.push({
    id: nextLogId(),
    type: 'system',
    text: shellDesc.value,
    prompt: promptSign.value,
  });
  window.addEventListener('ide:kernel-run', (e: any) => runKernel(e.detail));
  nextTick(() => inputRef.value?.focus());
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

        <span class="ml-2 px-2 py-0.5 rounded bg-indigo-500/20 text-indigo-300 border border-indigo-500/30 text-[10px] font-mono">
          System Kernel Ready
        </span>
        <span v-if="execTime !== null" class="text-[10px] text-slate-500 font-mono">{{ execTime }}ms</span>
      </div>
      <div class="flex items-center gap-2 text-[11px]">
        <button class="px-2 py-0.5 rounded hover:bg-slate-800 text-slate-400 hover:text-slate-200 transition" @click="clearActive">
          <i class="fa-solid fa-trash-can mr-1 text-[10px]"></i> Clear
        </button>
        <button class="px-2 py-0.5 rounded hover:bg-slate-800 text-slate-400 hover:text-slate-200 transition" @click="isMinimized = !isMinimized">
          <i class="fa-solid text-[10px]" :class="isMinimized ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
        </button>
      </div>
    </div>

    <!-- Output body -->
    <div
      v-show="!isMinimized"
      ref="outputRef"
      class="flex-1 p-3 overflow-y-auto font-mono text-[11px] leading-5 text-slate-300 bg-[#060910] space-y-1 select-text"
    >
      <div v-for="log in visibleLogs" :key="log.id">
        <div v-if="log.type === 'system'" class="text-slate-500 flex items-center gap-2">
          <span class="text-cyan-400 font-bold">{{ log.prompt }}</span>
          <span>{{ log.text }}</span>
        </div>
        <div v-else-if="log.type === 'command'" class="flex items-center gap-2 text-slate-200 font-semibold mt-1">
          <span class="text-cyan-400 font-bold">{{ log.prompt }}</span>
          <span>{{ log.text }}</span>
        </div>
        <pre v-else-if="log.type === 'output'" class="text-slate-300 whitespace-pre-wrap pl-4">{{ log.text }}</pre>
        <pre v-else-if="log.type === 'error'" class="text-rose-400 whitespace-pre-wrap pl-4">{{ log.text }}</pre>
        <div v-else-if="log.type === 'meta'" class="text-[10px] text-slate-500 pl-4">{{ log.text }}</div>
      </div>
      <div v-if="isExecuting" class="text-indigo-400 animate-pulse flex items-center gap-2 pl-4">
        <i class="fa-solid fa-circle-notch fa-spin text-xs"></i>
        <span>Executing command...</span>
      </div>
    </div>

    <!-- Prompt (shell tab only) -->
    <div
      v-show="!isMinimized && activeTab === 'shell'"
      class="shrink-0 bg-[#0a0f1a] border-t border-slate-800/70 px-3 py-1.5 flex items-center gap-2 font-mono text-[11px]"
    >
      <span class="text-cyan-400 font-bold whitespace-nowrap select-none">{{ promptCwd }}</span>
      <input
        ref="inputRef"
        v-model="commandInput"
        type="text"
        class="flex-1 bg-transparent text-slate-200 outline-none border-none placeholder-slate-600 caret-cyan-400 font-mono text-[11px]"
        :placeholder="`Type command here... (e.g. ${examples})`"
        autocomplete="off"
        spellcheck="false"
        :disabled="isExecuting"
        @keydown="onKeydown"
      />
    </div>
  </div>
</template>
