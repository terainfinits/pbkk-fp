<script setup lang="ts">
import { inject, onMounted, onBeforeUnmount, ref } from 'vue';
import TreeNode from './TreeNode.vue';
import type { IdeApi } from '@/composables/useIdeApi';
import type { useIdeState, OpenTab } from '@/composables/useIdeState';

const { api, state, openCreateModal } = inject<{
  api: IdeApi;
  state: ReturnType<typeof useIdeState>;
  openCreateModal: (t: 'file' | 'folder') => void;
}>('ide')!;

/* ✅ Destructure so template auto-unwraps correctly */
const {
  projectTree,
  workspaceName,
  targetDirectory,
  openTabs,
  activeTabPath,
} = state;

const isLoading = ref(true);
const loadError = ref<string | null>(null);

const targetDisplay = () =>
  targetDirectory.value ? '/' + targetDirectory.value : '/';

async function loadTree() {
  isLoading.value = true;
  loadError.value = null;
  try {
    const data = await api.fetchTree();
    console.log('[tree] raw:', data); // 🔎 verify shape

    // ✅ Accept both `{success, tree}` and bare `{tree}` shapes
    const tree = data?.tree ?? (Array.isArray(data) ? data : null);
    const root = data?.root ?? workspaceName.value;

    if (Array.isArray(tree)) {
      projectTree.value = tree;
      workspaceName.value = root;
    } else {
      loadError.value = data?.error || 'Unexpected response shape';
      state.pushToast('Tree load failed: ' + loadError.value, true);
    }
  } catch (err: any) {
    loadError.value = err?.message || 'Network error';
    state.pushToast('Tree load failed: ' + loadError.value, true);
  } finally {
    isLoading.value = false;
  }
}

async function openFile(filePath: string) {
  let tab = openTabs.find(t => t.path === filePath);
  if (!tab) {
    const data = await api.readFile(filePath);
    if (!data.success) {
      state.pushToast('Failed to read file: ' + data.error, true);
      return;
    }
    tab = {
      path: data.path,
      filename: data.filename,
      content: data.content,
      extension: data.extension,
      isDirty: false,
    };
    openTabs.push(tab);
  }
  activeTabPath.value = tab.path;
  window.dispatchEvent(new CustomEvent('ide:switch-tab', { detail: tab.path }));
}

function setTarget(dir: string) {
  targetDirectory.value = dir;
  state.pushToast(`Target folder set to /${dir}`);
}

async function handleRename(path: string) {
  const newName = prompt('Masukkan nama baru:', path.split('/').pop() || '');
  if (!newName || newName === path.split('/').pop()) return;
  try {
    const res = await api.renameFile(path, newName);
    if (res.success) {
      state.pushToast(`Diubah nama menjadi ${newName}`);
      void loadTree();
    } else {
      state.pushToast('Gagal rename: ' + (res.error || ''), true);
    }
  } catch (err: any) {
    state.pushToast('Error rename: ' + err?.message, true);
  }
}

async function handleDelete(path: string, isDir: boolean) {
  const name = path.split('/').pop() || path;
  const confirmed = confirm(isDir ? `Hapus folder "${name}" dan isinya?` : `Hapus file "${name}"?`);
  if (!confirmed) return;
  try {
    const res = await api.deleteFile(path);
    if (res.success) {
      state.pushToast('Dihapus');
      void loadTree();
    } else {
      state.pushToast('Gagal hapus: ' + (res.error || ''), true);
    }
  } catch (err: any) {
    state.pushToast('Error hapus: ' + err?.message, true);
  }
}

function openUpload() {
  document.getElementById('upload-file-input')?.click();
}

async function handleUpload(event: Event) {
  const input = event.target as HTMLInputElement;
  const file = input.files?.[0];
  if (!file) return;
  try {
    const res = await api.uploadFile(file, targetDirectory.value);
    if (res.success) {
      state.pushToast('Upload berhasil');
      input.value = '';
      void loadTree();
    } else {
      state.pushToast('Gagal upload: ' + (res.error || ''), true);
    }
  } catch (err: any) {
    state.pushToast('Error upload: ' + err?.message, true);
  }
}

/* Respond to refresh events from CreateModal / ChatPanel */
function handleOpenFileEvent(event: Event) {
  const filePath = (event as CustomEvent<string>).detail;

  if (typeof filePath === 'string' && filePath.length > 0) {
    void openFile(filePath);
  }
}

onMounted(() => {
  // Load the project tree only in the browser
  void loadTree();

  // Register browser event listeners only in the browser
  window.addEventListener('ide:refresh-tree', loadTree);
  window.addEventListener('ide:open-file', handleOpenFileEvent);
});

onBeforeUnmount(() => {
  // Remove listeners when the component is destroyed
  window.removeEventListener('ide:refresh-tree', loadTree);
  window.removeEventListener('ide:open-file', handleOpenFileEvent);
});
</script>

<template>
  <aside class="w-64 bg-[#0b0f1a] border-r border-slate-800 flex flex-col shrink-0">
    <!-- header unchanged -->
    <div class="h-10 px-3 border-b border-slate-800 flex items-center justify-between text-xs font-semibold text-slate-400 uppercase tracking-wider bg-[#0d1322]">
      <div class="flex items-center gap-2">
        <i class="fa-solid fa-folder-tree text-indigo-400"></i>
        <span>Explorer</span>
      </div>
      <div class="flex items-center gap-1">
        <button title="New File" class="w-6 h-6 rounded hover:bg-slate-800 text-slate-400 hover:text-slate-200 flex items-center justify-center transition" @click="openCreateModal('file')">
          <i class="fa-solid fa-file-circle-plus text-xs"></i>
        </button>
        <button title="New Folder" class="w-6 h-6 rounded hover:bg-slate-800 text-slate-400 hover:text-slate-200 flex items-center justify-center transition" @click="openCreateModal('folder')">
          <i class="fa-solid fa-folder-plus text-xs"></i>
        </button>
        <button title="Refresh" class="w-6 h-6 rounded hover:bg-slate-800 text-slate-400 hover:text-slate-200 flex items-center justify-center transition" @click="loadTree">
          <i class="fa-solid fa-rotate text-xs"></i>
        </button>
        <button title="Upload" class="w-6 h-6 rounded hover:bg-slate-800 text-slate-400 hover:text-slate-200 flex items-center justify-center transition" @click="openUpload">
          <i class="fa-solid fa-upload text-xs"></i>
        </button>
        <input type="file" id="upload-file-input" class="hidden" @change="handleUpload" />
      </div>
    </div>

    <div class="p-2 bg-slate-900/60 border-b border-slate-800/80 text-[11px]">
      <div class="flex items-center justify-between text-slate-400 mb-1">
        <span>AI Target Folder:</span>
        <button class="text-indigo-400 hover:underline" @click="targetDirectory.value = ''">Reset Root</button>
      </div>
      <div class="px-2 py-1 bg-slate-950 rounded text-indigo-300 font-mono text-[10px] truncate border border-slate-800">
        {{ targetDisplay() }}
      </div>
    </div>

    <div class="flex-1 overflow-y-auto p-2 space-y-0.5 text-xs select-none">
      <div v-if="isLoading" class="p-4 text-center text-slate-500 animate-pulse">
        <i class="fa-solid fa-circle-notch fa-spin mr-2"></i> Loading project tree...
      </div>

      <!-- ✅ Explicit error state instead of silent empty tree -->
      <div v-else-if="loadError" class="p-3 text-red-400 text-xs">
        <i class="fa-solid fa-triangle-exclamation mr-1"></i>
        {{ loadError }}
      </div>

      <!-- ✅ Use `projectTree` directly (auto-unwrapped) -->
      <TreeNode
        v-for="item in projectTree"
        :key="item.path || item.name"
        :item="item"
        :level="0"
        @select="openFile"
        @target="setTarget"
        @rename="handleRename"
        @delete="(p, isDir) => handleDelete(p, isDir)"
      />

      <div v-if="!isLoading && !loadError && projectTree.length === 0" class="p-3 text-slate-500 text-xs italic">
        Workspace is empty.
      </div>
    </div>

    <div class="p-2.5 border-t border-slate-800 bg-[#090d16] text-[10px] text-slate-500">
      <span class="text-slate-400 font-medium">Tip:</span> Select folder to target AI file generation.
    </div>
  </aside>
</template>
