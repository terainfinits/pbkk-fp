{{-- Center pane: tabs, breadcrumbs, code editor, diff drawer, terminal console, status bar. --}}
<main class="flex-1 flex flex-col bg-[#0b0f19] overflow-hidden">
    <div id="tabs-container" class="h-10 bg-[#0e1424] border-b border-slate-800 flex items-center px-1 overflow-x-auto shrink-0 space-x-1"></div>

    <div class="h-8 bg-[#0d1220] border-b border-slate-800/60 px-4 flex items-center justify-between text-xs text-slate-400 shrink-0">
        <div class="flex items-center gap-2">
            <i class="fa-regular fa-file-code text-indigo-400"></i>
            <span id="current-filepath" class="font-mono text-slate-300 text-xs">No file selected</span>
            <span id="unsaved-indicator" class="hidden w-2 h-2 rounded-full bg-amber-400" title="Unsaved changes"></span>
        </div>
        <div class="flex items-center gap-3 text-[11px]">
            <span id="active-kernel-badge" class="px-2 py-0.5 rounded bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 font-mono text-[10px] flex items-center gap-1">
                <i class="fa-solid fa-bolt text-[9px]"></i> <span id="kernel-badge-text">Python / PHP / Node</span>
            </span>
            <span id="file-language-badge" class="px-1.5 py-0.5 rounded bg-slate-800 text-slate-400 uppercase font-mono text-[10px]">TEXT</span>
            <span id="editor-cursor-pos" class="font-mono text-slate-500">Ln 1, Col 1</span>
            <button id="btn-editor-run" class="px-2.5 py-0.5 rounded bg-emerald-600/30 hover:bg-emerald-600/40 text-emerald-300 border border-emerald-500/40 font-medium transition flex items-center gap-1 text-[11px]">
                <i class="fa-solid fa-play text-[10px]"></i> Run Code
            </button>
            <button id="btn-toggle-diff" class="hidden px-2 py-0.5 rounded bg-purple-600/30 text-purple-300 border border-purple-500/40 hover:bg-purple-600/40 transition">
                <i class="fa-solid fa-code-compare mr-1"></i> Diff View
            </button>
        </div>
    </div>

    <div class="flex-1 relative overflow-hidden flex flex-col bg-[#0c101c]">
        <div class="flex-1 relative overflow-hidden flex">
            <div id="line-numbers" class="w-12 py-3 bg-[#0a0e1a] text-right pr-3 text-slate-600 font-mono text-xs select-none border-r border-slate-800/60 overflow-hidden leading-6">
                1
            </div>

            <div class="flex-1 relative h-full overflow-hidden">
                <textarea id="code-editor-input" spellcheck="false" class="editor-textarea code-font w-full h-full p-3 bg-transparent text-slate-200 text-xs leading-6 outline-none border-none overflow-auto font-mono z-10 relative" placeholder="// Select a file from the explorer or ask the AI to generate code... (Python, PHP, Node supported)"></textarea>
            </div>

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

        @include('agent-ide.partials.terminal-drawer')
    </div>

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
