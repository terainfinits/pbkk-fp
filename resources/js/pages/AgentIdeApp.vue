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

const props = withDefaults(defineProps<{ routes?: Partial<IdeRoutes> }>(), {
  routes: () => ({}),
});

const routes: IdeRoutes = { ...defaultRoutes, ...props.routes };
const api = createIdeApi(routes);
const state = useIdeState();

// Modals
const isSettingsOpen = ref(false);
const harnessOpen = ref(false);
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
    <button class="self-end px-4 py-1 text-sm" @click="harnessOpen = !harnessOpen">{{ harnessOpen ? 'Kembali ke chat' : 'Tugas coding' }}</button>

    <div class="flex-1 flex overflow-hidden">
      <SidebarExplorer />
      <EditorPane />
      <HarnessPanel v-if="harnessOpen" />
      <ChatPanel v-else />
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
