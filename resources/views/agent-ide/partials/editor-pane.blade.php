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

    {{-- Agent Changes Pending Review Bar --}}
    <div id="editor-agent-review-bar" class="hidden bg-gradient-to-r from-purple-950/90 via-indigo-950/90 to-slate-900 border-b border-indigo-500/40 px-4 py-2 flex items-center justify-between z-20 shrink-0 shadow-lg animate-fadeIn">
        <div class="flex items-center gap-3 min-w-0">
            <span class="flex h-2.5 w-2.5 relative">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
            </span>
            <div class="min-w-0">
                <div class="text-xs font-semibold text-slate-100 flex items-center gap-2 truncate">
                    <i class="fa-solid fa-wand-magic-sparkles text-indigo-400"></i>
                    <span>Agent wrote changes to</span>
                    <span id="review-bar-filepath" class="font-mono text-indigo-300 bg-indigo-950/80 px-2 py-0.5 rounded border border-indigo-500/30 text-[11px]"></span>
                    <span class="text-[10px] uppercase font-mono px-1.5 py-0.5 rounded bg-amber-500/20 text-amber-300 border border-amber-500/30 font-bold">Unsaved AI Draft</span>
                </div>
                <div class="text-[11px] text-slate-400 flex items-center gap-2 mt-0.5">
                    <span>Review changes below. Click <strong>Accept</strong> to save or <strong>Reject</strong> to revert.</span>
                    <span class="text-slate-600 hidden sm:inline">•</span>
                    <span class="font-mono text-[10px] text-slate-500 hidden sm:inline">Shortcuts: Ctrl+Enter (Accept) | Esc (Reject)</span>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-2 shrink-0">
            <button id="btn-editor-diff" class="px-2.5 py-1.5 rounded bg-purple-600/20 hover:bg-purple-600/30 text-purple-300 border border-purple-500/30 font-medium text-xs flex items-center gap-1.5 transition">
                <i class="fa-solid fa-code-compare"></i>
                <span id="btn-editor-diff-text">View Diff</span>
            </button>
            <button id="btn-editor-reject" class="px-3 py-1.5 rounded bg-rose-600/20 hover:bg-rose-600/30 text-rose-300 border border-rose-500/40 font-semibold text-xs flex items-center gap-1.5 shadow-sm transition">
                <i class="fa-solid fa-xmark"></i>
                <span>Reject</span>
            </button>
            <button id="btn-editor-accept" class="px-3.5 py-1.5 rounded bg-emerald-600 hover:bg-emerald-500 text-white font-semibold text-xs flex items-center gap-1.5 shadow-lg shadow-emerald-600/20 transition">
                <i class="fa-solid fa-check"></i>
                <span>Accept Changes</span>
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
                        <button id="btn-apply-diff" class="px-3 py-1 bg-emerald-600 hover:bg-emerald-500 text-white rounded text-xs font-semibold shadow transition flex items-center gap-1">
                            <i class="fa-solid fa-check"></i> Accept Changes
                        </button>
                        <button id="btn-reject-diff" class="px-3 py-1 bg-rose-600/20 hover:bg-rose-600/30 text-rose-300 border border-rose-500/40 rounded text-xs font-semibold transition flex items-center gap-1">
                            <i class="fa-solid fa-xmark"></i> Reject Changes
                        </button>
                        <button id="btn-close-diff" class="px-2 py-1 text-slate-400 hover:text-white text-xs">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                </div>
                <div class="flex-1 p-3 overflow-auto font-mono text-xs bg-slate-950/80">
                    <pre id="diff-code-view" class="text-slate-300 font-mono"></pre>
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
