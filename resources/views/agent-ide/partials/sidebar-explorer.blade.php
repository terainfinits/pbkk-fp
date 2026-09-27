{{-- Left sidebar: file explorer / project tree + AI target folder picker. --}}
<aside class="w-64 bg-[#0b0f1a] border-r border-slate-800 flex flex-col shrink-0">
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

    <div class="p-2 bg-slate-900/60 border-b border-slate-800/80 text-[11px]">
        <div class="flex items-center justify-between text-slate-400 mb-1">
            <span>AI Target Folder:</span>
            <button id="btn-reset-target-dir" class="text-indigo-400 hover:underline">Reset Root</button>
        </div>
        <div id="active-target-display" class="px-2 py-1 bg-slate-950 rounded text-indigo-300 font-mono text-[10px] truncate border border-slate-800">
            /
        </div>
    </div>

    <div id="file-tree-container" class="flex-1 overflow-y-auto p-2 space-y-0.5 text-xs select-none">
        <div class="p-4 text-center text-slate-500 animate-pulse">
            <i class="fa-solid fa-circle-notch fa-spin mr-2"></i> Loading project tree...
        </div>
    </div>

    <div class="p-2.5 border-t border-slate-800 bg-[#090d16] text-[10px] text-slate-500">
        <span class="text-slate-400 font-medium">Tip:</span> Select folder to target AI file generation.
    </div>
</aside>
