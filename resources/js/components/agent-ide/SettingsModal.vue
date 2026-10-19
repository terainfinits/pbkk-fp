<script setup lang="ts">
import { inject } from 'vue';
import type { useIdeState } from '@/composables/useIdeState';

defineProps<{ isOpen: boolean }>();
const emit = defineEmits<{ close: [] }>();

const { state } = inject<{ state: ReturnType<typeof useIdeState> }>('ide')!;

const fields = [
    { key: 'gemini', label: 'Google Gemini API Key', placeholder: 'AIzaSy...' },
    {
        key: 'claude',
        label: 'Anthropic Claude API Key',
        placeholder: 'sk-ant-...',
    },
    { key: 'gpt', label: 'OpenAI API Key (GPT)', placeholder: 'sk-...' },
    { key: 'kimi', label: 'Moonshot Kimi API Key', placeholder: 'sk-...' },
    { key: 'deepseek', label: 'DeepSeek API Key', placeholder: 'sk-...' },
    {
        key: 'ollama_cloud',
        label: 'Ollama Cloud API Key',
        placeholder: 'fe...',
    },
    { key: 'senopati', label: 'Senopati API Key', placeholder: 'sk-...' },
];

function save() {
    state.pushToast('API Keys saved successfully!');
    emit('close');
}
</script>

<template>
    <div
        v-if="isOpen"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4 backdrop-blur-sm"
    >
        <div
            class="w-full max-w-md space-y-5 rounded-2xl border border-slate-700 bg-[#0f172a] p-6 shadow-2xl"
        >
            <div
                class="flex items-center justify-between border-b border-slate-800 pb-3"
            >
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-key text-amber-400"></i>
                    <h3 class="text-sm font-bold text-slate-100">
                        LLM Provider API Keys
                    </h3>
                </div>
                <button
                    class="text-slate-400 hover:text-white"
                    @click="emit('close')"
                >
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <p class="text-xs leading-relaxed text-slate-400">
                Keys stay in memory on this page and are cleared when you log
                out or reload. Leave a key empty to use demo generation.
            </p>

            <div class="space-y-3 text-xs">
                <div v-for="f in fields" :key="f.key">
                    <label class="mb-1 block font-medium text-slate-300">{{
                        f.label
                    }}</label>
                    <input
                        :id="'key-' + f.key"
                        v-model="state.apiKeys[f.key]"
                        type="password"
                        :placeholder="f.placeholder"
                        class="w-full rounded border border-slate-700 bg-slate-900 px-3 py-1.5 text-slate-200 outline-none focus:border-indigo-500"
                    />
                </div>
            </div>

            <div class="flex justify-end gap-2 border-t border-slate-800 pt-2">
                <button
                    class="rounded-lg bg-indigo-600 px-4 py-2 text-xs font-semibold text-white transition hover:bg-indigo-500"
                    @click="save"
                >
                    Done
                </button>
            </div>
        </div>
    </div>
</template>
