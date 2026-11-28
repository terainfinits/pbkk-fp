<script setup lang="ts">
import { ref } from 'vue';

const props = defineProps<{ blockedReason: string; startAgent: (text: string) => Promise<boolean> }>();
const prompt = ref('');

async function submit() {
  const text = prompt.value;
  if (props.blockedReason || !text.trim()) return;
  if (await props.startAgent(text) && prompt.value === text) prompt.value = '';
}
</script>

<template>
  <aside class="chat-panel bg-[#0e1424] border-l border-slate-800 flex flex-col shrink-0">
    <header class="flex items-center gap-3 border-b border-slate-800 px-4 py-3">
      <i class="fa-solid fa-robot rounded-lg bg-indigo-600 p-2.5 text-white" aria-hidden="true"></i>
      <div>
        <h2 class="text-sm font-semibold">Tanya</h2>
        <p class="text-xs text-slate-400">Tugas coding dengan Senopati</p>
      </div>
    </header>
    <slot name="agent" />
    <!-- Prompt input -->
    <form class="space-y-2 border-t border-slate-800 bg-[#0b0f1a] p-3" @submit.prevent="submit">
      <p v-if="blockedReason" id="prompt-blocked" role="status" class="text-xs text-amber-300">{{ blockedReason }}</p>
      <textarea
        v-model="prompt"
        rows="3"
        aria-label="Prompt tugas coding"
        :aria-describedby="blockedReason ? 'prompt-blocked' : undefined"
        class="w-full select-text resize-y rounded-xl border border-slate-700 bg-slate-900 p-3 text-sm text-slate-200 outline-none placeholder:text-slate-400 focus:border-indigo-400"
        placeholder="Apa yang ingin dikerjakan?"
        @keydown.enter.exact.prevent="submit"
      ></textarea>
      <div class="flex items-center justify-between">
        <span class="text-[10px] text-slate-500 font-mono">Shift+Enter untuk baris baru</span>
        <button
          type="submit"
          :disabled="!!blockedReason || !prompt.trim()"
          class="px-4 py-2 bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 hover:from-blue-500 hover:to-purple-500 disabled:opacity-50 text-white font-semibold text-xs rounded-lg shadow-lg shadow-indigo-500/20 flex items-center gap-1.5 transition"
        >
          <i class="fa-solid fa-paper-plane"></i>
          <span>Kirim</span>
        </button>
      </div>
    </form>
  </aside>
</template>
