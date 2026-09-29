// Entry point for the Agentic AI IDE. Loaded as a Vite module
// (see resources/views/agent-ide/index.blade.php) — module scripts are
// deferred by default, so the DOM is ready by the time this file runs.

import { dom } from './dom.js';
import { state } from './state.js';
import { showNotification } from './utils.js';
import { configureMarkdown } from './markdown.js';
import { initSettings } from './settings.js';
import { initFileTree, fetchTree, openFile, renderTabs, closeTab, openOrCreateTab } from './fileTree.js';
import { initEditor } from './editor.js';
import { initTerminal, fetchKernels, runCodeInKernel } from './terminal.js';
import { initModals } from './modals.js';
import { initChat } from './chat.js';

function runActiveCode() {
    const activeTab = state.openTabs.find((t) => t.path === state.activeTabPath);
    const code = dom.codeEditorInput.value;
    const path = activeTab ? activeTab.path : '';
    const language = activeTab ? activeTab.extension : 'php';

    if (!code || !code.trim()) {
        showNotification('Editor is empty. Write code to execute.', true);
        return;
    }

    runCodeInKernel(code, language, path);
}

configureMarkdown();
initSettings();
initFileTree();
initEditor({ runActiveCode, renderTabs, fetchTree, closeTab, openOrCreateTab });
initTerminal();
initModals({ fetchTree, openFile });
initChat();

fetchTree();
fetchKernels();
