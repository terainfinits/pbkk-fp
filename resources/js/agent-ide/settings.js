// LLM provider/model dropdowns + the API key settings modal (localStorage-backed).

import { dom } from './dom.js';
import { providerModels } from './state.js';
import { showNotification } from './utils.js';

export function updateModelOptions() {
    const provider = dom.selectProvider.value;
    const models = providerModels[provider] || [];
    dom.selectModel.innerHTML = '';
    models.forEach((m) => {
        const opt = document.createElement('option');
        opt.value = m.id;
        opt.textContent = m.name;
        dom.selectModel.appendChild(opt);
    });
    dom.modelBadge.textContent = models[0]?.name || provider.toUpperCase();
}

function loadSavedKeys() {
    document.getElementById('key-gemini').value = localStorage.getItem('agent_key_gemini') || '';
    document.getElementById('key-claude').value = localStorage.getItem('agent_key_claude') || '';
    document.getElementById('key-gpt').value = localStorage.getItem('agent_key_gpt') || '';
    document.getElementById('key-kimi').value = localStorage.getItem('agent_key_kimi') || '';
    document.getElementById('key-deepseek').value = localStorage.getItem('agent_key_deepseek') || '';
    const savedOllama = localStorage.getItem('agent_key_ollama_cloud') || localStorage.getItem('agent_key_ollama') || '';
    document.getElementById('key-ollama-cloud').value = savedOllama;
}

export function getActiveApiKey() {
    const provider = dom.selectProvider.value;
    let key = localStorage.getItem('agent_key_' + provider) || '';
    if (!key && (provider === 'ollama_cloud' || provider === 'ollama')) {
        key = localStorage.getItem('agent_key_ollama_cloud') || localStorage.getItem('agent_key_ollama') || '';
    }
    return key;
}

export function initSettings() {
    dom.selectProvider.addEventListener('change', updateModelOptions);
    dom.selectModel.addEventListener('change', () => {
        const selectedOpt = dom.selectModel.options[dom.selectModel.selectedIndex];
        if (selectedOpt) dom.modelBadge.textContent = selectedOpt.textContent;
    });
    updateModelOptions();

    const settingsModal = document.getElementById('settings-modal');
    document.getElementById('btn-open-settings').addEventListener('click', () => {
        loadSavedKeys();
        settingsModal.classList.remove('hidden');
    });
    document.getElementById('btn-close-settings').addEventListener('click', () => {
        settingsModal.classList.add('hidden');
    });
    document.getElementById('btn-save-keys').addEventListener('click', () => {
        localStorage.setItem('agent_key_gemini', document.getElementById('key-gemini').value.trim());
        localStorage.setItem('agent_key_claude', document.getElementById('key-claude').value.trim());
        localStorage.setItem('agent_key_gpt', document.getElementById('key-gpt').value.trim());
        localStorage.setItem('agent_key_kimi', document.getElementById('key-kimi').value.trim());
        localStorage.setItem('agent_key_deepseek', document.getElementById('key-deepseek').value.trim());
        const ollamaKey = document.getElementById('key-ollama-cloud').value.trim();
        localStorage.setItem('agent_key_ollama_cloud', ollamaKey);
        localStorage.setItem('agent_key_ollama', ollamaKey);
        settingsModal.classList.add('hidden');
        showNotification('API Keys saved successfully!');
    });

    loadSavedKeys();
}
