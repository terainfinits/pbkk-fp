// Code editor textarea: line numbers, char count, saving, shortcuts,
// and the in-editor Agent Changes review lifecycle (Accept / Reject / Diff).

import { api } from './api.js';
import { dom } from './dom.js';
import { state } from './state.js';
import { escapeHtml, showNotification } from './utils.js';

// Callbacks injected by initEditor() to avoid circular imports with fileTree.js
let renderTabsFn = () => {};
let fetchTreeFn = async () => {};
let closeTabFn = () => {};
let openOrCreateTabFn = async () => {};

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

/**
 * Automatically loads the agent-generated code into the text editor,
 * switches to that file's tab, and stages it for user review with
 * Accept / Reject controls directly in the text editor.
 */
export async function stageAgentChangesInEditor(targetPath, newCode) {
    if (!targetPath || typeof newCode !== 'string') return;

    const cleanPath = targetPath.replace(/\\/g, '/').replace(/^\/+/, '');

    // Check if the tab already existed in memory
    const existingTab = state.openTabs.find((t) => t.path === cleanPath);
    let originalCode = '';

    // Switch or create the tab
    const tab = await openOrCreateTabFn(cleanPath);
    if (existingTab) {
        originalCode = existingTab.content || '';
    } else if (tab && !tab.isNew) {
        originalCode = tab.content || '';
    }

    // Save pending review state
    state.pendingReview = {
        targetPath: cleanPath,
        originalCode,
        newCode,
        isNewTab: !!(tab && tab.isNew),
    };

    // Automatically write generated code into the active editor
    dom.codeEditorInput.value = newCode;
    if (tab) {
        tab.content = newCode;
        tab.isDirty = true;
    }

    updateLineNumbers();
    updateCharCount();
    renderTabsFn();

    // Show the in-editor review bar
    if (dom.editorAgentReviewBar && dom.reviewBarFilepath) {
        dom.reviewBarFilepath.textContent = cleanPath;
        dom.editorAgentReviewBar.classList.remove('hidden');
    }

    // Prepare diff view for immediate inspection if desired
    renderDiffContent(cleanPath, originalCode, newCode);
    if (dom.btnToggleDiff) {
        dom.btnToggleDiff.classList.remove('hidden');
    }

    showNotification(`Agent wrote changes to ${cleanPath}. Review and Accept/Reject in the editor.`);
}

/**
 * Permanently saves the agent-generated changes to disk and finishes the review.
 */
export async function acceptPendingChanges() {
    if (!state.pendingReview) return;

    const { targetPath, newCode } = state.pendingReview;
    dom.statusMessage.textContent = `Applying and saving changes to ${targetPath}...`;

    try {
        const data = await api.saveFile(targetPath, newCode);
        if (data.success) {
            const tab = state.openTabs.find((t) => t.path === targetPath);
            if (tab) {
                tab.content = newCode;
                tab.isDirty = false;
                delete tab.isNew;
            }

            dismissReviewState();
            renderTabsFn();
            await fetchTreeFn();
            showNotification(`Changes accepted and saved to ${targetPath}!`);
            dom.statusMessage.textContent = 'Saved at ' + new Date().toLocaleTimeString();
        } else {
            showNotification('Failed to save changes: ' + (data.error || 'Unknown error'), true);
        }
    } catch (err) {
        showNotification('Network error saving changes', true);
    }
}

/**
 * Reverts the text editor to the original content (or closes the new tab) and discards agent changes.
 */
export function rejectPendingChanges() {
    if (!state.pendingReview) return;

    const { targetPath, originalCode, isNewTab } = state.pendingReview;
    const tab = state.openTabs.find((t) => t.path === targetPath);

    if (isNewTab) {
        // Discard unsaved new file
        closeTabFn(targetPath);
    } else if (tab) {
        // Revert to original content
        tab.content = originalCode;
        tab.isDirty = false;
        if (state.activeTabPath === targetPath) {
            dom.codeEditorInput.value = originalCode;
            dom.unsavedIndicator.classList.add('hidden');
            updateLineNumbers();
            updateCharCount();
        }
        renderTabsFn();
    }

    dismissReviewState();
    showNotification(`Changes rejected. Reverted ${targetPath}.`);
}

function dismissReviewState() {
    state.pendingReview = null;
    if (dom.editorAgentReviewBar) {
        dom.editorAgentReviewBar.classList.add('hidden');
    }
    if (dom.diffDrawer) {
        dom.diffDrawer.classList.add('hidden');
    }
    if (dom.btnEditorDiffText) {
        dom.btnEditorDiffText.textContent = 'View Diff';
    }
}

export function toggleDiffView() {
    if (!dom.diffDrawer) return;

    const isHidden = dom.diffDrawer.classList.contains('hidden');
    if (isHidden) {
        dom.diffDrawer.classList.remove('hidden');
        if (dom.btnEditorDiffText) dom.btnEditorDiffText.textContent = 'Hide Diff';
    } else {
        dom.diffDrawer.classList.add('hidden');
        if (dom.btnEditorDiffText) dom.btnEditorDiffText.textContent = 'View Diff';
    }
}

function computeUnifiedDiffHtml(aLines, bLines) {
    const n = aLines.length;
    const m = bLines.length;

    if (aLines.join('\n') === bLines.join('\n')) {
        return '<div class="p-4 text-slate-400 text-xs italic">Original and generated code are identical.</div>';
    }

    // Standard LCS for diff
    const dp = Array.from({ length: n + 1 }, () => new Int32Array(m + 1));
    for (let i = 0; i < n; i++) {
        for (let j = 0; j < m; j++) {
            if (aLines[i] === bLines[j]) {
                dp[i + 1][j + 1] = dp[i][j] + 1;
            } else {
                dp[i + 1][j + 1] = Math.max(dp[i + 1][j], dp[i][j + 1]);
            }
        }
    }

    let i = n;
    let j = m;
    const diff = [];
    while (i > 0 || j > 0) {
        if (i > 0 && j > 0 && aLines[i - 1] === bLines[j - 1]) {
            diff.push({ type: 'same', text: aLines[i - 1] });
            i--; j--;
        } else if (j > 0 && (i === 0 || dp[i][j - 1] >= dp[i - 1][j])) {
            diff.push({ type: 'add', text: bLines[j - 1] });
            j--;
        } else if (i > 0 && (j === 0 || dp[i][j - 1] < dp[i - 1][j])) {
            diff.push({ type: 'del', text: aLines[i - 1] });
            i--;
        }
    }
    diff.reverse();

    return diff.map((item) => {
        if (item.type === 'add') {
            return `<div class="diff-line-add px-3 py-0.5 font-mono text-[11px]">+ ${escapeHtml(item.text)}</div>`;
        }
        if (item.type === 'del') {
            return `<div class="diff-line-del px-3 py-0.5 font-mono text-[11px]">- ${escapeHtml(item.text)}</div>`;
        }
        return `<div class="diff-line-same px-3 py-0.5 font-mono text-[11px]">  ${escapeHtml(item.text)}</div>`;
    }).join('');
}

export function renderDiffContent(targetPath, originalCode, newCode) {
    if (!dom.diffCodeView) return;

    const origLines = (originalCode || '').split('\n');
    const newLines = (newCode || '').split('\n');

    let headerHtml = `
        <div class="p-3 bg-slate-900 border-b border-slate-800 text-xs font-mono">
            <div class="text-indigo-400 font-bold mb-1"><i class="fa-solid fa-code-compare mr-1.5"></i> Target: ${escapeHtml(targetPath)}</div>
            <div class="text-slate-400 text-[11px]">
                <span class="text-rose-400 font-semibold mr-3">--- Original (${origLines.length} lines)</span>
                <span class="text-emerald-400 font-semibold">+++ Proposed AI Changes (${newLines.length} lines)</span>
            </div>
        </div>
    `;

    let bodyHtml = '';
    if (!originalCode) {
        bodyHtml = `
            <div class="p-2 text-emerald-400 text-xs font-semibold bg-emerald-950/20 border-b border-emerald-500/20 mb-1">
                <i class="fa-solid fa-plus-circle mr-1"></i> New file creation (+${newLines.length} lines)
            </div>
        ` + newLines.map((line) => `<div class="diff-line-add px-3 py-0.5 font-mono text-[11px]">+ ${escapeHtml(line)}</div>`).join('');
    } else {
        bodyHtml = computeUnifiedDiffHtml(origLines, newLines);
    }

    dom.diffCodeView.innerHTML = headerHtml + `<div class="py-2">${bodyHtml}</div>`;
}

export function initEditor({ runActiveCode, renderTabs, fetchTree, closeTab, openOrCreateTab }) {
    renderTabsFn = renderTabs;
    fetchTreeFn = fetchTree || (async () => {});
    closeTabFn = closeTab || (() => {});
    openOrCreateTabFn = openOrCreateTab || (async () => {});

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

    // In-editor review controls
    if (dom.btnEditorAccept) {
        dom.btnEditorAccept.addEventListener('click', acceptPendingChanges);
    }
    if (dom.btnEditorReject) {
        dom.btnEditorReject.addEventListener('click', rejectPendingChanges);
    }
    if (dom.btnEditorDiff) {
        dom.btnEditorDiff.addEventListener('click', toggleDiffView);
    }

    // Diff drawer controls
    if (dom.btnApplyDiff) {
        dom.btnApplyDiff.addEventListener('click', acceptPendingChanges);
    }
    if (dom.btnRejectDiff) {
        dom.btnRejectDiff.addEventListener('click', rejectPendingChanges);
    }
    if (dom.btnCloseDiff) {
        dom.btnCloseDiff.addEventListener('click', () => {
            dom.diffDrawer.classList.add('hidden');
            if (dom.btnEditorDiffText) dom.btnEditorDiffText.textContent = 'View Diff';
        });
    }
    if (dom.btnToggleDiff) {
        dom.btnToggleDiff.addEventListener('click', toggleDiffView);
    }

    window.addEventListener('keydown', (e) => {
        // Keyboard shortcuts for accepting/rejecting agent changes
        if (state.pendingReview) {
            if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
                e.preventDefault();
                acceptPendingChanges();
                return;
            }
            if (e.key === 'Escape') {
                e.preventDefault();
                rejectPendingChanges();
                return;
            }
        }

        if ((e.ctrlKey || e.metaKey) && e.key === 's') {
            e.preventDefault();
            saveCurrentFile();
        } else if (e.key === 'F5' || ((e.ctrlKey || e.metaKey) && e.key === 'Enter')) {
            e.preventDefault();
            runActiveCode();
        }
    });

    const btnHeaderRun = document.getElementById('btn-header-run');
    if (btnHeaderRun) {
        btnHeaderRun.addEventListener('click', runActiveCode);
    }
    const btnEditorRun = document.getElementById('btn-editor-run');
    if (btnEditorRun) {
        btnEditorRun.addEventListener('click', runActiveCode);
    }
}
