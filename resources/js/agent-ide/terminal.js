// Bottom terminal drawer: kernel detection + code execution output.

import { api } from './api.js';
import { dom } from './dom.js';
import { escapeHtml, showNotification } from './utils.js';

let isTerminalMinimized = false;

export function openTerminalDrawer() {
    if (isTerminalMinimized) {
        isTerminalMinimized = false;
        dom.terminalDrawer.style.height = '176px';
        dom.terminalToggleIcon.className = 'fa-solid fa-chevron-down text-[10px]';
    }
}

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

/**
 * Run a code snippet in the matching kernel. Writes to the main terminal
 * drawer always, and optionally mirrors a compact copy into a chat card's
 * own live-output container (`targetConsole`).
 */
export async function runCodeInKernel(codeContent, language, filePath = '', targetConsole = null) {
    openTerminalDrawer();

    const execLang = language || (filePath ? filePath.split('.').pop() : 'php');

    const promptLine = document.createElement('div');
    promptLine.className = 'text-indigo-300 font-bold flex items-center gap-2 mt-2 pt-2 border-t border-slate-800/80';
    promptLine.innerHTML = `<span class="text-emerald-400">➜</span> <span>Executing ${escapeHtml(execLang.toUpperCase())} kernel${filePath ? ' (' + escapeHtml(filePath) + ')' : ''}...</span>`;
    dom.terminalOutputBody.appendChild(promptLine);
    dom.terminalOutputBody.scrollTop = dom.terminalOutputBody.scrollHeight;

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

            const outBlock = document.createElement('div');
            outBlock.className = 'pl-3 border-l-2 border-slate-700 my-1 space-y-1';
            outBlock.innerHTML = outputHtml + statusBadge;
            dom.terminalOutputBody.appendChild(outBlock);
            dom.terminalOutputBody.scrollTop = dom.terminalOutputBody.scrollHeight;

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
            const errDiv = document.createElement('div');
            errDiv.className = 'text-red-400 pl-3 border-l-2 border-red-500 my-1';
            errDiv.textContent = `Error: ${data.error || 'Execution failed'}`;
            dom.terminalOutputBody.appendChild(errDiv);

            if (targetConsole) {
                targetConsole.innerHTML = `<div class="text-red-400 text-[10px] font-mono">Execution failed: ${escapeHtml(data.error)}</div>`;
            }
        }
    } catch (err) {
        const errDiv = document.createElement('div');
        errDiv.className = 'text-red-400 pl-3 border-l-2 border-red-500 my-1';
        errDiv.textContent = 'Execution network exception.';
        dom.terminalOutputBody.appendChild(errDiv);
        if (targetConsole) {
            targetConsole.innerHTML = `<div class="text-red-400 text-[10px]">Execution exception.</div>`;
        }
    }
}

export function initTerminal() {
    dom.btnToggleTerminal.addEventListener('click', () => {
        isTerminalMinimized = !isTerminalMinimized;
        if (isTerminalMinimized) {
            dom.terminalDrawer.style.height = '32px';
            dom.terminalToggleIcon.className = 'fa-solid fa-chevron-up text-[10px]';
        } else {
            dom.terminalDrawer.style.height = '176px';
            dom.terminalToggleIcon.className = 'fa-solid fa-chevron-down text-[10px]';
        }
    });

    dom.btnClearTerminal.addEventListener('click', () => {
        dom.terminalOutputBody.innerHTML = `
            <div class="text-slate-500 flex items-center gap-2">
                <span class="text-emerald-400 font-bold">➜</span>
                <span>Console cleared. Ready for kernel execution.</span>
            </div>
        `;
    });
}
