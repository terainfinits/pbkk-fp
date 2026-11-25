<!-- Fitur: Panel chat dan pengaturan model
connect with ../../composables/useIdeState.ts, ../../pages/AgentIdeApp.vue -->
<script setup lang="ts">
import { inject, reactive, ref, watch } from 'vue';
import type { useIdeState } from '@/composables/useIdeState';

const props = defineProps<{ isOpen: boolean }>();
const emit = defineEmits<{ close: [] }>();

const { state } = inject<{ state: ReturnType<typeof useIdeState> }>('ide')!;

const fields = [
  { key: 'gemini', label: 'Google Gemini API Key', placeholder: 'AIzaSy...' },
  { key: 'claude', label: 'Anthropic Claude API Key', placeholder: 'sk-ant-...' },
  { key: 'gpt', label: 'OpenAI API Key (GPT)', placeholder: 'sk-...' },
  { key: 'kimi', label: 'Moonshot Kimi API Key', placeholder: 'sk-...' },
  { key: 'deepseek', label: 'DeepSeek API Key', placeholder: 'sk-...' },
  { key: 'ollama_cloud', label: 'Ollama Cloud API Key', placeholder: 'fe...' },
  { key: 'senopati', label: 'Senopati API Key', placeholder: 'sk-...' },
];
const keys = reactive<Record<string, string>>({});
const storageError = ref('');

watch(
  () => props.isOpen,
  open => {
    if (!open) return;
    storageError.value = '';
    try {
      fields.forEach(f => {
        keys[f.key] = localStorage.getItem('agent_key_' + f.key) || '';
      });
    } catch {
      storageError.value = 'Browser memblokir penyimpanan API key. Izinkan penyimpanan situs lalu coba kembali.';
    }
  },
  { immediate: true }
);

function save() {
  storageError.value = '';
  try {
    fields.forEach(f => {
      const v = (keys[f.key] || '').trim();
      localStorage.setItem('agent_key_' + f.key, v);
      if (f.key === 'ollama_cloud') localStorage.setItem('agent_key_ollama', v);
    });
  } catch {
    storageError.value = 'API key gagal disimpan. Periksa izin penyimpanan browser lalu coba kembali.';
    return;
  }
  state.pushToast('API Keys saved successfully!');
  emit('close');
}
</script>

<template>
  <div v-if="isOpen" class="fixed inset-0 bg-black/70 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="w-full max-w-md bg-[#0f172a] border border-slate-700 rounded-2xl shadow-2xl p-6 space-y-5">
      <div class="flex items-center justify-between border-b border-slate-800 pb-3">
        <div class="flex items-center gap-2">
          <i class="fa-solid fa-key text-amber-400"></i>
          <h3 class="font-bold text-sm text-slate-100">LLM Provider API Keys</h3>
        </div>
        <button class="text-slate-400 hover:text-white" @click="emit('close')">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>

      <p class="text-xs text-slate-400 leading-relaxed">
        Enter your custom API key for real-time live model completion. If left empty, built-in intelligent autonomous generation will be used.
      </p>

      <div class="space-y-3 text-xs">
        <div v-for="f in fields" :key="f.key">
          <label class="block font-medium text-slate-300 mb-1">{{ f.label }}</label>
          <input
            :id="'key-' + f.key"
            v-model="keys[f.key]"
            type="password"
            autocomplete="off"
            :placeholder="f.placeholder"
            class="w-full bg-slate-900 border border-slate-700 rounded px-3 py-1.5 text-slate-200 outline-none focus:border-indigo-500"
          />
        </div>
      </div>

      <p v-if="storageError" role="alert" class="text-xs text-red-300">{{ storageError }}</p>
      <div class="flex justify-end gap-2 pt-2 border-t border-slate-800">
        <button class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg text-xs font-semibold transition" @click="save">
          Save Keys to Storage
        </button>
      </div>
    </div>
  </div>
</template>
