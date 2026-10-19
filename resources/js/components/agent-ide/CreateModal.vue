<script setup lang="ts">
import { inject, ref, watch } from 'vue';
import type { IdeApi } from '@/composables/useIdeApi';
import type { useIdeState } from '@/composables/useIdeState';

const props = defineProps<{ isOpen: boolean; type: 'file' | 'folder' }>();
const emit = defineEmits<{ close: [] }>();

const { api, state } = inject<{
    api: IdeApi;
    state: ReturnType<typeof useIdeState>;
}>('ide')!;

const path = ref('');

watch(
    () => props.isOpen,
    (open) => {
        if (!open) return;
        const base = state.targetDirectory.value
            ? state.targetDirectory.value + '/'
            : '';
        path.value =
            base + (props.type === 'file' ? 'script.py' : 'new-folder');
    },
    { immediate: true },
);

async function confirm() {
    const p = path.value.trim();
    if (!p) return;
    const type = props.type === 'file' ? 'file' : 'directory';
    const data = await api.createFile(p, type as any, '');
    if (data.success) {
        state.pushToast(data.message || 'Created!');
        emit('close');
        window.dispatchEvent(new CustomEvent('ide:refresh-tree'));
        if (props.type === 'file') {
            window.dispatchEvent(
                new CustomEvent('ide:open-file', { detail: p }),
            );
        }
    } else {
        state.pushToast(data.error || 'Failed to create item', true);
    }
}
</script>

<template>
    <div
        v-if="isOpen"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4 backdrop-blur-sm"
    >
        <div
            class="w-full max-w-sm space-y-4 rounded-2xl border border-slate-700 bg-[#0f172a] p-5 shadow-2xl"
        >
            <h3 class="text-sm font-bold text-slate-100">
                {{
                    type === 'file' ? 'Create New File' : 'Create New Directory'
                }}
            </h3>
            <div>
                <label class="mb-1 block text-xs text-slate-400"
                    >Relative Path / Name</label
                >
                <input
                    v-model="path"
                    type="text"
                    class="w-full rounded border border-slate-700 bg-slate-900 px-3 py-2 text-xs text-slate-200 outline-none focus:border-indigo-500"
                    @keydown.enter="confirm"
                />
            </div>
            <div class="flex justify-end gap-2">
                <button
                    class="rounded bg-slate-800 px-3 py-1.5 text-xs font-medium text-slate-300"
                    @click="emit('close')"
                >
                    Cancel
                </button>
                <button
                    class="rounded bg-indigo-600 px-4 py-1.5 text-xs font-semibold text-white"
                    @click="confirm"
                >
                    Create
                </button>
            </div>
        </div>
    </div>
</template>
