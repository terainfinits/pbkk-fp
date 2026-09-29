// Builds the content of a single bot chat message: the collapsible
// reasoning-steps accordion, the markdown-rendered explanation, and the
// interactive code card (Run / Apply / Diff / Copy).

import { escapeHtml, showNotification } from './utils.js';
import { extractProseFromRaw, renderMarkdown, highlightMarkdownCode } from './markdown.js';
import { runCodeInKernel } from './terminal.js';
import { showDiffView } from './modals.js';

function buildStepsHtml(steps) {
    if (!steps || steps.length === 0) return '';

    const stepsList = steps.map((st) => `
        <div class="flex items-start gap-2 text-[11px] text-slate-300">
            <i class="fa-solid fa-check-circle text-emerald-400 mt-0.5"></i>
            <div>
                <span class="font-semibold text-slate-200">${escapeHtml(st.title)}:</span>
                <span class="text-slate-400">${escapeHtml(st.detail)}</span>
            </div>
        </div>
    `).join('');

    return `
        <details class="group bg-slate-950/80 rounded-lg border border-slate-800 p-2.5 transition">
            <summary class="flex items-center justify-between text-[11px] font-semibold text-purple-300 cursor-pointer select-none">
                <span class="flex items-center gap-1.5">
                    <i class="fa-solid fa-brain text-purple-400"></i> Agent Reasoning Steps (${steps.length})
                </span>
                <i class="fa-solid fa-chevron-down text-[10px] group-open:rotate-180 transition"></i>
            </summary>
            <div class="mt-2 space-y-2 pt-2 border-t border-slate-800/80">
                ${stepsList}
            </div>
        </details>
    `;
}

function buildMarkdownHtml(data) {
    const prose = extractProseFromRaw(data.raw) || data.explanation || '';
    return `<div class="markdown-body">${renderMarkdown(prose)}</div>`;
}

function buildCodeCardHtml(data) {
    if (!data.code) return '';

    const codeLang = data.language || 'code';
    return `
        <div class="mt-3 rounded-xl bg-[#060910] border border-slate-800 overflow-hidden shadow-md">
            <div class="bg-[#0e1424] px-3 py-2 flex items-center justify-between border-b border-slate-800 text-[11px]">
                <div class="flex items-center gap-2 font-mono text-indigo-300">
                    <i class="fa-regular fa-file-code"></i>
                    <span class="font-semibold">${escapeHtml(data.targetPath || 'snippet.' + codeLang)}</span>
                </div>
                <span class="px-1.5 py-0.5 rounded bg-slate-800 text-slate-400 text-[10px] font-mono uppercase">${escapeHtml(codeLang)}</span>
            </div>
            <pre class="max-h-48 overflow-y-auto p-3 font-mono text-[11px] text-slate-200 leading-5 bg-[#090d16]"><code>${escapeHtml(data.code)}</code></pre>

            <div class="p-2 bg-[#0c101c] border-t border-slate-800/80 flex flex-wrap items-center justify-between gap-2">
                <div class="flex items-center gap-1.5">
                    <button class="btn-chat-run-kernel px-2.5 py-1.5 rounded bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-semibold text-[11px] flex items-center gap-1 shadow transition">
                        <i class="fa-solid fa-play text-[10px]"></i>
                        <span>Run in Kernel</span>
                    </button>
                    <button class="btn-chat-copy px-2 py-1.5 rounded bg-slate-800 hover:bg-slate-700 text-slate-300 text-[11px] transition" title="Copy Code">
                        <i class="fa-regular fa-copy"></i>
                    </button>
                </div>
                <div class="flex items-center gap-1.5 px-2.5 py-1 rounded bg-indigo-500/10 text-indigo-300 border border-indigo-500/20 text-[10px] font-mono">
                    <i class="fa-solid fa-file-pen text-indigo-400"></i>
                    <span>Written to editor</span>
                </div>
            </div>

            <div class="chat-live-console-container p-2"></div>
        </div>
    `;
}

/**
 * Render a successful agent response into the bot message's content
 * element and wire up its action buttons.
 */
export function renderBotResponse(contentEl, data) {
    contentEl.innerHTML = buildStepsHtml(data.steps) + buildMarkdownHtml(data) + buildCodeCardHtml(data);

    highlightMarkdownCode(contentEl);

    const runKernelBtn = contentEl.querySelector('.btn-chat-run-kernel');
    const liveConsole = contentEl.querySelector('.chat-live-console-container');
    if (runKernelBtn) {
        runKernelBtn.addEventListener('click', async () => {
            await runCodeInKernel(data.code, data.language, data.targetPath, liveConsole);
        });
    }

    const copyBtn = contentEl.querySelector('.btn-chat-copy');
    if (copyBtn) {
        copyBtn.addEventListener('click', () => {
            navigator.clipboard.writeText(data.code);
            showNotification('Code copied to clipboard!');
        });
    }
}

export function renderBotError(contentEl, message) {
    contentEl.innerHTML = `
        <div class="p-3 rounded bg-red-950/50 border border-red-500/30 text-red-300 text-xs flex items-start gap-2">
            <i class="fa-solid fa-circle-exclamation text-red-400 mt-0.5"></i>
            <div>
                <div class="font-bold">Chatbot Error</div>
                <div>${escapeHtml(message || 'Failed to process prompt.')}</div>
            </div>
        </div>
    `;
}
