<x-layout>

    @include('partials.navbar')

    <nav class="bg-white/80 backdrop-blur-md border-b border-slate-200/80 py-3 sticky top-0 z-40 shadow-sm">
        <div class="max-w-7xl mx-auto flex justify-between items-center px-4 sm:px-6 lg:px-8 text-sm font-semibold">

            <div class="font-black tracking-widest text-lg flex items-center gap-2 bg-gradient-to-r from-fuchsia-600 via-pink-500 to-purple-600 bg-clip-text text-transparent">
                <i class="fa-solid fa-code text-fuchsia-600"></i>
                MAGENTIC
            </div>

            <div class="hidden md:flex gap-6 text-slate-600">
                <a href="#problem" class="hover:text-its-accent transition">The Problem</a>
                <a href="#features" class="hover:text-its-accent transition">Features</a>
                <a href="#agent" class="hover:text-its-accent transition">Agentic AI</a>
                <a href="#architecture" class="hover:text-its-accent transition">Tech Stack</a>
            </div>

            <a href="#features"
                class="px-4 py-1.5 bg-fuchsia-50 text-fuchsia-600 border border-fuchsia-200 rounded-full hover:bg-fuchsia-600 hover:text-white transition text-xs font-semibold">
                Explore Project
            </a>

        </div>
    </nav>

    <section class="relative bg-gradient-to-b from-fuchsia-50/40 via-white to-slate-50 py-16 lg:py-24 overflow-hidden border-b border-slate-200/60">
        <div class="absolute top-1/3 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-fuchsia-400/10 blur-[140px] rounded-full pointer-events-none -z-10"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center flex flex-col items-center">
            
            <span class="text-xs font-semibold tracking-wider uppercase bg-fuchsia-100 text-fuchsia-700 px-3.5 py-1 rounded-full mb-4">
                Capstone Project Pitch
            </span>

            <h1 class="text-6xl sm:text-7xl lg:text-9xl font-black tracking-tighter mb-4 bg-gradient-to-r from-fuchsia-600 via-pink-500 to-purple-600 bg-clip-text text-transparent drop-shadow-sm">
                MAGENTIC
            </h1>

            <p class="text-xl sm:text-2xl text-slate-500 font-light mb-6 tracking-wide max-w-2xl">
                The Next Generation of Web-Based Collaborative Development
            </p>

            <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 mb-6">
                MAJESTIC IN EVERY LINE.
            </h2>

            <p class="text-base sm:text-lg text-slate-600 max-w-3xl mb-10 leading-relaxed">
                A collaborative web-based development environment powered by autonomous Agentic AI, seamlessly integrated with GitHub and real-time multiplayer editing.
            </p>

            <div class="flex flex-col sm:flex-row gap-4 justify-center mb-16 w-full sm:w-auto">
                <a href="#features" class="px-8 py-3.5 rounded-xl bg-gradient-to-r from-fuchsia-600 to-pink-600 hover:from-fuchsia-700 hover:to-pink-700 text-white font-semibold text-center shadow-lg shadow-fuchsia-500/25 transition duration-200">
                    Explore Features
                </a>
                <a href="#architecture" class="px-8 py-3.5 rounded-xl bg-white border border-slate-200 hover:border-slate-300 text-slate-700 font-semibold text-center shadow-sm hover:shadow transition duration-200">
                    View Architecture
                </a>
            </div>

            <div class="w-full max-w-5xl rounded-2xl border border-slate-700/80 shadow-2xl overflow-hidden text-left bg-[#1e1e1e]">
                <div class="bg-[#2d2d2d] px-4 py-3 flex justify-between items-center border-b border-gray-700 shrink-0">
                    <div class="flex gap-2">
                        <div class="w-3 h-3 rounded-full bg-[#ff5f56]"></div>
                        <div class="w-3 h-3 rounded-full bg-[#ffbd2e]"></div>
                        <div class="w-3 h-3 rounded-full bg-[#27c93f]"></div>
                    </div>
                    <div class="text-gray-400 text-xs font-mono flex gap-4">
                        <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-blue-400"></span> Arda</span>
                        <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-amber-500"></span> Aji</span>
                        <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-emerald-400"></span> Agent</span>
                    </div>
                </div>

                <div class="flex flex-col lg:flex-row font-mono text-xs sm:text-sm">
                    <div class="p-6 text-gray-300 overflow-x-auto flex-1 leading-relaxed">
                        <div><span class="text-pink-400">public</span> <span class="text-blue-400">function</span> <span class="text-yellow-200">authenticate</span>(<span class="text-orange-300">Request</span> <span class="text-blue-200">$request</span>) {</div>
                        <div class="pl-6 text-gray-500 italic">// Validate incoming request</div>
                        <div class="pl-6"><span class="text-blue-200">$credentials</span> = <span class="text-blue-200">$request</span>-&gt;<span class="text-yellow-200">validate</span>([</div>
                        <div class="pl-12"><span class="text-green-300">'email'</span> =&gt; <span class="text-green-300">'required|email'</span>,</div>
                        <div class="pl-12"><span class="text-green-300">'password'</span> =&gt; <span class="text-green-300">'required'</span></div>
                        <div class="pl-6">]);</div>
                        <br>
                        <div class="pl-6 text-gray-500 italic">// Check credentials</div>

                        <div class="pl-6 relative inline-block">
                            <span class="text-pink-400">if</span> (<span class="text-teal-400">Auth</span>::<span class="text-yellow-200">attempt</span>(<span class="text-blue-200">$credentials</span>)) {
                            <span class="absolute top-0 left-[-4px] w-[2px] h-5 bg-blue-400 animate-pulse"></span>
                            <span class="absolute -top-5 left-0 bg-blue-400 text-white text-[10px] font-sans px-1.5 py-0.5 rounded shadow">Arda</span>
                        </div>

                        <div class="pl-12"><span class="text-blue-200">$request</span>-&gt;<span class="text-yellow-200">session</span>()-&gt;<span class="text-yellow-200">regenerate</span>();</div>

                        <div class="pl-12 relative mt-1">
                            <span class="text-pink-400">return</span> <span class="text-yellow-200">redirect</span>()-&gt;
                            <span class="inline-block w-[2px] h-4 bg-amber-500 animate-pulse align-middle ml-1"></span>
                            <span class="absolute -top-5 left-28 bg-amber-500 text-white text-[10px] font-sans px-1.5 py-0.5 rounded shadow z-10 whitespace-nowrap">Aji is typing...</span>
                        </div>

                        <div class="pl-6">}</div>
                        <div class="pl-6"><span class="text-pink-400">return</span> <span class="text-yellow-200">back</span>()-&gt;<span class="text-yellow-200">withErrors</span>([<span class="text-green-300">'email'</span> =&gt; <span class="text-green-300">'Invalid login.'</span>]);</div>
                        <div>}</div>
                    </div>

                    <div class="w-full lg:w-80 border-t lg:border-t-0 lg:border-l border-gray-700 bg-[#1e1e1e] flex flex-col font-sans text-xs">
                        <div class="px-4 py-2.5 bg-[#252526] border-b border-gray-700 flex justify-between items-center text-gray-300">
                            <span class="font-semibold flex items-center gap-1.5">
                                <i class="fa-solid fa-sparkles text-fuchsia-400"></i> Agent Copilot
                            </span>
                            <span class="text-[10px] bg-emerald-500/20 text-emerald-400 px-2 py-0.5 rounded-full font-mono">Online</span>
                        </div>

                        <div class="p-4 flex-1 space-y-3 overflow-y-auto max-h-[300px]">
                            <div class="flex gap-2 items-start">
                                <div class="w-6 h-6 rounded-full bg-blue-600 flex items-center justify-center text-white text-[10px] font-bold shrink-0">A</div>
                                <div class="bg-[#2d2d2d] p-2.5 rounded-lg border border-gray-700/50 space-y-1 w-full text-gray-300">
                                    <p class="text-[11px] text-gray-400 font-semibold">Arda</p>
                                    <p>Can we make sure the session regeneration is protected against fixation?</p>
                                </div>
                            </div>

                            <div class="flex gap-2 items-start">
                                <div class="w-6 h-6 rounded-full bg-fuchsia-600 flex items-center justify-center text-white text-[10px] font-bold shrink-0">AI</div>
                                <div class="bg-[#252526] p-2.5 rounded-lg border border-gray-700/50 space-y-1 w-full text-gray-300">
                                    <p class="text-[11px] text-fuchsia-400 font-semibold flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-fuchsia-400 animate-ping"></span> Agent Copilot
                                    </p>
                                    <p>Yes! Laravel's <code class="bg-[#1e1e1e] px-1 py-0.5 rounded text-pink-400 font-mono text-[10px]">Auth::attempt()</code> handles session regeneration safely out of the box.</p>
                                </div>
                            </div>
                        </div>

                        <div class="p-3 bg-[#252526] border-t border-gray-700">
                            <div class="bg-[#1e1e1e] border border-gray-700 rounded-md p-2 flex flex-col gap-2">
                                <input type="text" placeholder="Ask Magentic Agent..." class="bg-transparent text-gray-200 text-xs focus:outline-none placeholder-gray-500 font-sans" readonly />
                                <div class="flex justify-between items-center text-[10px] text-gray-400 pt-1 border-t border-gray-800">
                                    <span class="flex gap-1.5">
                                        <span class="bg-[#2d2d2d] px-1.5 py-0.5 rounded">@code</span>
                                        <span class="bg-[#2d2d2d] px-1.5 py-0.5 rounded">#file</span>
                                    </span>
                                    <button class="bg-fuchsia-600 text-white px-2.5 py-0.5 rounded hover:bg-fuchsia-500 transition">Send</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>

    <section id="problem" class="py-20 lg:py-28 bg-white border-b border-slate-200/80">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <span class="text-xs font-semibold tracking-wider text-its-accent uppercase bg-its-light px-3 py-1 rounded-full">Problem & Motivation</span>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight mt-3">
                    A Unified Starting Point for <span class="text-fuchsia-600">Modern Devs</span>
                </h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-16">
                <article class="p-8 bg-slate-50 rounded-2xl border border-slate-200/80 hover:border-fuchsia-300 transition duration-300">
                    <div class="text-5xl font-black text-fuchsia-600 mb-4">01</div>
                    <h3 class="text-xl font-bold mb-2 text-slate-900">Fragmented Workflow</h3>
                    <p class="text-sm text-slate-600 leading-relaxed">Code editors, GitHub repositories, AI assistants, and communication channels live in separate windows, breaking developer focus.</p>
                </article>

                <article class="p-8 bg-slate-50 rounded-2xl border border-slate-200/80 hover:border-fuchsia-300 transition duration-300">
                    <div class="text-5xl font-black text-fuchsia-600 mb-4">02</div>
                    <h3 class="text-xl font-bold mb-2 text-slate-900">Passive AI Assistants</h3>
                    <p class="text-sm text-slate-600 leading-relaxed">Traditional AI wait for manual prompts instead of actively reasoning about code architecture and proposing proactive pull requests.</p>
                </article>

                <article class="p-8 bg-slate-50 rounded-2xl border border-slate-200/80 hover:border-fuchsia-300 transition duration-300">
                    <div class="text-5xl font-black text-fuchsia-600 mb-4">03</div>
                    <h3 class="text-xl font-bold mb-2 text-slate-900">Complex Setup Friction</h3>
                    <p class="text-sm text-slate-600 leading-relaxed">Setting up matching local environments across team members consumes valuable sprint hours that should be spent writing software.</p>
                </article>
            </div>

            <div class="text-center text-2xl sm:text-3xl font-black text-slate-900">
                All unified into <span class="text-its-accent">one browser workspace.</span>
            </div>
        </div>
    </section>

    <section class="py-20 lg:py-24 bg-slate-50 border-b border-slate-200/80">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 mb-4">One Workspace for Every Workflow</h2>
            <p class="text-slate-600 max-w-2xl mx-auto mb-10 text-sm sm:text-base">
                An interconnected architecture linking client-side Monaco Editor, backend Laravel service, GitHub API, and Autonomous Agents.
            </p>

            <div class="bg-slate-900 p-6 sm:p-10 rounded-2xl border border-slate-800 shadow-xl overflow-hidden mb-8">
                <canvas id="workspaceCanvas" class="w-full h-[400px]"></canvas>
            </div>
        </div>
    </section>

    <section id="features" class="py-20 lg:py-28 bg-white border-b border-slate-200/80">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <span class="text-xs font-semibold tracking-wider text-its-accent uppercase bg-its-light px-3 py-1 rounded-full">Core Capabilities</span>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight mt-3">Key Features</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="p-8 rounded-2xl bg-slate-50 border border-slate-200 hover:border-fuchsia-300 hover:shadow-lg transition duration-300">
                    <div class="w-14 h-14 bg-fuchsia-100 text-fuchsia-600 rounded-xl flex items-center justify-center text-2xl mb-6">
                        <i class="fa-solid fa-robot"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-slate-900 mb-3">Agentic AI Engine</h3>
                    <p class="text-slate-600 text-sm leading-relaxed mb-6">AI that doesn't just answer questions — it analyzes codebase context, reasons through issues, and generates multi-file edits automatically.</p>
                    <ul class="space-y-2 text-xs font-semibold text-slate-700">
                        <li class="flex items-center gap-2"><i class="fa-solid fa-check text-fuchsia-600"></i> Codebase contextual reasoning</li>
                        <li class="flex items-center gap-2"><i class="fa-solid fa-check text-fuchsia-600"></i> Autonomous bug detection</li>
                        <li class="flex items-center gap-2"><i class="fa-solid fa-check text-fuchsia-600"></i> Automated test execution</li>
                    </ul>
                </div>

                <div class="p-8 rounded-2xl bg-slate-50 border border-slate-200 hover:border-blue-300 hover:shadow-lg transition duration-300">
                    <div class="w-14 h-14 bg-blue-100 text-its-accent rounded-xl flex items-center justify-center text-2xl mb-6">
                        <i class="fa-brands fa-github"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-slate-900 mb-3">GitHub Integration</h3>
                    <p class="text-slate-600 text-sm leading-relaxed mb-6">Seamlessly connect projects with GitHub repositories directly from the browser without needing complex local Git setups.</p>
                    <ul class="space-y-2 text-xs font-semibold text-slate-700">
                        <li class="flex items-center gap-2"><i class="fa-solid fa-check text-its-accent"></i> Visual Clone, Commit & Push</li>
                        <li class="flex items-center gap-2"><i class="fa-solid fa-check text-its-accent"></i> Branch & PR management</li>
                        <li class="flex items-center gap-2"><i class="fa-solid fa-check text-its-accent"></i> Real-time repo sync</li>
                    </ul>
                </div>

                <div class="p-8 rounded-2xl bg-slate-50 border border-slate-200 hover:border-amber-300 hover:shadow-lg transition duration-300">
                    <div class="w-14 h-14 bg-amber-100 text-amber-600 rounded-xl flex items-center justify-center text-2xl mb-6">
                        <i class="fa-solid fa-users"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-slate-900 mb-3">Real-Time Multiplayer</h3>
                    <p class="text-slate-600 text-sm leading-relaxed mb-6">Work on the exact same codebase with your team members simultaneously, complete with live presence cursors.</p>
                    <ul class="space-y-2 text-xs font-semibold text-slate-700">
                        <li class="flex items-center gap-2"><i class="fa-solid fa-check text-amber-600"></i> Multi-user live cursor sync</li>
                        <li class="flex items-center gap-2"><i class="fa-solid fa-check text-amber-600"></i> WebSocket broadcast via Laravel Reverb</li>
                        <li class="flex items-center gap-2"><i class="fa-solid fa-check text-amber-600"></i> Shared state preservation</li>
                    </ul>
                </div>

                <div class="p-8 rounded-2xl bg-slate-50 border border-slate-200 hover:border-purple-300 hover:shadow-lg transition duration-300">
                    <div class="w-14 h-14 bg-purple-100 text-purple-600 rounded-xl flex items-center justify-center text-2xl mb-6">
                        <i class="fa-solid fa-laptop-code"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-slate-900 mb-3">Monaco Web IDE</h3>
                    <p class="text-slate-600 text-sm leading-relaxed mb-6">A familiar VS Code-like editing experience running in the browser with full syntax highlighting and keyboard shortcuts.</p>
                    <ul class="space-y-2 text-xs font-semibold text-slate-700">
                        <li class="flex items-center gap-2"><i class="fa-solid fa-check text-purple-600"></i> Full file tree explorer</li>
                        <li class="flex items-center gap-2"><i class="fa-solid fa-check text-purple-600"></i> Multi-tab editing</li>
                        <li class="flex items-center gap-2"><i class="fa-solid fa-check text-purple-600"></i> In-browser terminal output</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <section id="agent" class="py-20 lg:py-28 bg-slate-900 text-white border-b border-slate-800">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-14">
                <span class="text-xs font-semibold tracking-wider text-fuchsia-400 uppercase bg-fuchsia-950/80 border border-fuchsia-800 px-3 py-1 rounded-full">Autonomous Loop</span>
                <h2 class="text-3xl sm:text-5xl font-extrabold text-white tracking-tight mt-3">
                    Not Just Any Assistant.
                </h2>
                <p class="text-slate-400 mt-3 text-sm sm:text-base">
                    Unlike static prompt-response chat models, Magentic AI executes a systematic loop: analyze, plan, execute, and verify.
                </p>
            </div>

            <div class="bg-slate-950 rounded-2xl border border-slate-800 p-6 sm:p-10 font-mono text-xs sm:text-sm shadow-2xl">
                <div class="mb-10">
                    <div class="text-slate-500 mb-2 font-bold tracking-wider text-xs">USER PROMPT</div>
                    <div class="text-white bg-slate-800 px-5 py-3.5 rounded-r-xl rounded-bl-xl inline-block border border-slate-700">
                        "Fix the authentication bug on login."
                    </div>
                </div>

                <div class="mb-10 pl-8 border-l-2 border-fuchsia-800/60 relative">
                    <div class="absolute -left-[13px] top-0 w-6 h-6 bg-slate-950 border-2 border-fuchsia-500 rounded-full flex items-center justify-center text-fuchsia-400 text-[10px]">
                        <i class="fa-solid fa-arrow-down"></i>
                    </div>
                    <div class="text-fuchsia-400 mb-3 font-bold tracking-wider text-xs">AGENT REASONING PIPELINE</div>
                    <div class="space-y-3 text-slate-300 bg-slate-900/60 p-5 rounded-xl border border-slate-800">
                        <div class="flex items-center gap-3"><span class="text-fuchsia-400 animate-pulse">●</span> <span class="text-white font-semibold w-20">Analyze</span> <span class="text-slate-400">Scanning AuthController.php...</span></div>
                        <div class="flex items-center gap-3"><span class="text-fuchsia-400 animate-pulse">●</span> <span class="text-white font-semibold w-20">Plan</span> <span class="text-slate-400">Identified missing password hash verification step.</span></div>
                        <div class="flex items-center gap-3"><span class="text-fuchsia-400 animate-pulse">●</span> <span class="text-white font-semibold w-20">Execute</span> <span class="text-slate-400">Applying patch to line 45.</span></div>
                        <div class="flex items-center gap-3"><span class="text-fuchsia-400 animate-pulse">●</span> <span class="text-white font-semibold w-20">Verify</span> <span class="text-slate-400">Running AuthTest suite... 100% Passed.</span></div>
                    </div>
                </div>

                <div class="text-right">
                    <div class="text-fuchsia-400 mb-2 font-bold tracking-wider text-xs">AI AGENT RESPONSE</div>
                    <div class="text-fuchsia-100 bg-fuchsia-950/40 px-5 py-3.5 rounded-l-xl rounded-br-xl inline-block border border-fuchsia-500/30 text-left max-w-2xl">
                        "I found the issue in <code class="bg-slate-900 px-1 py-0.5 rounded text-pink-300">AuthController.php</code>. I've applied the fix to securely verify password hashes and verified the affected test suites."
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="architecture" class="py-20 lg:py-28 bg-slate-50">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-14">
                <span class="text-xs font-semibold tracking-wider text-its-accent uppercase bg-its-light px-3 py-1 rounded-full">Implementation</span>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight mt-3">Built With Modern Stack</h2>
            </div>

            <div class="space-y-3">
                <div class="p-5 bg-white rounded-xl border border-slate-200 flex flex-col sm:flex-row sm:items-center gap-4 hover:border-slate-300 transition">
                    <div class="w-32 font-bold text-slate-400 uppercase tracking-widest text-xs">Frontend</div>
                    <div class="text-slate-800 font-semibold text-base">Monaco Editor, JavaScript, Tailwind CSS</div>
                </div>

                <div class="p-5 bg-white rounded-xl border border-slate-200 flex flex-col sm:flex-row sm:items-center gap-4 hover:border-slate-300 transition">
                    <div class="w-32 font-bold text-slate-400 uppercase tracking-widest text-xs">Backend</div>
                    <div class="text-slate-800 font-semibold text-base">Laravel 11+ / PHP 8.4 / MySQL</div>
                </div>

                <div class="p-5 bg-white rounded-xl border border-slate-200 flex flex-col sm:flex-row sm:items-center gap-4 hover:border-slate-300 transition">
                    <div class="w-32 font-bold text-slate-400 uppercase tracking-widest text-xs">AI Core</div>
                    <div class="text-slate-800 font-semibold text-base">Custom Agentic Framework + LLM API</div>
                </div>

                <div class="p-5 bg-white rounded-xl border border-slate-200 flex flex-col sm:flex-row sm:items-center gap-4 hover:border-slate-300 transition">
                    <div class="w-32 font-bold text-slate-400 uppercase tracking-widest text-xs">Integration</div>
                    <div class="text-slate-800 font-semibold text-base">GitHub REST &amp; GraphQL API</div>
                </div>

                <div class="p-5 bg-white rounded-xl border border-slate-200 flex flex-col sm:flex-row sm:items-center gap-4 hover:border-slate-300 transition">
                    <div class="w-32 font-bold text-slate-400 uppercase tracking-widest text-xs">Realtime</div>
                    <div class="text-slate-800 font-semibold text-base">WebSockets / Laravel Reverb</div>
                </div>
            </div>
        </div>
    </section>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const cv = document.getElementById('workspaceCanvas');
            if(!cv) return;

            const ctx = cv.getContext('2d');
            const rect = cv.getBoundingClientRect();
            const dpr = window.devicePixelRatio || 1;

            cv.width = rect.width * dpr;
            cv.height = rect.height * dpr;
            ctx.scale(dpr, dpr);

            const W = rect.width;
            const H = rect.height;
            const centerX = W / 2;
            const centerY = H / 2;

            ctx.font = '13px sans-serif';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';

            const drawArrow = (x1, y1, x2, y2) => {
                ctx.beginPath();
                ctx.moveTo(x1, y1);
                ctx.lineTo(x2, y2);
                ctx.strokeStyle = '#64748b';
                ctx.lineWidth = 1.5;
                ctx.stroke();

                ctx.fillStyle = '#64748b';
                const angle = Math.atan2(y2 - y1, x2 - x1);

                const head = (x, y, rotation) => {
                    ctx.save();
                    ctx.translate(x, y);
                    ctx.rotate(rotation);
                    ctx.beginPath();
                    ctx.moveTo(0, 0);
                    ctx.lineTo(-8, 3.5);
                    ctx.lineTo(-8, -3.5);
                    ctx.closePath();
                    ctx.fill();
                    ctx.restore();
                };

                head(x2, y2, angle);
                head(x1, y1, angle + Math.PI);
            };

            const centerW = 180;
            const sideW = 140;
            const verticalW = 160;
            const nodeH = 38;
            const gap = 70;

            const centerNode = {x: centerX, y: centerY};
            const github = {x: centerX, y: centerY - nodeH / 2 - gap - nodeH / 2};
            const backend = {x: centerX, y: centerY + nodeH / 2 + gap + nodeH / 2};
            const collaborator = {x: centerX - centerW / 2 - gap - sideW / 2, y: centerY};
            const ai = {x: centerX + centerW / 2 + gap + sideW / 2, y: centerY};

            drawArrow(centerNode.x, github.y + nodeH / 2, centerNode.x, centerNode.y - nodeH / 2);
            drawArrow(centerNode.x, centerNode.y + nodeH / 2, centerNode.x, backend.y - nodeH / 2);
            drawArrow(collaborator.x + sideW / 2, collaborator.y, centerNode.x - centerW / 2, centerNode.y);
            drawArrow(centerNode.x + centerW / 2, centerNode.y, ai.x - sideW / 2, ai.y);

            [
                [github.x, github.y, verticalW, 'GitHub API', '#ffffff', '#475569', '#1e293b'],
                [collaborator.x, collaborator.y, sideW, 'Collaborators', '#34d399', '#059669', '#064e3b'],
                [ai.x, ai.y, sideW, 'Agentic AI', '#f0abfc', '#c084fc', '#581c87'],
                [backend.x, backend.y, verticalW, 'Laravel Backend', '#f87171', '#dc2626', '#7f1d1d'],
                [centerNode.x, centerNode.y, centerW, 'Web Monaco IDE', '#ffffff', '#0055B8', '#002B66', '#0055B8']
            ].forEach(([x, y, w, text, tc, bc, bg, glow]) => {
                ctx.shadowBlur = glow ? 12 : 0;
                ctx.shadowColor = glow || 'transparent';

                ctx.fillStyle = bg;
                ctx.strokeStyle = bc;
                ctx.lineWidth = glow ? 2 : 1.5;

                ctx.beginPath();
                ctx.roundRect(x - w / 2, y - nodeH / 2, w, nodeH, 8);
                ctx.fill();
                ctx.stroke();

                ctx.shadowBlur = 0;
                ctx.fillStyle = tc;
                ctx.fillText(text, x, y);
            });
        });
    </script>

    @include('partials.footer')

</x-layout>