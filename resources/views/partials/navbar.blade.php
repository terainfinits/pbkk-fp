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
            </ul>
        </nav>

    </div>
</header>
