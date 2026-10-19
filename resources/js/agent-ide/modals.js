// "Create new file/folder" modal + upload modal + delete-confirm modal + diff-view drawer.

import { api } from './api.js';
import { dom } from './dom.js';
import { state } from './state.js';
import { showNotification } from './utils.js';

export function showDiffView(targetPath, newCode) {
    dom.diffDrawer.classList.remove('hidden');
    dom.btnToggleDiff.classList.remove('hidden');
    dom.diffCodeView.textContent = `--- Original (${targetPath})\n+++ Proposed AI Changes\n\n` + newCode;
}

export function initModals({ fetchTree, openFile, confirmDelete, cancelDelete, executeDelete }) {
    // ── Create modal ────────────────────────────────────────────────────
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

    // ── Upload modal ────────────────────────────────────────────────────
    const uploadModal = document.getElementById('upload-modal');
    const uploadModalFile = document.getElementById('upload-modal-file');
    const uploadModalDir = document.getElementById('upload-modal-dir');

    document.getElementById('btn-upload').addEventListener('click', () => {
        uploadModalFile.value = '';
        uploadModalDir.value = state.targetDirectory ? `${state.targetDirectory}` : '';
        uploadModal.classList.remove('hidden');
    });

    document.getElementById('btn-cancel-upload').addEventListener('click', () => {
        uploadModal.classList.add('hidden');
    });

    document.getElementById('btn-confirm-upload').addEventListener('click', async () => {
        const file = uploadModalFile.files[0];
        if (!file) {
            showNotification('Please select a file to upload', true);
            return;
        }
        try {
            const data = await api.uploadFile(file, uploadModalDir.value.trim());
            if (data.success) {
                uploadModal.classList.add('hidden');
                showNotification(data.message);
                await fetchTree();
            } else {
                showNotification(data.error || 'Upload failed', true);
            }
        } catch (err) {
            showNotification('Error uploading file', true);
        }
    });

    // ── Delete confirm modal ────────────────────────────────────────────
    document.getElementById('btn-cancel-delete').addEventListener('click', () => {
        cancelDelete();
    });

    document.getElementById('btn-confirm-delete').addEventListener('click', () => {
        executeDelete();
    });
}