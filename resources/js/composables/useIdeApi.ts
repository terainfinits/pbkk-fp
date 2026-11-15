// Centralized API client. Replaces all the inline `fetch(...)` calls in the Blade file.

export interface IdeRoutes {
  tree: string;
  fileRead: string;
  fileSave: string;
  fileCreate: string;
  fileDelete: string;
  fileRename: string;
  fileUpload: string;
  kernels: string;
  codeRun: string;
  agentPrompt: string;
  chats?: string;
  terminalExecute: string;
  terminalSession: string;
}

export const defaultRoutes: IdeRoutes = {
  tree: '/ide/api/tree',
  fileRead: '/ide/api/file/read',
  fileSave: '/ide/api/file/save',
  fileCreate: '/ide/api/file/create',
  fileDelete: '/ide/api/file/delete',
  fileRename: '/ide/api/file/rename',
  fileUpload: '/ide/api/file/upload',
  kernels: '/ide/api/kernels',
  codeRun: '/ide/api/code/run',
  agentPrompt: '/ide/api/agent/prompt',
  chats: '/ide/api/chats',
  terminalExecute: '/ide/api/terminal/execute',
  terminalSession: '/ide/api/terminal/session',
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
  if (ct.includes('application/json')) {
    const data = await res.json();
    if (!res.ok) {
      return { ...data, success: false, error: res.status === 419
        ? 'Sesi berakhir. Muat ulang halaman lalu coba kembali.'
        : data.error || data.message || `HTTP ${res.status}` } as T;
    }
    return data;
  }
  const text = await res.text();
  return { success: false, error: text || `HTTP ${res.status}` } as T;
}

async function del<T = any>(url: string): Promise<T> {
  const res = await fetch(url, {
    method: 'DELETE',
    headers: {
      'X-CSRF-TOKEN': csrf(),
      'Accept': 'application/json',
    },
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
  const chatsEndpoint = routes.chats || '/ide/api/chats';
  return {
    routes,
    fetchTree: () => get(routes.tree),
    readFile: (path: string) => post(routes.fileRead, { path }),
    saveFile: (path: string, content: string) => post(routes.fileSave, { path, content }),
    createFile: (path: string, type: 'file' | 'directory', content = '') =>
      post(routes.fileCreate, { path, type, content }),
renameFile: (path: string, newName: string) =>
    post(routes.fileRename, { path, newName }),
uploadFile: (file: File, targetDirectory: string) => {
    const formData = new FormData();
    formData.append('upload', file);
    formData.append('targetDirectory', targetDirectory || '');
    return fetch(routes.fileUpload, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrf(),
            'Accept': 'application/json',
        },
        body: formData,
    }).then(async (res) => {
        const ct = res.headers.get('content-type') || '';
        if (ct.includes('application/json')) {
            const data = await res.json();
            return { success: data.success ?? false, error: data.error ?? `HTTP ${res.status}` };
        }
        // If not JSON, give a concise status-based message instead of raw HTML dump
        const status = res.status;
        const concise = status === 413 ? 'File terlalu besar (batas upload terlampaui).'
            : status === 419 ? 'Sesi berakhir / CSRF mismatch.'
            : status === 400 ? 'Permintaan tidak valid.'
            : status === 401 ? 'Belum terautentikasi.'
            : status === 0 ? 'Tidak ada koneksi ke server.'
            : `HTTP ${status}`;
        return { success: false, error: concise };
    });
},
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
      sessionId?: number | null;
    }) => post(routes.agentPrompt, payload),
    fetchChatSessions: (search = '') =>
      get(`${chatsEndpoint}${search ? `?search=${encodeURIComponent(search)}` : ''}`),
    createChatSession: (title?: string, provider?: string, model?: string) =>
      post(chatsEndpoint, { title, provider, model }),
    fetchChatSession: (id: number) => get(`${chatsEndpoint}/${id}`),
    exportChatUrl: (id: number) => `${chatsEndpoint}/${id}/export`,
    deleteChatSession: (id: number) => del(`${chatsEndpoint}/${id}`),
    terminalExecute: (command: string, cwd = '') =>
      post(routes.terminalExecute, { command, cwd }),
        createTerminalSession: () =>
  post<TerminalSessionResponse>(routes.terminalSession, {}),
  };
}

export interface TerminalSessionResponse {
  session_id: string;
  ticket: string;
  ws_url: string;
  expires_in: number;
}

export type IdeApi = ReturnType<typeof createIdeApi>;
