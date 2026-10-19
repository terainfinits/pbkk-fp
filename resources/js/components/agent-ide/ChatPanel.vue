<script setup lang="ts">
import { inject, ref, computed } from 'vue';
import type { IdeApi } from '@/composables/useIdeApi';
import type { useIdeState } from '@/composables/useIdeState';

const { api, state } = inject<{
    api: IdeApi;
    state: ReturnType<typeof useIdeState>;
}>('ide')!;

/* ─── Provider / model registry ─── */
const providerModels: Record<string, { id: string; name: string }[]> = {
    gemini: [
        { id: 'gemini-3.5-flash-lite', name: 'Gemini 3.5 Flash Lite' },
        { id: 'gemini-3.6-flash', name: 'Gemini 3.6 Flash' },
        { id: 'gemini-3.1-pro', name: 'Gemini 3.1 Pro' },
    ],
    claude: [
        { id: 'claude-3-7-sonnet-20250219', name: 'Claude 3.7 Sonnet' },
        { id: 'claude-3-5-sonnet-20241022', name: 'Claude 3.5 Sonnet' },
        { id: 'claude-3-5-haiku-20241022', name: 'Claude 3.5 Haiku' },
    ],
    gpt: [{ id: 'gpt-oss 120b', name: 'gpt-oss 120b' }],
    kimi: [
        { id: 'moonshot-v1-8k', name: 'Kimi (Moonshot v1 8k)' },
        { id: 'moonshot-v1-32k', name: 'Kimi (Moonshot v1 32k)' },
        { id: 'moonshot-v1-128k', name: 'Kimi (Moonshot v1 128k)' },
    ],
    deepseek: [
        { id: 'deepseek-chat', name: 'DeepSeek V3' },
        { id: 'deepseek-reasoner', name: 'DeepSeek R1' },
    ],
    ollama_cloud: [
        { id: 'gpt-oss:120b', name: 'gpt-oss:120b' },
        { id: 'gemma4:31b', name: 'gemma4:31b' },
        { id: 'llama3.3', name: 'llama3.3' },
        { id: 'qwen2.5-coder', name: 'qwen2.5-coder' },
        { id: 'deepseek-r1', name: 'deepseek-r1' },
    ],
    senopati: [{ id: 'qwen3:14b', name: 'Qwen3 14B' }],
};

const provider = ref('ollama_cloud');
const model = ref('gpt-oss:120b');
const prompt = ref('');
const isThinking = ref(false);

const models = computed(() => providerModels[provider.value] || []);

function onProviderChange() {
    model.value = models.value[0]?.id || '';
}

/* ─── Chat messages ─── */
interface ChatMessage {
    id: number;
    role: 'user' | 'assistant';
    content: string;
    raw?: string;
    code?: string;
    language?: string;
    targetPath?: string;
    steps?: { title: string; detail: string }[];
    loading?: boolean;
    error?: string;
}
const messages = ref<ChatMessage[]>([]);
let msgSeq = 0;

const promptPills = [
    {
        label: '🐍 Python Script',
        text: 'Write a Python script for Fibonacci calculation and prime numbers up to 50',
    },
    {
        label: '⚡ Laravel CRUD',
        text: 'Create a Laravel UserController with full CRUD and validation rules',
    },
    {
        label: '🟢 Vue 3 Component',
        text: 'Create a modern Vue 3 component for Agent Dashboard with Tailwind and reactive metrics',
    },
    {
        label: '🧪 Pest Tests',
        text: 'Generate a complete Pest test file for testing the AgentIdeController endpoints',
    },
];

/* ─── API key from localStorage ─── */
function getApiKey(): string {
    return state.apiKeys[provider.value]?.trim() || '';
}

async function submit() {
    const text = prompt.value.trim();
    if (!text || isThinking.value) return;

    const userMsg: ChatMessage = { id: ++msgSeq, role: 'user', content: text };
    messages.value.push(userMsg);
    const botMsg: ChatMessage = {
        id: ++msgSeq,
        role: 'assistant',
        content: '',
        loading: true,
    };
    messages.value.push(botMsg);

    prompt.value = '';
    isThinking.value = true;
    state.agentStatusLabel.value = 'AI Chatbot Synthesizing...';

    try {
        const activeTab = state.openTabs.find(
            (t) => t.path === state.activeTabPath.value,
        );
        const data = await api.agentPrompt({
            provider: provider.value,
            model: model.value,
            apiKey: getApiKey(),
            prompt: text,
            targetDirectory: state.targetDirectory.value,
            targetFile: activeTab?.path || '',
            currentCode: activeTab?.content || '',
        });

        botMsg.loading = false;

        if (data.success) {
            botMsg.raw = data.raw;
            botMsg.code = data.code;
            botMsg.language = data.language;
            botMsg.targetPath = data.targetPath;
            botMsg.steps = data.steps || [];
            botMsg.content = data.explanation || '';
            state.lastGeneratedCode.value = data.code;
            state.lastGeneratedTarget.value = data.targetPath;
            state.pushToast('Agentic Chatbot response completed.');
        } else {
            botMsg.error = data.error || 'Failed to process prompt.';
        }
    } catch (err: any) {
        botMsg.loading = false;
        botMsg.error =
            err?.message || 'Network error connecting to Chatbot engine.';
    } finally {
        isThinking.value = false;
        state.agentStatusLabel.value = 'Agent Engine Ready';
    }
}

async function runInKernel(msg: ChatMessage) {
    if (!msg.code) return;
    window.dispatchEvent(
        new CustomEvent('ide:kernel-run', {
            detail: {
                code: msg.code,
                language: msg.language || 'php',
                path: msg.targetPath || '',
            },
        }),
    );
}

async function applyToFile(msg: ChatMessage) {
    if (!msg.targetPath || !msg.code) {
        state.pushToast('No target path specified', true);
        return;
    }
    const data = await api.saveFile(msg.targetPath, msg.code);
    if (data.success) {
        state.pushToast(`Successfully wrote code to ${msg.targetPath}!`);
        window.dispatchEvent(new CustomEvent('ide:refresh-tree'));
    } else {
        state.pushToast('Failed to write file: ' + data.error, true);
    }
}

async function openDiff(msg: ChatMessage) {
    if (!msg.code) {
        state.pushToast('No generated code to diff.', true);
        return;
    }

    const target = msg.targetPath || '';
    let originalContent = '';
    let isNewFile = false;

    if (target) {
        const targetTab = state.openTabs.find((t) => t.path === target);

        if (targetTab) {
            originalContent = targetTab.content;
        } else {
            try {
                state.agentStatusLabel.value = 'Fetching file for diff...';

                const data = await api.readFile(target);

                if (data.success) {
                    originalContent = data.content || '';
                } else {
                    isNewFile = true;
                    originalContent = '';
                }
            } catch (err: any) {
                console.warn(
                    `File ${target} does not exist yet. Treating as new file.`,
                );
                isNewFile = true;
                originalContent = '';
            } finally {
                state.agentStatusLabel.value = 'Agent Engine Ready';
            }
        }
    } else {
        const activeTab = state.openTabs.find(
            (t) => t.path === state.activeTabPath.value,
        );
        originalContent = activeTab?.content || '';
    }

    const ext = target.split('.').pop()?.toLowerCase() || '';
    const languageMap: Record<string, string> = {
        js: 'javascript',
        ts: 'typescript',
        py: 'python',
        md: 'markdown',
        yml: 'yaml',
        sh: 'shell',
    };
    const resolvedLanguage =
        msg.language || languageMap[ext] || ext || 'plaintext';

    window.dispatchEvent(
        new CustomEvent('ide:open-diff', {
            detail: {
                original: originalContent,
                modified: msg.code,
                language: resolvedLanguage,
                targetPath: target,
                isNewFile: isNewFile,
            },
        }),
    );
}

function copyCode(msg: ChatMessage) {
    if (msg.code) {
        navigator.clipboard.writeText(msg.code);
        state.pushToast('Code copied to clipboard!');
    }
}
</script>

<template>
    <aside
        class="flex w-96 shrink-0 flex-col border-l border-slate-800 bg-[#0e1424]"
    >
        <!-- Header -->
        <div class="space-y-2.5 border-b border-slate-800 bg-[#0d1322] p-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div
                        class="h-2.5 w-2.5 animate-pulse rounded-full bg-gradient-to-r from-purple-500 to-pink-500"
                    ></div>
                    <span
                        class="text-xs font-bold tracking-wider text-slate-200 uppercase"
                        >Magentic Chatbot</span
                    >
                </div>
                <span
                    class="rounded-full border border-purple-500/30 bg-purple-500/20 px-2 py-0.5 text-[10px] font-semibold text-purple-300"
                >
                    {{ models.find((m) => m.id === model)?.name || model }}
                </span>
            </div>

            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label
                        class="mb-1 block text-[10px] font-medium text-slate-400"
                        >Provider</label
                    >
                    <select
                        v-model="provider"
                        class="w-full rounded-md border border-slate-700 bg-slate-900 px-2 py-1.5 text-xs text-slate-200 transition outline-none focus:border-indigo-500"
                        @change="onProviderChange"
                    >
                        <option value="gemini">Google Gemini</option>
                        <option value="claude">Anthropic Claude</option>
                        <option value="gpt">OpenAI (GPT)</option>
                        <option value="kimi">Moonshot Kimi</option>
                        <option value="deepseek">DeepSeek AI</option>
                        <option value="ollama_cloud">Ollama Cloud</option>
                        <option value="senopati">Senopati</option>
                    </select>
                </div>
                <div>
                    <label
                        class="mb-1 block text-[10px] font-medium text-slate-400"
                        >Model</label
                    >
                    <select
                        v-model="model"
                        class="w-full rounded-md border border-slate-700 bg-slate-900 px-2 py-1.5 text-xs text-slate-200 transition outline-none focus:border-indigo-500"
                    >
                        <option v-for="m in models" :key="m.id" :value="m.id">
                            {{ m.name }}
                        </option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Chat stream -->
        <div class="flex-1 space-y-4 overflow-y-auto p-3 text-xs">
            <!-- Welcome -->
            <div
                class="space-y-2.5 rounded-xl border border-indigo-500/30 bg-slate-900/90 p-3.5"
            >
                <div class="flex items-center justify-between">
                    <div
                        class="flex items-center gap-2 text-xs font-semibold text-indigo-300"
                    >
                        <i class="fa-solid fa-robot text-indigo-400"></i>
                        <span>Magentic AI Assistant</span>
                    </div>
                    <span
                        class="rounded border border-emerald-500/20 bg-emerald-500/10 px-1.5 py-0.5 font-mono text-[10px] text-emerald-400"
                        >Kernel Ready</span
                    >
                </div>
                <p class="text-[11px] leading-relaxed text-slate-300">
                    Welcome to Magentic Chatbot! Ask me to write code in Python,
                    PHP, or Node.js. I can run code directly in live system
                    kernels and apply patches to your workspace.
                </p>
                <div
                    class="space-y-1 rounded border border-slate-800 bg-slate-950/90 p-2 font-mono text-[10px] text-slate-400"
                >
                    <div>
                        🎯 <span class="text-slate-300">Target Folder:</span>
                        <span class="text-indigo-400">{{
                            state.targetDirectory.value
                                ? '/' + state.targetDirectory.value
                                : '/'
                        }}</span>
                    </div>
                    <div>
                        📄 <span class="text-slate-300">Active File:</span>
                        <span class="text-amber-400">{{
                            state.openTabs.find(
                                (t) => t.path === state.activeTabPath.value,
                            )?.filename || 'None'
                        }}</span>
                    </div>
                </div>
            </div>

            <!-- Prompt pills -->
            <div class="space-y-1.5">
                <span
                    class="text-[10px] font-semibold tracking-wider text-slate-500 uppercase"
                    >Quick Prompt Templates</span
                >
                <div class="flex flex-wrap gap-1.5">
                    <button
                        v-for="p in promptPills"
                        :key="p.label"
                        class="rounded border border-slate-800 bg-slate-900 px-2.5 py-1 text-[11px] text-slate-300 transition hover:border-indigo-500/40 hover:bg-indigo-950/40 hover:text-indigo-200"
                        @click="prompt = p.text"
                    >
                        {{ p.label }}
                    </button>
                </div>
            </div>

            <!-- Messages -->
            <div class="space-y-4">
                <div v-for="msg in messages" :key="msg.id">
                    <!-- User -->
                    <div v-if="msg.role === 'user'" class="flex justify-end">
                        <div
                            class="max-w-[85%] space-y-1 rounded-2xl rounded-tr-none bg-indigo-600 p-3 text-white shadow-lg"
                        >
                            <div
                                class="mb-1 flex items-center justify-between border-b border-indigo-500/40 pb-1 font-mono text-[10px] text-indigo-200"
                            >
                                <span class="font-bold"
                                    ><i class="fa-solid fa-user mr-1"></i>
                                    You</span
                                >
                            </div>
                            <p
                                class="text-xs leading-relaxed whitespace-pre-wrap"
                            >
                                {{ msg.content }}
                            </p>
                        </div>
                    </div>

                    <!-- Assistant -->
                    <div v-else class="flex justify-start">
                        <div
                            class="w-full max-w-[95%] space-y-3 rounded-2xl rounded-tl-none border border-purple-500/30 bg-slate-900 p-4 shadow-xl"
                        >
                            <div
                                class="flex items-center justify-between border-b border-slate-800 pb-2"
                            >
                                <div class="flex items-center gap-2">
                                    <div
                                        class="flex h-6 w-6 items-center justify-center rounded-md bg-gradient-to-tr from-purple-600 to-indigo-600 text-[11px] text-white shadow"
                                    >
                                        <i class="fa-solid fa-robot"></i>
                                    </div>
                                    <span
                                        class="text-xs font-bold text-slate-100"
                                        >{{
                                            provider.toUpperCase()
                                        }}
                                        Assistant</span
                                    >
                                </div>
                                <span
                                    class="rounded border border-purple-500/20 bg-purple-500/10 px-2 py-0.5 font-mono text-[10px] text-purple-300"
                                    >{{ model }}</span
                                >
                            </div>

                            <div
                                v-if="msg.loading"
                                class="flex items-center gap-2 py-2 text-xs text-slate-400"
                            >
                                <i
                                    class="fa-solid fa-circle-notch fa-spin text-indigo-400"
                                ></i>
                                <span
                                    >Analyzing prompt context & generating
                                    response...</span
                                >
                            </div>

                            <div
                                v-else-if="msg.error"
                                class="flex items-start gap-2 rounded border border-red-500/30 bg-red-950/50 p-3 text-xs text-red-300"
                            >
                                <i
                                    class="fa-solid fa-circle-exclamation mt-0.5 text-red-400"
                                ></i>
                                <div>
                                    <div class="font-bold">Chatbot Error</div>
                                    <div>{{ msg.error }}</div>
                                </div>
                            </div>

                            <template v-else>
                                <!-- Reasoning steps -->
                                <details
                                    v-if="msg.steps?.length"
                                    class="group rounded-lg border border-slate-800 bg-slate-950/80 p-2.5 transition"
                                >
                                    <summary
                                        class="flex cursor-pointer items-center justify-between text-[11px] font-semibold text-purple-300 select-none"
                                    >
                                        <span class="flex items-center gap-1.5">
                                            <i
                                                class="fa-solid fa-brain text-purple-400"
                                            ></i>
                                            Agent Reasoning Steps ({{
                                                msg.steps.length
                                            }})
                                        </span>
                                        <i
                                            class="fa-solid fa-chevron-down text-[10px] transition group-open:rotate-180"
                                        ></i>
                                    </summary>
                                    <div
                                        class="mt-2 space-y-2 border-t border-slate-800/80 pt-2"
                                    >
                                        <div
                                            v-for="(st, i) in msg.steps"
                                            :key="i"
                                            class="flex items-start gap-2 text-[11px] text-slate-300"
                                        >
                                            <i
                                                class="fa-solid fa-check-circle mt-0.5 text-emerald-400"
                                            ></i>
                                            <div>
                                                <span
                                                    class="font-semibold text-slate-200"
                                                    >{{ st.title }}:</span
                                                >
                                                <span class="text-slate-400">{{
                                                    st.detail
                                                }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </details>

                                <!-- Markdown body -->
                                <div
                                    v-if="msg.raw"
                                    class="break-words whitespace-pre-wrap"
                                >
                                    {{ msg.raw }}
                                </div>

                                <!-- Code card -->
                                <div
                                    v-if="msg.code"
                                    class="overflow-hidden rounded-xl border border-slate-800 bg-[#060910] shadow-md"
                                >
                                    <div
                                        class="flex items-center justify-between border-b border-slate-800 bg-[#0e1424] px-3 py-2 text-[11px]"
                                    >
                                        <div
                                            class="flex items-center gap-2 font-mono text-indigo-300"
                                        >
                                            <i
                                                class="fa-regular fa-file-code"
                                            ></i>
                                            <span class="font-semibold">{{
                                                msg.targetPath ||
                                                'snippet.' +
                                                    (msg.language || 'code')
                                            }}</span>
                                        </div>
                                        <span
                                            class="rounded bg-slate-800 px-1.5 py-0.5 font-mono text-[10px] text-slate-400 uppercase"
                                            >{{ msg.language }}</span
                                        >
                                    </div>
                                    <pre
                                        class="max-h-48 overflow-y-auto bg-[#090d16] p-3 font-mono text-[11px] leading-5 text-slate-200"
                                    ><code>{{ msg.code }}</code></pre>
                                    <div
                                        class="flex flex-wrap items-center gap-2 border-t border-slate-800/80 bg-[#0c101c] p-2"
                                    >
                                        <button
                                            class="flex items-center gap-1.5 rounded bg-gradient-to-r from-emerald-600 to-teal-600 px-3 py-1.5 text-[11px] font-semibold text-white shadow transition hover:from-emerald-500 hover:to-teal-500"
                                            @click="runInKernel(msg)"
                                        >
                                            <i class="fa-solid fa-play"></i> Run
                                            Code in Kernel
                                        </button>
                                        <button
                                            class="flex items-center gap-1.5 rounded bg-indigo-600 px-3 py-1.5 text-[11px] font-semibold text-white shadow transition hover:bg-indigo-500"
                                            @click="applyToFile(msg)"
                                        >
                                            <i class="fa-solid fa-bolt"></i>
                                            Apply to File
                                        </button>
                                        <button
                                            class="rounded border border-purple-500/30 bg-purple-600/20 px-2.5 py-1.5 text-[11px] font-medium text-purple-300 transition hover:bg-purple-600/30"
                                            @click="openDiff(msg)"
                                        >
                                            <i
                                                class="fa-solid fa-code-compare mr-1"
                                            ></i>
                                            Diff
                                        </button>
                                        <button
                                            class="rounded bg-slate-800 px-2.5 py-1.5 text-[11px] text-slate-300 transition hover:bg-slate-700"
                                            @click="copyCode(msg)"
                                        >
                                            <i class="fa-regular fa-copy"></i>
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Prompt input -->
        <div class="space-y-2 border-t border-slate-800 bg-[#0b0f1a] p-3">
            <textarea
                v-model="prompt"
                rows="3"
                class="w-full resize-none rounded-lg border border-slate-700/80 bg-slate-900 p-2.5 text-xs text-slate-200 placeholder-slate-500 transition outline-none focus:border-indigo-500"
                placeholder="Chat with AI..."
                @keydown.enter.exact.prevent="submit"
            ></textarea>
            <div class="flex items-center justify-between">
                <span class="font-mono text-[10px] text-slate-500"
                    >Shift+Enter for new line</span
                >
                <button
                    :disabled="isThinking"
                    class="flex items-center gap-1.5 rounded-lg bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 px-4 py-2 text-xs font-semibold text-white shadow-lg shadow-indigo-500/20 transition hover:from-blue-500 hover:to-purple-500 disabled:opacity-50"
                    @click="submit"
                >
                    <i
                        class="fa-solid"
                        :class="
                            isThinking ? 'fa-spinner fa-spin' : 'fa-paper-plane'
                        "
                    ></i>
                    <span>{{
                        isThinking ? 'Thinking...' : 'Send Message'
                    }}</span>
                </button>
            </div>
        </div>
    </aside>
</template>
