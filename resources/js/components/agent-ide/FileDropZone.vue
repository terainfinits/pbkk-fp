<!-- Fitur: Explorer, pembuatan dan unggah file
connect with ../../composables/useIdeApi.ts, ../../composables/useIdeState.ts, ../../pages/AgentIdeApp.vue
connect with ./SidebarExplorer.vue -->
<script setup lang="ts">
import { inject, onBeforeUnmount, onMounted, ref } from 'vue';
import type { IdeApi } from '@/composables/useIdeApi';
import type { useIdeState } from '@/composables/useIdeState';

const { api, state } = inject<{ api: IdeApi; state: ReturnType<typeof useIdeState> }>('ide')!;

const isDragging = ref(false);
let dragDepth = 0;

function hasFiles(e: DragEvent): boolean {
  return Array.from(e.dataTransfer?.types ?? []).includes('Files');
}

/* Listeners live on window so the drop zone is the entire screen and
   preventDefault() stops the browser default (download zip / open file in tab). */
function onDragEnter(e: DragEvent) {
  e.preventDefault();
  if (!hasFiles(e)) return;
  dragDepth++;
  if (dragDepth === 1) isDragging.value = true;
}

function onDragOver(e: DragEvent) {
  e.preventDefault();
}

function onDragLeave(e: DragEvent) {
  dragDepth = Math.max(0, dragDepth - 1);
  if (dragDepth === 0) isDragging.value = false;
}

async function onDrop(e: DragEvent) {
  e.preventDefault();
  dragDepth = 0;
  isDragging.value = false;

  const files = e.dataTransfer?.files;
  if (!files?.length) return;

  const target = state.targetDirectory.value;
  let success = 0;
  const failures: string[] = [];

  for (const file of Array.from(files)) {
    const res = await api.uploadFile(file, target);
    if (res.success) {
      success++;
      state.pushToast(`Uploaded ${file.name}`);
    } else {
      failures.push(`${file.name}: ${res.error || 'unknown error'}`);
    }
  }

  if (success) state.pushToast(`${success} file(s) uploaded.`);
  if (failures.length) state.pushToast('Upload failed: ' + failures.join('; '), true);
  window.dispatchEvent(new CustomEvent('ide:refresh-tree'));
}

onMounted(() => {
  window.addEventListener('dragenter', onDragEnter);
  window.addEventListener('dragover', onDragOver);
  window.addEventListener('dragleave', onDragLeave);
  window.addEventListener('drop', onDrop);
});

onBeforeUnmount(() => {
  window.removeEventListener('dragenter', onDragEnter);
  window.removeEventListener('dragover', onDragOver);
  window.removeEventListener('dragleave', onDragLeave);
  window.removeEventListener('drop', onDrop);
});
</script>

<!-- Overlay is visual only: pointer-events none so drag events hit the page
     below and bubble to window, avoiding double drop handling. -->
<template>
  <div
    v-if="isDragging"
    class="fixed inset-0 z-[9999] pointer-events-none bg-[#0b0f19]/60 border-2 border-dashed border-indigo-500/60 flex flex-col items-center justify-center select-none"
  >
    <i class="fa-solid fa-cloud-arrow-up text-4xl text-indigo-400 mb-3"></i>
    <p class="text-sm text-indigo-300 font-semibold">Drop files to upload to workspace</p>
  </div>
</template>
