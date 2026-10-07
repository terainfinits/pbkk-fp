import { reactive, ref } from 'vue';

export interface OpenTab {
  path: string;
  filename: string;
  content: string;
  extension: string;
  isDirty: boolean;
}

export interface Toast {
  id: number;
  message: string;
  isError: boolean;
}

export interface KernelInfo {
  available: boolean;
  version: string;
}

export interface KernelsResponse {
  python?: KernelInfo;
  php?: KernelInfo;
  node?: KernelInfo;
}

export function useIdeState() {
  const openTabs = reactive<OpenTab[]>([]);
  const activeTabPath = ref<string | null>(null);
  const targetDirectory = ref('');
  const workspaceName = ref('PBKK-FP');
  const projectTree = ref<any[]>([]);
  const lastGeneratedCode = ref('');
  const lastGeneratedTarget = ref('');
  const statusMessage = ref('Project ready');
  const agentStatusLabel = ref('Agent Engine Ready');
  const kernels = ref<KernelsResponse>({});
  const toasts = ref<Toast[]>([]);

  let toastSeq = 0;
  function pushToast(message: string, isError = false) {
    const id = ++toastSeq;
    toasts.value.push({ id, message, isError });
    setTimeout(() => {
      toasts.value = toasts.value.filter(t => t.id !== id);
    }, 3500);
  }

  return {
    openTabs,
    activeTabPath,
    targetDirectory,
    workspaceName,
    projectTree,
    lastGeneratedCode,
    lastGeneratedTarget,
    statusMessage,
    agentStatusLabel,
    kernels,
    toasts,
    pushToast,
  };
}
