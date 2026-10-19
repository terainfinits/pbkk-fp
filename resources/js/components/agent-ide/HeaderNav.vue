<script setup lang="ts">
import { computed, inject } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import type { IdeApi } from '@/composables/useIdeApi';
import type { useIdeState } from '@/composables/useIdeState';

const emit = defineEmits<{ 'open-settings': [] }>();
const page = usePage();
function logout() {
    state.openTabs.splice(0);
    Object.keys(state.apiKeys).forEach((key) => delete state.apiKeys[key]);
    router.post('/logout');
}
const { state } = inject<{
    api: IdeApi;
    state: ReturnType<typeof useIdeState>;
}>('ide')!;

const targetDisplay = computed(() =>
    state.targetDirectory.value
        ? '/' + state.targetDirectory.value
        : '/ (Root)',
);

const kernelText = computed(() => {
    const k = state.kernels.value;
    const parts: string[] = [];
    if (k.python?.available) parts.push(`🐍 ${k.python.version}`);
    if (k.php?.available) parts.push(`🐘 ${k.php.version}`);
    if (k.node?.available) parts.push(`🟢 ${k.node.version}`);
    return parts.join(' | ') || 'Execution unavailable';
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
    <header
        class="z-30 flex h-14 shrink-0 items-center justify-between border-b border-slate-800/80 bg-[#0e1424] px-4"
    >
        <div class="flex items-center space-x-4">
            <a href="/" class="group flex items-center gap-2">
                <div
                    class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-tr from-blue-600 via-indigo-600 to-purple-500 text-white shadow-md shadow-indigo-500/20 transition group-hover:scale-105"
                >
                    <i class="fa-solid fa-atom text-sm"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span
                            class="text-sm font-bold tracking-tight text-white"
                            >MAGENTIC</span
                        >
                        <span
                            class="rounded border border-indigo-500/30 bg-indigo-500/20 px-1.5 py-0.5 text-[10px] font-bold tracking-widest text-indigo-400 uppercase"
                            >IDE</span
                        >
                    </div>
                    <p class="font-mono text-[10px] text-slate-400">
                        Agentic AI Code Studio
                    </p>
                </div>
            </a>

            <div class="h-5 w-px bg-slate-800"></div>

            <div
                class="flex items-center gap-2 rounded-md border border-slate-800 bg-slate-900/90 px-2.5 py-1 text-xs text-slate-300"
            >
                <i class="fa-regular fa-folder-open text-amber-400"></i>
                <span class="font-normal text-slate-400">Workspace:</span>
                <span class="font-semibold text-slate-200">{{
                    state.workspaceName.value
                }}</span>
            </div>

            <div
                class="hidden items-center gap-2 rounded-md border border-indigo-500/30 bg-indigo-950/40 px-2.5 py-1 text-xs text-indigo-300 sm:flex"
            >
                <i class="fa-solid fa-crosshairs text-indigo-400"></i>
                <span class="text-indigo-400/80">Target Dir:</span>
                <span class="font-mono text-indigo-200">{{
                    targetDisplay
                }}</span>
            </div>

            <div
                class="hidden items-center gap-2 rounded-md border border-slate-800 bg-slate-900 px-2.5 py-1 text-xs text-slate-300 md:flex"
            >
                <i
                    class="fa-solid fa-microchip animate-pulse text-emerald-400"
                ></i>
                <span class="font-normal text-slate-400">Kernels:</span>
                <span class="font-mono text-[11px] text-emerald-300">{{
                    kernelText
                }}</span>
            </div>
        </div>

        <div class="flex items-center space-x-3">
            <button
                title="Run current file in kernel (F5)"
                class="flex items-center gap-1.5 rounded-md bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white shadow-md shadow-emerald-600/20 transition hover:bg-emerald-500"
                @click="runActiveFile"
            >
                <i class="fa-solid fa-play"></i>
                <span>Run Code</span>
                <span class="font-mono text-[10px] text-emerald-200">F5</span>
            </button>

            <button
                title="Save current file (Ctrl+S)"
                class="flex items-center gap-1.5 rounded-md border border-slate-700 bg-slate-800 px-3 py-1.5 text-xs font-medium text-slate-200 transition hover:bg-slate-700"
                @click="saveActiveFile"
            >
                <i class="fa-regular fa-floppy-disk text-slate-400"></i>
                <span>Save</span>
                <span class="font-mono text-[10px] text-slate-500">Ctrl+S</span>
            </button>

            <button
                class="flex items-center gap-1.5 rounded-md border border-slate-700/80 bg-slate-800/80 px-3 py-1.5 text-xs font-medium text-slate-300 transition hover:bg-slate-700"
                @click="emit('open-settings')"
            >
                <i class="fa-solid fa-key text-amber-400"></i>
                <span>API Keys</span>
            </button>

            <span class="max-w-32 truncate text-xs text-slate-300">{{
                page.props.auth.user?.name
            }}</span>
            <button
                @click="logout"
                class="flex items-center gap-1.5 rounded-md border border-indigo-500/30 bg-indigo-600/20 px-3 py-1.5 text-xs font-semibold text-indigo-300 transition hover:bg-indigo-600/30"
            >
                <i class="fa-solid fa-arrow-left"></i>
                <span>Log out</span>
            </button>
        </div>
    </header>
</template>
