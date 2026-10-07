<script setup lang="ts">
import { inject, ref, watch } from 'vue';
import type { IdeApi } from '@/composables/useIdeApi';
import type { useIdeState } from '@/composables/useIdeState';

const props = defineProps<{ isOpen: boolean; type: 'file' | 'folder' }>();
const emit = defineEmits<{ close: [] }>();

const { api, state } = inject<{ api: IdeApi; state: ReturnType<typeof useIdeState> }>('ide')!;

const path = ref('');

watch(
  () => props.isOpen,
  open => {
    if (!open) return;
    const base = state.targetDirectory.value ? state.targetDirectory.value + '/' : '';
    path.value = base + (props.type === 'file' ? 'script.py' : 'new-folder');
  },
  { immediate: true }
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
      window.dispatchEvent(new CustomEvent('ide:open-file', { detail: p }));
    }
  } else {
    state.pushToast(data.error || 'Failed to create item', true);
  }
}
</script>

<template>
  <div v-if="isOpen" class="fixed inset-0 bg-black/70 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="w-full max-w-sm bg-[#0f172a] border border-slate-700 rounded-2xl shadow-2xl p-5 space-y-4">
      <h3 class="font-bold text-sm text-slate-100">
        {{ type === 'file' ? 'Create New File' : 'Create New Directory' }}
      </h3>
      <div>
        <label class="block text-xs text-slate-400 mb-1">Relative Path / Name</label>
        <input
          v-model="path"
          type="text"
          class="w-full bg-slate-900 border border-slate-700 rounded px-3 py-2 text-xs text-slate-200 outline-none focus:border-indigo-500"
          @keydown.enter="confirm"
        />
      </div>
      <div class="flex justify-end gap-2">
        <button class="px-3 py-1.5 bg-slate-800 text-slate-300 rounded text-xs font-medium" @click="emit('close')">Cancel</button>
        <button class="px-4 py-1.5 bg-indigo-600 text-white rounded text-xs font-semibold" @click="confirm">Create</button>
      </div>
    </div>
  </div>
</template>
