// Conversational chatbot: message bubbles, prompt submission, and the
// "apply generated code to a file" action shared by the code card.

import { api } from './api.js';
import { dom } from './dom.js';
import { state } from './state.js';
import { escapeHtml, showNotification } from './utils.js';
import { getActiveApiKey } from './settings.js';
import { getActiveTab, fetchTree, openFile } from './fileTree.js';
import { renderBotResponse, renderBotError } from './chatResponse.js';
import { stageAgentChangesInEditor } from './editor.js';

function appendUserMessage(prompt) {
    const userMsgDiv = document.createElement('div');
    userMsgDiv.className = 'flex justify-end';
    userMsgDiv.innerHTML = `
        <div class="max-w-[85%] p-3 rounded-2xl rounded-tr-none bg-indigo-600 text-white shadow-lg space-y-1">
            <div class="flex items-center justify-between text-[10px] text-indigo-200 border-b border-indigo-500/40 pb-1 mb-1 font-mono">
                <span class="font-bold"><i class="fa-solid fa-user mr-1"></i> You</span>
                <span>${new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}</span>
            </div>
            <p class="text-xs leading-relaxed whitespace-pre-wrap">${escapeHtml(prompt)}</p>
        </div>
    `;
    dom.agentTimeline.appendChild(userMsgDiv);
}

function appendBotPlaceholder(provider, model) {
    const botMsgDiv = document.createElement('div');
    botMsgDiv.className = 'flex justify-start min-w-0';
    botMsgDiv.innerHTML = `
        <div class="max-w-[95%] w-full p-4 rounded-2xl rounded-tl-none bg-slate-900 border border-purple-500/30 shadow-xl space-y-3 min-w-0">
            <div class="flex items-center justify-between border-b border-slate-800 pb-2">
                <div class="flex items-center gap-2">
                    <div class="w-6 h-6 rounded-md bg-gradient-to-tr from-purple-600 to-indigo-600 flex items-center justify-center text-white text-[11px] shadow">
                        <i class="fa-solid fa-robot"></i>
                    </div>
                    <span class="font-bold text-slate-100 text-xs">${escapeHtml(provider.toUpperCase())} Assistant</span>
                </div>
                <span class="text-[10px] font-mono px-2 py-0.5 rounded bg-purple-500/10 text-purple-300 border border-purple-500/20">${escapeHtml(model)}</span>
            </div>

            <div class="chat-loading flex items-center gap-2 text-slate-400 text-xs py-2">
                <i class="fa-solid fa-circle-notch fa-spin text-indigo-400"></i>
                <span>Analyzing prompt context &amp; generating response...</span>
            </div>

            <div class="chat-response-content hidden space-y-3 text-xs text-slate-200 leading-relaxed min-w-0"></div>
        </div>
    `;
    dom.agentTimeline.appendChild(botMsgDiv);
    return botMsgDiv;
}

export async function writeCodeDirectly(targetPath, codeContent) {
    if (!targetPath) {
        showNotification('No target path specified', true);
        return;
    }

    try {
        dom.statusMessage.textContent = 'Writing code to ' + targetPath + '...';
        const data = await api.saveFile(targetPath, codeContent);
        if (data.success) {
            showNotification(`Successfully wrote code to ${targetPath}!`);
            await fetchTree();
            await openFile(targetPath);
        } else {
            showNotification('Failed to write file: ' + data.error, true);
        }
    } catch (err) {
        showNotification('Network error writing file', true);
    }
}

export async function executeAgentPrompt() {
    const prompt = dom.agentPromptInput.value.trim();
    if (!prompt) return;

    const provider = dom.selectProvider.value;
    const model = dom.selectModel.value;
    const apiKey = getActiveApiKey();

    dom.btnSubmitPrompt.disabled = true;
    dom.btnSubmitPrompt.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1"></i> Thinking...';
    dom.agentStatusLabel.textContent = 'AI Chatbot Synthesizing...';

    appendUserMessage(prompt);
    const botMsgDiv = appendBotPlaceholder(provider, model);
    dom.agentTimeline.scrollTop = dom.agentTimeline.scrollHeight;

    try {
        const activeTab = getActiveTab();
        const data = await api.promptAgent({
            provider,
            model,
            apiKey,
            prompt,
            targetDirectory: state.targetDirectory,
            targetFile: activeTab ? activeTab.path : '',
            currentCode: activeTab ? activeTab.content : '',
        });

        const loadingEl = botMsgDiv.querySelector('.chat-loading');
        const contentEl = botMsgDiv.querySelector('.chat-response-content');
        if (loadingEl) loadingEl.remove();
        if (contentEl) contentEl.classList.remove('hidden');

        if (data.success) {
            state.lastGeneratedCode = data.code;
            state.lastGeneratedTarget = data.targetPath;
            renderBotResponse(contentEl, data);
            dom.agentPromptInput.value = '';

            // Automatically write generated code to text editor & stage for Accept/Reject
            if (data.code && data.targetPath) {
                await stageAgentChangesInEditor(data.targetPath, data.code);
            } else {
                showNotification('Agentic Chatbot response completed.');
            }
        } else {
            renderBotError(contentEl, data.error);
        }
    } catch (err) {
        const contentEl = botMsgDiv.querySelector('.chat-response-content');
        if (contentEl) {
            contentEl.classList.remove('hidden');
            contentEl.innerHTML = `
                <div class="p-3 rounded bg-red-950/50 border border-red-500/30 text-red-300 text-xs">
                    <i class="fa-solid fa-triangle-exclamation mr-1.5"></i> Network error connecting to Chatbot engine.
                </div>
            `;
        }
    } finally {
        dom.btnSubmitPrompt.disabled = false;
        dom.btnSubmitPrompt.innerHTML = '<i class="fa-solid fa-paper-plane text-xs mr-1"></i> Send Message';
        dom.agentStatusLabel.textContent = 'Agent Engine Ready';
        dom.agentTimeline.scrollTop = dom.agentTimeline.scrollHeight;
    }
}

export function initChat() {
    dom.btnSubmitPrompt.addEventListener('click', executeAgentPrompt);
    dom.agentPromptInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            executeAgentPrompt();
        }
    });

    document.querySelectorAll('.prompt-pill').forEach((btn) => {
        btn.addEventListener('click', () => {
            dom.agentPromptInput.value = btn.getAttribute('data-prompt');
            dom.agentPromptInput.focus();
        });
    });
}
