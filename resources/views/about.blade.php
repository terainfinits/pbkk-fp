<?php
$members = [
    [
        'initials' => 'RF',
        'name' => 'Raden Kurniawan Agung Fitrianto',
        'nim' => '5025241104',
        'major' => 'Teknik Informatika',
        'summary' => 'All Role',
        'detail' => 'Normal informatics engineering student at  Sepuluh Nopember Institute of Technology.',
        'email' => 'rkurniawanagung32@gmail.com',
        'github' => 'https://github.com/theRadn',
    ],
    [
        'initials' => 'AB',
        'name' => 'Abdullah Sultan Barizy',
        'nim' => '5025241092',
        'major' => 'Informatics Engineering',
        'summary' => 'The Project Manager',
        'detail' => 'The (future) Head of Student Social Development department in HMTC and do stuff in PKT Lab',
        'email' => 'abdullahsultanbarizy@gmail.com',
        'github' => 'https://github.com/lamphyon',
    ],
    [
        'initials' => 'WN',
        'name' => 'Willy Dava Nugraha',
        'nim' => '5025241090',
        'major' => 'Teknik Informatika',
        'summary' => 'Junior Software Developer',
        'detail' => 'Assisting in designing, coding, testing, and maintaining software applications.',
        'email' =>  'wdnugraha24@gmail.com',
        'github' => 'https://github.com/terainfinits',
    ],
    [
        'initials' => 'AZ',
        'name' => 'Addien Zafriyan Al Akhsan',
        'nim' => '5025241058',
        'major' => 'Teknik Informatika',
        'summary' => 'Dynamic Assistant',
        'detail' => 'ITS Global Engagement Volunteer and a Normal Human',
        'email' => 'addien.zafriyan@example.com',
        'github' => 'https://github.com/RevEnterprise',
    ],
    [
        'initials' => 'AJ',
        'name' => 'Aji Zaenul Musthofa',
        'nim' => '5025241065',
        'major' => 'Teknik Informatika',
        'summary' => 'Informatics Engineering Student',
        'detail' => 'Informatics Engineering student at Sepuluh Nopember Institute of Technology.',
        'email' => 'ajizaenulm33@gmail.com',
        'github' => 'https://github.com/ajizaenulm',
    ],
    [
    'initials' => 'AR',
    'name' => 'Anak Agung Putu Arda Nareswara',
    'nim' => '5025241074',
    'major' => 'Teknik Informatika',
    'summary' => 'Frontend Developer',
    'detail' => 'Developing and implementing responsive user interfaces to deliver a smooth and engaging web experience.',
    'email' => 'agungarda81@gmail.com',
    'github' => 'https://github.com/gungardaa',
    ],
];
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Department Profile - ITS Academic Profile</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .bg-grid-pattern {
            background-size: 40px 40px;
            background-image:
                linear-gradient(to right, rgba(255, 255, 255, 0.03) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
        }

        .text-glow {
            text-shadow: 0 0 25px rgba(245, 158, 11, 0.6);
        }

        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #030712; }
        ::-webkit-scrollbar-thumb { background: #1f2937; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #374151; }

        dialog::backdrop {
            background: rgba(0, 0, 0, 0.7);
        }
        dialog {
            background: transparent;
            border: none;
            padding: 0;
            max-width: 32rem;
            width: 90vw;
        }
        dialog[open] {
            animation: pop 0.25s ease-out;
        }
        @keyframes pop {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }
    </style>
</head>
<body class="bg-gray-950 text-gray-300 font-sans antialiased selection:bg-emerald-500 selection:text-gray-950 overflow-x-hidden">

    <!-- ==================== NAVBAR ==================== -->
    <nav class="bg-gray-900 border-b border-gray-800 p-4 sticky top-0 z-50 shadow-lg">
        <div class="max-w-4xl mx-auto flex justify-center gap-6 text-sm font-semibold">
            <a href="/" class="text-gray-400 hover:text-gray-200 transition">Landing Page</a>
            <a href="/about" class="text-emerald-400 hover:text-emerald-300 transition">Department Profile</a>
            <a href="/project-idea" class="text-gray-400 hover:text-gray-200 transition">Project Plan</a>
            <a href="/hitung" class="text-gray-400 hover:text-gray-200 transition">Calculator</a>
        </div>
    </nav>

    <!-- ==================== HERO ==================== -->
    <header class="relative min-h-screen flex flex-col items-center justify-center bg-grid-pattern pt-20 pb-28">
        <div class="absolute top-1/3 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[700px] h-[700px] bg-emerald-500/10 blur-[150px] rounded-full pointer-events-none z-0"></div>

        <div class="z-10 text-center max-w-5xl px-6 flex flex-col items-center">
            <div class="text-emerald-400 font-black tracking-widest text-sm uppercase mb-6 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253m0-13V21m0-15.747V4"></path></svg>
                ITS Academic Profile
            </div>

            <h1 class="text-4xl md:text-6xl lg:text-7xl font-extrabold tracking-tighter mb-6 bg-clip-text text-transparent bg-gradient-to-b from-white to-gray-500">
                Informatics Engineering Department ITS
            </h1>

            <h2 class="text-2xl md:text-4xl font-bold text-emerald-400 mb-6 text-glow">
                CETAK GENERASI TEKNOLOGI MASA DEPAN!
            </h2>

            <p class="text-lg md:text-xl text-gray-300 max-w-3xl leading-relaxed font-light">
                Developing outstanding human resources in the fields of computing, software, and artificial intelligence toward international standards.
            </p>

            <div class="mt-12 flex flex-col sm:flex-row gap-6 justify-center">
                <a href="#profil" class="px-8 py-4 bg-emerald-500 hover:bg-emerald-400 text-gray-950 rounded-full font-bold text-lg transition duration-300 shadow-[0_0_30px_rgba(245,158,11,0.4)]">
                    Department Profile
                </a>
                <a href="#tim" class="px-8 py-4 bg-transparent hover:bg-gray-900 text-white rounded-full font-bold text-lg transition duration-300 border border-gray-700">
                    Our team
                </a>
            </div>
        </div>
    </header>

    <!-- ==================== PROFIL JURUSAN ==================== -->
    <section id="profil" class="min-h-screen py-28 px-6 bg-gray-950 border-t border-gray-800">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-16">
                <div class="text-emerald-400 font-black tracking-widest text-xs uppercase mb-4">About The Department</div>
                <h2 class="text-3xl md:text-5xl font-bold text-white tracking-tight">
                    Department Profile
                </h2>
            </div>

            <div class="grid lg:grid-cols-5 gap-12 items-start">
                <div class="lg:col-span-3 space-y-8">
                    <p class="text-gray-300 leading-relaxed text-lg">
                       The Department of Informatics Engineering (TC) at Institut Teknologi Sepuluh Nopember (ITS) focuses on education, research,
                        and innovation in the fields of computing, software engineering, and artificial intelligence. The department is committed to developing
                        highly capable graduates equipped with strong theoretical foundations, practical skills, and the ability to address complex technological challenges.
                    </p>
                    <p class="text-gray-400 leading-relaxed">
                        Through a combination of academic learning, research activities, and industry-oriented projects, the department provides students with opportunities to explore areas
                        such as software development, data science, intelligent systems, computer networks, and computational methods. By fostering collaboration between students, lecturers,
                        researchers, and industry partners, the department strives to contribute to technological advancement both nationally and internationally.
                    </p>

                    <div class="p-6 bg-gray-900 rounded-2xl border border-gray-800 border-l-4 border-l-emerald-500 shadow-inner mt-4">
                        <div class="flex items-center gap-2 text-emerald-400 font-bold tracking-wider text-sm uppercase mb-3">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Visi
                        </div>
                        <p class="italic text-gray-300 leading-relaxed">
                            The department’s vision is to become an internationally recognized leading institution for higher education in the field of Informatics Engineering.
                        </p>
                    </div>
                </div>

                <div class="lg:col-span-2 grid sm:grid-cols-2 gap-6">
                    <div class="p-6 bg-gray-900 rounded-2xl border border-gray-800">
                        <div class="text-4xl font-black text-emerald-400 mb-2">1985</div>
                        <div class="text-gray-400 text-sm font-semibold uppercase tracking-wider">Established</div>
                    </div>
                    <div class="p-6 bg-gray-900 rounded-2xl border border-gray-800">
                        <div class="text-4xl font-black text-emerald-400 mb-2">5+</div>
                        <div class="text-gray-400 text-sm font-semibold uppercase tracking-wider">Study program</div>
                    </div>
                    <div class="p-6 bg-gray-900 rounded-2xl border border-gray-800">
                        <div class="text-4xl font-black text-emerald-400 mb-2">10+</div>
                        <div class="text-gray-400 text-sm font-semibold uppercase tracking-wider">Research Group</div>
                    </div>
                    <div class="p-6 bg-gray-900 rounded-2xl border border-gray-800">
                        <div class="text-4xl font-black text-emerald-400 mb-2">50+</div>
                        <div class="text-gray-400 text-sm font-semibold uppercase tracking-wider">Lecturer</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ==================== TIM ==================== -->
    <section id="tim" class="py-28 px-6 bg-gray-950 border-t border-gray-800">
        <div class="max-w-6xl mx-auto">
            <div class="text-center mb-16">
                <div class="text-emerald-400 font-black tracking-widest text-xs uppercase mb-4">Our Team</div>
                <h2 class="text-3xl md:text-5xl font-bold text-white tracking-tight">
                    Team Member
                </h2>
            </div>

            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach ($members as $i => $m): ?>
                <article
                    onclick="openMemberModal(<?= $i ?>)"
                    class="cursor-pointer p-8 bg-gray-950 rounded-3xl border border-gray-800 hover:border-emerald-500/50 hover:-translate-y-2 transition duration-500"
                >
                    <div class="w-16 h-16 bg-emerald-500/10 border border-emerald-500/30 rounded-2xl flex items-center justify-center text-2xl font-black text-emerald-400 mb-6">
                        <?= htmlspecialchars($m['initials']) ?>
                    </div>
                    <h3 class="text-lg font-bold text-white mb-1"><?= htmlspecialchars($m['name']) ?></h3>
                    <p class="text-sm font-mono text-gray-500 mb-2"><?= htmlspecialchars($m['nim']) ?></p>
                    <p class="text-sm text-emerald-400 font-semibold mb-4"><?= htmlspecialchars($m['major']) ?></p>
                    <p class="text-sm text-gray-400 leading-relaxed">
                        <?= htmlspecialchars($m['summary']) ?>
                    </p>
                </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <dialog id="memberModal">
        <div class="bg-gray-950 border border-gray-800 rounded-3xl p-8 relative">
            <button
                onclick="document.getElementById('memberModal').close()"
                class="absolute top-5 right-5 text-gray-500 hover:text-white transition"
                aria-label="Close"
            >
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>

            <div id="modalInitials" class="w-16 h-16 bg-emerald-500/10 border border-emerald-500/30 rounded-2xl flex items-center justify-center text-2xl font-black text-emerald-400 mb-6"></div>
            <h3 id="modalName" class="text-xl font-bold text-white mb-1"></h3>
            <p id="modalNim" class="text-sm font-mono text-gray-500 mb-2"></p>
            <p id="modalMajor" class="text-sm text-emerald-400 font-semibold mb-4"></p>
            <p id="modalDetail" class="text-sm text-gray-400 leading-relaxed mb-6"></p>

            <div class="flex flex-col gap-2 border-t border-gray-800 pt-5 text-sm">
                <div class="flex items-center gap-2 text-gray-400">
                    <span class="text-gray-600">Email:</span>
                    <span id="modalEmail" class="text-white"></span>
                </div>
                <div class="flex items-center gap-2 text-gray-400">
                    <span class="text-gray-600">GitHub:</span>
                    <a id="modalGithub" href="#" target="_blank" class="text-emerald-400 hover:underline"></a>
                </div>
            </div>
        </div>
    </dialog>

    <!-- ==================== FOOTER ==================== -->
    <footer class="bg-gray-950 border-t border-gray-800 p-10 text-center text-gray-500 text-sm font-medium">
        <p>PBKK Tugas 1 &copy; 2026. Built with Laravel & Tailwind CSS.</p>
    </footer>

    <script>
        const members = <?= json_encode($members) ?>;

        function openMemberModal(index) {
            const m = members[index];
            document.getElementById('modalInitials').textContent = m.initials;
            document.getElementById('modalName').textContent = m.name;
            document.getElementById('modalNim').textContent = m.nim;
            document.getElementById('modalMajor').textContent = m.major;
            document.getElementById('modalDetail').textContent = m.detail;
            document.getElementById('modalEmail').textContent = m.email;
            document.getElementById('modalGithub').textContent = m.github;
            document.getElementById('modalGithub').href = m.github;

            const dialog = document.getElementById('memberModal');
            dialog.showModal();
        }

        document.getElementById('memberModal').addEventListener('click', function(e) {
            if (e.target === this) this.close();
        });
    </script>

</body>
</html>
