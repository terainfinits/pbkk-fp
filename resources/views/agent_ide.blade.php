<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Agentic AI IDE | Antigravity Engine</title>
    
    <!-- Google Fonts & Font Awesome -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@400;500;600&family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Prism Syntax Highlighting -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism-tomorrow.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/prism.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-markup.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-css.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-javascript.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-typescript.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-php.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-json.min.js"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: #0b0f19;
            color: #e2e8f0;
            overflow: hidden;
        }
        .code-font {
            font-family: 'Fira Code', monospace;
        }
        /* Custom scrollbars */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #0f172a;
        }
        ::-webkit-scrollbar-thumb {
            background: #334155;
            border-radius: 3px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #475569;
        }
        /* Line numbering sync */
        .editor-textarea {
            tab-size: 4;
            caret-color: #38bdf8;
            resize: none;
        }
        /* Glowing neon pulse */
        .neon-glow {
            box-shadow: 0 0 20px -3px rgba(129, 140, 248, 0.35);
        }
        .agent-active-badge {
            background: linear-gradient(135deg, #6366f1 0%, #a855f7 50%, #ec4899 100%);
        }
    </style>
</head>
<body class="h-screen flex flex-col antialiased select-none bg-[#090d16]">

    <!-- TOP NAV / HEADER -->
    <header class="h-14 bg-[#0e1424] border-b border-slate-800/80 px-4 flex items-center justify-between shrink-0 z-30">
        <div class="flex items-center space-x-4">
            <!-- Brand -->
            <a href="{{ route('home') }}" class="flex items-center gap-2 group">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-tr from-blue-600 via-indigo-600 to-purple-500 flex items-center justify-center text-white shadow-md shadow-indigo-500/20 group-hover:scale-105 transition">
                    <i class="fa-solid fa-atom text-sm"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="font-bold text-sm tracking-tight text-white">Antigravity</span>
                        <span class="text-[10px] uppercase font-bold tracking-widest px-1.5 py-0.5 rounded bg-indigo-500/20 text-indigo-400 border border-indigo-500/30">IDE v2.0</span>
                    </div>
                    <p class="text-[10px] text-slate-400 font-mono">Agentic AI Code Studio</p>
                </div>
            </a>

            <div class="h-5 w-px bg-slate-800"></div>

            <!-- Workspace Directory Indicator -->
            <div class="flex items-center gap-2 text-xs bg-slate-900/90 border border-slate-800 px-2.5 py-1 rounded-md text-slate-300">
                <i class="fa-regular fa-folder-open text-amber-400"></i>
                <span class="text-slate-400 font-normal">Workspace:</span>
                <span id="workspace-root-name" class="font-semibold text-slate-200">PBKK-Tugas-2</span>
            </div>

            <!-- Target Write Directory -->
            <div class="hidden sm:flex items-center gap-2 text-xs bg-indigo-950/40 border border-indigo-500/30 px-2.5 py-1 rounded-md text-indigo-300">
                <i class="fa-solid fa-crosshairs text-indigo-400"></i>
                <span class="text-indigo-400/80">Target Dir:</span>
                <span id="target-dir-badge" class="font-mono text-indigo-200">/ (Root)</span>
            </div>
        </div>

        <!-- Right Action Controls -->
        <div class="flex items-center space-x-3">
            <!-- Save Button -->
            <button id="btn-save-file" title="Save current file (Ctrl+S)" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-medium rounded-md border border-slate-700 flex items-center gap-1.5 transition">
                <i class="fa-regular fa-floppy-disk text-slate-400"></i>
                <span>Save</span>
                <span class="text-[10px] text-slate-500 font-mono">Ctrl+S</span>
            </button>

            <!-- API Keys Settings Modal Trigger -->
            <button id="btn-open-settings" class="px-3 py-1.5 bg-slate-800/80 hover:bg-slate-700 text-slate-300 text-xs font-medium rounded-md border border-slate-700/80 flex items-center gap-1.5 transition">
                <i class="fa-solid fa-key text-amber-400"></i>
                <span>API Keys</span>
            </button>

            <!-- Exit / Back to App -->
            <a href="{{ route('home') }}" class="px-3 py-1.5 bg-indigo-600/20 hover:bg-indigo-600/30 text-indigo-300 text-xs font-semibold rounded-md border border-indigo-500/30 flex items-center gap-1.5 transition">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Back to App</span>
            </a>
        </div>
    </header>

    <!-- MAIN 3-PANE WORKSPACE -->
    <div class="flex-1 flex overflow-hidden">

        <!-- PANE 1: FILE EXPLORER SIDEBAR -->
        <aside class="w-64 bg-[#0b0f1a] border-r border-slate-800 flex flex-col shrink-0">
            <!-- Explorer Header -->
            <div class="h-10 px-3 border-b border-slate-800 flex items-center justify-between text-xs font-semibold text-slate-400 uppercase tracking-wider bg-[#0d1322]">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-folder-tree text-indigo-400"></i>
                    <span>Explorer</span>
                </div>
                <div class="flex items-center gap-1">
                    <button id="btn-new-file" title="New File" class="w-6 h-6 rounded hover:bg-slate-800 text-slate-400 hover:text-slate-200 flex items-center justify-center transition">
                        <i class="fa-solid fa-file-circle-plus text-xs"></i>
                    </button>
                    <button id="btn-new-folder" title="New Folder" class="w-6 h-6 rounded hover:bg-slate-800 text-slate-400 hover:text-slate-200 flex items-center justify-center transition">
                        <i class="fa-solid fa-folder-plus text-xs"></i>
                    </button>
                    <button id="btn-refresh-tree" title="Refresh" class="w-6 h-6 rounded hover:bg-slate-800 text-slate-400 hover:text-slate-200 flex items-center justify-center transition">
                        <i class="fa-solid fa-rotate text-xs"></i>
                    </button>
                </div>
            </div>

            <!-- Target Directory Selector Action -->
            <div class="p-2 bg-slate-900/60 border-b border-slate-800/80 text-[11px]">
                <div class="flex items-center justify-between text-slate-400 mb-1">
                    <span>AI Target Folder:</span>
                    <button id="btn-reset-target-dir" class="text-indigo-400 hover:underline">Reset Root</button>
                </div>
                <div id="active-target-display" class="px-2 py-1 bg-slate-950 rounded text-indigo-300 font-mono text-[10px] truncate border border-slate-800">
                    /
                </div>
            </div>

            <!-- File Tree View Container -->
            <div id="file-tree-container" class="flex-1 overflow-y-auto p-2 space-y-0.5 text-xs select-none">
                <!-- Tree items dynamically loaded here -->
                <div class="p-4 text-center text-slate-500 animate-pulse">
                    <i class="fa-solid fa-circle-notch fa-spin mr-2"></i> Loading project tree...
                </div>
            </div>

            <!-- File Explorer Quick Help Footer -->
            <div class="p-2.5 border-t border-slate-800 bg-[#090d16] text-[10px] text-slate-500">
                <span class="text-slate-400 font-medium">Tip:</span> Right click or select folder to set AI target write directory.
            </div>
        </aside>

        <!-- PANE 2: TABBED CODE EDITOR -->
        <main class="flex-1 flex flex-col bg-[#0b0f19] overflow-hidden">
            <!-- Tabs Bar -->
            <div id="tabs-container" class="h-10 bg-[#0e1424] border-b border-slate-800 flex items-center px-1 overflow-x-auto shrink-0 space-x-1">
                <!-- Dynamic tabs -->
            </div>

            <!-- Breadcrumbs & Editor Actions -->
            <div class="h-8 bg-[#0d1220] border-b border-slate-800/60 px-4 flex items-center justify-between text-xs text-slate-400 shrink-0">
                <div class="flex items-center gap-2">
                    <i class="fa-regular fa-file-code text-indigo-400"></i>
                    <span id="current-filepath" class="font-mono text-slate-300 text-xs">No file selected</span>
                    <span id="unsaved-indicator" class="hidden w-2 h-2 rounded-full bg-amber-400" title="Unsaved changes"></span>
                </div>
                <div class="flex items-center gap-3 text-[11px]">
                    <span id="file-language-badge" class="px-1.5 py-0.5 rounded bg-slate-800 text-slate-400 uppercase font-mono text-[10px]">TEXT</span>
                    <span id="editor-cursor-pos" class="font-mono text-slate-500">Ln 1, Col 1</span>
                    <button id="btn-toggle-diff" class="hidden px-2 py-0.5 rounded bg-purple-600/30 text-purple-300 border border-purple-500/40 hover:bg-purple-600/40 transition">
                        <i class="fa-solid fa-code-compare mr-1"></i> Diff View
                    </button>
                </div>
            </div>

            <!-- Editor Work Area -->
            <div class="flex-1 relative overflow-hidden flex bg-[#0c101c]">
                <!-- Line Numbers -->
                <div id="line-numbers" class="w-12 py-3 bg-[#0a0e1a] text-right pr-3 text-slate-600 font-mono text-xs select-none border-r border-slate-800/60 overflow-hidden leading-6">
                    1
                </div>

                <!-- Textarea Code Input & Highlight Layer -->
                <div class="flex-1 relative h-full overflow-hidden">
                    <textarea id="code-editor-input" spellcheck="false" class="editor-textarea code-font w-full h-full p-3 bg-transparent text-slate-200 text-xs leading-6 outline-none border-none overflow-auto font-mono z-10 relative" placeholder="// Select a file from the explorer or ask the AI to generate code..."></textarea>
                </div>

                <!-- Diff Side-by-Side Drawer (Hidden by default) -->
                <div id="diff-drawer" class="hidden absolute inset-0 bg-[#090d16] z-20 flex flex-col border-l border-slate-700">
                    <div class="h-9 bg-slate-900 px-4 flex items-center justify-between border-b border-slate-800">
                        <span class="text-xs font-semibold text-purple-300 flex items-center gap-2">
                            <i class="fa-solid fa-code-compare"></i> Proposed AI Diff Patch
                        </span>
                        <div class="flex items-center gap-2">
                            <button id="btn-apply-diff" class="px-3 py-1 bg-emerald-600 hover:bg-emerald-500 text-white rounded text-xs font-semibold shadow transition">
                                <i class="fa-solid fa-check mr-1"></i> Apply Changes to File
                            </button>
                            <button id="btn-close-diff" class="px-2 py-1 text-slate-400 hover:text-white text-xs">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>
                    </div>
                    <div class="flex-1 p-3 overflow-auto font-mono text-xs bg-slate-950/80">
                        <pre id="diff-code-view" class="text-slate-300"></pre>
                    </div>
                </div>
            </div>

            <!-- Status Bar -->
            <div class="h-6 bg-[#090d16] border-t border-slate-800/80 px-3 flex items-center justify-between text-[11px] text-slate-500 shrink-0">
                <div class="flex items-center gap-3">
                    <span class="flex items-center gap-1.5 text-emerald-400">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-ping"></span>
                        <span id="agent-status-label">Agent Engine Ready</span>
                    </span>
                    <span class="text-slate-600">|</span>
                    <span id="status-message" class="text-slate-400">Project ready</span>
                </div>
                <div class="flex items-center gap-3 font-mono text-[10px]">
                    <span id="status-chars">0 chars</span>
                    <span>UTF-8</span>
                </div>
            </div>
        </main>

        <!-- PANE 3: AGENTIC AI ASSISTANT PANEL -->
        <aside class="w-96 bg-[#0e1424] border-l border-slate-800 flex flex-col shrink-0">
            <!-- Agent Header & Model Selector -->
            <div class="p-3 border-b border-slate-800 bg-[#0d1322] space-y-2.5">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="w-2.5 h-2.5 rounded-full bg-gradient-to-r from-purple-500 to-pink-500 animate-pulse"></div>
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-200">Agentic AI Model</span>
                    </div>
                    <span id="model-badge" class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-purple-500/20 text-purple-300 border border-purple-500/30">
                        Gemini 3.6 Flash
                    </span>
                </div>

                <!-- LLM Provider & Model Selector Dropdown -->
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-[10px] font-medium text-slate-400 mb-1">Provider</label>
                        <select id="select-provider" class="w-full bg-slate-900 border border-slate-700 text-slate-200 text-xs rounded-md px-2 py-1.5 outline-none focus:border-indigo-500 transition">
                            <option value="gemini" selected>Google Gemini</option>
                            <option value="claude">Anthropic Claude</option>
                            <option value="gpt">OpenAI (GPT)</option>
                            <option value="kimi">Moonshot Kimi</option>
                            <option value="deepseek">DeepSeek AI</option>
                            <option value="ollama">Local Ollama</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-medium text-slate-400 mb-1">Model</label>
                        <select id="select-model" class="w-full bg-slate-900 border border-slate-700 text-slate-200 text-xs rounded-md px-2 py-1.5 outline-none focus:border-indigo-500 transition">
                            <!-- Populated dynamically based on provider -->
                        </select>
                    </div>
                </div>
            </div>

            <!-- Agent Chat & Reasoning Stream Area -->
            <div id="agent-chat-stream" class="flex-1 overflow-y-auto p-3 space-y-4 text-xs">
                <!-- Welcome Agent Card -->
                <div class="p-3.5 rounded-xl bg-slate-900/80 border border-slate-800 space-y-2.5">
                    <div class="flex items-center gap-2 text-indigo-300 font-semibold text-xs">
                        <i class="fa-solid fa-robot"></i>
                        <span>Antigravity AI Agent</span>
                    </div>
                    <p class="text-slate-400 text-[11px] leading-relaxed">
                        I can autonomously write code, build entire controllers, generate Vue components, create files, and apply diffs to any selected folder.
                    </p>
                    <div class="p-2 bg-slate-950/80 rounded border border-slate-800/80 text-[10px] text-slate-400 space-y-1 font-mono">
                        <div>🎯 <span class="text-slate-300">Target Folder:</span> <span id="chat-target-folder" class="text-indigo-400">/</span></div>
                        <div>📄 <span class="text-slate-300">Active File:</span> <span id="chat-active-file" class="text-amber-400">None</span></div>
                    </div>
                </div>

                <!-- Template Prompt Pills -->
                <div class="space-y-1.5">
                    <span class="text-[10px] uppercase font-semibold tracking-wider text-slate-500">Quick Actions</span>
                    <div class="flex flex-wrap gap-1.5">
                        <button class="prompt-pill text-[11px] px-2.5 py-1 rounded bg-slate-900 hover:bg-indigo-900/40 text-slate-300 hover:text-indigo-200 border border-slate-800 hover:border-indigo-500/40 transition" data-prompt="Create a Laravel UserController with full CRUD and validation rules">
                            ⚡ CRUD Controller
                        </button>
                        <button class="prompt-pill text-[11px] px-2.5 py-1 rounded bg-slate-900 hover:bg-indigo-900/40 text-slate-300 hover:text-indigo-200 border border-slate-800 hover:border-indigo-500/40 transition" data-prompt="Create a modern Vue 3 component for Agent Dashboard with Tailwind and reactive metrics">
                            ⚡ Vue 3 Component
                        </button>
                        <button class="prompt-pill text-[11px] px-2.5 py-1 rounded bg-slate-900 hover:bg-indigo-900/40 text-slate-300 hover:text-indigo-200 border border-slate-800 hover:border-indigo-500/40 transition" data-prompt="Generate a complete Pest test file for testing the AgentIdeController endpoints">
                            ⚡ Pest Tests
                        </button>
                        <button class="prompt-pill text-[11px] px-2.5 py-1 rounded bg-slate-900 hover:bg-indigo-900/40 text-slate-300 hover:text-indigo-200 border border-slate-800 hover:border-indigo-500/40 transition" data-prompt="Refactor this active file to follow clean architecture and add error handling">
                            ⚡ Refactor & Fix
                        </button>
                    </div>
                </div>

                <!-- Live Stream Steps Container -->
                <div id="agent-timeline" class="space-y-3">
                    <!-- Dynamic reasoning steps injected here -->
                </div>
            </div>

            <!-- Agent Prompt Input Box -->
            <div class="p-3 border-t border-slate-800 bg-[#0b0f1a] space-y-2">
                <div class="relative">
                    <textarea id="agent-prompt-input" rows="3" class="w-full bg-slate-900 border border-slate-700/80 rounded-lg p-2.5 text-xs text-slate-200 placeholder-slate-500 outline-none focus:border-indigo-500 transition resize-none" placeholder="Ask AI to write code in selected directory... (e.g. 'Create a ProductService.php')"></textarea>
                </div>

                <div class="flex items-center justify-between">
                    <span class="text-[10px] text-slate-500 font-mono">Press Shift+Enter for new line</span>
                    <button id="btn-submit-prompt" class="px-4 py-2 bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 hover:from-blue-500 hover:to-purple-500 text-white font-semibold text-xs rounded-lg shadow-lg shadow-indigo-500/20 flex items-center gap-1.5 transition">
                        <i class="fa-solid fa-paper-plane text-xs"></i>
                        <span>Generate & Write</span>
                    </button>
                </div>
            </div>
        </aside>
    </div>

    <!-- API KEY SETTINGS MODAL -->
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
            </div>

            <div class="flex justify-end gap-2 pt-2 border-t border-slate-800">
                <button id="btn-save-keys" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg text-xs font-semibold transition">
                    Save Keys to Storage
                </button>
            </div>
        </div>
    </div>

    <!-- NEW FILE / FOLDER PROMPT MODAL -->
    <div id="create-modal" class="hidden fixed inset-0 bg-black/70 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="w-full max-w-sm bg-[#0f172a] border border-slate-700 rounded-2xl shadow-2xl p-5 space-y-4">
            <h3 id="create-modal-title" class="font-bold text-sm text-slate-100">Create New File</h3>
            <div>
                <label class="block text-xs text-slate-400 mb-1">Relative Path / Name</label>
                <input type="text" id="create-modal-input" placeholder="e.g. app/Services/MyService.php" class="w-full bg-slate-900 border border-slate-700 rounded px-3 py-2 text-xs text-slate-200 outline-none focus:border-indigo-500">
            </div>
            <div class="flex justify-end gap-2">
                <button id="btn-cancel-create" class="px-3 py-1.5 bg-slate-800 text-slate-300 rounded text-xs font-medium">Cancel</button>
                <button id="btn-confirm-create" class="px-4 py-1.5 bg-indigo-600 text-white rounded text-xs font-semibold">Create</button>
            </div>
        </div>
    </div>

    <!-- JAVASCRIPT APPLICATION LOGIC -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            // Model definitions per provider
            const providerModels = {
                gemini: [
                    { id: 'gemini-3.6-flash', name: 'Gemini 3.6 Flash' },
                    { id: 'gemini-3.6-pro', name: 'Gemini 3.6 Pro' },
                    { id: 'gemini-3.0-flash', name: 'Gemini 3.0 Flash' },
                    { id: 'gemini-2.5-flash', name: 'Gemini 2.5 Flash' }
                ],
                claude: [
                    { id: 'claude-3-7-sonnet-20250219', name: 'Claude 3.7 Sonnet' },
                    { id: 'claude-3-5-sonnet-20241022', name: 'Claude 3.5 Sonnet' },
                    { id: 'claude-3-5-haiku-20241022', name: 'Claude 3.5 Haiku' }
                ],
                gpt: [
                    { id: 'gpt-4o', name: 'GPT-4o' },
                    { id: 'gpt-4o-mini', name: 'GPT-4o-mini' },
                    { id: 'o3-mini', name: 'o3-mini' }
                ],
                kimi: [
                    { id: 'moonshot-v1-8k', name: 'Kimi (Moonshot v1 8k)' },
                    { id: 'moonshot-v1-32k', name: 'Kimi (Moonshot v1 32k)' },
                    { id: 'moonshot-v1-128k', name: 'Kimi (Moonshot v1 128k)' }
                ],
                deepseek: [
                    { id: 'deepseek-chat', name: 'DeepSeek V3' },
                    { id: 'deepseek-reasoner', name: 'DeepSeek R1' }
                ],
                ollama: [
                    { id: 'llama3', name: 'Llama 3' },
                    { id: 'qwen2.5-coder', name: 'Qwen 2.5 Coder' },
                    { id: 'deepseek-coder', name: 'DeepSeek Coder' }
                ]
            };

            // State management
            const state = {
                openTabs: [], // array of { path, filename, content, isDirty, extension }
                activeTabPath: null,
                targetDirectory: '',
                projectTree: [],
                lastGeneratedCode: '',
                lastGeneratedTarget: '',
                createModalType: 'file', // 'file' or 'directory'
            };

            // DOM elements
            const selectProvider = document.getElementById('select-provider');
            const selectModel = document.getElementById('select-model');
            const modelBadge = document.getElementById('model-badge');
            const fileTreeContainer = document.getElementById('file-tree-container');
            const tabsContainer = document.getElementById('tabs-container');
            const codeEditorInput = document.getElementById('code-editor-input');
            const lineNumbers = document.getElementById('line-numbers');
            const currentFilePath = document.getElementById('current-filepath');
            const fileLangBadge = document.getElementById('file-language-badge');
            const unsavedIndicator = document.getElementById('unsaved-indicator');
            const statusMessage = document.getElementById('status-message');
            const statusChars = document.getElementById('status-chars');
            const targetDirBadge = document.getElementById('target-dir-badge');
            const activeTargetDisplay = document.getElementById('active-target-display');
            const chatTargetFolder = document.getElementById('chat-target-folder');
            const chatActiveFile = document.getElementById('chat-active-file');
            const agentPromptInput = document.getElementById('agent-prompt-input');
            const btnSubmitPrompt = document.getElementById('btn-submit-prompt');
            const agentTimeline = document.getElementById('agent-timeline');
            const btnSaveFile = document.getElementById('btn-save-file');
            const btnToggleDiff = document.getElementById('btn-toggle-diff');
            const diffDrawer = document.getElementById('diff-drawer');
            const diffCodeView = document.getElementById('diff-code-view');
            const btnApplyDiff = document.getElementById('btn-apply-diff');
            const btnCloseDiff = document.getElementById('btn-close-diff');

            // Populate Model Selector
            function updateModelOptions() {
                const provider = selectProvider.value;
                const models = providerModels[provider] || [];
                selectModel.innerHTML = '';
                models.forEach(m => {
                    const opt = document.createElement('option');
                    opt.value = m.id;
                    opt.textContent = m.name;
                    selectModel.appendChild(opt);
                });
                modelBadge.textContent = models[0]?.name || provider.toUpperCase();
            }

            selectProvider.addEventListener('change', () => {
                updateModelOptions();
            });

            selectModel.addEventListener('change', () => {
                const selectedOpt = selectModel.options[selectModel.selectedIndex];
                if (selectedOpt) modelBadge.textContent = selectedOpt.textContent;
            });

            updateModelOptions();

            // Load API Keys from LocalStorage
            function loadSavedKeys() {
                document.getElementById('key-gemini').value = localStorage.getItem('agent_key_gemini') || '';
                document.getElementById('key-claude').value = localStorage.getItem('agent_key_claude') || '';
                document.getElementById('key-gpt').value = localStorage.getItem('agent_key_gpt') || '';
                document.getElementById('key-kimi').value = localStorage.getItem('agent_key_kimi') || '';
                document.getElementById('key-deepseek').value = localStorage.getItem('agent_key_deepseek') || '';
            }

            function getActiveApiKey() {
                const provider = selectProvider.value;
                return localStorage.getItem('agent_key_' + provider) || '';
            }

            // Settings Modal Logic
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
                settingsModal.classList.add('hidden');
                showNotification('API Keys saved successfully!');
            });

            // Target Directory handling
            function setTargetDirectory(dirPath) {
                state.targetDirectory = dirPath || '';
                const display = state.targetDirectory ? '/' + state.targetDirectory : '/ (Root)';
                targetDirBadge.textContent = display;
                activeTargetDisplay.textContent = display;
                chatTargetFolder.textContent = display;
            }

            document.getElementById('btn-reset-target-dir').addEventListener('click', () => {
                setTargetDirectory('');
            });

            // Fetch and Render Project Tree
            async function fetchTree() {
                fileTreeContainer.innerHTML = `
                    <div class="p-4 text-center text-slate-500">
                        <i class="fa-solid fa-circle-notch fa-spin mr-2"></i> Loading project tree...
                    </div>
                `;
                try {
                    const res = await fetch('{{ route("ide.api.tree") }}');
                    const data = await res.json();
                    if (data.success) {
                        state.projectTree = data.tree;
                        document.getElementById('workspace-root-name').textContent = data.root;
                        renderTree(data.tree, fileTreeContainer);
                    }
                } catch (err) {
                    fileTreeContainer.innerHTML = `<div class="p-3 text-red-400 text-xs">Failed to load file tree.</div>`;
                }
            }

            function getFileIcon(extension, filename) {
                const ext = (extension || '').toLowerCase();
                if (filename === 'package.json') return '<i class="fa-brands fa-npm text-red-400"></i>';
                if (filename === 'composer.json') return '<i class="fa-brands fa-php text-indigo-400"></i>';
                if (ext === 'php') return '<i class="fa-brands fa-php text-blue-400"></i>';
                if (ext === 'vue') return '<i class="fa-brands fa-vuejs text-emerald-400"></i>';
                if (ext === 'js' || ext === 'ts') return '<i class="fa-brands fa-js text-amber-300"></i>';
                if (ext === 'json') return '<i class="fa-solid fa-brackets-curly text-yellow-500"></i>';
                if (ext === 'css' || ext === 'scss') return '<i class="fa-brands fa-css3-alt text-cyan-400"></i>';
                if (ext === 'html' || ext === 'blade.php') return '<i class="fa-brands fa-html5 text-orange-400"></i>';
                if (ext === 'md') return '<i class="fa-brands fa-markdown text-sky-400"></i>';
                return '<i class="fa-regular fa-file-code text-slate-400"></i>';
            }

            function renderTree(items, container, level = 0) {
                container.innerHTML = '';
                items.forEach(item => {
                    const row = document.createElement('div');
                    row.className = `flex items-center justify-between px-2 py-1 rounded hover:bg-slate-800/80 cursor-pointer group text-slate-300 transition text-[11px]`;
                    row.style.paddingLeft = `${level * 12 + 8}px`;

                    if (item.isDir) {
                        row.innerHTML = `
                            <div class="flex items-center gap-1.5 truncate flex-1">
                                <i class="fa-solid fa-chevron-right text-[10px] text-slate-500 group-hover:text-slate-300 transition transform dir-arrow"></i>
                                <i class="fa-solid fa-folder text-amber-400 text-xs"></i>
                                <span class="font-medium text-slate-200 truncate">${item.name}</span>
                            </div>
                            <button title="Set as AI Target Directory" class="btn-set-target opacity-0 group-hover:opacity-100 px-1.5 py-0.5 rounded bg-indigo-950 text-indigo-400 hover:text-white text-[9px] border border-indigo-500/30">
                                Target
                            </button>
                        `;

                        const childContainer = document.createElement('div');
                        childContainer.className = 'hidden space-y-0.5';

                        row.addEventListener('click', (e) => {
                            if (e.target.closest('.btn-set-target')) {
                                setTargetDirectory(item.path);
                                showNotification(`Target folder set to /${item.path}`);
                                return;
                            }
                            const isHidden = childContainer.classList.contains('hidden');
                            const arrow = row.querySelector('.dir-arrow');
                            if (isHidden) {
                                childContainer.classList.remove('hidden');
                                arrow.classList.add('rotate-90');
                            } else {
                                childContainer.classList.add('hidden');
                                arrow.classList.remove('rotate-90');
                            }
                        });

                        container.appendChild(row);
                        container.appendChild(childContainer);
                        if (item.children && item.children.length > 0) {
                            renderTree(item.children, childContainer, level + 1);
                        }
                    } else {
                        row.innerHTML = `
                            <div class="flex items-center gap-1.5 truncate flex-1">
                                ${getFileIcon(item.extension, item.name)}
                                <span class="text-slate-300 truncate">${item.name}</span>
                            </div>
                        `;

                        row.addEventListener('click', () => {
                            openFile(item.path);
                        });

                        container.appendChild(row);
                    }
                });
            }

            document.getElementById('btn-refresh-tree').addEventListener('click', fetchTree);

            // Open File into Tabs
            async function openFile(filePath) {
                // Check if already open in tabs
                let tab = state.openTabs.find(t => t.path === filePath);
                if (!tab) {
                    try {
                        const res = await fetch('{{ route("ide.api.file.read") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken
                            },
                            body: JSON.stringify({ path: filePath })
                        });
                        const data = await res.json();
                        if (data.success) {
                            tab = {
                                path: data.path,
                                filename: data.filename,
                                content: data.content,
                                extension: data.extension,
                                isDirty: false
                            };
                            state.openTabs.push(tab);
                        } else {
                            showNotification('Failed to read file: ' + data.error, true);
                            return;
                        }
                    } catch (err) {
                        showNotification('Network error loading file', true);
                        return;
                    }
                }

                switchTab(tab.path);
            }

            function renderTabs() {
                tabsContainer.innerHTML = '';
                state.openTabs.forEach(tab => {
                    const tabEl = document.createElement('div');
                    const isActive = tab.path === state.activeTabPath;
                    tabEl.className = `flex items-center gap-2 px-3 py-1.5 rounded-t text-xs font-medium cursor-pointer border-t-2 transition ${isActive ? 'bg-[#0c101c] text-indigo-300 border-indigo-500' : 'bg-[#0d1322] text-slate-400 border-transparent hover:bg-slate-800'}`;
                    
                    tabEl.innerHTML = `
                        ${getFileIcon(tab.extension, tab.filename)}
                        <span class="truncate max-w-[120px]">${tab.filename}</span>
                        <span class="w-1.5 h-1.5 rounded-full ${tab.isDirty ? 'bg-amber-400' : 'hidden'}"></span>
                        <button class="close-tab hover:text-red-400 ml-1 text-[11px]">&times;</button>
                    `;

                    tabEl.addEventListener('click', (e) => {
                        if (e.target.classList.contains('close-tab')) {
                            e.stopPropagation();
                            closeTab(tab.path);
                        } else {
                            switchTab(tab.path);
                        }
                    });

                    tabsContainer.appendChild(tabEl);
                });
            }

            function switchTab(path) {
                state.activeTabPath = path;
                const tab = state.openTabs.find(t => t.path === path);
                if (tab) {
                    codeEditorInput.value = tab.content;
                    currentFilePath.textContent = tab.path;
                    fileLangBadge.textContent = (tab.extension || 'txt').toUpperCase();
                    chatActiveFile.textContent = tab.filename;
                    unsavedIndicator.classList.toggle('hidden', !tab.isDirty);
                    updateLineNumbers();
                    updateCharCount();
                } else {
                    codeEditorInput.value = '';
                    currentFilePath.textContent = 'No file selected';
                    fileLangBadge.textContent = 'TEXT';
                    chatActiveFile.textContent = 'None';
                    unsavedIndicator.classList.add('hidden');
                }
                renderTabs();
            }

            function closeTab(path) {
                const idx = state.openTabs.findIndex(t => t.path === path);
                if (idx !== -1) {
                    state.openTabs.splice(idx, 1);
                    if (state.activeTabPath === path) {
                        const nextTab = state.openTabs[state.openTabs.length - 1];
                        switchTab(nextTab ? nextTab.path : null);
                    } else {
                        renderTabs();
                    }
                }
            }

            // Editor updates and line numbers
            function updateLineNumbers() {
                const lines = codeEditorInput.value.split('\n').length;
                let numHtml = '';
                for (let i = 1; i <= Math.max(lines, 1); i++) {
                    numHtml += `${i}<br>`;
                }
                lineNumbers.innerHTML = numHtml;
            }

            function updateCharCount() {
                statusChars.textContent = `${codeEditorInput.value.length} chars`;
            }

            codeEditorInput.addEventListener('input', () => {
                const activeTab = state.openTabs.find(t => t.path === state.activeTabPath);
                if (activeTab) {
                    activeTab.content = codeEditorInput.value;
                    activeTab.isDirty = true;
                    unsavedIndicator.classList.remove('hidden');
                    renderTabs();
                }
                updateLineNumbers();
                updateCharCount();
            });

            // Sync line numbers scrolling
            codeEditorInput.addEventListener('scroll', () => {
                lineNumbers.scrollTop = codeEditorInput.scrollTop;
            });

            // Tab key indentation support in textarea
            codeEditorInput.addEventListener('keydown', (e) => {
                if (e.key === 'Tab') {
                    e.preventDefault();
                    const start = codeEditorInput.selectionStart;
                    const end = codeEditorInput.selectionEnd;
                    codeEditorInput.value = codeEditorInput.value.substring(0, start) + '    ' + codeEditorInput.value.substring(end);
                    codeEditorInput.selectionStart = codeEditorInput.selectionEnd = start + 4;
                    codeEditorInput.dispatchEvent(new Event('input'));
                }
            });

            // Save File
            async function saveCurrentFile() {
                const activeTab = state.openTabs.find(t => t.path === state.activeTabPath);
                if (!activeTab) {
                    showNotification('No active file to save', true);
                    return;
                }

                try {
                    statusMessage.textContent = 'Saving file...';
                    const res = await fetch('{{ route("ide.api.file.save") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({
                            path: activeTab.path,
                            content: codeEditorInput.value
                        })
                    });
                    const data = await res.json();
                    if (data.success) {
                        activeTab.isDirty = false;
                        unsavedIndicator.classList.add('hidden');
                        renderTabs();
                        statusMessage.textContent = 'Saved at ' + new Date().toLocaleTimeString();
                        showNotification(`File saved: ${activeTab.path}`);
                    } else {
                        showNotification('Save failed: ' + data.error, true);
                    }
                } catch (err) {
                    showNotification('Error saving file', true);
                }
            }

            btnSaveFile.addEventListener('click', saveCurrentFile);

            // Ctrl+S shortcut
            window.addEventListener('keydown', (e) => {
                if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                    e.preventDefault();
                    saveCurrentFile();
                }
            });

            // Modal: Create New File / Folder
            const createModal = document.getElementById('create-modal');
            const createModalTitle = document.getElementById('create-modal-title');
            const createModalInput = document.getElementById('create-modal-input');

            document.getElementById('btn-new-file').addEventListener('click', () => {
                state.createModalType = 'file';
                createModalTitle.textContent = 'Create New File in Workspace';
                createModalInput.value = state.targetDirectory ? `${state.targetDirectory}/NewFile.php` : 'NewFile.php';
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
                    const res = await fetch('{{ route("ide.api.file.create") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({
                            path: path,
                            type: state.createModalType,
                            content: ''
                        })
                    });
                    const data = await res.json();
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

            // Prompt pills click
            document.querySelectorAll('.prompt-pill').forEach(btn => {
                btn.addEventListener('click', () => {
                    agentPromptInput.value = btn.getAttribute('data-prompt');
                    agentPromptInput.focus();
                });
            });

            // Submit Agent Prompt
            btnSubmitPrompt.addEventListener('click', executeAgentPrompt);
            agentPromptInput.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    executeAgentPrompt();
                }
            });

            async function executeAgentPrompt() {
                const prompt = agentPromptInput.value.trim();
                if (!prompt) return;

                const provider = selectProvider.value;
                const model = selectModel.value;
                const apiKey = getActiveApiKey();

                btnSubmitPrompt.disabled = true;
                btnSubmitPrompt.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1"></i> Synthesizing...';
                document.getElementById('agent-status-label').textContent = 'AI Agent Thinking...';

                // Add User Prompt Card to Stream
                const userCard = document.createElement('div');
                userCard.className = 'p-3 rounded-lg bg-indigo-950/40 border border-indigo-500/30 text-xs space-y-1';
                userCard.innerHTML = `
                    <div class="flex items-center justify-between text-indigo-300 font-semibold text-[11px]">
                        <span class="flex items-center gap-1.5"><i class="fa-solid fa-user"></i> You</span>
                        <span class="text-[10px] text-indigo-400/70 font-mono">${new Date().toLocaleTimeString()}</span>
                    </div>
                    <p class="text-slate-200">${escapeHtml(prompt)}</p>
                `;
                agentTimeline.appendChild(userCard);

                // Add Agent Execution Card with Loading Steps
                const agentCard = document.createElement('div');
                agentCard.className = 'p-3.5 rounded-xl bg-slate-900 border border-purple-500/30 shadow-lg space-y-3';
                agentCard.innerHTML = `
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-wand-magic-sparkles text-purple-400"></i>
                            <span class="font-bold text-slate-100 text-xs">${provider.toUpperCase()} Agent</span>
                        </div>
                        <span class="text-[10px] font-mono px-2 py-0.5 rounded bg-purple-500/10 text-purple-300">${model}</span>
                    </div>
                    <div class="steps-box space-y-2 text-[11px]">
                        <div class="flex items-center gap-2 text-slate-400">
                            <i class="fa-solid fa-circle-notch fa-spin text-indigo-400"></i>
                            <span>Analyzing workspace & formulating reasoning...</span>
                        </div>
                    </div>
                `;
                agentTimeline.appendChild(agentCard);
                agentTimeline.scrollTop = agentTimeline.scrollHeight;

                try {
                    const activeTab = state.openTabs.find(t => t.path === state.activeTabPath);
                    const res = await fetch('{{ route("ide.api.agent.prompt") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({
                            provider: provider,
                            model: model,
                            apiKey: apiKey,
                            prompt: prompt,
                            targetDirectory: state.targetDirectory,
                            targetFile: activeTab ? activeTab.path : '',
                            currentCode: activeTab ? activeTab.content : ''
                        })
                    });

                    const data = await res.json();
                    if (data.success) {
                        state.lastGeneratedCode = data.code;
                        state.lastGeneratedTarget = data.targetPath;

                        // Render Agent Reasoning Steps
                        let stepsHtml = '';
                        (data.steps || []).forEach(step => {
                            stepsHtml += `
                                <div class="p-2 rounded bg-slate-950/60 border border-slate-800 flex items-start gap-2">
                                    <i class="fa-solid fa-circle-check text-emerald-400 text-xs mt-0.5"></i>
                                    <div>
                                        <div class="font-semibold text-slate-200">${escapeHtml(step.title)}</div>
                                        <div class="text-slate-400 text-[10px] mt-0.5">${escapeHtml(step.detail)}</div>
                                    </div>
                                </div>
                            `;
                        });

                        // Action Card (Write to File / Open / Copy)
                        let codePreviewHtml = '';
                        if (data.code) {
                            codePreviewHtml = `
                                <div class="mt-3 p-2.5 rounded-lg bg-slate-950 border border-slate-800 space-y-2">
                                    <div class="flex items-center justify-between text-[11px]">
                                        <span class="text-indigo-300 font-mono flex items-center gap-1.5">
                                            <i class="fa-regular fa-file-code"></i> ${escapeHtml(data.targetPath || 'Generated')}
                                        </span>
                                        <span class="text-[10px] text-slate-500 uppercase font-mono">${data.language || 'code'}</span>
                                    </div>
                                    <pre class="max-h-40 overflow-y-auto p-2 bg-slate-900/90 rounded text-[11px] font-mono text-slate-300 leading-5"><code>${escapeHtml(data.code)}</code></pre>
                                    <div class="flex flex-wrap items-center gap-2 pt-1">
                                        <button class="btn-write-target px-3 py-1.5 rounded-md bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-semibold text-[11px] flex items-center gap-1.5 shadow transition">
                                            <i class="fa-solid fa-bolt"></i>
                                            <span>Write to Target File</span>
                                        </button>
                                        <button class="btn-view-diff px-2.5 py-1.5 rounded-md bg-purple-600/20 hover:bg-purple-600/30 text-purple-300 border border-purple-500/30 font-medium text-[11px] transition">
                                            <i class="fa-solid fa-code-compare mr-1"></i> View Diff
                                        </button>
                                        <button class="btn-copy-code px-2.5 py-1.5 rounded-md bg-slate-800 hover:bg-slate-700 text-slate-300 text-[11px] transition">
                                            <i class="fa-regular fa-copy"></i>
                                        </button>
                                    </div>
                                </div>
                            `;
                        }

                        agentCard.querySelector('.steps-box').innerHTML = stepsHtml + codePreviewHtml;

                        // Wire up buttons in generated card
                        const writeBtn = agentCard.querySelector('.btn-write-target');
                        if (writeBtn) {
                            writeBtn.addEventListener('click', async () => {
                                await writeCodeDirectly(data.targetPath, data.code);
                            });
                        }

                        const diffBtn = agentCard.querySelector('.btn-view-diff');
                        if (diffBtn) {
                            diffBtn.addEventListener('click', () => {
                                showDiffView(data.targetPath, data.code);
                            });
                        }

                        const copyBtn = agentCard.querySelector('.btn-copy-code');
                        if (copyBtn) {
                            copyBtn.addEventListener('click', () => {
                                navigator.clipboard.writeText(data.code);
                                showNotification('Code copied to clipboard!');
                            });
                        }

                        agentPromptInput.value = '';
                        showNotification('Agentic task completed successfully!');
                    } else {
                        agentCard.querySelector('.steps-box').innerHTML = `
                            <div class="p-2.5 rounded bg-red-950/40 border border-red-500/30 text-red-300 text-xs">
                                <i class="fa-solid fa-circle-exclamation mr-1.5"></i> ${escapeHtml(data.error || 'Agent generation failed')}
                            </div>
                        `;
                    }
                } catch (err) {
                    agentCard.querySelector('.steps-box').innerHTML = `
                        <div class="p-2.5 rounded bg-red-950/40 border border-red-500/30 text-red-300 text-xs">
                            <i class="fa-solid fa-triangle-exclamation mr-1.5"></i> Network error connecting to agent
                        </div>
                    `;
                } finally {
                    btnSubmitPrompt.disabled = false;
                    btnSubmitPrompt.innerHTML = '<i class="fa-solid fa-paper-plane text-xs mr-1"></i> Generate & Write';
                    document.getElementById('agent-status-label').textContent = 'Agent Engine Ready';
                    agentTimeline.scrollTop = agentTimeline.scrollHeight;
                }
            }

            // Write Code Directly into Selected File / Directory
            async function writeCodeDirectly(targetPath, codeContent) {
                if (!targetPath) {
                    showNotification('No target path specified', true);
                    return;
                }

                try {
                    statusMessage.textContent = 'Writing code to ' + targetPath + '...';
                    const res = await fetch('{{ route("ide.api.file.save") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({
                            path: targetPath,
                            content: codeContent
                        })
                    });
                    const data = await res.json();
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

            // Diff View Logic
            function showDiffView(targetPath, newCode) {
                diffDrawer.classList.remove('hidden');
                btnToggleDiff.classList.remove('hidden');
                diffCodeView.textContent = `--- Original (${targetPath})\n+++ Proposed AI Changes\n\n` + newCode;
            }

            btnCloseDiff.addEventListener('click', () => {
                diffDrawer.classList.add('hidden');
            });

            btnApplyDiff.addEventListener('click', async () => {
                if (state.lastGeneratedTarget && state.lastGeneratedCode) {
                    await writeCodeDirectly(state.lastGeneratedTarget, state.lastGeneratedCode);
                    diffDrawer.classList.add('hidden');
                }
            });

            // Notification toast helper
            function showNotification(msg, isError = false) {
                const toast = document.createElement('div');
                toast.className = `fixed bottom-8 right-8 z-50 px-4 py-2.5 rounded-lg text-xs font-semibold shadow-2xl flex items-center gap-2 border transition-all duration-300 transform translate-y-2 opacity-0 ${isError ? 'bg-red-950 border-red-500 text-red-200' : 'bg-slate-900 border-indigo-500 text-indigo-200'}`;
                toast.innerHTML = `<i class="fa-solid ${isError ? 'fa-triangle-exclamation text-red-400' : 'fa-circle-check text-emerald-400'}"></i> <span>${escapeHtml(msg)}</span>`;
                document.body.appendChild(toast);

                requestAnimationFrame(() => {
                    toast.classList.remove('translate-y-2', 'opacity-0');
                });

                setTimeout(() => {
                    toast.classList.add('opacity-0', 'translate-y-2');
                    setTimeout(() => toast.remove(), 300);
                }, 3500);
            }

            function escapeHtml(str) {
                if (!str) return '';
                return String(str)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;');
            }

            // Initial load
            fetchTree();
            loadSavedKeys();
        });
    </script>
</body>
</html>
