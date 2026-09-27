// Code editor textarea: line numbers, char count, saving, and shortcuts.

import { api } from './api.js';
import { dom } from './dom.js';
import { state } from './state.js';
import { showNotification } from './utils.js';

// Set once by initEditor() to avoid a circular import with fileTree.js
// (fileTree.js needs updateLineNumbers/updateCharCount from this module,
// this module needs renderTabs from fileTree.js).
let renderTabsFn = () => {};

export function updateLineNumbers() {
    const lines = dom.codeEditorInput.value.split('\n').length;
    let numHtml = '';
    for (let i = 1; i <= Math.max(lines, 1); i++) {
        numHtml += `${i}<br>`;
    }
    dom.lineNumbers.innerHTML = numHtml;
}

export function updateCharCount() {
    dom.statusChars.textContent = `${dom.codeEditorInput.value.length} chars`;
}

export async function saveCurrentFile() {
    const activeTab = state.openTabs.find((t) => t.path === state.activeTabPath);
    if (!activeTab) {
        showNotification('No active file to save', true);
        return;
    }

    try {
        dom.statusMessage.textContent = 'Saving file...';
        const data = await api.saveFile(activeTab.path, dom.codeEditorInput.value);
        if (data.success) {
            activeTab.isDirty = false;
            dom.unsavedIndicator.classList.add('hidden');
            renderTabsFn();
            dom.statusMessage.textContent = 'Saved at ' + new Date().toLocaleTimeString();
            showNotification(`File saved: ${activeTab.path}`);
        } else {
            showNotification('Save failed: ' + data.error, true);
        }
    } catch (err) {
        showNotification('Error saving file', true);
    }
}

export function initEditor({ runActiveCode, renderTabs }) {
    renderTabsFn = renderTabs;

    dom.codeEditorInput.addEventListener('input', () => {
        const activeTab = state.openTabs.find((t) => t.path === state.activeTabPath);
        if (activeTab) {
            activeTab.content = dom.codeEditorInput.value;
            activeTab.isDirty = true;
            dom.unsavedIndicator.classList.remove('hidden');
            renderTabsFn();
        }
        updateLineNumbers();
        updateCharCount();
    });

    dom.codeEditorInput.addEventListener('scroll', () => {
        dom.lineNumbers.scrollTop = dom.codeEditorInput.scrollTop;
    });

    dom.codeEditorInput.addEventListener('keydown', (e) => {
        if (e.key === 'Tab') {
            e.preventDefault();
            const start = dom.codeEditorInput.selectionStart;
            const end = dom.codeEditorInput.selectionEnd;
            dom.codeEditorInput.value = dom.codeEditorInput.value.substring(0, start) + '    ' + dom.codeEditorInput.value.substring(end);
            dom.codeEditorInput.selectionStart = dom.codeEditorInput.selectionEnd = start + 4;
            dom.codeEditorInput.dispatchEvent(new Event('input'));
        }
    });

    dom.btnSaveFile.addEventListener('click', saveCurrentFile);

    window.addEventListener('keydown', (e) => {
        if ((e.ctrlKey || e.metaKey) && e.key === 's') {
            e.preventDefault();
            saveCurrentFile();
        } else if (e.key === 'F5' || ((e.ctrlKey || e.metaKey) && e.key === 'Enter')) {
            e.preventDefault();
            runActiveCode();
        }
    });

    document.getElementById('btn-header-run').addEventListener('click', runActiveCode);
    document.getElementById('btn-editor-run').addEventListener('click', runActiveCode);
}
