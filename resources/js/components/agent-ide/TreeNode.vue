<script setup lang="ts">
import { ref } from 'vue';

interface TreeNodeItem {
  name: string;
  path?: string;
  isDir?: boolean;
  extension?: string;
  children?: TreeNodeItem[];
}

defineProps<{ item: TreeNodeItem; level: number }>();
const emit = defineEmits<{ select: [path: string]; target: [path: string] }>();

const open = ref(false);

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
        <i
          v-if="item.isDir"
          class="fa-solid fa-chevron-right text-[10px] text-slate-500 group-hover:text-slate-300 transition"
          :class="{ 'rotate-90': open }"
        ></i>
        <i
          :class="item.isDir ? 'fa-solid fa-folder text-amber-400 text-xs' : iconFor(item.extension || '', item.name)"
        ></i>
        <span :class="item.isDir ? 'font-medium text-slate-200' : 'text-slate-300'" class="truncate">{{ item.name }}</span>
      </div>
      <button
        v-if="item.isDir"
        title="Set as AI Target Directory"
        class="opacity-0 group-hover:opacity-100 px-1.5 py-0.5 rounded bg-indigo-950 text-indigo-400 hover:text-white text-[9px] border border-indigo-500/30"
        @click.stop="emit('target', item.path!)"
      >
        Target
      </button>
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
