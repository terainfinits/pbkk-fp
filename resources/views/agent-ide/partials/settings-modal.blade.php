{{-- API key settings modal (stored client-side in localStorage). --}}
<div id="settings-modal" class="hidden fixed inset-0 bg-black/70 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="w-full max-w-md bg-[#0f172a] border border-slate-700 rounded-2xl shadow-2xl p-6 space-y-5">
        <div class="flex items-center justify-between border-b border-slate-800 pb-3">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-key text-amber-400"></i>
                <h3 class="font-bold text-sm text-slate-100">LLM Provider API Keys</h3>
            </div>
            <button id="btn-close-settings" class="text-slate-400 hover:text-white">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <p class="text-xs text-slate-400 leading-relaxed">
            Enter your custom API key for real-time live model completion (Gemini, Claude, OpenAI GPT, Moonshot Kimi, DeepSeek). If left empty, built-in intelligent autonomous generation will be used.
        </p>

        <div class="space-y-3 text-xs">
            <div>
                <label class="block font-medium text-slate-300 mb-1">Google Gemini API Key</label>
                <input type="password" id="key-gemini" placeholder="AIzaSy..." class="w-full bg-slate-900 border border-slate-700 rounded px-3 py-1.5 text-slate-200 outline-none focus:border-indigo-500">
            </div>
            <div>
                <label class="block font-medium text-slate-300 mb-1">Anthropic Claude API Key</label>
                <input type="password" id="key-claude" placeholder="sk-ant-..." class="w-full bg-slate-900 border border-slate-700 rounded px-3 py-1.5 text-slate-200 outline-none focus:border-indigo-500">
            </div>
            <div>
                <label class="block font-medium text-slate-300 mb-1">OpenAI API Key (GPT)</label>
                <input type="password" id="key-gpt" placeholder="sk-..." class="w-full bg-slate-900 border border-slate-700 rounded px-3 py-1.5 text-slate-200 outline-none focus:border-indigo-500">
            </div>
            <div>
                <label class="block font-medium text-slate-300 mb-1">Moonshot Kimi API Key</label>
                <input type="password" id="key-kimi" placeholder="sk-..." class="w-full bg-slate-900 border border-slate-700 rounded px-3 py-1.5 text-slate-200 outline-none focus:border-indigo-500">
            </div>
            <div>
                <label class="block font-medium text-slate-300 mb-1">DeepSeek API Key</label>
                <input type="password" id="key-deepseek" placeholder="sk-..." class="w-full bg-slate-900 border border-slate-700 rounded px-3 py-1.5 text-slate-200 outline-none focus:border-indigo-500">
            </div>
            <div>
                <label class="block font-medium text-slate-300 mb-1">Ollama Cloud API Key</label>
                <input type="password" id="key-ollama-cloud" placeholder="fe..." class="w-full bg-slate-900 border border-slate-700 rounded px-3 py-1.5 text-slate-200 outline-none focus:border-indigo-500">
            </div>
        </div>

        <div class="flex justify-end gap-2 pt-2 border-t border-slate-800">
            <button id="btn-save-keys" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg text-xs font-semibold transition">
                Save Keys to Storage
            </button>
        </div>
    </div>
</div>
