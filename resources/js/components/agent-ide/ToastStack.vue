<script setup lang="ts">
import { inject } from 'vue';
import type { useIdeState } from '@/composables/useIdeState';

const { state } = inject<{ state: ReturnType<typeof useIdeState> }>('ide')!;
</script>

<template>
    <div class="fixed right-8 bottom-8 z-[60] space-y-2">
        <TransitionGroup name="toast">
            <div
                v-for="t in state.toasts.value"
                :key="t.id"
                class="flex items-center gap-2 rounded-lg border px-4 py-2.5 text-xs font-semibold shadow-2xl"
                :class="
                    t.isError
                        ? 'border-red-500 bg-red-950 text-red-200'
                        : 'border-indigo-500 bg-slate-900 text-indigo-200'
                "
            >
                <i
                    class="fa-solid"
                    :class="
                        t.isError
                            ? 'fa-triangle-exclamation text-red-400'
                            : 'fa-circle-check text-emerald-400'
                    "
                ></i>
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
