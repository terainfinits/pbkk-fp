<script setup lang="ts">
import { computed, inject, nextTick, onMounted, ref, watch } from 'vue';
import TerminalDrawer from './TerminalDrawer.vue';
import type { IdeApi } from '@/composables/useIdeApi';
import type { useIdeState, OpenTab } from '@/composables/useIdeState';

const { api, state } = inject<{ api: IdeApi; state: ReturnType<typeof useIdeState> }>('ide')!;

const textareaRef = ref<HTMLTextAreaElement | null>(null);
const lineNumbersRef = ref<HTMLDivElement | null>(null);
const cursorPos = ref('Ln 1, Col 1');
const diffOpen = ref(false);
const diffContent = ref('');

const activeTab = computed<OpenTab | undefined>(() =>
  state.openTabs.find(t => t.path === state.activeTabPath.value)
);

const code = ref('');

// Sync textarea with active tab
watch(
  () => state.activeTabPath.value,
  () => {
    const t = activeTab.value;
    code.value = t ? t.content : '';
    nextTick(updateLineNumbers);
  },
  { immediate: true }
);

watch(code, val => {
  const t = activeTab.value;
  if (t) {
    t.content = val;
    if (!t.isDirty) t.isDirty = true;
    updateLineNumbers();
  }
});

const charCount = computed(() => code.value.length);

function updateLineNumbers() {
  if (!lineNumbersRef.value) return;
  const lines = Math.max(code.value.split('\n').length, 1);
  lineNumbersRef.value.innerHTML = Array.from({ length: lines }, (_, i) => i + 1).join('<br>');
}

function onScroll() {
  if (lineNumbersRef.value && textareaRef.value) {
    lineNumbersRef.value.scrollTop = textareaRef.value.scrollTop;
  }
}

function onTab(e: KeyboardEvent) {
  if (e.key !== 'Tab') return;
  e.preventDefault();
  const el = textareaRef.value!;
  const start = el.selectionStart;
  const end = el.selectionEnd;
  code.value = code.value.substring(0, start) + '    ' + code.value.substring(end);
  nextTick(() => {
    el.selectionStart = el.selectionEnd = start + 4;
  });
}

function updateCursor() {
  const el = textareaRef.value;
  if (!el) return;
  const before = el.value.substring(0, el.selectionStart);
  const lines = before.split('\n');
  cursorPos.value = `Ln ${lines.length}, Col ${lines[lines.length - 1].length + 1}`;
}

function closeTab(path: string) {
  const idx = state.openTabs.findIndex(t => t.path === path);
  if (idx === -1) return;
  state.openTabs.splice(idx, 1);
  if (state.activeTabPath.value === path) {
    const next = state.openTabs[state.openTabs.length - 1];
    state.activeTabPath.value = next ? next.path : null;
  }
}

async function saveCurrent() {
  const t = activeTab.value;
  if (!t) {
    state.pushToast('No active file to save', true);
    return;
  }
  state.statusMessage.value = 'Saving file...';
  const data = await api.saveFile(t.path, code.value);
  if (data.success) {
    t.isDirty = false;
    state.statusMessage.value = 'Saved at ' + new Date().toLocaleTimeString();
    state.pushToast(`File saved: ${t.path}`);
  } else {
    state.pushToast('Save failed: ' + data.error, true);
  }
}

async function runActive() {
  const t = activeTab.value;
  if (!code.value.trim()) {
    state.pushToast('Editor is empty. Write code to execute.', true);
    return;
  }
  window.dispatchEvent(
    new CustomEvent('ide:kernel-run', {
      detail: { code: code.value, language: t?.extension || 'php', path: t?.path || '' },
    })
  );
}

function openDiff(content: string) {
  diffContent.value = content;
  diffOpen.value = true;
}

// Keyboard shortcuts
onMounted(() => {
  window.addEventListener('keydown', e => {
    if ((e.ctrlKey || e.metaKey) && e.key === 's') {
      e.preventDefault();
      saveCurrent();
    } else if (e.key === 'F5' || ((e.ctrlKey || e.metaKey) && e.key === 'Enter')) {
      e.preventDefault();
      runActive();
    }
  });
  window.addEventListener('ide:save-active', saveCurrent);
  window.addEventListener('ide:run-active', runActive);
  window.addEventListener('ide:open-diff', (e: any) => openDiff(e.detail));
});
</script>

<template>
  <main class="flex-1 flex flex-col bg-[#0b0f19] overflow-hidden">
    <!-- Tabs -->
    <div class="h-10 bg-[#0e1424] border-b border-slate-800 flex items-center px-1 overflow-x-auto shrink-0 space-x-1">
      <div
        v-for="tab in state.openTabs"
        :key="tab.path"
        class="flex items-center gap-2 px-3 py-1.5 rounded-t text-xs font-medium cursor-pointer border-t-2 transition"
        :class="
          tab.path === state.activeTabPath.value
            ? 'bg-[#0c101c] text-indigo-300 border-indigo-500'
            : 'bg-[#0d1322] text-slate-400 border-transparent hover:bg-slate-800'
        "
        @click="state.activeTabPath.value = tab.path"
      >
        <i class="fa-regular fa-file-code"></i>
        <span class="truncate max-w-[120px]">{{ tab.filename }}</span>
        <span v-if="tab.isDirty" class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
        <button class="hover:text-red-400 ml-1 text-[11px]" @click.stop="closeTab(tab.path)">&times;</button>
      </div>
    </div>

    <!-- Breadcrumbs -->
    <div class="h-8 bg-[#0d1220] border-b border-slate-800/60 px-4 flex items-center justify-between text-xs text-slate-400 shrink-0">
      <div class="flex items-center gap-2">
        <i class="fa-regular fa-file-code text-indigo-400"></i>
        <span class="font-mono text-slate-300 text-xs">{{ activeTab?.path || 'No file selected' }}</span>
        <span v-if="activeTab?.isDirty" class="w-2 h-2 rounded-full bg-amber-400"></span>
      </div>
      <div class="flex items-center gap-3 text-[11px]">
        <span class="px-1.5 py-0.5 rounded bg-slate-800 text-slate-400 uppercase font-mono text-[10px]">
          {{ (activeTab?.extension || 'txt').toUpperCase() }}
        </span>
        <span class="font-mono text-slate-500">{{ cursorPos }}</span>
        <button
          class="px-2.5 py-0.5 rounded bg-emerald-600/30 hover:bg-emerald-600/40 text-emerald-300 border border-emerald-500/40 font-medium transition flex items-center gap-1 text-[11px]"
          @click="runActive"
        >
          <i class="fa-solid fa-play text-[10px]"></i> Run Code
        </button>
      </div>
    </div>

    <!-- Editor Work Area -->
    <div class="flex-1 relative overflow-hidden flex flex-col bg-[#0c101c]">
      <div class="flex-1 relative overflow-hidden flex">
        <div
          ref="lineNumbersRef"
          class="w-12 py-3 bg-[#0a0e1a] text-right pr-3 text-slate-600 font-mono text-xs select-none border-r border-slate-800/60 overflow-hidden leading-6"
        >1</div>
        <div class="flex-1 relative h-full overflow-hidden">
          <textarea
            ref="textareaRef"
            v-model="code"
            spellcheck="false"
            class="editor-textarea code-font w-full h-full p-3 bg-transparent text-slate-200 text-xs leading-6 outline-none border-none overflow-auto font-mono z-10 relative"
            placeholder="// Select a file from the explorer or ask the AI to generate code..."
            @scroll="onScroll"
            @keydown="onTab"
            @keyup="updateCursor"
            @click="updateCursor"
          ></textarea>
        </div>
      </div>

      <!-- Terminal Drawer -->
      <TerminalDrawer />

      <!-- Status Bar -->
      <div class="h-6 bg-[#090d16] border-t border-slate-800/80 px-3 flex items-center justify-between text-[11px] text-slate-500 shrink-0">
        <div class="flex items-center gap-3">
          <span class="flex items-center gap-1.5 text-emerald-400">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-ping"></span>
            <span>{{ state.agentStatusLabel.value }}</span>
          </span>
          <span class="text-slate-600">|</span>
          <span class="text-slate-400">{{ state.statusMessage.value }}</span>
        </div>
        <div class="flex items-center gap-3 font-mono text-[10px]">
          <span>{{ charCount }} chars</span>
          <span>UTF-8</span>
        </div>
      </div>
    </div>
  </main>
</template>
