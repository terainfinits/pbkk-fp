<x-layout>

    <!-- Navbar -->
    @include('partials.navbar')

    <!-- Hero Section -->
    <section class="relative bg-gradient-to-b from-blue-50/50 via-white to-slate-50 py-16 lg:py-24 overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-8 items-center">

                <!-- Left Text Column -->
                <div class="lg:col-span-6 space-y-6 text-center lg:text-left">

                    <h1
                        class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-slate-900 tracking-tight leading-tight">
                        Shaping the Future of <span class="text-its-accent">Computer Science</span>
                    </h1>

                    <p class="text-lg text-slate-600 max-w-2xl mx-auto lg:mx-0 leading-relaxed">
                        Welcome to the Department of Informatics at ITS. We pioneer research, foster world-class tech
                        leaders, and build innovative solutions for global impact.
                    </p>

                    <div class="flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-4 pt-2">
                        <a href="#majors"
                            class="w-full sm:w-auto px-8 py-3.5 rounded-xl bg-its-accent hover:bg-its-blue text-white font-semibold text-center shadow-lg shadow-blue-500/25 transition duration-200">
                            Explore Majors
                        </a>
                        <a href="#labs"
                            class="w-full sm:w-auto px-8 py-3.5 rounded-xl bg-white border border-slate-200 hover:border-slate-300 text-slate-700 font-semibold text-center shadow-sm hover:shadow transition duration-200">
                            Our Laboratories
                        </a>
                    </div>

                    <!-- Quick Stats -->
                    <div class="grid grid-cols-3 gap-4 pt-8 border-t border-slate-200/80">
                        <div>
                            <div class="text-2xl sm:text-3xl font-extrabold text-its-accent">1985</div>
                            <div class="text-xs sm:text-sm text-slate-500 font-medium">Established</div>
                        </div>
                        <div>
                            <div class="text-2xl sm:text-3xl font-extrabold text-its-accent">50+</div>
                            <div class="text-xs sm:text-sm text-slate-500 font-medium">Lecturers</div>
                        </div>
                        <div>
                            <div class="text-2xl sm:text-3xl font-extrabold text-its-accent">5+</div>
                            <div class="text-xs sm:text-sm text-slate-500 font-medium">Study Programs</div>
                        </div>
                    </div>
                </div>

                <!-- Right Image Column (Placeholder) -->
                <div class="lg:col-span-6 relative">
                    <div class="relative mx-auto max-w-md lg:max-w-none">
                        <!-- Decorative Accent backdrop -->
                        <div
                            class="absolute -top-4 -bottom-4 -left-4 -right-4 bg-gradient-to-tr from-blue-200 to-indigo-100 rounded-3xl transform -rotate-1 -z-10">
                        </div>

                        <!-- Main Hero Image Box -->
                        <div
                            class="relative aspect-[16/10] sm:aspect-[4/3] rounded-2xl bg-slate-200 border-4 border-white shadow-2xl overflow-hidden flex flex-col items-center justify-center text-slate-400 group">
                            <!-- Background Grid Effect -->
                            <div
                                class="absolute inset-0 bg-[linear-gradient(to_right,#00000008_1px,transparent_1px),linear-gradient(to_bottom,#00000008_1px,transparent_1px)] bg-[size:16px_16px]">
                            </div>

                            <img src="/images/department.jpg" alt="Informatics Engineering Department"
                                class="w-full h-full object-cover">
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- Available Majors Section -->
    <section id="majors" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- Section Header -->
            <div class="text-center max-w-3xl mx-auto mb-16 space-y-3">
                <h2 class="text-xs font-bold uppercase tracking-widest text-its-accent">Undergraduate Academic Programs
                </h2>
                <h3 class="text-3xl sm:text-4xl font-extrabold text-slate-900">Available Majors</h3>
                <p class="text-slate-600">Explore our undergraduate study programs designed to equip future tech
                    pioneers with theoretical depth and industrial mastery.</p>
            </div>

            <!-- Majors Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">

                @php
                    $majors = [
                        [
                            'code' => 'IF',
                            'title' => 'Informatics Engineering',
                            'native' => 'Teknik Informatika',
                            'icon' => 'fa-laptop-code',
                            'desc' =>
                                'Focuses on theoretical computer science, algorithms, database systems, cybersecurity, and intelligent systems engineering.',
                            'curriculum_url' => '/curriculum/informatics-engineering',
                        ],
                        [
                            'code' => 'RPL',
                            'title' => 'Software Engineering',
                            'native' => 'Rekayasa Perangkat Lunak',
                            'icon' => 'fa-cubes text-3xl',
                            'desc' =>
                                'Dedicated to modern enterprise software construction, microservices architecture, DevOps, agile methodologies, and quality assurance.',
                            'curriculum_url' => '/curriculum/software-engineering',
                        ],
                        [
                            'code' => 'RKA',
                            'title' => 'AI Engineering',
                            'native' => 'Rekayasa Kecerdasan Artifisial',
                            'icon' => 'fa-brain text-3xl',
                            'desc' =>
                                'Specializes in machine learning, deep learning, computer vision, natural language processing, and scalable AI infrastructure.',
                            'curriculum_url' => '/curriculum/ai-engineering',
                        ],
                    ];
                @endphp

                @foreach ($majors as $major)
                    <div
                        class="bg-slate-50 border border-slate-200/80 rounded-2xl p-8 hover:shadow-xl transition duration-300 flex flex-col justify-between group relative overflow-hidden">
                        <!-- Background Accent Card Line -->
                        <div
                            class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-its-accent to-its-blue group-hover:h-2 transition-all">
                        </div>

                        <div>
                            <!-- Header with Badge & Icon -->
                            <div class="flex items-center justify-between mb-6">
                                <div
                                    class="w-14 h-14 rounded-xl bg-blue-100/80 text-its-accent flex items-center justify-center text-2xl group-hover:scale-110 transition duration-300">
                                    <i class="fa-solid {{ $major['icon'] }}"></i>
                                </div>
                                <span
                                    class="text-sm font-extrabold px-3.5 py-1.5 rounded-full bg-its-blue text-white shadow-sm">
                                    {{ $major['code'] }}
                                </span>
                            </div>

                            <h4 class="text-2xl font-bold text-slate-900 group-hover:text-its-accent transition mb-1">
                                {{ $major['title'] }}
                            </h4>
                            <p class="text-xs font-semibold text-slate-400 mb-4">{{ $major['native'] }}</p>

                            <p class="text-sm text-slate-600 leading-relaxed mb-6">
                                {{ $major['desc'] }}
                            </p>
                        </div>

                        <div class="pt-6 border-t border-slate-200/80 flex items-center justify-between">
                            <a href="{{ $major['curriculum_url'] }}"
                                class="inline-flex items-center text-xs font-bold text-its-accent hover:text-its-blue transition group-hover:translate-x-1">
                                Curriculum <i class="fa-solid fa-arrow-right ml-1.5"></i>
                            </a>
                        </div>
                    </div>
                @endforeach

            </div>
        </div>
    </section>

    <!-- Research Laboratories Section -->
    <section id="labs" class="py-20 bg-slate-100/70 overflow-hidden border-y border-slate-200/60">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-12">
            <div class="text-center max-w-3xl mx-auto space-y-3">
                <h2 class="text-xs font-bold uppercase tracking-widest text-its-accent">Research & Innovation
                    Laboratories</h2>
                <h3 class="text-3xl sm:text-4xl font-extrabold text-slate-900">Informatics Laboratories</h3>
                <p class="text-slate-600">Explore our 8 dedicated laboratories driving cutting-edge research, industry
                    collaborations, and hands-on learning.</p>
            </div>
        </div>

        @php
            $labs = [
                [
                    'name' => 'Algoritma dan Pemrograman (ALPRO)',
                    'icon' => 'fa-code',
                    'url' => '/labs/alpro',
                    'image_url' => '/images/labs/alpro.png',
                ],
                [
                    'name' => 'Rekayasa Perangkat Lunak (RPL)',
                    'icon' => 'fa-diagram-project',
                    'url' => '/labs/rpl',
                    'image_url' => '/images/labs/rpl.png',
                ],
                [
                    'name' => 'Net-Centric Computing (NCC)',
                    'icon' => 'fa-network-wired',
                    'url' => '/labs/kbj',
                    'image_url' => '/images/labs/ncc.jpg',
                ],
                [
                    'name' => 'Komputasi Cerdas Visi (KCV)',
                    'icon' => 'fa-eye',
                    'url' => '/labs/kcv',
                    'image_url' => '/images/labs/kcv.jpeg',
                ],
                [
                    'name' => 'Arsitektur dan Jaringan Komputer (Netics)',
                    'icon' => 'fa-server',
                    'url' => '/labs/ajk',
                    'image_url' => '/images/labs/netics.png',
                ],
                [
                    'name' => 'Pemodelan dan Komputasi Terapan (PKT)',
                    'icon' => 'fa-chart-pie',
                    'url' => '/labs/sdk',
                    'image_url' => '/images/labs/pkt.jpg',
                ],
                [
                    'name' => 'Manajemen Cerdas Informasi (MCI)',
                    'icon' => 'fa-database',
                    'url' => '/labs/mi',
                    'image_url' => '/images/labs/mci.jpeg',
                ],
                [
                    'name' => 'Grafika, Interaksi, dan Game (GIGA)',
                    'icon' => 'fa-vr-cardboard',
                    'url' => '/labs/kcg',
                    'image_url' => '/images/labs/giga.png',
                ],
            ];
        @endphp

        <!-- Draggable Marquee Wrapper -->
        <div class="relative w-full overflow-hidden py-4 select-none">
            <!-- Left and Right Gradient Overlay Fades -->
            <div
                class="absolute left-0 top-0 bottom-0 w-24 bg-gradient-to-r from-slate-100/90 to-transparent z-10 pointer-events-none">
            </div>
            <div
                class="absolute right-0 top-0 bottom-0 w-24 bg-gradient-to-l from-slate-100/90 to-transparent z-10 pointer-events-none">
            </div>

            <!-- Scrollable & Draggable Outer Container -->
            <div id="marqueeContainer" class="overflow-x-auto no-scrollbar cursor-grab active:cursor-grabbing flex">
                <div id="marqueeTrack" class="flex space-x-6 w-max">
                    <!-- Triple loop array to guarantee continuous infinite drag without empty white spaces -->
                    @foreach (array_merge($labs, $labs, $labs) as $lab)
                        <a href="{{ $lab['url'] }}" draggable="false"
                            class="lab-card w-72 h-56 bg-white border border-slate-200/80 rounded-2xl p-5 shadow-sm hover:shadow-xl hover:border-its-accent transition-all duration-300 flex flex-col items-center justify-center text-center space-y-3 shrink-0 group">
                            <!-- Lab Logo Placeholder -->
                            <div
                                class="w-14 h-14 rounded-xl bg-white text-its-blue flex items-center justify-center text-2xl border border-blue-100 shadow-inner group-hover:scale-110 group-hover:text-white transition duration-300">
                                {{-- <i class="fa-solid {{ $lab['icon'] }}"></i> --}}
                                <img src="{{ $lab['image_url'] }}" alt="{{ $lab['name'] }} Logo"
                                    class="w-full h-full object-cover rounded-xl">
                            </div>
                            <!-- Lab Name -->
                            <h5
                                class="text-sm font-bold text-slate-800 group-hover:text-its-accent transition line-clamp-2 px-2">
                                {{ $lab['name'] }}
                            </h5>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <!-- Single Featured Student Spotlight Section -->
    <section id="student" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- Section Header -->
            <div class="text-center max-w-3xl mx-auto mb-16 space-y-3">
                <h3 class="text-xs font-bold uppercase tracking-widest text-its-accent">Student Spotlights</h2>
                    <h3 class="text-3xl sm:text-4xl font-extrabold text-slate-900">Featured Student Spotlight</h3>
                    <p class="text-slate-600">Celebrating excellence and innovations pioneered by Informatics ITS
                        students
                        on global stages.</p>
            </div>

            <!-- Single Student Profile Container -->
            <div class="max-w-4xl mx-auto bg-slate-50 border border-slate-200/80 rounded-3xl overflow-hidden shadow-xl">
                <div class="grid grid-cols-1 md:grid-cols-12 items-stretch">

                    <!-- Left Column: Student Image Placeholder (3:4 ratio container) -->
                    <div
                        class="md:col-span-4 relative bg-slate-200 min-h-[380px] flex flex-col items-center justify-center text-slate-400 p-6 group overflow-hidden">
                        <!-- Grid pattern backdrop -->
                        <div
                            class="absolute inset-0 bg-[linear-gradient(to_right,#00000008_1px,transparent_1px),linear-gradient(to_bottom,#00000008_1px,transparent_1px)] bg-[size:14px_14px]">
                        </div>

                        <img src="/images/student.jpg" alt="Raden Kurniawan Agung Fitrianto"
                            class="absolute inset-0 w-full h-full object-cover z-0">

                        <!-- Corner Badge Overlay -->
                        <div
                            class="absolute top-4 left-4 bg-its-blue text-white px-3.5 py-1 rounded-full text-xs font-bold shadow">
                            Informatics 2024
                        </div>
                    </div>

                    <!-- Right Column: Student Profile Information -->
                    <div class="md:col-span-7 p-8 md:p-10 flex flex-col justify-between space-y-6">
                        <div>

                            <h4 class="text-3xl font-extrabold text-slate-900 mb-2">
                                Raden Kurniawan Agung Fitrianto
                            </h4>
                            <p class="text-sm font-medium text-slate-500 mb-6">
                                Undergraduate Informatics Engineering Student
                            </p>

                            <p class="text-slate-600 leading-relaxed text-sm mb-4">
                                "Specializing in Computer Vision and Edge AI applications. Led the ITS engineering team
                                to secure 1st Place in the International Smart Campus Challenge 2024 by deploying
                                real-time AI flood detection models."
                            </p>

                        </div>

                        <!-- Card Footer Links -->
                        <div class="pt-6 border-t border-slate-200 flex items-center justify-between">
                            <a href={{ route('dashboard.mahasiswa.detail', ['nrp' => '5025241104']) }}
                                class="inline-flex items-center text-xs font-bold text-its-accent hover:text-its-blue transition">
                                Read Full Profile <i class="fa-solid fa-arrow-right ml-2"></i>
                            </a>
                        </div>

                    </div>

                </div>
            </div>

        </div>
    </section>

    <!-- Footer -->
    @include('partials.footer')

    <!-- Smooth Infinite JavaScript Draggable Marquee Script -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const container = document.getElementById('marqueeContainer');
            const track = document.getElementById('marqueeTrack');

            let speed = 1.5; // Auto-scroll speed
            let isHovered = false;
            let isDragging = false;
            let startX = 0;
            let scrollLeftPos = 0;
            let dragDistance = 0;

            // Start scroll position in middle set of cloned elements to allow bidirectional dragging
            container.scrollLeft = track.scrollWidth / 3;

            function tick() {
                if (!isHovered && !isDragging) {
                    container.scrollLeft += speed;
                }

                // Infinite Loop Wrapping Logic
                const oneSetWidth = track.scrollWidth / 3;
                if (container.scrollLeft >= oneSetWidth * 2) {
                    container.scrollLeft -= oneSetWidth;
                } else if (container.scrollLeft <= 0) {
                    container.scrollLeft += oneSetWidth;
                }

                requestAnimationFrame(tick);
            }
            requestAnimationFrame(tick);

            // Pause marquee on hover
            container.addEventListener('mouseenter', () => isHovered = true);
            container.addEventListener('mouseleave', () => {
                isHovered = false;
                isDragging = false;
            });

            // Mouse Drag Controls
            container.addEventListener('mousedown', (e) => {
                isDragging = true;
                dragDistance = 0;
                startX = e.pageX - container.offsetLeft;
                scrollLeftPos = container.scrollLeft;
            });

            container.addEventListener('mouseup', () => {
                isDragging = false;
            });

            container.addEventListener('mousemove', (e) => {
                if (!isDragging) return;
                e.preventDefault();
                const x = e.pageX - container.offsetLeft;
                const walk = (x - startX) * 1.5;
                dragDistance = Math.abs(walk);
                container.scrollLeft = scrollLeftPos - walk;
            });

            // Prevent link click when dragging cards
            const labCards = document.querySelectorAll('.lab-card');
            labCards.forEach(card => {
                card.addEventListener('click', (e) => {
                    if (dragDistance > 8) {
                        e.preventDefault();
                    }
                });
            });

            // Mobile Touch Support
            let touchStartX = 0;
            container.addEventListener('touchstart', (e) => {
                isHovered = true;
                touchStartX = e.touches[0].pageX;
                scrollLeftPos = container.scrollLeft;
            }, {
                passive: true
            });

            container.addEventListener('touchmove', (e) => {
                const touchX = e.touches[0].pageX;
                const walk = (touchX - touchStartX) * 1.5;
                container.scrollLeft = scrollLeftPos - walk;
            }, {
                passive: true
            });

            container.addEventListener('touchend', () => {
                isHovered = false;
            });
        });
    </script>
</x-layout>
