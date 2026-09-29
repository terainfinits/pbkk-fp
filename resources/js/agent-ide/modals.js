// "Create new file/folder" modal + the diff-view drawer.

import { api } from './api.js';
import { dom } from './dom.js';
import { state } from './state.js';
import { showNotification } from './utils.js';

export function showDiffView(targetPath, newCode) {
    dom.diffDrawer.classList.remove('hidden');
    dom.btnToggleDiff.classList.remove('hidden');
    dom.diffCodeView.textContent = `--- Original (${targetPath})\n+++ Proposed AI Changes\n\n` + newCode;
}

export function initModals({ fetchTree, openFile }) {
    // Create modal
    const createModal = document.getElementById('create-modal');
    const createModalTitle = document.getElementById('create-modal-title');
    const createModalInput = document.getElementById('create-modal-input');

    document.getElementById('btn-new-file').addEventListener('click', () => {
        state.createModalType = 'file';
        createModalTitle.textContent = 'Create New File in Workspace';
        createModalInput.value = state.targetDirectory ? `${state.targetDirectory}/script.py` : 'script.py';
        createModal.classList.remove('hidden');
        createModalInput.focus();
    });

    document.getElementById('btn-new-folder').addEventListener('click', () => {
        state.createModalType = 'directory';
        createModalTitle.textContent = 'Create New Directory in Workspace';
        createModalInput.value = state.targetDirectory ? `${state.targetDirectory}/new-folder` : 'new-folder';
        createModal.classList.remove('hidden');
        createModalInput.focus();
    });

    document.getElementById('btn-cancel-create').addEventListener('click', () => {
        createModal.classList.add('hidden');
    });

    document.getElementById('btn-confirm-create').addEventListener('click', async () => {
        const path = createModalInput.value.trim();
        if (!path) return;

        try {
            const data = await api.createItem(path, state.createModalType, '');
            if (data.success) {
                createModal.classList.add('hidden');
                showNotification(data.message);
                await fetchTree();
                if (state.createModalType === 'file') {
                    openFile(path);
                }
            } else {
                showNotification(data.error || 'Failed to create item', true);
            }
        } catch (err) {
            showNotification('Error creating item', true);
        }
    });
}
