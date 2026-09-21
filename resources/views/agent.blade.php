<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Pitch - Agentic AI IDE</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Custom Background Effects */
        .bg-grid-pattern {
            background-size: 40px 40px;
            background-image:
                linear-gradient(to right, rgba(255, 255, 255, 0.03) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
        }

        .text-glow {
            text-shadow: 0 0 25px rgba(16, 185, 129, 0.6);
        }

        .box-glow {
            box-shadow: 0 0 40px rgba(16, 185, 129, 0.15);
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #030712; }
        ::-webkit-scrollbar-thumb { background: #1f2937; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #374151; }
    </style>
</head>
<body class="bg-gray-950 text-gray-300 font-sans antialiased selection:bg-emerald-500 selection:text-white overflow-x-hidden">

    <!-- ==================== SHARED SITE NAVBAR ==================== -->
    <nav class="bg-gray-900 border-b border-gray-800 h-14 sticky top-0 z-50 shadow-lg">
        <div class="max-w-4xl mx-auto h-full flex justify-center items-center gap-6 text-sm font-semibold">
            <a href="/" class="text-gray-400 hover:text-gray-200 transition">Landing Page</a>
            <a href="/about" class="text-gray-400 hover:text-gray-200 transition">Department Profile</a>
            <a href="/project-idea" class="text-emerald-400 hover:text-emerald-300 transition">Project Plan</a>
            <a href="/hitung" class="text-gray-400 hover:text-gray-200 transition">Calculator</a>
        </div>
    </nav>

    <!-- ==================== PAGE SECTION LINKS ==================== -->
    <nav class="bg-gray-950/80 backdrop-blur-lg border-b border-gray-800/50 py-3 sticky top-14 z-40">
        <div class="max-w-7xl mx-auto flex justify-between items-center px-5 text-sm font-semibold">

            <div class="text-emerald-400 font-black tracking-widest text-base text-glow flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4">
                    </path>
                </svg>
                MAGENTIC
            </div>

            <div class="hidden md:flex gap-6">
                <a href="#problem" class="text-gray-400 hover:text-white transition">The Problem</a>
                <a href="#features" class="text-gray-400 hover:text-white transition">Features</a>
                <a href="#agent" class="text-gray-400 hover:text-white transition">Agentic AI</a>
            </div>

            <a href="#features"
                class="px-4 py-1.5 bg-emerald-500/10 text-emerald-400 border border-emerald-500/30 rounded-full hover:bg-emerald-500 hover:text-white transition text-xs">
                Explore Project
            </a>

        </div>
    </nav>

    <!-- ==================== 1. HERO SECTION ==================== -->
    <header class="relative min-h-screen flex flex-col items-center justify-center bg-grid-pattern pt-20 pb-32">
        <!-- Ambient Glow Background -->
        <div class="absolute top-1/3 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[800px] h-[800px] bg-emerald-500/10 blur-[150px] rounded-full pointer-events-none z-0"></div>

        <div class="z-10 text-center max-w-6xl px-6 w-full flex flex-col items-center">
            <h1 class="text-5xl md:text-7xl lg:text-8xl font-extrabold tracking-tighter mb-6 bg-clip-text text-transparent bg-gradient-to-b from-white to-gray-500">
                MAGENTIC
            </h1>
            <p class="text-xl md:text-2xl text-gray-400 font-light mb-12 tracking-wide">
                The Next Generation of Web-Based Development.
            </p>

            <h2 class="text-3xl md:text-5xl font-bold text-emerald-400 mb-8 text-glow">
                MAJESTIC IN EVERY LINE.
            </h2>
            <p class="text-lg md:text-xl text-gray-300 max-w-3xl mb-12 leading-relaxed">
                A collaborative web-based development environment powered by Agentic AI seamlessly integrated with GitHub.
            </p>

            <div class="flex flex-col sm:flex-row gap-6 justify-center mb-20">
                <a href="#features" class="px-8 py-4 bg-emerald-500 hover:bg-emerald-400 text-gray-950 rounded-full font-bold text-lg transition duration-300 shadow-[0_0_30px_rgba(16,185,129,0.4)]">
                    Explore the Project
                </a>
                <a href="#architecture" class="px-8 py-4 bg-transparent hover:bg-gray-900 text-white rounded-full font-bold text-lg transition duration-300 border border-gray-700">
                    View Architecture
                </a>
            </div>

            <!-- UI Mockup Placeholder -->
            <!-- please insert image here: UI Mockup of the Web IDE showing Code + AI Agent Chat side by side -->
            <div class="w-full aspect-video bg-gray-900/60 backdrop-blur-md rounded-2xl border border-gray-700 box-glow flex flex-col overflow-hidden relative text-left">                <!-- Mockup Header -->
                <div class="bg-[#2d2d2d] px-4 py-3 flex justify-between items-center border-b border-gray-700 shrink-0">
                    <div class="flex gap-2">
                        <div class="w-3 h-3 rounded-full bg-[#ff5f56]"></div>
                        <div class="w-3 h-3 rounded-full bg-[#ffbd2e]"></div>
                        <div class="w-3 h-3 rounded-full bg-[#27c93f]"></div>
                    </div>
                    <div class="text-gray-400 text-xs flex gap-4">
                        <span class="flex items-center gap-1"><div class="w-2 h-2 rounded-full bg-blue-400"></div> Arda</span>
                        <span class="flex items-center gap-1"><div class="w-2 h-2 rounded-full bg-amber-500"></div> Aji</span>
                        <span class="flex items-center gap-1"><div class="w-2 h-2 rounded-full bg-emerald-400"></div> Agent</span>
                    </div>
                </div>

                <!-- Main Workspace (Editor + Chat Panel side by side) -->
                <div class="flex flex-col lg:flex-row flex-1">
                    <!-- Code Body -->
                    <div class="p-6 text-gray-300 overflow-x-auto flex-1">
                        <div><span class="text-pink-400">public</span> <span class="text-blue-400">function</span> <span class="text-yellow-200">authenticate</span>(<span class="text-orange-300">Request</span> <span class="text-blue-200">$request</span>) {</div>
                        <div class="pl-8 text-gray-500 italic">// Validate incoming request</div>
                        <div class="pl-8"><span class="text-blue-200">$credentials</span> = <span class="text-blue-200">$request</span>-><span class="text-yellow-200">validate</span>([</div>
                        <div class="pl-16"><span class="text-green-300">'email'</span> => <span class="text-green-300">'required|email'</span>,</div>
                        <div class="pl-16"><span class="text-green-300">'password'</span> => <span class="text-green-300">'required'</span></div>
                        <div class="pl-8">]);</div>
                        <br>
                        <div class="pl-8 text-gray-500 italic">// Check credentials</div>

                        <!-- Simulating Arda's Cursor -->
                        <div class="pl-8 relative inline-block">
                            <span class="text-pink-400">if</span> (<span class="text-teal-400">Auth</span>::<span class="text-yellow-200">attempt</span>(<span class="text-blue-200">$credentials</span>)) {
                            <span class="absolute top-0 left-[-4px] w-[2px] h-5 bg-blue-400 animate-pulse"></span>
                            <span class="absolute -top-5 left-0 bg-blue-400 text-white text-[10px] font-sans px-2 py-0.5 rounded shadow">Arda</span>
                        </div>

                        <div class="pl-16"><span class="text-blue-200">$request</span>-><span class="text-yellow-200">session</span>()-><span class="text-yellow-200">regenerate</span>();</div>

                        <!-- Simulating Aji's Cursor Typing -->
                        <div class="pl-16 relative mt-2">
                            <span class="text-pink-400">return</span> <span class="text-yellow-200">redirect</span>()->
                            <span class="inline-block w-[2px] h-4 bg-amber-500 animate-pulse align-middle ml-1"></span>
                            <span class="absolute -top-5 left-32 bg-amber-500 text-white text-[10px] font-sans px-2 py-0.5 rounded shadow z-10 whitespace-nowrap">Aji is typing...</span>
                        </div>

                        <div class="pl-8">}</div>
                        <div class="pl-8"><span class="text-pink-400">return</span> <span class="text-yellow-200">back</span>()-><span class="text-yellow-200">withErrors</span>([<span class="text-green-300">'email'</span> => <span class="text-green-300">'Invalid login.'</span>]);</div>
                        <div>}</div>
                    </div>

                    <!-- VS Code AI Chat Panel (Right Side) -->
                    <div class="w-full lg:w-80 border-t lg:border-t-0 lg:border-l border-gray-700 bg-[#1e1e1e] flex flex-col text-xs font-sans shrink-0">
                        <!-- Panel Header -->
                        <div class="px-4 py-2.5 bg-[#252526] border-b border-gray-700 flex justify-between items-center text-gray-300">
                            <span class="font-semibold flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                Chat
                            </span>
                            <div class="flex items-center gap-2 text-gray-400">
                                <button class="hover:text-white" title="New Chat">+</button>
                                <button class="hover:text-white" title="Close">×</button>
                            </div>
                        </div>

                        <!-- Chat Messages Body -->
                        <div class="p-4 flex-1 space-y-4 overflow-y-auto max-h-[350px] lg:max-h-none text-gray-300">
                            <!-- User Message -->
                            <div class="flex gap-2 items-start">
                                <div class="w-6 h-6 rounded-full bg-blue-600 flex items-center justify-center text-white text-[10px] font-bold shrink-0">A</div>
                                <div class="bg-[#2d2d2d] p-2.5 rounded-lg border border-gray-700/50 space-y-1 w-full">
                                    <p class="text-[11px] text-gray-400 font-semibold">Arda</p>
                                    <p>Can we make sure the session regeneration is protected against fixation?</p>
                                </div>
                            </div>

                            <!-- AI / Agent Message -->
                            <div class="flex gap-2 items-start">
                                <div class="w-6 h-6 rounded-full bg-emerald-600 flex items-center justify-center text-white text-[10px] font-bold shrink-0">AI</div>
                                <div class="bg-[#252526] p-2.5 rounded-lg border border-gray-700/50 space-y-2 w-full">
                                    <p class="text-[11px] text-emerald-400 font-semibold flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-ping"></span> Agent Copilot
                                    </p>
                                    <p class="text-gray-300">Yes! Laravel's <code class="bg-[#1e1e1e] px-1 py-0.5 rounded text-pink-400 font-mono text-[10px]">Auth::attempt()</code> handles session regeneration safely out of the box.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Chat Input Footer -->
                        <div class="p-3 bg-[#252526] border-t border-gray-700">
                            <div class="bg-[#1e1e1e] border border-gray-700 rounded-md p-2 flex flex-col gap-2 focus-within:border-blue-500 transition-colors">
                                <input type="text" placeholder="Ask Copilot or type @/..." class="bg-transparent text-gray-200 text-xs focus:outline-none placeholder-gray-500 font-sans" readonly />
                                <div class="flex justify-between items-center text-[10px] text-gray-400 pt-1 border-t border-gray-800">
                                    <span class="flex gap-2">
                                        <button class="hover:text-white flex items-center gap-1 bg-[#2d2d2d] px-1.5 py-0.5 rounded">@code</button>
                                        <button class="hover:text-white flex items-center gap-1 bg-[#2d2d2d] px-1.5 py-0.5 rounded">#file</button>
                                    </span>
                                    <button class="bg-blue-600 text-white px-2 py-0.5 rounded hover:bg-blue-500 transition">Send</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- ==================== 2. WHY THIS PROJECT? ==================== -->
    <section id="problem" class="py-32 px-6 bg-gray-950 border-t border-gray-800">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-24">
                <h2 class="text-4xl md:text-6xl font-bold mb-6 text-white tracking-tight">
                    A Starting Point<br>
                </h2>
                <!--
                <p class="text-xl text-gray-400 max-w-4xl mx-auto leading-relaxed">
                    Modern software development is no longer an individual activity. Developers work across repositories, branches, pull requests, and increasingly, AI-assisted workflows. Yet these capabilities are often scattered across different tools.
                </p>
                -->
            </div>

            <div class="grid lg:grid-cols-3 gap-8 mb-24">
                <!-- Problem 01 -->
                <article class="p-10 bg-gray-900/40 rounded-3xl border border-gray-800 hover:border-gray-600 transition duration-300">
                    <div class="text-7xl  text-emerald-400 text-glow font-black mb-6">01</div>
                    <h3 class="text-2xl font-bold mb-4 text-white">Fragmented Workflow</h3>
                    <p class="text-gray-400 leading-relaxed">Code editor, GitHub, AI assistant, and collaboration tools often live separately, constantly breaking developer focus and context.</p>
                </article>
                <!-- Problem 02 -->
                <article class="p-10 bg-gray-900/40 rounded-3xl border border-gray-800 hover:border-gray-600 transition duration-300">
                    <div class="text-7xl text-emerald-400 text-glow font-black mb-6">02</div>
                    <h3 class="text-2xl font-bold mb-4 text-white">AI That Only Responds</h3>
                    <p class="text-gray-400 leading-relaxed">Traditional AI assistants wait passively for prompts instead of actively reasoning about the project architecture and proposing solutions.</p>
                </article>
                <!-- Problem 03 -->
                <article class="p-10 bg-gray-900/40 rounded-3xl border border-gray-800 hover:border-gray-600 transition duration-300">
                    <div class="text-7xl  text-emerald-400 text-glow font-black mb-6">03</div>
                    <h3 class="text-2xl font-bold mb-4 text-white">Collaboration is Hard</h3>
                    <p class="text-gray-400 leading-relaxed">Developers need to switch between local IDEs, Git platforms, and communication tools just to solve a single shared issue.</p>
                </article>
            </div>

            <div class="text-center text-3xl md:text-5xl font-black text-emerald-400 text-glow mt-10">
                All in one environment.
            </div>
        </div>
    </section>

    <!-- ==================== 3. WHAT ARE WE BUILDING? ==================== -->
    <section class="py-24 px-6 bg-gray-900 border-t border-gray-800 relative overflow-hidden">
        <div class="max-w-5xl mx-auto text-center relative z-10">
            <h2 class="text-4xl md:text-5xl font-bold mb-12 text-white">One Workspace for Every Workflow.</h2>

            <!-- Conceptual Diagram (Styled HTML/CSS instead of raw ASCII) -->
            <div class="bg-gray-950 p-10 rounded-3xl border border-gray-700 mb-12 shadow-2xl overflow-hidden">
                <canvas id="workspaceCanvas" class="w-full h-[420px]"></canvas>
            </div>

           <script>
            const cv = document.getElementById('workspaceCanvas');
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

            ctx.font = '14px monospace';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';

            const drawArrow = (x1, y1, x2, y2) => {
                ctx.beginPath();
                ctx.moveTo(x1, y1);
                ctx.lineTo(x2, y2);
                ctx.strokeStyle = '#8b949e';
                ctx.lineWidth = 1.5;
                ctx.stroke();

                ctx.fillStyle = '#8b949e';

                const angle = Math.atan2(y2 - y1, x2 - x1);

                const head = (x, y, rotation) => {
                    ctx.save();
                    ctx.translate(x, y);
                    ctx.rotate(rotation);
                    ctx.beginPath();
                    ctx.moveTo(0, 0);
                    ctx.lineTo(-10, 4);
                    ctx.lineTo(-10, -4);
                    ctx.closePath();
                    ctx.fill();
                    ctx.restore();
                };

                head(x2, y2, angle);
                head(x1, y1, angle + Math.PI);
            };

            const centerW = 200;
            const sideW = 150;
            const verticalW = 160;
            const nodeH = 40;

            const gap = 80;

            const centerNode = {x: centerX, y: centerY, w: centerW};

            const github = {x: centerX, y: centerY - nodeH / 2 - gap - nodeH / 2};
            const backend = {x: centerX, y: centerY + nodeH / 2 + gap + nodeH / 2};
            const collaborator = {x: centerX - centerW / 2 - gap - sideW / 2, y: centerY};
            const ai = {x: centerX + centerW / 2 + gap + sideW / 2, y: centerY};

            drawArrow(centerNode.x, github.y + nodeH / 2, centerNode.x, centerNode.y - nodeH / 2);
            drawArrow(centerNode.x, centerNode.y + nodeH / 2, centerNode.x, backend.y - nodeH / 2);
            drawArrow(collaborator.x + sideW / 2, collaborator.y, centerNode.x - centerW / 2, centerNode.y);
            drawArrow(centerNode.x + centerW / 2, centerNode.y, ai.x - sideW / 2, ai.y);

            [
                [github.x, github.y, verticalW, 'GitHub', '#ffffff', '#374151', '#1f2937'],
                [collaborator.x, collaborator.y, sideW, 'Collaborator', '#34d399', '#064e3b', '#06251d'],
                [ai.x, ai.y, sideW, 'Agentic AI', '#c084fc', '#581c87', '#170d24'],
                [backend.x, backend.y, verticalW, 'Laravel Backend', '#f87171', '#7f1d1d', '#250b0b'],
                [centerNode.x, centerNode.y, centerW, 'Web-based IDE', '#ffffff', '#10b981', '#111827', '#10b981']
            ].forEach(([x, y, w, text, tc, bc, bg, glow]) => {
                ctx.shadowBlur = glow ? 15 : 0;
                ctx.shadowColor = glow || 'transparent';

                ctx.fillStyle = bg;
                ctx.strokeStyle = bc;
                ctx.lineWidth = glow ? 2 : 1.5;

                ctx.beginPath();
                ctx.roundRect(x - w / 2, y - nodeH / 2, w, nodeH, 6);
                ctx.fill();
                ctx.stroke();

                ctx.shadowBlur = 0;
                ctx.fillStyle = tc;
                ctx.fillText(text, x, y);
            });
            </script>
            <p class="text-xl text-gray-300 leading-relaxed max-w-4xl mx-auto">
                A browser-based development environment that combines code editing, GitHub integration, real-time collaboration, and autonomous AI agents into a single, cohesive workspace.
            </p>
        </div>
    </section>

    <!-- ==================== 4. KEY FEATURES ==================== -->
    <section id="features" class="py-32 px-6 bg-gray-950 border-t border-gray-800">
        <div class="max-w-7xl mx-auto">
            <h2 class="text-4xl md:text-5xl font-bold mb-20 text-center text-white">Key Features</h2>

            <div class="grid md:grid-cols-2 gap-8">

                <!-- Feature 1: Agentic AI -->
                <article class="p-10 bg-gray-900 rounded-3xl border border-gray-800 flex flex-col justify-between hover:-translate-y-2 hover:shadow-[0_10px_40px_rgba(16,185,129,0.15)] hover:border-emerald-500/50 transition duration-500">
                    <div>
                        <div class="w-16 h-16 bg-emerald-500/10 rounded-2xl flex items-center justify-center mb-8 border border-emerald-500/20 text-3xl">🤖</div>
                        <h3 class="text-3xl font-bold text-white mb-4">Agentic AI</h3>
                        <p class="text-gray-400 mb-8 leading-relaxed text-lg">AI that doesn't just answer questions — it can analyze the codebase, reason about problems, and execute development tasks.</p>
                    </div>
                    <ul class="space-y-3 text-base font-mono text-emerald-400/80">
                        <li class="flex items-center gap-3"><span class="text-emerald-500">▹</span> Analyze codebase context</li>
                        <li class="flex items-center gap-3"><span class="text-emerald-500">▹</span> Detect bugs autonomously</li>
                        <li class="flex items-center gap-3"><span class="text-emerald-500">▹</span> Execute development workflows</li>
                    </ul>
                </article>

                <!-- Feature 2: GitHub -->
                <article class="p-10 bg-gray-900 rounded-3xl border border-gray-800 flex flex-col justify-between hover:-translate-y-2 hover:shadow-[0_10px_40px_rgba(59,130,246,0.15)] hover:border-blue-500/50 transition duration-500">
                    <div>
                        <div class="w-16 h-16 bg-blue-500/10 rounded-2xl flex items-center justify-center mb-8 border border-blue-500/20 text-3xl">🔗</div>
                        <h3 class="text-3xl font-bold text-white mb-4">GitHub Integration</h3>
                        <p class="text-gray-400 mb-8 leading-relaxed text-lg">Seamlessly connect projects with GitHub repositories directly from the browser without local setups.</p>
                    </div>
                    <ul class="space-y-3 text-base font-mono text-blue-400/80">
                        <li class="flex items-center gap-3"><span class="text-blue-500">▹</span> Clone & Commit visually</li>
                        <li class="flex items-center gap-3"><span class="text-blue-500">▹</span> Branch management</li>
                        <li class="flex items-center gap-3"><span class="text-blue-500">▹</span> Pull request synchronization</li>
                    </ul>
                </article>

                <!-- Feature 3: Collaboration -->
                <article class="p-10 bg-gray-900 rounded-3xl border border-gray-800 flex flex-col justify-between hover:-translate-y-2 hover:shadow-[0_10px_40px_rgba(245,158,11,0.15)] hover:border-amber-500/50 transition duration-500">
                    <div>
                        <div class="w-16 h-16 bg-amber-500/10 rounded-2xl flex items-center justify-center mb-8 border border-amber-500/20 text-3xl">👥</div>
                        <h3 class="text-3xl font-bold text-white mb-4">Real-Time Collaboration</h3>
                        <p class="text-gray-400 mb-8 leading-relaxed text-lg">Work on the same codebase with your teammates simultaneously, just like editing a Google Doc.</p>
                    </div>
                    <ul class="space-y-3 text-base font-mono text-amber-400/80">
                        <li class="flex items-center gap-3"><span class="text-amber-500">▹</span> Multiple users & cursors</li>
                        <li class="flex items-center gap-3"><span class="text-amber-500">▹</span> Real-time text broadcasting</li>
                        <li class="flex items-center gap-3"><span class="text-amber-500">▹</span> Live presence indicators</li>
                    </ul>
                </article>

                <!-- Feature 4: IDE -->
                <article class="p-10 bg-gray-900 rounded-3xl border border-gray-800 flex flex-col justify-between hover:-translate-y-2 hover:shadow-[0_10px_40px_rgba(168,85,247,0.15)] hover:border-purple-500/50 transition duration-500">
                    <div>
                        <div class="w-16 h-16 bg-purple-500/10 rounded-2xl flex items-center justify-center mb-8 border border-purple-500/20 text-3xl">💻</div>
                        <h3 class="text-3xl font-bold text-white mb-4">VS Code-like Environment</h3>
                        <p class="text-gray-400 mb-8 leading-relaxed text-lg">A familiar, fully-featured development environment powered by Monaco Editor, delivered in-browser.</p>
                    </div>
                    <ul class="space-y-3 text-base font-mono text-purple-400/80">
                        <li class="flex items-center gap-3"><span class="text-purple-500">▹</span> File explorer & Tabs</li>
                        <li class="flex items-center gap-3"><span class="text-purple-500">▹</span> Advanced syntax highlighting</li>
                        <li class="flex items-center gap-3"><span class="text-purple-500">▹</span> Integrated web terminal</li>
                    </ul>
                </article>

            </div>
        </div>
    </section>

    <!-- ==================== 5. THE AGENT SECTION ==================== -->
    <section id="agent" class="py-32 px-6 bg-gray-900 border-t border-gray-800">
        <div class="max-w-5xl mx-auto">
            <h2 class="text-4xl md:text-6xl font-bold mb-6 text-center text-white">
                Not Just Any Assistant.<br>
            </h2>
            <p class="text-xl text-gray-400 text-center mb-16 max-w-3xl mx-auto">
                Unlike any other standard chat models, our Agentic AI executes a systematic loop, analyzes, forms a plan, executes changes, and verifies the result.
            </p>

            <!-- Agent Visual Demo -->
            <!-- please insert image here: Optional animated GIF of the agent working -->
            <div class="bg-gray-950 rounded-3xl border border-gray-700 p-8 md:p-12 box-glow">
                <div class="font-mono text-base max-w-3xl mx-auto">

                    <!-- User Prompt -->
                    <div class="mb-12">
                        <div class="text-gray-500 mb-2 font-bold tracking-wider text-sm">YOU</div>
                        <div class="text-white bg-gray-800 px-6 py-4 rounded-r-2xl rounded-bl-2xl inline-block border border-gray-700 shadow-lg">
                            "Fix the authentication bug on login."
                        </div>
                    </div>

                    <!-- Agent Process -->
                    <div class="mb-12 pl-12 border-l-2 border-emerald-900/50 relative">
                        <div class="absolute -left-[17px] top-0 w-8 h-8 bg-gray-950 border-2 border-emerald-500 rounded-full flex items-center justify-center text-emerald-500">↓</div>
                        <div class="text-emerald-500/70 mb-4 font-bold tracking-wider text-sm">AGENT REASONING</div>
                        <div class="space-y-4 text-gray-400 bg-gray-900/50 p-6 rounded-xl border border-gray-800">
                            <div class="flex items-center gap-3"><span class="text-emerald-400 animate-pulse">●</span> <span class="text-white font-semibold w-20">Analyze</span> <span class="text-gray-500">Scanning AuthController.php...</span></div>
                            <div class="flex items-center gap-3"><span class="text-emerald-400 animate-pulse" style="animation-delay: 200ms">●</span> <span class="text-white font-semibold w-20">Plan</span> <span class="text-gray-500">Missing password hash verification step.</span></div>
                            <div class="flex items-center gap-3"><span class="text-emerald-400 animate-pulse" style="animation-delay: 400ms">●</span> <span class="text-white font-semibold w-20">Execute</span> <span class="text-gray-500">Patching line 45.</span></div>
                            <div class="flex items-center gap-3"><span class="text-emerald-400 animate-pulse" style="animation-delay: 600ms">●</span> <span class="text-white font-semibold w-20">Verify</span> <span class="text-gray-500">Running AuthTest... Passed.</span></div>
                        </div>
                    </div>

                    <!-- Agent Output -->
                    <div class="text-right">
                        <div class="text-emerald-500 mb-2 font-bold tracking-wider text-sm">AI AGENT</div>
                        <div class="text-emerald-50 bg-emerald-900/30 px-6 py-4 rounded-l-2xl rounded-br-2xl inline-block border border-emerald-500/30 shadow-lg text-left max-w-2xl">
                            "I found the issue in `AuthController.php`. I've applied the fix to securely verify the password hash and checked the affected tests. The login is now secure."
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>

    <!-- ==================== 6. COLLABORATION IN ACTION ==================== -->
    <section class="py-32 px-6 bg-gray-950 border-t border-gray-800 relative">
        <div class="max-w-6xl mx-auto flex flex-col items-center">
            <h2 class="text-4xl md:text-5xl font-bold mb-6 text-white text-center">One Codebase for All</h2>
            <p class="text-xl text-gray-400 mb-16 text-center max-w-3xl">
                Just like Google Docs, developers can work on the same project simultaneously together an AI agent to assists along the way.
            </p>

            <!-- Collaborative Code Editor Mockup -->
            <!-- please insert image here: Visual showing multiple cursors with names like "Arda" and "Aji" editing the same code -->
            <div class="w-full bg-[#1e1e1e] rounded-xl border border-gray-700 shadow-2xl overflow-hidden font-mono text-sm sm:text-base leading-loose relative flex flex-col">

                <!-- Mac style header -->
                <div class="bg-[#2d2d2d] px-4 py-3 flex justify-between items-center border-b border-gray-700 shrink-0">
                    <div class="flex gap-2">
                        <div class="w-3 h-3 rounded-full bg-[#ff5f56]"></div>
                        <div class="w-3 h-3 rounded-full bg-[#ffbd2e]"></div>
                        <div class="w-3 h-3 rounded-full bg-[#27c93f]"></div>
                    </div>

                    <div class="text-gray-400 text-xs flex gap-4">
                        <span class="flex items-center gap-1">
                            <div class="w-2 h-2 rounded-full bg-blue-400"></div> Arda
                        </span>
                        <span class="flex items-center gap-1">
                            <div class="w-2 h-2 rounded-full bg-amber-500"></div> Aji
                        </span>
                        <span class="flex items-center gap-1">
                            <div class="w-2 h-2 rounded-full bg-emerald-400"></div> Agent
                        </span>
                    </div>
                </div>

                <!-- Main Workspace (Editor + Chat Panel side by side) -->
                <div class="flex flex-col lg:flex-row flex-1">

                    <!-- Code Body -->
                    <div class="p-6 text-gray-300 overflow-x-auto flex-1 min-w-0">
                        <div><span class="text-pink-400">public</span> <span class="text-blue-400">function</span> <span class="text-yellow-200">authenticate</span>(<span class="text-orange-300">Request</span> <span class="text-blue-200">$request</span>) {</div>
                        <div class="pl-8 text-gray-500 italic">// Validate incoming request</div>
                        <div class="pl-8"><span class="text-blue-200">$credentials</span> = <span class="text-blue-200">$request</span>-><span class="text-yellow-200">validate</span>([</div>
                        <div class="pl-16"><span class="text-green-300">'email'</span> => <span class="text-green-300">'required|email'</span>,</div>
                        <div class="pl-16"><span class="text-green-300">'password'</span> => <span class="text-green-300">'required'</span></div>
                        <div class="pl-8">]);</div>
                        <br>
                        <div class="pl-8 text-gray-500 italic">// Check credentials</div>

                        <!-- Simulating Arda's Cursor -->
                        <div class="pl-8 relative inline-block">
                            <span class="text-pink-400">if</span> (<span class="text-teal-400">Auth</span>::<span class="text-yellow-200">attempt</span>(<span class="text-blue-200">$credentials</span>)) {
                            <span class="absolute top-0 left-[-4px] w-[2px] h-5 bg-blue-400 animate-pulse"></span>
                            <span class="absolute -top-5 left-0 bg-blue-400 text-white text-[10px] font-sans px-2 py-0.5 rounded shadow">Arda</span>
                        </div>

                        <div class="pl-16"><span class="text-blue-200">$request</span>-><span class="text-yellow-200">session</span>()-><span class="text-yellow-200">regenerate</span>();</div>

                        <!-- Simulating Aji's Cursor Typing -->
                        <div class="pl-16 relative mt-2">
                            <span class="text-pink-400">return</span> <span class="text-yellow-200">redirect</span>()->
                            <span class="inline-block w-[2px] h-4 bg-amber-500 animate-pulse align-middle ml-1"></span>
                            <span class="absolute -top-5 left-32 bg-amber-500 text-white text-[10px] font-sans px-2 py-0.5 rounded shadow z-10 whitespace-nowrap">Aji is typing...</span>
                        </div>

                        <div class="pl-8">}</div>
                        <div class="pl-8"><span class="text-pink-400">return</span> <span class="text-yellow-200">back</span>()-><span class="text-yellow-200">withErrors</span>([<span class="text-green-300">'email'</span> => <span class="text-green-300">'Invalid login.'</span>]);</div>
                        <div>}</div>
                    </div>

                    <!-- VS Code AI Chat Panel (Right Side) -->
                    <div class="w-full lg:w-80 lg:flex-none border-t lg:border-t-0 lg:border-l border-gray-700 bg-[#1e1e1e] flex flex-col text-xs font-sans shrink-0">

                        <!-- Panel Header -->
                        <div class="px-4 py-2.5 bg-[#252526] border-b border-gray-700 flex justify-between items-center text-gray-300">
                            <span class="font-semibold flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                                Chat
                            </span>

                            <div class="flex items-center gap-2 text-gray-400">
                                <button class="hover:text-white" title="New Chat">+</button>
                                <button class="hover:text-white" title="Close">×</button>
                            </div>
                        </div>

                        <!-- Chat Messages Body -->
                        <div class="p-4 flex-1 space-y-4 overflow-y-auto max-h-[350px] lg:max-h-none text-gray-300">

                            <!-- User Message -->
                            <div class="flex gap-2 items-start">
                                <div class="w-6 h-6 rounded-full bg-blue-600 flex items-center justify-center text-white text-[10px] font-bold shrink-0">A</div>

                                <div class="bg-[#2d2d2d] p-2.5 rounded-lg border border-gray-700/50 space-y-1 w-full">
                                    <p class="text-[11px] text-gray-400 font-semibold">Arda</p>
                                    <p>Can we make sure the session regeneration is protected against fixation?</p>
                                </div>
                            </div>

                            <!-- AI / Agent Message -->
                            <div class="flex gap-2 items-start">
                                <div class="w-6 h-6 rounded-full bg-emerald-600 flex items-center justify-center text-white text-[10px] font-bold shrink-0">AI</div>

                                <div class="bg-[#252526] p-2.5 rounded-lg border border-gray-700/50 space-y-2 w-full">
                                    <p class="text-[11px] text-emerald-400 font-semibold flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-ping"></span> Agent Copilot
                                    </p>

                                    <p class="text-gray-300">
                                        Yes! Laravel's <code class="bg-[#1e1e1e] px-1 py-0.5 rounded text-pink-400 font-mono text-[10px]">Auth::attempt()</code> handles session regeneration safely out of the box.
                                    </p>
                                </div>
                            </div>

                        </div>

                        <!-- Chat Input Footer -->
                        <div class="p-3 bg-[#252526] border-t border-gray-700">

                            <div class="bg-[#1e1e1e] border border-gray-700 rounded-md p-2 flex flex-col gap-2 focus-within:border-blue-500 transition-colors">

                                <input type="text" placeholder="Ask Copilot or type @/..." class="bg-transparent text-gray-200 text-xs focus:outline-none placeholder-gray-500 font-sans" readonly />

                                <div class="flex justify-between items-center text-[10px] text-gray-400 pt-1 border-t border-gray-800">

                                    <span class="flex gap-2">
                                        <button class="hover:text-white flex items-center gap-1 bg-[#2d2d2d] px-1.5 py-0.5 rounded">@code</button>
                                        <button class="hover:text-white flex items-center gap-1 bg-[#2d2d2d] px-1.5 py-0.5 rounded">#file</button>
                                    </span>

                                    <button class="bg-blue-600 text-white px-2 py-0.5 rounded hover:bg-blue-500 transition">Send</button>

                                </div>

                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ==================== TECH STACK ==================== -->
    <section id="architecture" class="py-32 px-6 bg-gray-900 border-t border-gray-800">
        <div class="max-w-4xl mx-auto">

            <!-- Tech Stack -->
            <article>
                <h2 class="text-3xl font-bold mb-10 text-white center">Built With</h2>
                <div class="space-y-4">

                    <div class="p-6 bg-gray-950 rounded-2xl border border-gray-800 flex flex-col sm:flex-row sm:items-center gap-4 hover:border-gray-600 transition">
                        <div class="w-32 font-bold text-gray-500 uppercase tracking-widest text-xs">Frontend</div>
                        <div class="text-white font-medium text-lg">Monaco Editor, JS, Tailwind CSS</div>
                    </div>

                    <div class="p-6 bg-gray-950 rounded-2xl border border-gray-800 flex flex-col sm:flex-row sm:items-center gap-4 hover:border-gray-600 transition">
                        <div class="w-32 font-bold text-gray-500 uppercase tracking-widest text-xs">Backend</div>
                        <div class="text-white font-medium text-lg">Laravel 11+ / PHP 8.4 / MySQL</div>
                    </div>

                    <div class="p-6 bg-gray-950 rounded-2xl border border-gray-800 flex flex-col sm:flex-row sm:items-center gap-4 hover:border-gray-600 transition">
                        <div class="w-32 font-bold text-gray-500 uppercase tracking-widest text-xs">AI Core</div>
                        <div class="text-white font-medium text-lg">Custom Agent Framework + LLM API</div>
                    </div>

                    <div class="p-6 bg-gray-950 rounded-2xl border border-gray-800 flex flex-col sm:flex-row sm:items-center gap-4 hover:border-gray-600 transition">
                        <div class="w-32 font-bold text-gray-500 uppercase tracking-widest text-xs">Integration</div>
                        <div class="text-white font-medium text-lg">GitHub REST & GraphQL API</div>
                    </div>

                    <div class="p-6 bg-gray-950 rounded-2xl border border-gray-800 flex flex-col sm:flex-row sm:items-center gap-4 hover:border-gray-600 transition">
                        <div class="w-32 font-bold text-gray-500 uppercase tracking-widest text-xs">Realtime</div>
                        <div class="text-white font-medium text-lg">WebSockets / Laravel Reverb</div>
                    </div>

                </div>
            </article>

        </div>
    </section>


    <!-- Footer -->
    <footer class="bg-gray-950 border-t border-gray-800 p-10 text-center text-gray-500 text-sm font-medium">
        <p>PBKK Tugas 1 &copy; 2026. Built with Laravel, Monaco, & Tailwind CSS.</p>
    </footer>

</body>
</html>
