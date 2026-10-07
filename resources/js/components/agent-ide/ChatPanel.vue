<script setup lang="ts">
import { inject, onMounted, ref, computed } from 'vue';
import type { IdeApi } from '@/composables/useIdeApi';
import type { useIdeState } from '@/composables/useIdeState';

const { api, state } = inject<{ api: IdeApi; state: ReturnType<typeof useIdeState> }>('ide')!;

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
  { label: '🐍 Python Script', text: 'Write a Python script for Fibonacci calculation and prime numbers up to 50' },
  { label: '⚡ Laravel CRUD', text: 'Create a Laravel UserController with full CRUD and validation rules' },
  { label: '🟢 Vue 3 Component', text: 'Create a modern Vue 3 component for Agent Dashboard with Tailwind and reactive metrics' },
  { label: '🧪 Pest Tests', text: 'Generate a complete Pest test file for testing the AgentIdeController endpoints' },
];

/* ─── API key from localStorage ─── */
function getApiKey(): string {
  const p = provider.value;
  let key = localStorage.getItem('agent_key_' + p) || '';
  if (!key && (p === 'ollama_cloud' || p === 'ollama')) {
    key = localStorage.getItem('agent_key_ollama_cloud') || localStorage.getItem('agent_key_ollama') || '';
  }
  return key;
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
    const activeTab = state.openTabs.find(t => t.path === state.activeTabPath.value);
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
    botMsg.error = err?.message || 'Network error connecting to Chatbot engine.';
  } finally {
    isThinking.value = false;
    state.agentStatusLabel.value = 'Agent Engine Ready';
  }
}

async function runInKernel(msg: ChatMessage) {
  if (!msg.code) return;
  window.dispatchEvent(
    new CustomEvent('ide:kernel-run', {
      detail: { code: msg.code, language: msg.language || 'php', path: msg.targetPath || '' },
    })
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

function openDiff(msg: ChatMessage) {
  window.dispatchEvent(new CustomEvent('ide:open-diff', { detail: msg.code || '' }));
}

function copyCode(msg: ChatMessage) {
  if (msg.code) {
    navigator.clipboard.writeText(msg.code);
    state.pushToast('Code copied to clipboard!');
  }
}

function renderMarkdown(raw?: string): string {
  if (!raw) return '';
  const marked = (window as any).marked;
  if (marked) {
    try {
      return marked.parse(raw);
    } catch {
      /* fall through */
    }
  }
  return raw;
}

onMounted(() => {
  // Load saved keys on mount (in case SettingsModal was used before)
});
</script>

<template>
  <aside class="w-96 bg-[#0e1424] border-l border-slate-800 flex flex-col shrink-0">
    <!-- Header -->
    <div class="p-3 border-b border-slate-800 bg-[#0d1322] space-y-2.5">
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
          <div class="w-2.5 h-2.5 rounded-full bg-gradient-to-r from-purple-500 to-pink-500 animate-pulse"></div>
          <span class="text-xs font-bold uppercase tracking-wider text-slate-200">Magentic Chatbot</span>
        </div>
        <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-purple-500/20 text-purple-300 border border-purple-500/30">
          {{ models.find(m => m.id === model)?.name || model }}
        </span>
      </div>

      <div class="grid grid-cols-2 gap-2">
        <div>
          <label class="block text-[10px] font-medium text-slate-400 mb-1">Provider</label>
          <select
            v-model="provider"
            class="w-full bg-slate-900 border border-slate-700 text-slate-200 text-xs rounded-md px-2 py-1.5 outline-none focus:border-indigo-500 transition"
            @change="onProviderChange"
          >
            <option value="gemini">Google Gemini</option>
            <option value="claude">Anthropic Claude</option>
            <option value="gpt">OpenAI (GPT)</option>
            <option value="kimi">Moonshot Kimi</option>
            <option value="deepseek">DeepSeek AI</option>
            <option value="ollama_cloud">Ollama Cloud</option>
          </select>
        </div>
        <div>
          <label class="block text-[10px] font-medium text-slate-400 mb-1">Model</label>
          <select
            v-model="model"
            class="w-full bg-slate-900 border border-slate-700 text-slate-200 text-xs rounded-md px-2 py-1.5 outline-none focus:border-indigo-500 transition"
          >
            <option v-for="m in models" :key="m.id" :value="m.id">{{ m.name }}</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Chat stream -->
    <div class="flex-1 overflow-y-auto p-3 space-y-4 text-xs">
      <!-- Welcome -->
      <div class="p-3.5 rounded-xl bg-slate-900/90 border border-indigo-500/30 space-y-2.5">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-2 text-indigo-300 font-semibold text-xs">
            <i class="fa-solid fa-robot text-indigo-400"></i>
            <span>Magentic AI Assistant</span>
          </div>
          <span class="text-[10px] bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 px-1.5 py-0.5 rounded font-mono">Kernel Ready</span>
        </div>
        <p class="text-slate-300 text-[11px] leading-relaxed">
          Welcome to Magentic Chatbot! Ask me to write code in Python, PHP, or Node.js. I can run code directly in live system kernels and apply patches to your workspace.
        </p>
        <div class="p-2 bg-slate-950/90 rounded border border-slate-800 text-[10px] text-slate-400 space-y-1 font-mono">
          <div>🎯 <span class="text-slate-300">Target Folder:</span> <span class="text-indigo-400">{{ state.targetDirectory.value ? '/' + state.targetDirectory.value : '/' }}</span></div>
          <div>📄 <span class="text-slate-300">Active File:</span> <span class="text-amber-400">{{ state.openTabs.find(t => t.path === state.activeTabPath.value)?.filename || 'None' }}</span></div>
        </div>
      </div>

      <!-- Prompt pills -->
      <div class="space-y-1.5">
        <span class="text-[10px] uppercase font-semibold tracking-wider text-slate-500">Quick Prompt Templates</span>
        <div class="flex flex-wrap gap-1.5">
          <button
            v-for="p in promptPills"
            :key="p.label"
            class="text-[11px] px-2.5 py-1 rounded bg-slate-900 hover:bg-indigo-950/40 text-slate-300 hover:text-indigo-200 border border-slate-800 hover:border-indigo-500/40 transition"
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
            <div class="max-w-[85%] p-3 rounded-2xl rounded-tr-none bg-indigo-600 text-white shadow-lg space-y-1">
              <div class="flex items-center justify-between text-[10px] text-indigo-200 border-b border-indigo-500/40 pb-1 mb-1 font-mono">
                <span class="font-bold"><i class="fa-solid fa-user mr-1"></i> You</span>
              </div>
              <p class="text-xs leading-relaxed whitespace-pre-wrap">{{ msg.content }}</p>
            </div>
          </div>

          <!-- Assistant -->
          <div v-else class="flex justify-start">
            <div class="max-w-[95%] w-full p-4 rounded-2xl rounded-tl-none bg-slate-900 border border-purple-500/30 shadow-xl space-y-3">
              <div class="flex items-center justify-between border-b border-slate-800 pb-2">
                <div class="flex items-center gap-2">
                  <div class="w-6 h-6 rounded-md bg-gradient-to-tr from-purple-600 to-indigo-600 flex items-center justify-center text-white text-[11px] shadow">
                    <i class="fa-solid fa-robot"></i>
                  </div>
                  <span class="font-bold text-slate-100 text-xs">{{ provider.toUpperCase() }} Assistant</span>
                </div>
                <span class="text-[10px] font-mono px-2 py-0.5 rounded bg-purple-500/10 text-purple-300 border border-purple-500/20">{{ model }}</span>
              </div>

              <div v-if="msg.loading" class="flex items-center gap-2 text-slate-400 text-xs py-2">
                <i class="fa-solid fa-circle-notch fa-spin text-indigo-400"></i>
                <span>Analyzing prompt context & generating response...</span>
              </div>

              <div v-else-if="msg.error" class="p-3 rounded bg-red-950/50 border border-red-500/30 text-red-300 text-xs flex items-start gap-2">
                <i class="fa-solid fa-circle-exclamation text-red-400 mt-0.5"></i>
                <div>
                  <div class="font-bold">Chatbot Error</div>
                  <div>{{ msg.error }}</div>
                </div>
              </div>

              <template v-else>
                <!-- Reasoning steps -->
                <details v-if="msg.steps?.length" class="group bg-slate-950/80 rounded-lg border border-slate-800 p-2.5 transition">
                  <summary class="flex items-center justify-between text-[11px] font-semibold text-purple-300 cursor-pointer select-none">
                    <span class="flex items-center gap-1.5">
                      <i class="fa-solid fa-brain text-purple-400"></i> Agent Reasoning Steps ({{ msg.steps.length }})
                    </span>
                    <i class="fa-solid fa-chevron-down text-[10px] group-open:rotate-180 transition"></i>
                  </summary>
                  <div class="mt-2 space-y-2 pt-2 border-t border-slate-800/80">
                    <div v-for="(st, i) in msg.steps" :key="i" class="flex items-start gap-2 text-[11px] text-slate-300">
                      <i class="fa-solid fa-check-circle text-emerald-400 mt-0.5"></i>
                      <div>
                        <span class="font-semibold text-slate-200">{{ st.title }}:</span>
                        <span class="text-slate-400">{{ st.detail }}</span>
                      </div>
                    </div>
                  </div>
                </details>

                <!-- Markdown body -->
                <div v-if="msg.raw" v-html="renderMarkdown(msg.raw)"></div>

                <!-- Code card -->
                <div v-if="msg.code" class="rounded-xl bg-[#060910] border border-slate-800 overflow-hidden shadow-md">
                  <div class="bg-[#0e1424] px-3 py-2 flex items-center justify-between border-b border-slate-800 text-[11px]">
                    <div class="flex items-center gap-2 font-mono text-indigo-300">
                      <i class="fa-regular fa-file-code"></i>
                      <span class="font-semibold">{{ msg.targetPath || 'snippet.' + (msg.language || 'code') }}</span>
                    </div>
                    <span class="px-1.5 py-0.5 rounded bg-slate-800 text-slate-400 text-[10px] font-mono uppercase">{{ msg.language }}</span>
                  </div>
                  <pre class="max-h-48 overflow-y-auto p-3 font-mono text-[11px] text-slate-200 leading-5 bg-[#090d16]"><code>{{ msg.code }}</code></pre>
                  <div class="p-2 bg-[#0c101c] border-t border-slate-800/80 flex flex-wrap items-center gap-2">
                    <button class="px-3 py-1.5 rounded bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-semibold text-[11px] flex items-center gap-1.5 shadow transition" @click="runInKernel(msg)">
                      <i class="fa-solid fa-play"></i> Run Code in Kernel
                    </button>
                    <button class="px-3 py-1.5 rounded bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-[11px] flex items-center gap-1.5 shadow transition" @click="applyToFile(msg)">
                      <i class="fa-solid fa-bolt"></i> Apply to File
                    </button>
                    <button class="px-2.5 py-1.5 rounded bg-purple-600/20 text-purple-300 border border-purple-500/30 hover:bg-purple-600/30 font-medium text-[11px] transition" @click="openDiff(msg)">
                      <i class="fa-solid fa-code-compare mr-1"></i> Diff
                    </button>
                    <button class="px-2.5 py-1.5 rounded bg-slate-800 hover:bg-slate-700 text-slate-300 text-[11px] transition" @click="copyCode(msg)">
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
    <div class="p-3 border-t border-slate-800 bg-[#0b0f1a] space-y-2">
      <textarea
        v-model="prompt"
        rows="3"
        class="w-full bg-slate-900 border border-slate-700/80 rounded-lg p-2.5 text-xs text-slate-200 placeholder-slate-500 outline-none focus:border-indigo-500 transition resize-none"
        placeholder="Chat with AI..."
        @keydown.enter.exact.prevent="submit"
      ></textarea>
      <div class="flex items-center justify-between">
        <span class="text-[10px] text-slate-500 font-mono">Shift+Enter for new line</span>
        <button
          :disabled="isThinking"
          class="px-4 py-2 bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 hover:from-blue-500 hover:to-purple-500 disabled:opacity-50 text-white font-semibold text-xs rounded-lg shadow-lg shadow-indigo-500/20 flex items-center gap-1.5 transition"
          @click="submit"
        >
          <i class="fa-solid" :class="isThinking ? 'fa-spinner fa-spin' : 'fa-paper-plane'"></i>
          <span>{{ isThinking ? 'Thinking...' : 'Send Message' }}</span>
        </button>
      </div>
    </div>
  </aside>
</template>
