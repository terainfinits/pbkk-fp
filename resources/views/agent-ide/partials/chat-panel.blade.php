{{-- Right sidebar: conversational AI chatbot panel. --}}
<aside class="w-96 bg-[#0e1424] border-l border-slate-800 flex flex-col shrink-0 min-w-0">
    <div class="p-3 border-b border-slate-800 bg-[#0d1322] space-y-2.5">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="w-2.5 h-2.5 rounded-full bg-gradient-to-r from-purple-500 to-pink-500 animate-pulse"></div>
                <span class="text-xs font-bold uppercase tracking-wider text-slate-200">Magentic Chatbot</span>
            </div>
            <span id="model-badge" class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-purple-500/20 text-purple-300 border border-purple-500/30">
                Gemini 3.6 Flash
            </span>
        </div>

        <div class="grid grid-cols-2 gap-2">
            <div>
                <label class="block text-[10px] font-medium text-slate-400 mb-1">Provider</label>
                <select id="select-provider" class="w-full bg-slate-900 border border-slate-700 text-slate-200 text-xs rounded-md px-2 py-1.5 outline-none focus:border-indigo-500 transition">
                    <option value="gemini">Google Gemini</option>
                    <option value="claude">Anthropic Claude</option>
                    <option value="gpt">OpenAI (GPT)</option>
                    <option value="kimi">Moonshot Kimi</option>
                    <option value="deepseek">DeepSeek AI</option>
                    <option value="ollama_cloud" selected>Ollama Cloud</option>
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-medium text-slate-400 mb-1">Model</label>
                <select id="select-model" class="w-full bg-slate-900 border border-slate-700 text-slate-200 text-xs rounded-md px-2 py-1.5 outline-none focus:border-indigo-500 transition"></select>
            </div>
        </div>
    </div>

    <div id="agent-chat-stream" class="flex-1 overflow-y-auto p-3 space-y-4 text-xs min-w-0">
        <div class="p-3.5 rounded-xl bg-slate-900/90 border border-indigo-500/30 space-y-2.5">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2 text-indigo-300 font-semibold text-xs">
                    <i class="fa-solid fa-robot text-indigo-400"></i>
                    <span>Magentic AI Assistant</span>
                </div>
                <span class="text-[10px] bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 px-1.5 py-0.5 rounded font-mono">Kernel Ready</span>
            </div>
            <p class="text-slate-300 text-[11px] leading-relaxed">
                Welcome to Magentic Chatbot! Ask me to write code in Python, PHP, or Node.js. I can run code directly in live system kernels and apply patches to your workspace.
            </p>
            <div class="p-2 bg-slate-950/90 rounded border border-slate-800 text-[10px] text-slate-400 space-y-1 font-mono">
                <div>🎯 <span class="text-slate-300">Target Folder:</span> <span id="chat-target-folder" class="text-indigo-400">/</span></div>
                <div>📄 <span class="text-slate-300">Active File:</span> <span id="chat-active-file" class="text-amber-400">None</span></div>
            </div>
        </div>

        <div class="space-y-1.5">
            <span class="text-[10px] uppercase font-semibold tracking-wider text-slate-500">Quick Prompt Templates</span>
            <div class="flex flex-wrap gap-1.5">
                <button class="prompt-pill text-[11px] px-2.5 py-1 rounded bg-slate-900 hover:bg-amber-950/40 text-slate-300 hover:text-amber-200 border border-slate-800 hover:border-amber-500/40 transition" data-prompt="Write a Python script for Fibonacci calculation and prime numbers up to 50">
                    🐍 Python Script
                </button>
                <button class="prompt-pill text-[11px] px-2.5 py-1 rounded bg-slate-900 hover:bg-indigo-900/40 text-slate-300 hover:text-indigo-200 border border-slate-800 hover:border-indigo-500/40 transition" data-prompt="Create a Laravel UserController with full CRUD and validation rules">
                    ⚡ Laravel CRUD
                </button>
                <button class="prompt-pill text-[11px] px-2.5 py-1 rounded bg-slate-900 hover:bg-emerald-950/40 text-slate-300 hover:text-emerald-200 border border-slate-800 hover:border-emerald-500/40 transition" data-prompt="Create a modern Vue 3 component for Agent Dashboard with Tailwind and reactive metrics">
                    🟢 Vue 3 Component
                </button>
                <button class="prompt-pill text-[11px] px-2.5 py-1 rounded bg-slate-900 hover:bg-purple-950/40 text-slate-300 hover:text-purple-200 border border-slate-800 hover:border-purple-500/40 transition" data-prompt="Generate a complete Pest test file for testing the AgentIdeController endpoints">
                    🧪 Pest Tests
                </button>
            </div>
        </div>

        <div id="agent-timeline" class="space-y-4 min-w-0"></div>
    </div>

    <div class="p-3 border-t border-slate-800 bg-[#0b0f1a] space-y-2">
        <div class="relative">
            <textarea id="agent-prompt-input" rows="3" class="w-full bg-slate-900 border border-slate-700/80 rounded-lg p-2.5 text-xs text-slate-200 placeholder-slate-500 outline-none focus:border-indigo-500 transition resize-none" placeholder="Chat with AI... (e.g. 'Write a Python algorithm script' or 'Create a UserController')"></textarea>
        </div>

        <div class="flex items-center justify-between">
            <span class="text-[10px] text-slate-500 font-mono">Shift+Enter for new line</span>
            <button id="btn-submit-prompt" class="px-4 py-2 bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 hover:from-blue-500 hover:to-purple-500 text-white font-semibold text-xs rounded-lg shadow-lg shadow-indigo-500/20 flex items-center gap-1.5 transition">
                <i class="fa-solid fa-paper-plane text-xs"></i>
                <span>Send Message</span>
            </button>
        </div>
    </div>
</aside>
