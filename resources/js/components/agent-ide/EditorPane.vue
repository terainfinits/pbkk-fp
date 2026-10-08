<script setup lang="ts">
import { computed, inject, nextTick, onMounted, ref, watch } from 'vue';
import { CodeEditor } from 'monaco-editor-vue3';
import * as monaco from 'monaco-editor';
import TerminalDrawer from './TerminalDrawer.vue';
import type { IdeApi } from '@/composables/useIdeApi';
import type { useIdeState, OpenTab } from '@/composables/useIdeState';

const { api, state } = inject<{ api: IdeApi; state: ReturnType<typeof useIdeState> }>('ide')!;

const cursorPos = ref('Ln 1, Col 1');
const diffOpen = ref(false);
const diffContent = ref('');
const code = ref('');
const isSwitchingTab = ref(false); // Flag to prevent false "dirty" flags during switch

const activeTab = computed<OpenTab | undefined>(() =>
  state.openTabs.find(t => t.path === state.activeTabPath.value)
);

const editorLanguage = computed(() => {
  // Get extension, lowercase it, and remove any leading dot (e.g., ".js" -> "js")
  const ext = activeTab.value?.extension?.toLowerCase().replace(/^\./, '') || 'php';

  // Map file extensions to Monaco's official language identifiers
  const languageMap: Record<string, string> = {
    js: 'javascript',
    ts: 'typescript',
    py: 'python',
    md: 'markdown',
    yml: 'yaml',
    sh: 'shell',
    // php, html, css, json, sql already match Monaco's names
  };

  return languageMap[ext] || ext;
});

const currentTheme = ref('my-custom-dark')

const handleEditorMount = () => {
  monaco.editor.defineTheme('my-custom-dark', {
    base: 'vs-dark',
    inherit: true,
    rules: [],
    colors: {
      'editor.background': '#0b0f1a',
    }
  })

  // Forcefully set it if needed
  monaco.editor.setTheme('my-custom-dark')
}

// Sync editor content whenever the active tab path changes
watch(
  () => state.activeTabPath.value,
  (newPath) => {
    isSwitchingTab.value = true;
    const t = state.openTabs.find(tab => tab.path === newPath);
    code.value = t ? t.content : '';
    nextTick(() => {
      isSwitchingTab.value = false;
    });
  },
  { immediate: true }
);

// Sync user typing back to the active tab's content state
watch(code, (val) => {
  if (isSwitchingTab.value) return; // Ignore updates triggered by tab switches
  const t = activeTab.value;
  if (t && t.content !== val) {
    t.content = val;
    if (!t.isDirty) t.isDirty = true;
  }
});

const charCount = computed(() => code.value.length);

function updateCursor(e: any) {
  if (e && e.position) {
    cursorPos.value = `Ln ${e.position.lineNumber}, Col ${e.position.column}`;
  }
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
      <div class="flex-1 relative h-full overflow-hidden">
        <CodeEditor
          v-if="activeTab"
          :key="`${activeTab.path}::${editorLanguage}`"
          ref="editorRef"
          v-model:value="code"
          :language="editorLanguage"
          :theme="currentTheme"
          :options="{ minimap: { enabled: false } }"
          class="h-full w-full"
          @cursorPositionChange="updateCursor"
                    @editorDidMount="handleEditorMount"
        />
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
