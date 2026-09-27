{{-- Bottom terminal console drawer used for kernel run output. --}}
<div id="terminal-drawer" class="h-44 bg-[#080c14] border-t border-slate-800 flex flex-col shrink-0 transition-all duration-200">
    <div class="h-8 bg-[#0d1322] px-3 flex items-center justify-between border-b border-slate-800 text-xs select-none">
        <div class="flex items-center gap-3">
            <div class="flex items-center gap-1.5 text-slate-200 font-semibold">
                <i class="fa-solid fa-terminal text-emerald-400"></i>
                <span>Magentic Kernel Console</span>
            </div>
            <span id="terminal-kernel-badge" class="px-2 py-0.5 rounded bg-indigo-500/20 text-indigo-300 border border-indigo-500/30 text-[10px] font-mono">
                System Kernel Ready
            </span>
            <span id="terminal-exec-time" class="text-[10px] text-slate-500 font-mono hidden">0ms</span>
        </div>
        <div class="flex items-center gap-2 text-[11px]">
            <button id="btn-clear-terminal" class="px-2 py-0.5 rounded hover:bg-slate-800 text-slate-400 hover:text-slate-200 transition" title="Clear console">
                <i class="fa-solid fa-trash-can mr-1 text-[10px]"></i> Clear
            </button>
            <button id="btn-toggle-terminal" class="px-2 py-0.5 rounded hover:bg-slate-800 text-slate-400 hover:text-slate-200 transition" title="Minimize/Restore terminal">
                <i id="terminal-toggle-icon" class="fa-solid fa-chevron-down text-[10px]"></i>
            </button>
        </div>
    </div>
    <div id="terminal-output-body" class="flex-1 p-3 overflow-y-auto font-mono text-[11px] leading-5 text-slate-300 bg-[#060910] space-y-1 select-text">
        <div class="text-slate-500 flex items-center gap-2">
            <span class="text-emerald-400 font-bold">➜</span>
            <span>Magentic Kernel Console initialized. Click "Run Code" or execute snippets in Chatbot to run Python, PHP, or Node.js scripts.</span>
        </div>
    </div>
</div>
