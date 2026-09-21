<header class="sticky top-0 z-50 bg-white/90 backdrop-blur-md border-b border-slate-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-center">
        <nav>
            <ul class="flex items-center space-x-8 text-sm font-semibold">
                <li>
                    <a href="{{ route('home') }}"
                        class="py-2 transition duration-200 {{ request()->routeIs('home') ? 'text-[#0055B8] border-b-2 border-[#0055B8] font-bold' : 'text-slate-600 hover:text-blue-400 hover:border-b-2 hover:border-blue-400' }}">
                        Home
                    </a>
                </li>
                <li>
                    <a href="{{ route('dashboard.mahasiswa.detail', ['nrp' => '5025241104']) }}"
                        class="py-2 transition duration-200 {{ request()->routeIs('dashboard.mahasiswa.detail') ? 'text-[#0055B8] border-b-2 border-[#0055B8] font-bold' : 'text-slate-600 hover:text-blue-400 hover:border-b-2 hover:border-blue-400' }}">
                        Profile
                    </a>
                </li>
                <li>
                    <a href="{{ route('agent.idea', ['tema' => $nextTema ?? 'General Assistant Agent']) }}"
                        class="py-2 transition duration-200 {{ request()->routeIs('agent.idea') ? 'text-[#0055B8] border-b-2 border-[#0055B8] font-bold' : 'text-slate-600 hover:text-blue-400 hover:border-b-2 hover:border-blue-400' }}">
                        @if(request()->routeIs('agent.idea'))
                            Switch to {{ $isCoding ?? false ? 'General Assistant Agent' : 'Coding Assistant Agent' }}
                        @else
                            Agent
                        @endif
                    </a>
                </li>
                <li>
                    <a href="{{ route('calculator.ipk') }}"
                        class="py-2 transition duration-200 {{ request()->routeIs('calculator.ipk') ? 'text-[#0055B8] border-b-2 border-[#0055B8] font-bold' : 'text-slate-600 hover:text-blue-400 hover:border-b-2 hover:border-blue-400' }}">
                        IPK Calculator
                    </a>
                </li>
                <li>
                    <a href="{{ route('ide.index') }}"
                        class="relative inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-semibold transition duration-200 {{ request()->routeIs('ide.*') ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-md shadow-indigo-500/25' : 'bg-slate-900 text-slate-100 hover:bg-slate-800' }}">
                        <i class="fa-solid fa-wand-magic-sparkles text-amber-300 text-xs"></i>
                        <span>Agentic IDE</span>
                        <span class="inline-block w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    </a>
                </li>
            </ul>
        </nav>

    </div>
</header>
