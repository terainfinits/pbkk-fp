<!-- Fitur: Halaman IDE dan navigasi
connect with ../../../app/Http/Controllers/AgentIdeController.php, ../app.ts, ../components/agent-ide/ChatPanel.vue
connect with ../components/agent-ide/CreateModal.vue, ../components/agent-ide/EditorPane.vue, ../components/agent-ide/FileDropZone.vue
connect with ../components/agent-ide/HarnessPanel.vue, ../components/agent-ide/HeaderNav.vue, ../components/agent-ide/SettingsModal.vue
connect with ../components/agent-ide/SidebarExplorer.vue, ../components/agent-ide/ToastStack.vue, ../composables/useIdeApi.ts
connect with ../composables/useIdeState.ts -->
<script setup lang="ts">
import { computed, onMounted, provide, ref } from 'vue';
import HeaderNav from '@/components/agent-ide/HeaderNav.vue';
import SidebarExplorer from '@/components/agent-ide/SidebarExplorer.vue';
import EditorPane from '@/components/agent-ide/EditorPane.vue';
import ChatPanel from '@/components/agent-ide/ChatPanel.vue';
import HarnessPanel from '@/components/agent-ide/HarnessPanel.vue';
import SettingsModal from '@/components/agent-ide/SettingsModal.vue';
import CreateModal from '@/components/agent-ide/CreateModal.vue';
import ToastStack from '@/components/agent-ide/ToastStack.vue';
import FileDropZone from '@/components/agent-ide/FileDropZone.vue';
import { createIdeApi, defaultRoutes, type IdeRoutes } from '@/composables/useIdeApi';
import { useIdeState } from '@/composables/useIdeState';

type Project = { id: string; name: string; workspace: number | null; reason: string | null };
const props = withDefaults(defineProps<{ projects: Project[]; owner: number; routes?: Partial<IdeRoutes> }>(), {
  routes: () => ({}),
});

const routes: IdeRoutes = { ...defaultRoutes, ...props.routes };
const storageKey = 'ide.project.' + props.owner;
const project = ref(props.projects.find(p => p.id === localStorage.getItem(storageKey)) ?? props.projects[0]);
const api = createIdeApi(routes, project.value.id);
const pending = ref('');
const saving = ref(false);
function changeProject(event: Event) {
  const select = event.target as HTMLSelectElement;
  pending.value = select.value;
  select.value = project.value.id;
  if (!state.openTabs.some(t => t.isDirty)) switchProject(false);
}
async function saveDirty() {
  for (const tab of state.openTabs.filter(t => t.isDirty)) {
    const content = tab.content;
    const result = await api.saveFile(tab.path, content);
    if (!result.success) throw new Error(result.error);
    if (tab.content !== content) throw new Error('Isi editor berubah saat menyimpan. Coba lagi.');
    tab.isDirty = false;
  }
}
async function switchProject(save: boolean) {
  saving.value = true;
  try {
    if (save) for (const t of state.openTabs.filter(t => t.isDirty)) {
      const result = await api.saveFile(t.path, t.content);
      if (!result.success) throw new Error(result.error);
      t.isDirty = false;
    }
    localStorage.setItem(storageKey, pending.value);
    location.reload();
  } catch (e) { state.pushToast(String(e), true); } finally { saving.value = false; }
}
async function refreshFiles(paths: string[]) {
  void refreshProject().catch(e => state.pushToast(String(e), true));
  window.dispatchEvent(new Event('ide:refresh-tree'));
  for (const t of state.openTabs.filter(t => paths.includes(t.path) && !t.isDirty)) {
    const result = await api.readFile(t.path);
    if (t.isDirty) continue;
    if (result.success) t.content = result.content;
    else if (result.error === 'File not found or access denied') {
      state.openTabs.splice(state.openTabs.indexOf(t), 1);
      if (state.activeTabPath.value === t.path) state.activeTabPath.value = state.openTabs[0]?.path ?? '';
    }
  }
}
async function refreshProject() {
  const response = await fetch('/ide/api/projects', {headers:{Accept:'application/json'}});
  if (response.ok) {
    const list: Project[] = await response.json();
    const updated = list.find(p => p.id === project.value.id);
    if (updated) project.value = updated;
  }
}
const state = useIdeState();

// Modals
const isSettingsOpen = ref(false);
const harness = ref<InstanceType<typeof HarnessPanel>>();
async function startAgent(text: string) { return await harness.value?.start(text) ?? false; }

const isCreateOpen = ref(false);
const createType = ref<'file' | 'folder'>('file');

const openCreateModal = (type: 'file' | 'folder') => {
  createType.value = type;
  isCreateOpen.value = true;
};

// Provide to descendants (avoids prop drilling for deep components)
provide('ide', {
  api,
  state,
  openCreateModal,
  openSettings: () => (isSettingsOpen.value = true),
});

onMounted(() => {
  if (typeof window !== 'undefined') {
    (window as any).IdeRoutes = routes;
  }
  state.statusMessage.value = 'Project ready';
});
</script>

<template>
  <div class="h-screen flex flex-col antialiased select-none bg-[#090d16] text-slate-200">
    <FileDropZone />
    <HeaderNav @open-settings="isSettingsOpen = true" />
    <header class="flex items-center justify-between px-4">
      <label>Proyek aktif <select :value="project.id" @change="changeProject" class="bg-slate-800">
        <option v-for="p in props.projects" :key="p.id" :value="p.id">{{ p.name }}</option>
      </select></label>
    </header>
    <dialog :open="!!pending" class="z-50 p-5 bg-slate-800 text-white" aria-label="Perubahan belum disimpan">
      <p>Ada perubahan yang belum disimpan.</p>
      <button :disabled="saving" @click="switchProject(true)">Simpan dan pindah</button>
      <button :disabled="saving" @click="switchProject(false)">Buang dan pindah</button>
      <button :disabled="saving" @click="pending = ''">Batal</button>
    </dialog>

    <div class="flex-1 flex overflow-hidden">
      <SidebarExplorer />
      <EditorPane />
      <section class="flex w-2/5 min-w-0 flex-col">
        <ChatPanel :blocked-reason="harness?.blockedReason ?? 'Menyiapkan proyek…'" :start-agent="startAgent" class="!w-full flex-1 min-h-0">
        <template #agent><HarnessPanel ref="harness" :key="project.id" :has-dirty="state.openTabs.some(t => t.isDirty)" :save-dirty="saveDirty" :project="project" @prepared="refreshProject" @applied="refreshFiles" class="!w-full flex-1 min-h-0" />
        </template></ChatPanel>
      </section>
    </div>

    <SettingsModal :is-open="isSettingsOpen" @close="isSettingsOpen = false" />
    <CreateModal
      :is-open="isCreateOpen"
      :type="createType"
      @close="isCreateOpen = false"
    />
    <ToastStack />
  </div>
</template>
