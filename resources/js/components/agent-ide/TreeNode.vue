<script setup lang="ts">
import { computed, ref } from 'vue';

interface TreeNodeItem {
  name: string;
  path?: string;
  isDir?: boolean;
  extension?: string;
  children?: TreeNodeItem[];
}

const props = defineProps<{ item: TreeNodeItem; level: number }>();
const emit = defineEmits<{ select: [path: string]; target: [path: string]; rename: [path: string]; delete: [path: string, isDir: boolean] }>();

// Folder expand/collapse state (local to this node)
const open = ref(false);

// A folder is only toggleable when it actually contains children
const hasChildren = computed(
  () => !!props.item.isDir && Array.isArray(props.item.children) && props.item.children.length > 0
);

function iconFor(ext: string, name: string) {
  const e = (ext || '').toLowerCase();
  if (name === 'package.json') return 'fa-brands fa-npm text-red-400';
  if (name === 'composer.json') return 'fa-brands fa-php text-indigo-400';
  if (e === 'php') return 'fa-brands fa-php text-blue-400';
  if (e === 'vue') return 'fa-brands fa-vuejs text-emerald-400';
  if (e === 'js' || e === 'ts') return 'fa-brands fa-js text-amber-300';
  if (e === 'json') return 'fa-solid fa-brackets-curly text-yellow-500';
  if (e === 'css' || e === 'scss') return 'fa-brands fa-css3-alt text-cyan-400';
  if (e === 'html' || e === 'blade.php') return 'fa-brands fa-html5 text-orange-400';
  if (e === 'md') return 'fa-brands fa-markdown text-sky-400';
  return 'fa-regular fa-file-code text-slate-400';
}
</script>

<template>
  <div>
    <div
      class="flex items-center justify-between px-2 py-1 rounded hover:bg-slate-800/80 cursor-pointer group text-slate-300 transition text-[11px]"
      :style="{ paddingLeft: `${level * 12 + 8}px` }"
      @click="item.isDir ? (open = !open) : emit('select', item.path!)"
    >
      <div class="flex items-center gap-1.5 truncate flex-1">
        <!-- Chevron is visible for every folder: it points down when collapsed and up when
             expanded. Empty folders get a dimmed, static chevron since there's nothing to toggle. -->
        <i
          v-if="item.isDir"
          class="fa-solid fa-chevron-down w-2.5 text-center text-[10px] transition-transform"
          :class="[
            hasChildren
              ? 'text-slate-500 group-hover:text-slate-300'
              : 'text-slate-700',
            open ? 'rotate-0!' : '-rotate-90',
          ]"
        ></i>
        <i
          :class="item.isDir ? 'fa-solid fa-folder text-amber-400 text-xs' : iconFor(item.extension || '', item.name)"
        ></i>
        <span
          :class="[item.isDir ? 'font-medium text-slate-200' : 'text-slate-300', !hasChildren && item.isDir ? 'opacity-70' : '']"
          class="truncate"
        >{{ item.name }}{{ item.isDir ? '/' : '' }}</span>
      </div>
      <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition">
        <button
          v-if="item.isDir"
          title="Set as AI Target Directory"
          class="px-1.5 py-0.5 rounded bg-indigo-950 text-indigo-400 hover:text-white text-[9px] border border-indigo-500/30"
          @click.stop="emit('target', item.path!)"
        >
          Target
        </button>
        <button
          title="Rename"
          class="px-1.5 py-0.5 rounded bg-slate-800 text-slate-400 hover:text-slate-200 text-[9px] border border-slate-700"
          @click.stop="emit('rename', item.path!)"
        >
          <i class="fa-solid fa-pen"></i>
        </button>
        <button
          title="Delete"
          class="px-1.5 py-0.5 rounded bg-rose-950 text-rose-400 hover:text-white text-[9px] border border-rose-500/30"
          @click.stop="emit('delete', item.path!, !!item.isDir)"
        >
          <i class="fa-solid fa-trash-can"></i>
        </button>
      </div>
    </div>
    <div v-if="item.isDir && open && item.children?.length" class="space-y-0.5">
      <TreeNode
        v-for="child in item.children"
        :key="child.path || child.name"
        :item="child"
        :level="level + 1"
        @select="(p) => emit('select', p)"
        @target="(p) => emit('target', p)"
      />
    </div>
  </div>
</template>
