<script setup lang="ts">
import { inject } from 'vue';
import type { useIdeState } from '@/composables/useIdeState';

const { state } = inject<{ state: ReturnType<typeof useIdeState> }>('ide')!;
</script>

<template>
  <div class="fixed bottom-8 right-8 z-[60] space-y-2">
    <TransitionGroup name="toast">
      <div
        v-for="t in state.toasts.value"
        :key="t.id"
        class="px-4 py-2.5 rounded-lg text-xs font-semibold shadow-2xl flex items-center gap-2 border"
        :class="t.isError ? 'bg-red-950 border-red-500 text-red-200' : 'bg-slate-900 border-indigo-500 text-indigo-200'"
      >
        <i class="fa-solid" :class="t.isError ? 'fa-triangle-exclamation text-red-400' : 'fa-circle-check text-emerald-400'"></i>
        <span>{{ t.message }}</span>
      </div>
    </TransitionGroup>
  </div>
</template>

<style scoped>
.toast-enter-active,
.toast-leave-active {
  transition: all 0.3s ease;
}
.toast-enter-from {
  opacity: 0;
  transform: translateY(8px);
}
.toast-leave-to {
  opacity: 0;
  transform: translateY(8px);
}
</style>
