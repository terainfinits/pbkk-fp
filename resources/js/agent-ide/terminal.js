// Interactive terminal module — PowerShell-like shell inside the IDE.
// Supports command execution, `cd` navigation, command history (↑/↓),
// `cls`/`clear` screen clearing, and tab switching between Shell & Kernel.

import { api } from './api.js';
import { dom } from './dom.js';
import { escapeHtml, showNotification } from './utils.js';

// ── State ──────────────────────────────────────────────────────────────
let isTerminalMinimized = false;
let activeTab = 'shell';        // 'shell' | 'kernel'
let currentCwd = '';            // relative to basePath
let commandHistory = [];
let historyIndex = -1;
let isExecuting = false;

// We keep separate output buffers so switching tabs doesn't destroy content.
let shellOutputHtml = '';
let kernelOutputHtml = '';

// ── Public helpers ─────────────────────────────────────────────────────

export function openTerminalDrawer() {
    if (isTerminalMinimized) {
        isTerminalMinimized = false;
        dom.terminalDrawer.style.height = '220px';
        dom.terminalToggleIcon.className = 'fa-solid fa-chevron-down text-[10px]';
    }
}

// ── Kernel detection (unchanged) ───────────────────────────────────────

export async function fetchKernels() {
    try {
        const data = await api.fetchKernels();
        if (data.success && data.kernels) {
            const k = data.kernels;
            const available = [];
            if (k.python?.available) available.push(`🐍 ${k.python.version.split(' ')[0]} ${k.python.version.split(' ')[1] || ''}`);
            if (k.php?.available) available.push(`🐘 ${k.php.version.split(' ')[0]} ${k.php.version.split(' ')[1] || ''}`);
            if (k.node?.available) available.push(`🟢 ${k.node.version.split(' ')[0]} ${k.node.version.split(' ')[1] || ''}`);

            const headerText = available.join(' | ') || 'PHP Engine Active';
            document.getElementById('header-kernel-list').textContent = headerText;
            document.getElementById('kernel-badge-text').textContent = available.length > 0 ? available[0] : 'PHP / Python';
            dom.terminalKernelBadge.textContent = available.join(' • ') || 'Kernel Engine Ready';
        }
    } catch (err) {
        document.getElementById('header-kernel-list').textContent = 'PHP / Python Engine';
    }
}

// ── Run code in kernel (called from editor / chat) ─────────────────────

export async function runCodeInKernel(codeContent, language, filePath = '', targetConsole = null) {
    openTerminalDrawer();
    switchToTab('kernel');

    const execLang = language || (filePath ? filePath.split('.').pop() : 'php');

    appendToKernel(`<div class="text-indigo-300 font-bold flex items-center gap-2 mt-2 pt-2 border-t border-slate-800/80">
        <span class="text-emerald-400">➜</span>
        <span>Executing ${escapeHtml(execLang.toUpperCase())} kernel${filePath ? ' (' + escapeHtml(filePath) + ')' : ''}...</span>
    </div>`);

    if (targetConsole) {
        targetConsole.innerHTML = `<div class="text-indigo-400 animate-pulse"><i class="fa-solid fa-spinner fa-spin mr-1"></i> Running snippet in ${escapeHtml(execLang)} kernel...</div>`;
    }

    try {
        const data = await api.runCode(codeContent, execLang, filePath);

        if (data.success) {
            dom.terminalKernelBadge.textContent = `${data.kernel} (${data.executable})`;
            dom.terminalExecTime.textContent = `${data.executionTimeMs}ms`;
            dom.terminalExecTime.classList.remove('hidden');

            let outputHtml = '';
            let chatOutputHtml = '';

            if (data.stdout && data.stdout.trim()) {
                outputHtml += `<pre class="text-emerald-300 whitespace-pre-wrap leading-5">${escapeHtml(data.stdout)}</pre>`;
                chatOutputHtml += `<pre class="text-emerald-300 text-[10px] font-mono whitespace-pre-wrap">${escapeHtml(data.stdout)}</pre>`;
            }
            if (data.stderr && data.stderr.trim()) {
                outputHtml += `<pre class="text-red-400 whitespace-pre-wrap leading-5">${escapeHtml(data.stderr)}</pre>`;
                chatOutputHtml += `<pre class="text-red-400 text-[10px] font-mono whitespace-pre-wrap">${escapeHtml(data.stderr)}</pre>`;
            }
            if (!data.stdout && !data.stderr) {
                outputHtml += `<div class="text-slate-500 italic">[Process exited with code ${data.exitCode} (No output)]</div>`;
                chatOutputHtml += `<div class="text-slate-500 italic text-[10px]">[Done in ${data.executionTimeMs}ms]</div>`;
            }

            const statusBadge = `<div class="text-[10px] text-slate-400 flex items-center gap-3 mt-1">
                <span class="${data.exitCode === 0 ? 'text-emerald-400 font-semibold' : 'text-red-400 font-semibold'}">Exit Code: ${data.exitCode}</span>
                <span>Time: ${data.executionTimeMs}ms</span>
            </div>`;

            appendToKernel(`<div class="pl-3 border-l-2 border-slate-700 my-1 space-y-1">${outputHtml}${statusBadge}</div>`);

            if (targetConsole) {
                targetConsole.innerHTML = `
                    <div class="p-2.5 rounded bg-slate-950 border border-emerald-500/30 font-mono text-[11px] space-y-1">
                        <div class="flex items-center justify-between text-[10px] text-emerald-400 font-semibold border-b border-slate-800 pb-1 mb-1">
                            <span><i class="fa-solid fa-terminal mr-1"></i> Kernel Output (${escapeHtml(data.kernel)})</span>
                            <span>${data.executionTimeMs}ms</span>
                        </div>
                        ${chatOutputHtml || '<div class="text-slate-500">[Completed without output]</div>'}
                    </div>
                `;
            }

            showNotification(`Code executed in ${data.kernel} (${data.executionTimeMs}ms)`);
        } else {
            appendToKernel(`<div class="text-red-400 pl-3 border-l-2 border-red-500 my-1">Error: ${escapeHtml(data.error || 'Execution failed')}</div>`);
            if (targetConsole) {
                targetConsole.innerHTML = `<div class="text-red-400 text-[10px] font-mono">Execution failed: ${escapeHtml(data.error)}</div>`;
            }
        }
    } catch (err) {
        appendToKernel(`<div class="text-red-400 pl-3 border-l-2 border-red-500 my-1">Execution network exception.</div>`);
        if (targetConsole) {
            targetConsole.innerHTML = `<div class="text-red-400 text-[10px]">Execution exception.</div>`;
        }
    }
}

// ── Interactive shell command execution ─────────────────────────────────

async function executeShellCommand(command) {
    if (!command.trim() || isExecuting) return;

    isExecuting = true;

    // Push to history
    if (commandHistory.length === 0 || commandHistory[commandHistory.length - 1] !== command) {
        commandHistory.push(command);
    }
    historyIndex = commandHistory.length;

    const cwdLabel = currentCwd ? `~/${currentCwd}` : '~';

    // Show the command in output
    appendToShell(`<div class="flex items-start gap-2 mt-1">
        <span class="text-cyan-400 font-bold shrink-0">PS ${escapeHtml(cwdLabel)}></span>
        <span class="text-slate-100">${escapeHtml(command)}</span>
    </div>`);

    // Handle client-side clear
    if (['cls', 'clear'].includes(command.trim().toLowerCase())) {
        clearShellOutput();
        isExecuting = false;
        dom.terminalInput.focus();
        return;
    }

    try {
        const data = await api.executeCommand(command, currentCwd);

        if (data.success) {
            // Handle `cls`/`clear` returned by server
            if (data.stdout === '__CLEAR__') {
                clearShellOutput();
                isExecuting = false;
                dom.terminalInput.focus();
                return;
            }

            // Update cwd if server changed it (cd command)
            if (data.cwd !== undefined) {
                currentCwd = data.cwd || '';
                updatePromptCwd();
            }

            // Render stdout
            if (data.stdout && data.stdout.trim()) {
                appendToShell(`<pre class="text-slate-300 whitespace-pre-wrap leading-5 ml-0">${escapeHtml(data.stdout)}</pre>`);
            }
            // Render stderr
            if (data.stderr && data.stderr.trim()) {
                appendToShell(`<pre class="text-amber-400 whitespace-pre-wrap leading-5 ml-0">${escapeHtml(data.stderr)}</pre>`);
            }

            // Show exit code if non-zero (and there was output)
            if (data.exitCode !== 0) {
                appendToShell(`<div class="text-[10px] text-red-400 mt-0.5">Exit code: ${data.exitCode} (${data.executionTimeMs}ms)</div>`);
            }
        } else {
            appendToShell(`<div class="text-red-400">${escapeHtml(data.error || 'Command execution failed.')}</div>`);
        }
    } catch (err) {
        appendToShell(`<div class="text-red-400">Network error — could not reach server.</div>`);
    }

    isExecuting = false;
    dom.terminalInput.focus();
}

// ── Output helpers ─────────────────────────────────────────────────────

function appendToShell(html) {
    const el = document.createElement('div');
    el.innerHTML = html;
    dom.terminalOutputBody.appendChild(el);
    dom.terminalOutputBody.scrollTop = dom.terminalOutputBody.scrollHeight;
    // Cache
    shellOutputHtml = dom.terminalOutputBody.innerHTML;
}

function appendToKernel(html) {
    // If we're on the kernel tab, append directly
    if (activeTab === 'kernel') {
        const el = document.createElement('div');
        el.innerHTML = html;
        dom.terminalOutputBody.appendChild(el);
        dom.terminalOutputBody.scrollTop = dom.terminalOutputBody.scrollHeight;
        kernelOutputHtml = dom.terminalOutputBody.innerHTML;
    } else {
        // Buffer it
        kernelOutputHtml += html;
    }
}

function clearShellOutput() {
    const welcomeMsg = `<div class="text-slate-500 flex items-center gap-2">
        <span class="text-cyan-400 font-bold">PS></span>
        <span>Console cleared. Ready for commands.</span>
    </div>`;

    if (activeTab === 'shell') {
        dom.terminalOutputBody.innerHTML = welcomeMsg;
    }
    shellOutputHtml = welcomeMsg;
}

function updatePromptCwd() {
    const label = currentCwd ? `PS ~/${currentCwd}>` : 'PS ~>';
    dom.terminalPromptCwd.textContent = label;
}

// ── Tab switching ──────────────────────────────────────────────────────

function switchToTab(tabName) {
    if (activeTab === tabName) return;

    // Save current output
    if (activeTab === 'shell') {
        shellOutputHtml = dom.terminalOutputBody.innerHTML;
    } else {
        kernelOutputHtml = dom.terminalOutputBody.innerHTML;
    }

    activeTab = tabName;

    // Toggle tab button styles
    const shellTab = dom.terminalTabShell;
    const kernelTab = dom.terminalTabKernel;
    const inputBar = document.getElementById('terminal-input-bar');

    if (tabName === 'shell') {
        shellTab.className = 'terminal-tab px-3 py-1 rounded-t flex items-center gap-1.5 text-slate-200 font-semibold bg-[#080c14] border border-slate-700 border-b-0 -mb-px relative z-10 transition';
        kernelTab.className = 'terminal-tab px-3 py-1 rounded-t flex items-center gap-1.5 text-slate-500 hover:text-slate-300 bg-transparent border border-transparent transition';
        dom.terminalOutputBody.innerHTML = shellOutputHtml || getShellWelcome();
        inputBar.style.display = 'flex';
        dom.terminalInput.focus();
    } else {
        kernelTab.className = 'terminal-tab px-3 py-1 rounded-t flex items-center gap-1.5 text-slate-200 font-semibold bg-[#080c14] border border-slate-700 border-b-0 -mb-px relative z-10 transition';
        shellTab.className = 'terminal-tab px-3 py-1 rounded-t flex items-center gap-1.5 text-slate-500 hover:text-slate-300 bg-transparent border border-transparent transition';
        dom.terminalOutputBody.innerHTML = kernelOutputHtml || getKernelWelcome();
        inputBar.style.display = 'none';
    }

    dom.terminalOutputBody.scrollTop = dom.terminalOutputBody.scrollHeight;
}

function getShellWelcome() {
    return `<div class="text-slate-500 flex items-center gap-2">
        <span class="text-cyan-400 font-bold">PS></span>
        <span>Windows PowerShell — type commands below. Supports <span class="text-emerald-400">python</span>, <span class="text-indigo-400">php</span>, <span class="text-yellow-400">node</span>, <span class="text-slate-300">dir</span>, <span class="text-slate-300">cd</span>, and all PowerShell commands.</span>
    </div>`;
}

function getKernelWelcome() {
    return `<div class="text-slate-500 flex items-center gap-2">
        <span class="text-emerald-400 font-bold">➜</span>
        <span>Magentic Kernel Console initialized. Click "Run Code" or execute snippets in Chatbot to run Python, PHP, or Node.js scripts.</span>
    </div>`;
}

// ── Initialization ─────────────────────────────────────────────────────

export function initTerminal() {
    // Toggle minimize/restore
    dom.btnToggleTerminal.addEventListener('click', () => {
        isTerminalMinimized = !isTerminalMinimized;
        if (isTerminalMinimized) {
            dom.terminalDrawer.style.height = '32px';
            dom.terminalToggleIcon.className = 'fa-solid fa-chevron-up text-[10px]';
        } else {
            dom.terminalDrawer.style.height = '220px';
            dom.terminalToggleIcon.className = 'fa-solid fa-chevron-down text-[10px]';
        }
    });

    // Clear button
    dom.btnClearTerminal.addEventListener('click', () => {
        if (activeTab === 'shell') {
            clearShellOutput();
        } else {
            dom.terminalOutputBody.innerHTML = getKernelWelcome();
            kernelOutputHtml = '';
        }
    });

    // Tab switching
    dom.terminalTabShell.addEventListener('click', () => switchToTab('shell'));
    dom.terminalTabKernel.addEventListener('click', () => switchToTab('kernel'));

    // Keyboard handler for the terminal input
    dom.terminalInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            const command = dom.terminalInput.value;
            dom.terminalInput.value = '';
            executeShellCommand(command);
        }

        // History navigation
        if (e.key === 'ArrowUp') {
            e.preventDefault();
            if (commandHistory.length > 0 && historyIndex > 0) {
                historyIndex--;
                dom.terminalInput.value = commandHistory[historyIndex];
            }
        }
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            if (historyIndex < commandHistory.length - 1) {
                historyIndex++;
                dom.terminalInput.value = commandHistory[historyIndex];
            } else {
                historyIndex = commandHistory.length;
                dom.terminalInput.value = '';
            }
        }

        // Ctrl+L = clear
        if (e.key === 'l' && e.ctrlKey) {
            e.preventDefault();
            clearShellOutput();
        }

        // Ctrl+C = cancel / clear input
        if (e.key === 'c' && e.ctrlKey) {
            e.preventDefault();
            if (dom.terminalInput.value) {
                // Show cancelled command in output
                const cwdLabel = currentCwd ? `~/${currentCwd}` : '~';
                appendToShell(`<div class="flex items-start gap-2 mt-1">
                    <span class="text-cyan-400 font-bold shrink-0">PS ${escapeHtml(cwdLabel)}></span>
                    <span class="text-slate-400">${escapeHtml(dom.terminalInput.value)}^C</span>
                </div>`);
                dom.terminalInput.value = '';
            }
        }
    });

    // Click anywhere in terminal output focuses the input
    dom.terminalOutputBody.addEventListener('click', () => {
        if (activeTab === 'shell' && window.getSelection().toString() === '') {
            dom.terminalInput.focus();
        }
    });

    // Initialize shell welcome
    shellOutputHtml = getShellWelcome();

    // Focus the input on load
    setTimeout(() => {
        if (dom.terminalInput) dom.terminalInput.focus();
    }, 300);
}
