<script setup lang="ts">
import {
    computed,
    inject,
    nextTick,
    onMounted,
    onBeforeUnmount,
    ref,
    watch,
} from 'vue';
import type { IdeApi } from '@/composables/useIdeApi';
import type { useIdeState } from '@/composables/useIdeState';

const { api, state } = inject<{
    api: IdeApi;
    state: ReturnType<typeof useIdeState>;
}>('ide')!;

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
const kernelBusy = ref(false);
const pythonRunId = ref<string | null>(null);
const programInput = ref('');
const programInputRef = ref<HTMLInputElement | null>(null);
const sendingInput = ref(false);
let pollTimer: ReturnType<typeof setTimeout> | undefined;
let stdoutLength = 0;
let stderrLength = 0;
let disposed = false;
let pollFailures = 0;

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
    isWindows.value
        ? 'python script.py, dir, cd app'
        : 'python3 script.py, ls, cd app',
);
const shellDesc = computed(() =>
    isWindows.value
        ? 'Windows PowerShell — type commands below. Supports python, php, node, dir, cd, and all PowerShell commands.'
        : 'Unix Shell — type commands below. Supports python3, php, node, ls, cd, and standard shell commands.',
);

const visibleLogs = computed(() =>
    activeTab.value === 'shell' ? logs.value : kernelLogs.value,
);

const scrollToBottom = () => {
    nextTick(() => {
        if (outputRef.value)
            outputRef.value.scrollTop = outputRef.value.scrollHeight;
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
            logs.value.push({
                id: nextLogId(),
                type: 'output',
                text: data.stdout,
            });
        }
        if (data.stderr) {
            logs.value.push({
                id: nextLogId(),
                type: 'error',
                text: data.stderr,
            });
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
        if (history.value[history.value.length - 1] !== cmd)
            history.value.push(cmd);
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

function appendKernelOutput(text: string, type: 'output' | 'error') {
    if (!text) return;
    const last = kernelLogs.value[kernelLogs.value.length - 1];
    if (last?.type === type) last.text += text;
    else kernelLogs.value.push({ id: nextLogId(), type, text });
    scrollToBottom();
}

async function pollPython() {
    const runId = pythonRunId.value;
    if (!runId || disposed) return;
    try {
        const data = await api.pythonStatus(runId);
        if (disposed || pythonRunId.value !== runId) return;
        if (!data.success) {
            appendKernelOutput(
                `${data.message || 'This Python run is no longer available.'}\n`,
                'error',
            );
            pythonRunId.value = null;
            kernelBusy.value = false;
            return;
        }
        pollFailures = 0;
        appendKernelOutput(data.stdout.slice(stdoutLength), 'output');
        appendKernelOutput(data.stderr.slice(stderrLength), 'error');
        stdoutLength = data.stdout.length;
        stderrLength = data.stderr.length;
        execTime.value = data.executionTimeMs;
        if (!data.running) {
            pythonRunId.value = null;
            kernelBusy.value = false;
            kernelLogs.value.push({
                id: nextLogId(),
                type: 'meta',
                text: `Exit Code: ${data.exitCode} | Time: ${data.executionTimeMs}ms`,
            });
            state.pushToast(
                data.exitCode === 0
                    ? 'Python finished.'
                    : 'Python exited with an error or was stopped.',
                data.exitCode !== 0,
            );
            scrollToBottom();
            return;
        }
    } catch (error) {
        if (disposed) return;
        if (pollFailures++ === 0)
            appendKernelOutput(
                `${error instanceof Error ? error.message : 'Connection lost.'} Retrying…\n`,
                'error',
            );
    }
    if (!disposed && pythonRunId.value === runId) {
        pollTimer = setTimeout(
            () => void pollPython(),
            pollFailures ? 2000 : 200,
        );
    }
}

async function sendProgramInput() {
    const runId = pythonRunId.value;
    if (!runId || sendingInput.value) return;
    sendingInput.value = true;
    const input = programInput.value;
    try {
        const data = await api.pythonInput(runId, input);
        if (!data.success)
            throw new Error(data.message || 'Could not send input.');
        kernelLogs.value.push({
            id: nextLogId(),
            type: 'command',
            prompt: 'stdin ›',
            text: input || '(empty line)',
        });
        programInput.value = '';
        scrollToBottom();
    } catch (error) {
        appendKernelOutput(
            `${error instanceof Error ? error.message : 'Could not send input.'}\n`,
            'error',
        );
    } finally {
        sendingInput.value = false;
        nextTick(() => programInputRef.value?.focus());
    }
}

async function stopPython() {
    if (!pythonRunId.value) return;
    try {
        const data = await api.stopPython(pythonRunId.value);
        if (!data.success)
            throw new Error(data.message || 'Could not stop Python.');
    } catch (error) {
        appendKernelOutput(
            `${error instanceof Error ? error.message : 'Could not stop Python.'}\n`,
            'error',
        );
    }
}

async function runKernel(detail: KernelRunDetail) {
    activeTab.value = 'kernel';
    if (isMinimized.value) isMinimized.value = false;
    if (kernelBusy.value) {
        state.pushToast(
            'A program is already running. Finish or stop it before pressing F5 again.',
            true,
        );
        nextTick(() => programInputRef.value?.focus());
        return;
    }
    kernelBusy.value = true;

    const lang =
        detail.language ||
        (detail.path ? detail.path.split('.').pop() : 'php') ||
        'php';
    kernelLogs.value.push({
        id: nextLogId(),
        type: 'command',
        prompt: '➜',
        text: `Executing ${lang.toUpperCase()} kernel${detail.path ? ' (' + detail.path + ')' : ''}...`,
    });
    scrollToBottom();

    if (['py', 'python'].includes(lang.toLowerCase())) {
        try {
            const data = await api.startPython(detail.code);
            if (!data.success || !data.runId)
                throw new Error(
                    data.message || data.error || 'Unable to start Python.',
                );
            if (disposed) {
                void api.stopPython(data.runId).catch(() => {});
                return;
            }
            pythonRunId.value = data.runId;
            stdoutLength = 0;
            stderrLength = 0;
            pollFailures = 0;
            programInput.value = '';
            nextTick(() => programInputRef.value?.focus());
            void pollPython();
        } catch (error) {
            kernelBusy.value = false;
            appendKernelOutput(
                `${error instanceof Error ? error.message : 'Unable to start Python.'}\n`,
                'error',
            );
        }
        return;
    }

    try {
        const data = await api.runCode(detail.code, lang, detail.path || '');
        execTime.value = data.executionTimeMs ?? null;

        if (data.success) {
            if (data.stdout)
                kernelLogs.value.push({
                    id: nextLogId(),
                    type: 'output',
                    text: data.stdout,
                });
            if (data.stderr)
                kernelLogs.value.push({
                    id: nextLogId(),
                    type: 'error',
                    text: data.stderr,
                });
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
            state.pushToast(
                `Code executed in ${data.kernel} (${data.executionTimeMs}ms)`,
            );
        } else {
            kernelLogs.value.push({
                id: nextLogId(),
                type: 'error',
                text: `Error: ${data.error || 'Execution failed'}`,
            });
        }
        scrollToBottom();
    } catch (error) {
        appendKernelOutput(
            `${error instanceof Error ? error.message : 'Execution failed.'}\n`,
            'error',
        );
    } finally {
        kernelBusy.value = false;
    }
}

function clearActive() {
    if (activeTab.value === 'shell') logs.value = [];
    else kernelLogs.value = [];
}

watch(activeTab, () =>
    nextTick(() =>
        activeTab.value === 'kernel'
            ? programInputRef.value?.focus()
            : inputRef.value?.focus(),
    ),
);

const handleKernelRun = (event: Event) =>
    void runKernel((event as CustomEvent<KernelRunDetail>).detail);

onMounted(() => {
    logs.value.push({
        id: nextLogId(),
        type: 'system',
        text: shellDesc.value,
        prompt: promptSign.value,
    });
    window.addEventListener('ide:kernel-run', handleKernelRun);
    nextTick(() => inputRef.value?.focus());
});

onBeforeUnmount(() => {
    disposed = true;
    clearTimeout(pollTimer);
    window.removeEventListener('ide:kernel-run', handleKernelRun);
    if (pythonRunId.value)
        void api.stopPython(pythonRunId.value).catch(() => {});
});
</script>

<template>
    <div
        class="flex shrink-0 flex-col border-t border-slate-800 bg-[#080c14] transition-all duration-200"
        :style="{ height: isMinimized ? '32px' : '220px' }"
    >
        <!-- Tab bar -->
        <div
            class="flex h-8 shrink-0 items-center justify-between border-b border-slate-800 bg-[#0d1322] px-3 text-xs select-none"
        >
            <div class="flex items-center gap-1">
                <button
                    class="terminal-tab flex items-center gap-1.5 rounded-t px-3 py-1 font-semibold transition"
                    :class="
                        activeTab === 'shell'
                            ? 'relative z-10 -mb-px border border-b-0 border-slate-700 bg-[#080c14] text-slate-200'
                            : 'border border-transparent bg-transparent text-slate-500 hover:text-slate-300'
                    "
                    @click="activeTab = 'shell'"
                >
                    <i
                        class="fa-solid fa-terminal text-[10px] text-cyan-400"
                    ></i>
                    <span>Terminal</span>
                </button>
                <button
                    class="terminal-tab flex items-center gap-1.5 rounded-t px-3 py-1 transition"
                    :class="
                        activeTab === 'kernel'
                            ? 'relative z-10 -mb-px border border-b-0 border-slate-700 bg-[#080c14] text-slate-200'
                            : 'border border-transparent bg-transparent text-slate-500 hover:text-slate-300'
                    "
                    @click="activeTab = 'kernel'"
                >
                    <i
                        class="fa-solid fa-microchip text-[10px] text-indigo-400"
                    ></i>
                    <span>Kernel</span>
                </button>

                <span
                    class="ml-2 rounded border border-indigo-500/30 bg-indigo-500/20 px-2 py-0.5 font-mono text-[10px] text-indigo-300"
                >
                    {{ kernelBusy ? 'Program running' : 'System Kernel Ready' }}
                </span>
                <span
                    v-if="execTime !== null"
                    class="font-mono text-[10px] text-slate-500"
                    >{{ execTime }}ms</span
                >
            </div>
            <div class="flex items-center gap-2 text-[11px]">
                <button
                    v-if="pythonRunId"
                    class="rounded px-2 py-0.5 text-rose-400 hover:bg-slate-800"
                    @click="stopPython"
                >
                    <i class="fa-solid fa-stop mr-1"></i> Stop
                </button>
                <button
                    class="rounded px-2 py-0.5 text-slate-400 transition hover:bg-slate-800 hover:text-slate-200"
                    @click="clearActive"
                >
                    <i class="fa-solid fa-trash-can mr-1 text-[10px]"></i> Clear
                </button>
                <button
                    class="rounded px-2 py-0.5 text-slate-400 transition hover:bg-slate-800 hover:text-slate-200"
                    @click="isMinimized = !isMinimized"
                >
                    <i
                        class="fa-solid text-[10px]"
                        :class="
                            isMinimized ? 'fa-chevron-up' : 'fa-chevron-down'
                        "
                    ></i>
                </button>
            </div>
        </div>

        <!-- Output body -->
        <div
            v-show="!isMinimized"
            ref="outputRef"
            class="flex-1 space-y-1 overflow-y-auto bg-[#060910] p-3 font-mono text-[11px] leading-5 text-slate-300 select-text"
        >
            <div v-for="log in visibleLogs" :key="log.id">
                <div
                    v-if="log.type === 'system'"
                    class="flex items-center gap-2 text-slate-500"
                >
                    <span class="font-bold text-cyan-400">{{
                        log.prompt
                    }}</span>
                    <span>{{ log.text }}</span>
                </div>
                <div
                    v-else-if="log.type === 'command'"
                    class="mt-1 flex items-center gap-2 font-semibold text-slate-200"
                >
                    <span class="font-bold text-cyan-400">{{
                        log.prompt
                    }}</span>
                    <span>{{ log.text }}</span>
                </div>
                <pre
                    v-else-if="log.type === 'output'"
                    class="pl-4 whitespace-pre-wrap text-slate-300"
                    >{{ log.text }}</pre>
                <pre
                    v-else-if="log.type === 'error'"
                    class="pl-4 whitespace-pre-wrap text-rose-400"
                    >{{ log.text }}</pre>
                <div
                    v-else-if="log.type === 'meta'"
                    class="pl-4 text-[10px] text-slate-500"
                >
                    {{ log.text }}
                </div>
            </div>
            <div
                v-if="isExecuting"
                class="flex animate-pulse items-center gap-2 pl-4 text-indigo-400"
            >
                <i class="fa-solid fa-circle-notch fa-spin text-xs"></i>
                <span>Executing command...</span>
            </div>
        </div>

        <form
            v-if="!isMinimized && activeTab === 'kernel' && pythonRunId"
            class="flex shrink-0 items-center gap-2 border-t border-slate-800/70 bg-[#0a0f1a] px-3 py-2 font-mono text-xs"
            @submit.prevent="sendProgramInput"
        >
            <label for="python-stdin" class="whitespace-nowrap text-indigo-300"
                >Program input ›</label
            >
            <input
                id="python-stdin"
                ref="programInputRef"
                v-model="programInput"
                class="min-w-0 flex-1 bg-transparent text-slate-200 placeholder-slate-500 outline-none"
                placeholder="Type an answer and press Enter (Ctrl+C to stop)"
                autocomplete="off"
                :disabled="sendingInput"
                @keydown.ctrl.c.prevent="stopPython"
            />
            <button
                type="submit"
                :disabled="sendingInput"
                class="text-indigo-300 disabled:opacity-50"
            >
                Send
            </button>
        </form>

        <!-- Shell commands are separate from the running Python program's stdin. -->
        <div
            v-show="!isMinimized && activeTab === 'shell'"
            class="flex shrink-0 items-center gap-2 border-t border-slate-800/70 bg-[#0a0f1a] px-3 py-1.5 font-mono text-[11px]"
        >
            <span
                class="font-bold whitespace-nowrap text-cyan-400 select-none"
                >{{ promptCwd }}</span
            >
            <input
                ref="inputRef"
                v-model="commandInput"
                type="text"
                class="flex-1 border-none bg-transparent font-mono text-[11px] text-slate-200 placeholder-slate-600 caret-cyan-400 outline-none"
                :placeholder="`Type command here... (e.g. ${examples})`"
                autocomplete="off"
                spellcheck="false"
                :disabled="isExecuting"
                @keydown="onKeydown"
            />
        </div>
    </div>
</template>
