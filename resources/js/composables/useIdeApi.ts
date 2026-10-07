// Centralized API client. Replaces all the inline `fetch(...)` calls in the Blade file.

export interface IdeRoutes {
  tree: string;
  fileRead: string;
  fileSave: string;
  fileCreate: string;
  fileDelete: string;
  kernels: string;
  codeRun: string;
  agentPrompt: string;
  terminalExecute: string;
}

export const defaultRoutes: IdeRoutes = {
  tree: '/api/ide/tree',
  fileRead: '/api/ide/file/read',
  fileSave: '/api/ide/file/save',
  fileCreate: '/api/ide/file/create',
  fileDelete: '/api/ide/file/delete',
  kernels: '/api/ide/kernels',
  codeRun: '/api/ide/code/run',
  agentPrompt: '/api/ide/agent/prompt',
  terminalExecute: '/api/ide/terminal/execute',
};

function csrf(): string {
  if (typeof document === 'undefined') return '';
  return (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '';
}

async function post<T = any>(url: string, body: unknown): Promise<T> {
  const res = await fetch(url, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': csrf(),
      'Accept': 'application/json',
    },
    body: JSON.stringify(body),
  });
  const ct = res.headers.get('content-type') || '';
  if (ct.includes('application/json')) return res.json();
  const text = await res.text();
  return { success: false, error: text || `HTTP ${res.status}` } as T;
}

async function get<T = any>(url: string): Promise<T> {
  const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
  const ct = res.headers.get('content-type') || '';
  if (ct.includes('application/json')) return res.json();
  const text = await res.text();
  return { success: false, error: text || `HTTP ${res.status}` } as T;
}

export function createIdeApi(routes: IdeRoutes) {
  return {
    routes,
    fetchTree: () => get(routes.tree),
    readFile: (path: string) => post(routes.fileRead, { path }),
    saveFile: (path: string, content: string) => post(routes.fileSave, { path, content }),
    createFile: (path: string, type: 'file' | 'directory', content = '') =>
      post(routes.fileCreate, { path, type, content }),
    deleteFile: (path: string) => post(routes.fileDelete, { path }),
    fetchKernels: () => get(routes.kernels),
    runCode: (code: string, language: string, path = '') =>
      post(routes.codeRun, { code, language, path }),
    agentPrompt: (payload: {
      provider: string;
      model: string;
      apiKey: string;
      prompt: string;
      targetDirectory: string;
      targetFile: string;
      currentCode: string;
    }) => post(routes.agentPrompt, payload),
    terminalExecute: (command: string, cwd = '') =>
      post(routes.terminalExecute, { command, cwd }),
  };
}

export type IdeApi = ReturnType<typeof createIdeApi>;
