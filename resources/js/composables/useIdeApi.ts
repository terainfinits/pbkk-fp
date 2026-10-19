// Centralized API client. Replaces all the inline `fetch(...)` calls in the Blade file.

export interface IdeRoutes {
    tree: string;
    fileRead: string;
    fileSave: string;
    fileCreate: string;
    fileDelete: string;
    kernels: string;
    codeRun: string;
    pythonStart: string;
    pythonStatus: string;
    pythonInput: string;
    pythonStop: string;
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
    pythonStart: '/ide/api/code/python/start',
    pythonStatus: '/ide/api/code/python/status',
    pythonInput: '/ide/api/code/python/input',
    pythonStop: '/ide/api/code/python/stop',
    agentPrompt: '/api/ide/agent/prompt',
    terminalExecute: '/api/ide/terminal/execute',
};

function csrf(): string {
    if (typeof document === 'undefined') return '';
    return (
        (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)
            ?.content || ''
    );
}

async function parseResponse<T>(res: Response): Promise<T> {
    if (res.status === 401 || res.status === 419) {
        window.location.assign('/login');
        throw new Error('Your session expired. Please sign in again.');
    }
    const ct = res.headers.get('content-type') || '';
    if (ct.includes('application/json')) {
        const data = await res.json();
        if (!res.ok)
            return {
                ...data,
                success: false,
                error: data.error || data.message || `HTTP ${res.status}`,
            } as T;
        return data as T;
    }
    return {
        success: false,
        error: `Request failed (HTTP ${res.status}).`,
    } as T;
}

async function post<T = any>(url: string, body: unknown): Promise<T> {
    const res = await fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            ...(document.cookie.match(/(?:^|; )XSRF-TOKEN=([^;]*)/)
                ? {
                      'X-XSRF-TOKEN': decodeURIComponent(
                          document.cookie.match(
                              /(?:^|; )XSRF-TOKEN=([^;]*)/,
                          )![1],
                      ),
                  }
                : { 'X-CSRF-TOKEN': csrf() }),
            Accept: 'application/json',
        },
        body: JSON.stringify(body),
    });
    return parseResponse<T>(res);
}

async function get<T = any>(url: string): Promise<T> {
    const res = await fetch(url, { headers: { Accept: 'application/json' } });
    return parseResponse<T>(res);
}

export function createIdeApi(routes: IdeRoutes) {
    return {
        routes,
        fetchTree: () => get(routes.tree),
        readFile: (path: string) => post(routes.fileRead, { path }),
        saveFile: (path: string, content: string) =>
            post(routes.fileSave, { path, content }),
        createFile: (path: string, type: 'file' | 'directory', content = '') =>
            post(routes.fileCreate, { path, type, content }),
        deleteFile: (path: string) => post(routes.fileDelete, { path }),
        fetchKernels: () => get(routes.kernels),
        runCode: (code: string, language: string, path = '') =>
            post(routes.codeRun, { code, language, path }),
        startPython: (code: string) =>
            post<{
                success: boolean;
                runId?: string;
                message?: string;
                error?: string;
            }>(routes.pythonStart, { code }),
        pythonStatus: (runId: string) =>
            get<PythonRunStatus>(
                `${routes.pythonStatus}?${new URLSearchParams({ runId })}`,
            ),
        pythonInput: (runId: string, input: string) =>
            post<{ success: boolean; message?: string }>(routes.pythonInput, {
                runId,
                input,
            }),
        stopPython: (runId: string) =>
            post<{ success: boolean; message?: string }>(routes.pythonStop, {
                runId,
            }),
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

export interface PythonRunStatus {
    success: boolean;
    running: boolean;
    stdout: string;
    stderr: string;
    exitCode: number | null;
    executionTimeMs: number;
    message?: string;
}

export type IdeApi = ReturnType<typeof createIdeApi>;
