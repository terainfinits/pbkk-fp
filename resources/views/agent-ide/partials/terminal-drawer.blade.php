@php
    $isWindows = PHP_OS_FAMILY === 'Windows';
    $promptSign = $isWindows ? 'PS>' : '$';
    $promptCwd  = $isWindows ? 'PS ~>' : '~ $';
    $examples   = $isWindows ? 'python script.py, dir, cd app' : 'python3 script.py, ls, cd app';
    $shellDesc  = $isWindows 
        ? 'Windows PowerShell — type commands below. Supports python, php, node, dir, cd, and all PowerShell commands.' 
        : 'Unix Shell — type commands below. Supports python3, php, node, ls, cd, and standard shell commands.';
@endphp

{{-- Bottom terminal drawer — interactive shell + kernel console. --}}
<div id="terminal-drawer" class="bg-[#080c14] border-t border-slate-800 flex flex-col shrink-0 transition-all duration-200" style="height: 220px;">

    {{-- ── Tab bar ── --}}
    <div class="h-8 bg-[#0d1322] px-3 flex items-center justify-between border-b border-slate-800 text-xs select-none shrink-0">
        <div class="flex items-center gap-1">
            {{-- Shell tab --}}
            <button id="terminal-tab-shell"
                    class="terminal-tab px-3 py-1 rounded-t flex items-center gap-1.5 text-slate-200 font-semibold bg-[#080c14] border border-slate-700 border-b-0 -mb-px relative z-10 transition"
                    title="Terminal">
                <i class="fa-solid fa-terminal text-cyan-400 text-[10px]"></i>
                <span>Terminal</span>
            </button>
            {{-- Kernel console tab --}}
            <button id="terminal-tab-kernel"
                    class="terminal-tab px-3 py-1 rounded-t flex items-center gap-1.5 text-slate-500 hover:text-slate-300 bg-transparent border border-transparent transition"
                    title="Kernel execution output">
                <i class="fa-solid fa-microchip text-indigo-400 text-[10px]"></i>
                <span>Kernel</span>
            </button>

            <span id="terminal-kernel-badge" class="ml-2 px-2 py-0.5 rounded bg-indigo-500/20 text-indigo-300 border border-indigo-500/30 text-[10px] font-mono">
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

    {{-- ── Output body (shared between shell & kernel) ── --}}
    <div id="terminal-output-body" class="flex-1 p-3 overflow-y-auto font-mono text-[11px] leading-5 text-slate-300 bg-[#060910] space-y-0 select-text">
        <div class="text-slate-500 flex items-center gap-2">
            <span class="text-cyan-400 font-bold">{{ $promptSign }}</span>
            <span>{!! $shellDesc !!}</span>
        </div>
    </div>

    {{-- ── Interactive prompt input ── --}}
    <div id="terminal-input-bar" class="shrink-0 bg-[#0a0f1a] border-t border-slate-800/70 px-3 py-1.5 flex items-center gap-2 font-mono text-[11px]">
        <span id="terminal-prompt-cwd" class="text-cyan-400 font-bold whitespace-nowrap select-none">{{ $promptCwd }}</span>
        <input  id="terminal-input"
                type="text"
                class="flex-1 bg-transparent text-slate-200 outline-none border-none placeholder-slate-600 caret-cyan-400 font-mono text-[11px]"
                placeholder="Type command here... (e.g. {{ $examples }})"
                autocomplete="off"
                spellcheck="false" />
    </div>
</div>