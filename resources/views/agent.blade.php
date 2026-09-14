<x-layout>

    <!-- Navbar -->
    @include('partials.navbar')

    @php
        // Normalize tema input for reliable matching
        $rawTema = urldecode($tema ?? '');
        $normalizedTema = strtolower(trim($rawTema));
        $isDefault = empty($normalizedTema) || in_array($normalizedTema, ['general assistant agent', 'general-assistant-agent', 'general_assistant_agent', 'general-assistant', 'general']);

        // Data for default General Assistant Agent
        $agentData = $isDefault ? [
            'name'             => 'General Assistant Agent',
            'status'           => 'Active Specification',
            'model'            => 'LLM Ensemble / Hybrid Engine',
            'autonomy'         => 'Level 3 (Semi-Autonomous with Human-in-the-Loop)',
            'primary_domain'   => 'Academic & General Technical Assistance',
            'latency'          => '< 500 ms average',
            'description'      => 'A multi-modal AI agent engineered to assist Informatics students and faculty with academic inquiry, code analysis, document processing, and administrative workflows.',
            'capabilities'     => [
                [
                    'title' => 'Code Analysis & Refactoring',
                    'icon'  => 'fa-solid fa-code',
                    'desc'  => 'Parses multi-language codebases, identifies syntax bugs, recommends design patterns, and auto-generates unit test suites.'
                ],
                [
                    'title' => 'Contextual Documentation Search',
                    'icon'  => 'fa-solid fa-book-bookmark',
                    'desc'  => 'Ingests academic syllabi, lecture slides, and department manuals to provide precise citations and instant summaries.'
                ],
                [
                    'title' => 'Task & Workflow Automation',
                    'icon'  => 'fa-solid fa-gears',
                    'desc'  => 'Executes multi-step operations like scheduling lab sessions, generating progress reports, and syncing deadlines.'
                ],
                [
                    'title' => 'Natural Language Reasoning',
                    'icon'  => 'fa-solid fa-brain',
                    'desc'  => 'Performs step-by-step analytical reasoning for complex computer science theoretical problems and logic proofs.'
                ]
            ],
            'use_cases' => [
                'Automated Code Review for Lab Assignments',
                'Interactive ITS Academic Rulebook Q&A',
                'Research Paper Summarization & Citation Extraction',
                'Automated Schedule Optimization'
            ]
        ] : null;
    @endphp

    <!-- Agent Hero & Details Section -->
    <section class="relative bg-gradient-to-b from-blue-50/50 via-white to-slate-50 py-12 lg:py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- Section Header -->
            <div class="mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <span class="text-xs font-semibold tracking-wider text-its-accent uppercase bg-its-light px-3 py-1 rounded-full">AI Agent Hub</span>
                    <h1 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight mt-2">
                        Agent Spec: <span class="text-its-accent">{{ $isDefault ? 'General Assistant' : ($rawTema ?: 'Unknown Topic') }}</span>
                    </h1>
                </div>

                @if(!$isDefault)
                    <a href="{{ route('agent.idea', ['tema' => 'general assistant agent']) }}" 
                       class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white border border-slate-200 text-slate-700 text-xs font-semibold hover:border-its-accent hover:text-its-accent shadow-sm transition">
                        <i class="fa-solid fa-rotate-left"></i> Load Default Spec
                    </a>
                @endif
            </div>

            @if($isDefault)
                <!-- Default Theme View: General Assistant Agent Details -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

                    <!-- Left Column: Agent Meta Card -->
                    <div class="lg:col-span-5 bg-white border border-slate-200/80 rounded-2xl p-6 sm:p-8 shadow-sm">
                        <div class="flex items-center gap-4 border-b border-slate-100 pb-6 mb-6">
                            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-its-accent to-its-blue text-white flex items-center justify-center text-2xl shadow-md shrink-0">
                                <i class="fa-solid fa-robot"></i>
                            </div>
                            <div>
                                <h2 class="text-xl font-bold text-slate-900">{{ $agentData['name'] }}</h2>
                                <span class="inline-flex items-center gap-1.5 text-xs font-medium text-emerald-600 bg-emerald-50 px-2.5 py-0.5 rounded-full border border-emerald-200/60 mt-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> {{ $agentData['status'] }}
                                </span>
                            </div>
                        </div>

                        <p class="text-sm text-slate-600 leading-relaxed mb-6">
                            {{ $agentData['description'] }}
                        </p>

                        <!-- Metadata List -->
                        <div class="space-y-4 border-t border-slate-100 pt-6">
                            <div class="flex items-start justify-between">
                                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Model Engine</span>
                                <span class="text-xs font-bold text-slate-800 text-right">{{ $agentData['model'] }}</span>
                            </div>
                            <div class="flex items-start justify-between">
                                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Autonomy Level</span>
                                <span class="text-xs font-bold text-slate-800 text-right">{{ $agentData['autonomy'] }}</span>
                            </div>
                            <div class="flex items-start justify-between">
                                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Primary Domain</span>
                                <span class="text-xs font-bold text-slate-800 text-right">{{ $agentData['primary_domain'] }}</span>
                            </div>
                            <div class="flex items-start justify-between">
                                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Latency Target</span>
                                <span class="text-xs font-bold text-slate-800 text-right">{{ $agentData['latency'] }}</span>
                            </div>
                        </div>

                        <!-- Target Use Cases -->
                        <div class="mt-8 pt-6 border-t border-slate-100">
                            <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-3">Primary Use Cases</h3>
                            <ul class="space-y-2">
                                @foreach($agentData['use_cases'] as $useCase)
                                    <li class="flex items-center gap-2 text-xs font-medium text-slate-700 bg-slate-50 p-2.5 rounded-xl border border-slate-200/60">
                                        <i class="fa-solid fa-circle-check text-its-accent text-xs shrink-0"></i>
                                        <span>{{ $useCase }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>

                    <!-- Right Column: Capabilities & Architecture -->
                    <div class="lg:col-span-7 space-y-6">
                        <div class="bg-white border border-slate-200/80 rounded-2xl p-6 sm:p-8 shadow-sm">
                            <h3 class="text-xl font-bold text-slate-900 mb-2">Core Capabilities</h3>
                            <p class="text-sm text-slate-500 mb-6">Pre-configured operational modules for academic productivity.</p>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                @foreach($agentData['capabilities'] as $cap)
                                    <div class="p-5 rounded-xl bg-slate-50 border border-slate-200/80 hover:border-its-accent/40 transition duration-200">
                                        <div class="w-10 h-10 rounded-lg bg-blue-50 text-its-accent flex items-center justify-center text-lg mb-3">
                                            <i class="{{ $cap['icon'] }}"></i>
                                        </div>
                                        <h4 class="text-sm font-bold text-slate-800 mb-1">{{ $cap['title'] }}</h4>
                                        <p class="text-xs text-slate-500 leading-relaxed">{{ $cap['desc'] }}</p>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Technical Card -->
                        <div class="bg-gradient-to-br from-its-blue to-its-accent text-white rounded-2xl p-6 sm:p-8 shadow-md relative overflow-hidden">
                            <div class="absolute inset-0 bg-[linear-gradient(to_right,#ffffff08_1px,transparent_1px),linear-gradient(to_bottom,#ffffff08_1px,transparent_1px)] bg-[size:16px_16px]"></div>

                            <div class="relative z-10">
                                <span class="text-xs font-bold uppercase tracking-wider text-blue-200">Execution Pipeline</span>
                                <h3 class="text-lg font-bold text-white mt-1 mb-2">RAG + Multi-Tool Architecture</h3>
                                <p class="text-xs text-blue-100 leading-relaxed max-w-xl">
                                    Integrates Retrieval-Augmented Generation across departmental knowledge bases with local sandboxes for code execution and structured query execution.
                                </p>
                            </div>
                        </div>
                    </div>

                </div>

            @else
                <!-- Empty State & Placeholders for Custom/Unknown Input -->
                <div class="bg-white border border-slate-200/80 rounded-2xl p-8 sm:p-12 shadow-sm text-center">

                    <div class="w-20 h-20 rounded-3xl bg-slate-100 text-slate-400 flex items-center justify-center text-4xl mx-auto mb-6 border border-slate-200/60">
                        <i class="fa-solid fa-folder-open"></i>
                    </div>

                    <h2 class="text-2xl font-bold text-slate-900 mb-2">No Agent Specification Found</h2>
                    <p class="text-slate-500 text-sm max-w-md mx-auto mb-8 leading-relaxed">
                        No configured agent models or datasets match the topic <span class="font-mono font-semibold text-slate-700 bg-slate-100 px-2.5 py-1 rounded-lg border border-slate-200">"{{ $rawTema }}"</span>.
                    </p>

                    <!-- Placeholder Grid -->
                    <div class="max-w-2xl mx-auto grid grid-cols-1 sm:grid-cols-2 gap-4 text-left border border-dashed border-slate-300 rounded-xl p-6 bg-slate-50/50 mb-8">
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Agent Theme</dt>
                            <dd class="text-sm font-semibold text-slate-400 mt-1 italic">— Empty —</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Model Pipeline</dt>
                            <dd class="text-sm font-semibold text-slate-400 mt-1 italic">— Unspecified —</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Autonomy Tier</dt>
                            <dd class="text-sm font-semibold text-slate-400 mt-1 italic">— Unspecified —</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Status</dt>
                            <dd class="text-sm font-semibold text-slate-400 mt-1 italic">— Inactive / Placeholder —</dd>
                        </div>
                    </div>

                    <a href="{{ route('agent.idea', ['tema' => 'general assistant agent']) }}" 
                       class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-its-accent hover:bg-its-blue text-white font-semibold text-sm shadow-md shadow-blue-500/20 transition duration-200">
                        <i class="fa-solid fa-robot"></i> Switch to General Assistant Agent
                    </a>

                </div>
            @endif

        </div>
    </section>

    <!-- Footer -->
    @include('partials.footer')

</x-layout>