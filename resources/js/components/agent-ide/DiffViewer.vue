<script setup lang="ts">
import { ref, watch, nextTick } from 'vue';
import { DiffEditor } from 'monaco-editor-vue3';
import * as monaco from 'monaco-editor';

const props = defineProps<{
    visible: boolean;
    originalCode: string;
    modifiedCode: string;
    language?: string;
    targetPath?: string;
    isNewFile?: boolean;
}>();

const emit = defineEmits(['close', 'apply']);

const currentModified = ref(props.modifiedCode);
const diffEditorRef = ref<InstanceType<typeof DiffEditor> | null>(null);

watch(
    () => props.modifiedCode,
    (val) => {
        currentModified.value = val;
    },
);

function handleDiffEditorMount(editor: any) {
    try {
        monaco.editor.defineTheme('my-custom-dark', {
            base: 'vs-dark',
            inherit: true,
            rules: [],
            colors: {
                'editor.background': '#0b0f1a',
                // Force the background of the diff gutter/margin to match
                'diffEditor.insertedTextBackground': '#10b98120',
                'diffEditor.removedTextBackground': '#ef444420',
            },
        });
    } catch (e) {
        // Ignore if already defined
    }

    editor.updateOptions({ theme: 'my-custom-dark' });
    monaco.editor.setTheme('my-custom-dark');

    nextTick(() => {
        setTimeout(() => {
            editor.layout();
        }, 50);
    });
}

watch(
    () => props.visible,
    async (isVisible) => {
        if (isVisible) {
            await nextTick();
            setTimeout(() => {
                try {
                    const editor = (diffEditorRef.value as any)?.getEditor?.();
                    if (editor) {
                        editor.layout();
                        // Re-assert theme just in case
                        editor.updateOptions({ theme: 'my-custom-dark' });
                    }
                } catch (e) {
                    /* ignore */
                }
            }, 50);
        }
    },
);

function handleApply() {
    emit('apply', {
        code: currentModified.value,
        targetPath: props.targetPath,
    });
}

function handleClose() {
    emit('close');
}
</script>

<template>
    <Transition name="fade">
        <div
            v-if="visible"
            class="absolute inset-0 z-50 flex flex-col border-t border-slate-800 bg-[#0b0f19]/95 backdrop-blur-sm"
        >
            <!-- Header -->
            <div
                class="flex h-10 shrink-0 items-center justify-between border-b border-slate-800 bg-[#0e1424] px-4"
            >
                <div
                    class="flex items-center gap-2 text-xs font-semibold text-slate-200"
                >
                    <i class="fa-solid fa-code-compare text-purple-400"></i>
                    <span>Diff View</span>
                    <span
                        v-if="targetPath"
                        class="rounded border px-2 py-0.5 font-mono text-[10px]"
                        :class="
                            isNewFile
                                ? 'border-emerald-500/30 bg-emerald-500/10 text-emerald-400'
                                : 'border-indigo-500/30 bg-indigo-500/10 text-indigo-400'
                        "
                    >
                        <i
                            :class="
                                isNewFile
                                    ? 'fa-solid fa-plus mr-1'
                                    : 'fa-solid fa-pen-to-square mr-1'
                            "
                        ></i>
                        {{ isNewFile ? 'New File' : 'Modified' }}:
                        {{ targetPath }}
                    </span>
                </div>
                <div class="flex items-center gap-2">
                    <button
                        @click="handleApply"
                        class="flex items-center gap-1.5 rounded bg-gradient-to-r from-emerald-600 to-teal-600 px-3 py-1.5 text-[11px] font-semibold text-white shadow transition hover:from-emerald-500 hover:to-teal-500"
                    >
                        <i class="fa-solid fa-check"></i>
                        {{ isNewFile ? 'Create File' : 'Apply Changes' }}
                    </button>
                    <button
                        @click="handleClose"
                        class="flex h-7 w-7 items-center justify-center rounded text-slate-400 transition hover:bg-red-500/10 hover:text-red-400"
                    >
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            </div>

            <!-- Legend -->
            <div
                class="flex h-6 shrink-0 items-center gap-4 border-b border-slate-800/60 bg-[#0d1220] px-4 font-mono text-[10px]"
            >
                <span class="flex items-center gap-1.5 text-red-400">
                    <span
                        class="h-2 w-2 rounded-sm border border-red-500/30 bg-red-500/50"
                    ></span>
                    {{ isNewFile ? 'Empty (New)' : 'Original (Left)' }}
                </span>
                <span class="flex items-center gap-1.5 text-emerald-400">
                    <span
                        class="h-2 w-2 rounded-sm border border-emerald-500/30 bg-emerald-500/50"
                    ></span>
                    AI Generated (Right)
                </span>
            </div>

            <!-- Monaco Diff Container -->
            <div class="relative w-full flex-1 overflow-hidden bg-[#0b0f1a]">
                <DiffEditor
                    ref="diffEditorRef"
                    v-model:value="currentModified"
                    :original="originalCode"
                    :language="language || 'plaintext'"
                    theme="my-custom-dark"
                    :options="{
                        renderSideBySide: true,
                        readOnly: false,
                        originalEditable: false,
                        minimap: { enabled: false },
                        automaticLayout: true,
                        scrollbar: {
                            verticalScrollbarSize: 10,
                            horizontalScrollbarSize: 10,
                        },
                    }"
                    class="absolute inset-0 h-full w-full"
                    @editorDidMount="handleDiffEditorMount"
                />
            </div>
        </div>
    </Transition>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

:deep(.monaco-diff-editor),
:deep(.monaco-editor),
:deep(.monaco-editor-background),
:deep(.monaco-editor .margin),
:deep(.inputarea),
:deep(.overflow-guard) {
    background-color: #0b0f1a !important;
}

/* Ensure the empty side of a new file diff doesn't show default light styling */
:deep(.monaco-diff-editor .editor.original) {
    background-color: #0b0f1a !important;
}
</style>
