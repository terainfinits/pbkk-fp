// Thin fetch wrapper around the routes handed over by the Blade view
// (window.IdeRoutes — see resources/views/agent-ide/index.blade.php).

import { dom } from './dom.js';

const routes = window.IdeRoutes || {};

async function postJson(url, body) {
    const res = await fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': dom.csrfToken,
        },
        body: JSON.stringify(body),
    });
    return res.json();
}

async function getJson(url) {
    const res = await fetch(url);
    return res.json();
}

export const api = {
    fetchTree: () => getJson(routes.tree),
    readFile: (path) => postJson(routes.fileRead, { path }),
    saveFile: (path, content) => postJson(routes.fileSave, { path, content }),
    createItem: (path, type, content = '') => postJson(routes.fileCreate, { path, type, content }),
    deleteItem: (path) => postJson(routes.fileDelete, { path }),
    fetchKernels: () => getJson(routes.kernels),
    runCode: (code, language, path) => postJson(routes.codeRun, { code, language, path }),
    promptAgent: (payload) => postJson(routes.agentPrompt, payload),
};
