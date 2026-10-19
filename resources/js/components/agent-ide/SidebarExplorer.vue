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
const { projectTree, workspaceName, targetDirectory, openTabs, activeTabPath } =
    state;

const isLoading = ref(true);
const loadError = ref<string | null>(null);

const targetDisplay = () =>
    targetDirectory.value ? '/' + targetDirectory.value : '/';

async function loadTree() {
    isLoading.value = true;
    loadError.value = null;
    try {
        const data = await api.fetchTree();

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
    let tab = openTabs.find((t) => t.path === filePath);
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
    window.dispatchEvent(
        new CustomEvent('ide:switch-tab', { detail: tab.path }),
    );
}

function setTarget(dir: string) {
    targetDirectory.value = dir;
    state.pushToast(`Target folder set to /${dir}`);
}

onMounted(loadTree);

/* Respond to refresh events from CreateModal / ChatPanel */
const handleOpenFile = (event: Event) =>
    void openFile((event as CustomEvent<string>).detail);
window.addEventListener('ide:refresh-tree', loadTree);
window.addEventListener('ide:open-file', handleOpenFile);
onBeforeUnmount(() => {
    window.removeEventListener('ide:refresh-tree', loadTree);
    window.removeEventListener('ide:open-file', handleOpenFile);
});
</script>

<template>
    <aside
        class="flex w-64 shrink-0 flex-col border-r border-slate-800 bg-[#0b0f1a]"
    >
        <!-- header unchanged -->
        <div
            class="flex h-10 items-center justify-between border-b border-slate-800 bg-[#0d1322] px-3 text-xs font-semibold tracking-wider text-slate-400 uppercase"
        >
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-folder-tree text-indigo-400"></i>
                <span>Explorer</span>
            </div>
            <div class="flex items-center gap-1">
                <button
                    title="New File"
                    class="flex h-6 w-6 items-center justify-center rounded text-slate-400 transition hover:bg-slate-800 hover:text-slate-200"
                    @click="openCreateModal('file')"
                >
                    <i class="fa-solid fa-file-circle-plus text-xs"></i>
                </button>
                <button
                    title="New Folder"
                    class="flex h-6 w-6 items-center justify-center rounded text-slate-400 transition hover:bg-slate-800 hover:text-slate-200"
                    @click="openCreateModal('folder')"
                >
                    <i class="fa-solid fa-folder-plus text-xs"></i>
                </button>
                <button
                    title="Refresh"
                    class="flex h-6 w-6 items-center justify-center rounded text-slate-400 transition hover:bg-slate-800 hover:text-slate-200"
                    @click="loadTree"
                >
                    <i class="fa-solid fa-rotate text-xs"></i>
                </button>
            </div>
        </div>

        <div
            class="border-b border-slate-800/80 bg-slate-900/60 p-2 text-[11px]"
        >
            <div class="mb-1 flex items-center justify-between text-slate-400">
                <span>AI Target Folder:</span>
                <button
                    class="text-indigo-400 hover:underline"
                    @click="targetDirectory = ''"
                >
                    Reset Root
                </button>
            </div>
            <div
                class="truncate rounded border border-slate-800 bg-slate-950 px-2 py-1 font-mono text-[10px] text-indigo-300"
            >
                {{ targetDisplay() }}
            </div>
        </div>

        <div class="flex-1 space-y-0.5 overflow-y-auto p-2 text-xs select-none">
            <div
                v-if="isLoading"
                class="animate-pulse p-4 text-center text-slate-500"
            >
                <i class="fa-solid fa-circle-notch fa-spin mr-2"></i> Loading
                project tree...
            </div>

            <!-- ✅ Explicit error state instead of silent empty tree -->
            <div v-else-if="loadError" class="p-3 text-xs text-red-400">
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
            />

            <div
                v-if="!isLoading && !loadError && projectTree.length === 0"
                class="p-3 text-xs text-slate-500 italic"
            >
                Workspace is empty.
            </div>
        </div>

        <div
            class="border-t border-slate-800 bg-[#090d16] p-2.5 text-[10px] text-slate-500"
        >
            <span class="font-medium text-slate-400">Tip:</span> Select folder
            to target AI file generation.
        </div>
    </aside>
</template>
