// Central cache of DOM element references used across modules.
// Grabbed once at module-load time (module scripts are deferred, so the
// DOM is already parsed by the time this file executes).

export const dom = {
    csrfToken: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),

    selectProvider: document.getElementById('select-provider'),
    selectModel: document.getElementById('select-model'),
    modelBadge: document.getElementById('model-badge'),

    fileTreeContainer: document.getElementById('file-tree-container'),
    tabsContainer: document.getElementById('tabs-container'),
    codeEditorInput: document.getElementById('code-editor-input'),
    lineNumbers: document.getElementById('line-numbers'),
    currentFilePath: document.getElementById('current-filepath'),
    fileLangBadge: document.getElementById('file-language-badge'),
    unsavedIndicator: document.getElementById('unsaved-indicator'),
    statusMessage: document.getElementById('status-message'),
    statusChars: document.getElementById('status-chars'),

    targetDirBadge: document.getElementById('target-dir-badge'),
    activeTargetDisplay: document.getElementById('active-target-display'),
    chatTargetFolder: document.getElementById('chat-target-folder'),
    chatActiveFile: document.getElementById('chat-active-file'),

    agentPromptInput: document.getElementById('agent-prompt-input'),
    btnSubmitPrompt: document.getElementById('btn-submit-prompt'),
    agentTimeline: document.getElementById('agent-timeline'),
    agentStatusLabel: document.getElementById('agent-status-label'),

    btnSaveFile: document.getElementById('btn-save-file'),
    btnToggleDiff: document.getElementById('btn-toggle-diff'),
    diffDrawer: document.getElementById('diff-drawer'),
    diffCodeView: document.getElementById('diff-code-view'),
    btnApplyDiff: document.getElementById('btn-apply-diff'),
    btnCloseDiff: document.getElementById('btn-close-diff'),

    terminalDrawer: document.getElementById('terminal-drawer'),
    terminalOutputBody: document.getElementById('terminal-output-body'),
    btnToggleTerminal: document.getElementById('btn-toggle-terminal'),
    btnClearTerminal: document.getElementById('btn-clear-terminal'),
    terminalToggleIcon: document.getElementById('terminal-toggle-icon'),
    terminalKernelBadge: document.getElementById('terminal-kernel-badge'),
    terminalExecTime: document.getElementById('terminal-exec-time'),
};
