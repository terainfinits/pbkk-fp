// Shared mutable state + static reference data for the Agentic IDE.
// Every other module imports `state` from here instead of holding its own copy.

export const providerModels = {
    gemini: [
        { id: 'gemini-3.6-flash', name: 'Gemini 3.6 Flash' },
        { id: 'gemini-3.1-pro', name: 'Gemini 3.1 Pro' },
    ],
    claude: [
        { id: 'claude-3-7-sonnet-20250219', name: 'Claude 3.7 Sonnet' },
        { id: 'claude-3-5-sonnet-20241022', name: 'Claude 3.5 Sonnet' },
        { id: 'claude-3-5-haiku-20241022', name: 'Claude 3.5 Haiku' },
    ],
    gpt: [
        { id: 'gpt-oss 120b', name: 'gpt-oss 120b' },
    ],
    kimi: [
        { id: 'moonshot-v1-8k', name: 'Kimi (Moonshot v1 8k)' },
        { id: 'moonshot-v1-32k', name: 'Kimi (Moonshot v1 32k)' },
        { id: 'moonshot-v1-128k', name: 'Kimi (Moonshot v1 128k)' },
    ],
    deepseek: [
        { id: 'deepseek-chat', name: 'DeepSeek V3' },
        { id: 'deepseek-reasoner', name: 'DeepSeek R1' },
    ],
    ollama_cloud: [
        { id: 'gpt-oss:120b', name: 'gpt-oss:120b' },
        { id: 'gemma4:31b', name: 'gemma4:31b' },
        { id: 'llama3.3', name: 'llama3.3' },
        { id: 'qwen2.5-coder', name: 'qwen2.5-coder' },
        { id: 'deepseek-r1', name: 'deepseek-r1' },
    ],
    ollama_local: [
        { id: 'kimi-k2.6', name: 'kimi-k2.6' },
        { id: 'gemma4:31b', name: 'gemma4:31b' },
        { id: 'llama3.3', name: 'llama3.3' },
        { id: 'qwen2.5-coder', name: 'qwen2.5-coder' },
        { id: 'deepseek-r1', name: 'deepseek-r1' },
    ],
};

export const state = {
    openTabs: [], // { path, filename, content, isDirty, extension, isNew }
    activeTabPath: null,
    targetDirectory: '',
    projectTree: [],
    lastGeneratedCode: '',
    lastGeneratedTarget: '',
    createModalType: 'file', // 'file' | 'directory'
    pendingReview: null, // { targetPath, originalCode, newCode, isNewTab }
};
