<script setup lang="ts">
import { computed, inject } from 'vue';
import type { IdeApi } from '@/composables/useIdeApi';
import type { useIdeState } from '@/composables/useIdeState';

const emit = defineEmits<{ 'open-settings': [] }>();
const { state } = inject<{ api: IdeApi; state: ReturnType<typeof useIdeState> }>('ide')!;

const targetDisplay = computed(() =>
  state.targetDirectory.value ? '/' + state.targetDirectory.value : '/ (Root)'
);

const kernelText = computed(() => {
  const k = state.kernels.value;
  const parts: string[] = [];
  if (k.python?.available) parts.push(`🐍 ${k.python.version}`);
  if (k.php?.available) parts.push(`🐘 ${k.php.version}`);
  if (k.node?.available) parts.push(`🟢 ${k.node.version}`);
  return parts.join(' | ') || 'PHP Engine Active';
});

const runActiveFile = () => {
  // EditorPane listens to a window event; simplest cross-component trigger.
  window.dispatchEvent(new CustomEvent('ide:run-active'));
};

const saveActiveFile = () => {
  window.dispatchEvent(new CustomEvent('ide:save-active'));
};
</script>

<template>
  <header class="h-14 bg-[#0e1424] border-b border-slate-800/80 px-4 flex items-center justify-between shrink-0 z-30">
    <div class="flex items-center space-x-4">
      <a href="/" class="flex items-center gap-2 group">
        <div class="w-8 h-8 rounded-lg bg-gradient-to-tr from-blue-600 via-indigo-600 to-purple-500 flex items-center justify-center text-white shadow-md shadow-indigo-500/20 group-hover:scale-105 transition">
          <i class="fa-solid fa-atom text-sm"></i>
        </div>
        <div>
          <div class="flex items-center gap-2">
            <span class="font-bold text-sm tracking-tight text-white">MAGENTIC</span>
            <span class="text-[10px] uppercase font-bold tracking-widest px-1.5 py-0.5 rounded bg-indigo-500/20 text-indigo-400 border border-indigo-500/30">IDE</span>
          </div>
          <p class="text-[10px] text-slate-400 font-mono">Agentic AI Code Studio</p>
        </div>
      </a>

      <div class="h-5 w-px bg-slate-800"></div>

      <div class="flex items-center gap-2 text-xs bg-slate-900/90 border border-slate-800 px-2.5 py-1 rounded-md text-slate-300">
        <i class="fa-regular fa-folder-open text-amber-400"></i>
        <span class="text-slate-400 font-normal">Workspace:</span>
        <span class="font-semibold text-slate-200">{{ state.workspaceName.value }}</span>
      </div>

      <div class="hidden sm:flex items-center gap-2 text-xs bg-indigo-950/40 border border-indigo-500/30 px-2.5 py-1 rounded-md text-indigo-300">
        <i class="fa-solid fa-crosshairs text-indigo-400"></i>
        <span class="text-indigo-400/80">Target Dir:</span>
        <span class="font-mono text-indigo-200">{{ targetDisplay }}</span>
      </div>

      <div class="hidden md:flex items-center gap-2 text-xs bg-slate-900 border border-slate-800 px-2.5 py-1 rounded-md text-slate-300">
        <i class="fa-solid fa-microchip text-emerald-400 animate-pulse"></i>
        <span class="text-slate-400 font-normal">Kernels:</span>
        <span class="font-mono text-[11px] text-emerald-300">{{ kernelText }}</span>
      </div>
    </div>

    <div class="flex items-center space-x-3">
      <button
        title="Run current file in kernel (F5)"
        class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold rounded-md shadow-md shadow-emerald-600/20 flex items-center gap-1.5 transition"
        @click="runActiveFile"
      >
        <i class="fa-solid fa-play"></i>
        <span>Run Code</span>
        <span class="text-[10px] text-emerald-200 font-mono">F5</span>
      </button>

      <button
        title="Save current file (Ctrl+S)"
        class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-medium rounded-md border border-slate-700 flex items-center gap-1.5 transition"
        @click="saveActiveFile"
      >
        <i class="fa-regular fa-floppy-disk text-slate-400"></i>
        <span>Save</span>
        <span class="text-[10px] text-slate-500 font-mono">Ctrl+S</span>
      </button>

      <button
        class="px-3 py-1.5 bg-slate-800/80 hover:bg-slate-700 text-slate-300 text-xs font-medium rounded-md border border-slate-700/80 flex items-center gap-1.5 transition"
        @click="emit('open-settings')"
      >
        <i class="fa-solid fa-key text-amber-400"></i>
        <span>API Keys</span>
      </button>

      <a
        href="/"
        class="px-3 py-1.5 bg-indigo-600/20 hover:bg-indigo-600/30 text-indigo-300 text-xs font-semibold rounded-md border border-indigo-500/30 flex items-center gap-1.5 transition"
      >
        <i class="fa-solid fa-arrow-left"></i>
        <span>Back to App</span>
      </a>
    </div>
  </header>
</template>
