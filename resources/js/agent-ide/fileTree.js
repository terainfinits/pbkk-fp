// Project file tree (left sidebar) + open-file tabs (editor pane).

import { api } from './api.js';
import { dom } from './dom.js';
import { state } from './state.js';
import { escapeHtml, showNotification } from './utils.js';
import { updateLineNumbers, updateCharCount } from './editor.js';

export function setTargetDirectory(dirPath) {
    state.targetDirectory = dirPath || '';
    const display = state.targetDirectory ? '/' + state.targetDirectory : '/ (Root)';
    dom.targetDirBadge.textContent = display;
    dom.activeTargetDisplay.textContent = display;
    dom.chatTargetFolder.textContent = display;
}

export async function fetchTree() {
    dom.fileTreeContainer.innerHTML = `
        <div class="p-4 text-center text-slate-500">
            <i class="fa-solid fa-circle-notch fa-spin mr-2"></i> Loading project tree...
        </div>
    `;
    try {
        const data = await api.fetchTree();
        if (data.success) {
            state.projectTree = data.tree;
            document.getElementById('workspace-root-name').textContent = data.root;
            renderTree(data.tree, dom.fileTreeContainer);
        }
    } catch (err) {
        dom.fileTreeContainer.innerHTML = `<div class="p-3 text-red-400 text-xs">Failed to load file tree.</div>`;
    }
}

export function getFileIcon(extension, filename) {
    const ext = (extension || '').toLowerCase();
    if (filename === 'package.json') return '<i class="fa-brands fa-npm text-red-400"></i>';
    if (filename === 'composer.json') return '<i class="fa-brands fa-php text-indigo-400"></i>';
    if (ext === 'php') return '<i class="fa-brands fa-php text-blue-400"></i>';
    if (ext === 'vue') return '<i class="fa-brands fa-vuejs text-emerald-400"></i>';
    if (ext === 'js' || ext === 'ts') return '<i class="fa-brands fa-js text-amber-300"></i>';
    if (ext === 'json') return '<i class="fa-solid fa-brackets-curly text-yellow-500"></i>';
    if (ext === 'css' || ext === 'scss') return '<i class="fa-brands fa-css3-alt text-cyan-400"></i>';
    if (ext === 'html' || ext === 'blade.php') return '<i class="fa-brands fa-html5 text-orange-400"></i>';
    if (ext === 'md') return '<i class="fa-brands fa-markdown text-sky-400"></i>';
    return '<i class="fa-regular fa-file-code text-slate-400"></i>';
}

export function renderTree(items, container, level = 0) {
    container.innerHTML = '';
    items.forEach((item) => {
        const row = document.createElement('div');
        row.className = 'flex items-center justify-between px-2 py-1 rounded hover:bg-slate-800/80 cursor-pointer group text-slate-300 transition text-[11px]';
        row.style.paddingLeft = `${level * 12 + 8}px`;

        const hasChildren = item.isDir && Array.isArray(item.children) && item.children.length > 0;

        // Inline rename mode: replace the name with an input.
        if (state.renamingPath === item.path) {
            row.innerHTML = `
                <div class="flex items-center gap-1.5 flex-1">
                    ${item.isDir
                        ? '<i class="fa-solid fa-folder text-amber-400 text-xs"></i>'
                        : (item.extension ? getFileIcon(item.extension, item.name) : '<i class="fa-regular fa-file-code text-slate-400"></i>')}
                    <input type="text" id="rename-input-${item.path.replace(/\//g, '-')}" value="${escapeHtml(state.renameValue)}"
                        class="flex-1 bg-slate-900 border border-indigo-500 rounded px-1.5 py-0.5 text-xs text-slate-200 outline-none">
                </div>
            `;
            container.appendChild(row);
            const input = row.querySelector('input');
            input.focus();
            input.select();

            // Enter = save, Escape = cancel.
            input.addEventListener('keydown', async (e) => {
                e.stopPropagation();
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const newName = input.value.trim();
                    if (newName && newName !== item.name) {
                        await commitRename(item.path, newName);
                    } else {
                        cancelRename();
                    }
                } else if (e.key === 'Escape') {
                    e.preventDefault();
                    cancelRename();
                }
            });
            // Click outside = cancel (single document listener removed after first interaction).
            const onOutside = (ev) => {
                if (!row.contains(ev.target)) {
                    document.removeEventListener('click', onOutside);
                    cancelRename();
                }
            };
            // Use setTimeout so the current click doesn't immediately cancel.
            setTimeout(() => document.addEventListener('click', onOutside), 0);
            return;
        }

        if (item.isDir) {
            row.innerHTML = `
                <div class="flex items-center gap-1.5 truncate flex-1">
                    ${hasChildren
                        ? '<i class="fa-solid fa-chevron-down text-[10px] text-slate-500 group-hover:text-slate-300 transition dir-arrow -rotate-90"></i>'
                        : '<span class="w-[10px] inline-block shrink-0"></span>'}
                    <i class="fa-solid fa-folder text-amber-400 text-xs"></i>
                    <span class="font-medium text-slate-200 truncate">${item.name}/</span>
                </div>
                <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition">
                    <button title="Set as AI Target Directory" class="btn-set-target px-1.5 py-0.5 rounded bg-indigo-950 text-indigo-400 hover:text-white text-[9px] border border-indigo-500/30">
                        Target
                    </button>
                    <button title="Rename" class="btn-rename px-1.5 py-0.5 rounded bg-slate-800 text-slate-400 hover:text-slate-200 text-[9px] border border-slate-700">
                        <i class="fa-solid fa-pen"></i>
                    </button>
                    <button title="Delete" class="btn-delete px-1.5 py-0.5 rounded bg-rose-950 text-rose-400 hover:text-white text-[9px] border border-rose-500/30">
                        <i class="fa-solid fa-trash-can"></i>
                    </button>
                </div>
            `;
            // ... existing folder click handler ...
            const childContainer = document.createElement('div');
            childContainer.className = 'hidden space-y-0.5';

            if (hasChildren) {
                row.addEventListener('click', (e) => {
                    if (e.target.closest('.btn-set-target')) {
                        setTargetDirectory(item.path);
                        showNotification(`Target folder set to /${item.path}`);
                        return;
                    }
                    if (e.target.closest('.btn-rename')) {
                        e.stopPropagation();
                        startRename(item.path, item.name);
                        return;
                    }
                    if (e.target.closest('.btn-delete')) {
                        e.stopPropagation();
                        confirmDelete(item.path, true);
                        return;
                    }
                    const isHidden = childContainer.classList.contains('hidden');
                    const arrow = row.querySelector('.dir-arrow');
                    if (isHidden) {
                        childContainer.classList.remove('hidden');
                        arrow.classList.remove('-rotate-90');
                    } else {
                        childContainer.classList.add('hidden');
                        arrow.classList.add('-rotate-90');
                    }
                });
            } else {
                row.addEventListener('click', (e) => {
                    if (e.target.closest('.btn-set-target')) {
                        setTargetDirectory(item.path);
                        showNotification(`Target folder set to /${item.path}`);
                    } else if (e.target.closest('.btn-rename')) {
                        e.stopPropagation();
                        startRename(item.path, item.name);
                    } else if (e.target.closest('.btn-delete')) {
                        e.stopPropagation();
                        confirmDelete(item.path, true);
                    }
                });
            }

            container.appendChild(row);
            container.appendChild(childContainer);
            if (item.children && item.children.length > 0) {
                renderTree(item.children, childContainer, level + 1);
            }
        } else {
            row.innerHTML = `
                <div class="flex items-center gap-1.5 truncate flex-1">
                    ${getFileIcon(item.extension, item.name)}
                    <span class="text-slate-300 truncate">${item.name}</span>
                </div>
                <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition">
                    <button title="Rename" class="btn-rename px-1.5 py-0.5 rounded bg-slate-800 text-slate-400 hover:text-slate-200 text-[9px] border border-slate-700">
                        <i class="fa-solid fa-pen"></i>
                    </button>
                    <button title="Delete" class="btn-delete px-1.5 py-0.5 rounded bg-rose-950 text-rose-400 hover:text-white text-[9px] border border-rose-500/30">
                        <i class="fa-solid fa-trash-can"></i>
                    </button>
                </div>
            `;
            row.addEventListener('click', (e) => {
                if (e.target.closest('.btn-rename')) {
                    e.stopPropagation();
                    startRename(item.path, item.name);
                    return;
                }
                if (e.target.closest('.btn-delete')) {
                    e.stopPropagation();
                    confirmDelete(item.path, false);
                    return;
                }
                openFile(item.path);
            });
            container.appendChild(row);
        }
    });
}

export async function openFile(filePath) {
    let tab = state.openTabs.find((t) => t.path === filePath);
    if (!tab) {
        try {
            const data = await api.readFile(filePath);
            if (data.success) {
                tab = {
                    path: data.path,
                    filename: data.filename,
                    content: data.content,
                    extension: data.extension,
                    isDirty: false,
                    isNew: false,
                };
                state.openTabs.push(tab);
            } else {
                showNotification('Failed to read file: ' + data.error, true);
                return;
            }
        } catch (err) {
            showNotification('Network error loading file', true);
            return;
        }
    }

    switchTab(tab.path);
    return tab;
}

export async function openOrCreateTab(filePath, initialContent = '') {
    const cleanPath = (filePath || '').replace(/\\/g, '/').replace(/^\/+/, '');
    let tab = state.openTabs.find((t) => t.path === cleanPath);
    if (!tab) {
        try {
            const data = await api.readFile(cleanPath);
            if (data.success) {
                tab = {
                    path: data.path,
                    filename: data.filename,
                    content: data.content,
                    extension: data.extension,
                    isDirty: false,
                    isNew: false,
                };
                state.openTabs.push(tab);
            } else {
                const filename = cleanPath.split('/').pop() || cleanPath;
                const ext = filename.includes('.') ? filename.split('.').pop() : 'txt';
                tab = {
                    path: cleanPath,
                    filename: filename,
                    content: initialContent,
                    extension: ext,
                    isDirty: true,
                    isNew: true,
                };
                state.openTabs.push(tab);
            }
        } catch (err) {
            const filename = cleanPath.split('/').pop() || cleanPath;
            const ext = filename.includes('.') ? filename.split('.').pop() : 'txt';
            tab = {
                path: cleanPath,
                filename: filename,
                content: initialContent,
                extension: ext,
                isDirty: true,
                isNew: true,
            };
            state.openTabs.push(tab);
        }
    }

    switchTab(tab.path);
    return tab;
}

export function renderTabs() {
    dom.tabsContainer.innerHTML = '';
    state.openTabs.forEach((tab) => {
        const tabEl = document.createElement('div');
        const isActive = tab.path === state.activeTabPath;
        const isPendingThis = state.pendingReview && state.pendingReview.targetPath === tab.path;

        tabEl.className = `flex items-center gap-2 px-3 py-1.5 rounded-t text-xs font-medium cursor-pointer border-t-2 transition ${isActive ? 'bg-[#0c101c] text-indigo-300 border-indigo-500' : 'bg-[#0d1322] text-slate-400 border-transparent hover:bg-slate-800'}`;

        tabEl.innerHTML = `
            ${getFileIcon(tab.extension, tab.filename)}
            <span class="truncate max-w-[120px]">${tab.filename}</span>
            ${isPendingThis ? '<span class="px-1.5 py-0.5 rounded bg-purple-500/20 text-purple-300 font-mono text-[9px] font-semibold border border-purple-500/30">AI Draft</span>' : ''}
            <span class="w-1.5 h-1.5 rounded-full ${tab.isDirty && !isPendingThis ? 'bg-amber-400' : 'hidden'}"></span>
            <button class="close-tab hover:text-red-400 ml-1 text-[11px]">&times;</button>
        `;

        tabEl.addEventListener('click', (e) => {
            if (e.target.classList.contains('close-tab')) {
                e.stopPropagation();
                closeTab(tab.path);
            } else {
                switchTab(tab.path);
            }
        });

        dom.tabsContainer.appendChild(tabEl);
    });
}

export function switchTab(path) {
    state.activeTabPath = path;
    const tab = state.openTabs.find((t) => t.path === path);
    if (tab) {
        dom.codeEditorInput.value = tab.content;
        dom.currentFilePath.textContent = tab.path;
        dom.fileLangBadge.textContent = (tab.extension || 'txt').toUpperCase();
        dom.chatActiveFile.textContent = tab.filename;
        dom.unsavedIndicator.classList.toggle('hidden', !tab.isDirty);
        updateLineNumbers();
        updateCharCount();
    } else {
        dom.codeEditorInput.value = '';
        dom.currentFilePath.textContent = 'No file selected';
        dom.fileLangBadge.textContent = 'TEXT';
        dom.chatActiveFile.textContent = 'None';
        dom.unsavedIndicator.classList.add('hidden');
    }

    if (dom.editorAgentReviewBar) {
        if (state.pendingReview && state.pendingReview.targetPath === path) {
            dom.reviewBarFilepath.textContent = path;
            dom.editorAgentReviewBar.classList.remove('hidden');
        } else {
            dom.editorAgentReviewBar.classList.add('hidden');
        }
    }

    renderTabs();
}

export function closeTab(path) {
    const idx = state.openTabs.findIndex((t) => t.path === path);
    if (idx !== -1) {
        state.openTabs.splice(idx, 1);
        if (state.activeTabPath === path) {
            const nextTab = state.openTabs[state.openTabs.length - 1];
            switchTab(nextTab ? nextTab.path : null);
        } else {
            renderTabs();
        }
    }
}

export function getActiveTab() {
    return state.openTabs.find((t) => t.path === state.activeTabPath);
}

export function initFileTree() {
    document.getElementById('btn-refresh-tree').addEventListener('click', fetchTree);
    document.getElementById('btn-reset-target-dir').addEventListener('click', () => setTargetDirectory(''));
}

// ── Inline rename ──────────────────────────────────────────────────────

export function startRename(path, currentName) {
    state.renamingPath = path;
    state.renameValue = currentName;
    renderTree(state.projectTree, dom.fileTreeContainer);
}

function cancelRename() {
    state.renamingPath = null;
    state.renameValue = '';
    renderTree(state.projectTree, dom.fileTreeContainer);
}

async function commitRename(oldPath, newName) {
    try {
        const data = await api.renameItem(oldPath, newName);
        if (data.success) {
            // Update any open tab whose path matched the old path.
            const tab = state.openTabs.find((t) => t.path === oldPath);
            if (tab) {
                tab.path = data.newPath;
                if (state.activeTabPath === oldPath) {
                    state.activeTabPath = data.newPath;
                    dom.currentFilePath.textContent = data.newPath;
                }
            }
            state.renamingPath = null;
            state.renameValue = '';
            await fetchTree();
            renderTabs();
            showNotification(`Renamed to ${newName}`);
        } else {
            showNotification(data.error || 'Rename failed', true);
            cancelRename();
        }
    } catch (err) {
        showNotification('Network error during rename', true);
        cancelRename();
    }
}

// ── Delete confirmation ────────────────────────────────────────────────

export function confirmDelete(path, isDir) {
    state.deleteTarget = { path, isDir };
    const name = path.split('/').pop();
    dom.deleteConfirmMessage.innerHTML = isDir
        ? `Delete folder <strong class="text-slate-200">${escapeHtml(name)}</strong> and all its contents? This cannot be undone.`
        : `Delete file <strong class="text-slate-200">${escapeHtml(name)}</strong>? This cannot be undone.`;
    dom.deleteConfirmModal.classList.remove('hidden');
}

export function cancelDelete() {
    state.deleteTarget = null;
    dom.deleteConfirmModal.classList.add('hidden');
}

export async function executeDelete() {
    if (!state.deleteTarget) return;
    const { path } = state.deleteTarget;
    cancelDelete();

    try {
        const data = await api.deleteItem(path);
        if (data.success) {
            // Close any tab that was viewing this file.
            const idx = state.openTabs.findIndex((t) => t.path === path);
            if (idx !== -1) {
                state.openTabs.splice(idx, 1);
                if (state.openTabs.length > 0) {
                    switchTab(state.openTabs[state.openTabs.length - 1].path);
                } else {
                    switchTab(null);
                }
            }
            await fetchTree();
            showNotification('Deleted successfully');
        } else {
            showNotification(data.error || 'Delete failed', true);
        }
    } catch (err) {
        showNotification('Network error during delete', true);
    }
}
