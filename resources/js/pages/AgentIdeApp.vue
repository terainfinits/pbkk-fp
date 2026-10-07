<script setup lang="ts">
import { computed, onMounted, provide, ref } from 'vue';
import HeaderNav from '@/components/agent-ide/HeaderNav.vue';
import SidebarExplorer from '@/components/agent-ide/SidebarExplorer.vue';
import EditorPane from '@/components/agent-ide/EditorPane.vue';
import ChatPanel from '@/components/agent-ide/ChatPanel.vue';
import SettingsModal from '@/components/agent-ide/SettingsModal.vue';
import CreateModal from '@/components/agent-ide/CreateModal.vue';
import ToastStack from '@/components/agent-ide/ToastStack.vue';
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
    <HeaderNav @open-settings="isSettingsOpen = true" />

    <div class="flex-1 flex overflow-hidden">
      <SidebarExplorer />
      <EditorPane />
      <ChatPanel />
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
