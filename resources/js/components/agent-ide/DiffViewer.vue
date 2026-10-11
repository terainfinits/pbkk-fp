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

watch(() => props.modifiedCode, (val) => { currentModified.value = val; });

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
      }
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
        } catch(e) { /* ignore */ }
      }, 50);
    }
  }
);

function handleApply() {
  emit('apply', {
    code: currentModified.value,
    targetPath: props.targetPath
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
      class="absolute inset-0 z-50 bg-[#0b0f19]/95 backdrop-blur-sm flex flex-col border-t border-slate-800"
    >
      <!-- Header -->
      <div class="h-10 bg-[#0e1424] border-b border-slate-800 flex items-center justify-between px-4 shrink-0">
        <div class="flex items-center gap-2 text-xs font-semibold text-slate-200">
          <i class="fa-solid fa-code-compare text-purple-400"></i>
          <span>Diff View</span>
          <span
            v-if="targetPath"
            class="text-[10px] font-mono px-2 py-0.5 rounded border"
            :class="isNewFile
              ? 'text-emerald-400 bg-emerald-500/10 border-emerald-500/30'
              : 'text-indigo-400 bg-indigo-500/10 border-indigo-500/30'"
          >
            <i :class="isNewFile ? 'fa-solid fa-plus mr-1' : 'fa-solid fa-pen-to-square mr-1'"></i>
            {{ isNewFile ? 'New File' : 'Modified' }}: {{ targetPath }}
          </span>
        </div>
        <div class="flex items-center gap-2">
          <button
            @click="handleApply"
            class="px-3 py-1.5 rounded bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-semibold text-[11px] flex items-center gap-1.5 shadow transition"
          >
            <i class="fa-solid fa-check"></i>
            {{ isNewFile ? 'Create File' : 'Apply Changes' }}
          </button>
          <button
            @click="handleClose"
            class="w-7 h-7 rounded flex items-center justify-center text-slate-400 hover:text-red-400 hover:bg-red-500/10 transition"
          >
            <i class="fa-solid fa-xmark"></i>
          </button>
        </div>
      </div>

      <!-- Legend -->
      <div class="h-6 bg-[#0d1220] border-b border-slate-800/60 px-4 flex items-center gap-4 text-[10px] font-mono shrink-0">
        <span class="flex items-center gap-1.5 text-red-400">
          <span class="w-2 h-2 rounded-sm bg-red-500/50 border border-red-500/30"></span>
          {{ isNewFile ? 'Empty (New)' : 'Original (Left)' }}
        </span>
        <span class="flex items-center gap-1.5 text-emerald-400">
          <span class="w-2 h-2 rounded-sm bg-emerald-500/50 border border-emerald-500/30"></span>
          AI Generated (Right)
        </span>
      </div>

      <!-- Monaco Diff Container -->
      <div class="flex-1 w-full overflow-hidden relative bg-[#0b0f1a]">
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
            }
          }"
          class="absolute inset-0 w-full h-full"
          @editorDidMount="handleDiffEditorMount"
        />
      </div>
    </div>
  </Transition>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

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
