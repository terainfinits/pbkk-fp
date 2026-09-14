<x-layout>

    <!-- Navbar -->
    @include('partials.navbar')

    @php
        $isFound = ($nrp ?? '') == '5025241104';

        $profile = [
            'name'     => $isFound ? 'Raden Kurniawan Agung Fitrianto' : 'Student Data Not Found',
            'role'     => $isFound ? 'Undergraduate Informatics Engineering Student' : 'Department of Informatics Student',
            'nrp'      => $nrp ?? '—',
            'major'    => $isFound ? 'Informatics Engineering (S1 / Bachelor)' : '—',
            'email'    => $isFound ? '5025241104@student.its.ac.id' : '—',
            'standing' => $isFound ? 'Semester 5' : '—',
            'advisor'  => $isFound ? 'Daniel Oranova Siahaan, S.Kom., M.Sc., PD.Eng.' : '—',
            'focus'    => $isFound ? 'Full-Stack Software Development, Data Science, and Distributed Systems.' : '—',
            'profile_img' => $isFound ? '/images/student.jpg' : null,
        ];

        $skills = $isFound ? [
            ['category' => 'C / C++', 'icon' => 'fa-solid fa-code', 'tools' => ['Make', 'GDB']],
            ['category' => 'JavaScript', 'icon' => 'fa-brands fa-js', 'tools' => ['React', 'Express.js', 'Next.js', 'Three.js', 'TypeScript']],
            ['category' => 'CSS', 'icon' => 'fa-brands fa-css3-alt', 'tools' => ['Tailwind CSS']],
            ['category' => 'Database', 'icon' => 'fa-solid fa-database', 'tools' => ['MySQL', 'PostgreSQL', 'SQLite']],
            ['category' => 'Java', 'icon' => 'fa-brands fa-java', 'tools' => ['Gradle', 'Maven', 'JSP']],
            ['category' => 'Python', 'icon' => 'fa-brands fa-python', 'tools' => ['Flask', 'NumPy', 'Jupyter', 'Scikit-Learn', 'Pandas', 'Matplotlib']],
            ['category' => 'Graphics', 'icon' => 'fa-solid fa-palette', 'tools' => ['Figma', 'Canva', 'GIMP', 'Krita']],
            ['category' => 'Linux', 'icon' => 'fa-brands fa-linux', 'tools' => ['OpenSUSE', 'NixOS']],
            ['category' => 'Tools', 'icon' => 'fa-solid fa-toolbox', 'tools' => ['Git', 'Docker']],
        ] : [];
    @endphp

    <!-- Profile Hero Section -->
    <section class="relative bg-gradient-to-b from-blue-50/50 via-white to-slate-50 py-12 lg:py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- Section Header -->
            <div class="mb-8 flex items-center justify-between">
                <div>
                    <h1 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight mt-2">
                        Student <span class="text-its-accent">Profile</span>
                    </h1>
                </div>
            </div>

            <!-- Main Hero Content: Profile Card Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

                <!-- Left Column: Student Photo & Quick Badge -->
                <div class="lg:col-span-3 flex flex-col items-center">
                    <div class="relative w-full max-w-sm">
                        <!-- Decorative Backdrop -->
                        <div class="absolute -top-3 -bottom-3 -left-3 -right-3 bg-gradient-to-tr from-blue-200 to-indigo-100 rounded-3xl transform -rotate-1 -z-10"></div>

                        <!-- Main Photo Box -->
                        <div class="relative aspect-[3/4] rounded-2xl bg-slate-200 border-4 border-white shadow-xl overflow-hidden flex flex-col items-center justify-center text-slate-400 group">
                            <div class="absolute inset-0 bg-[linear-gradient(to_right,#00000008_1px,transparent_1px),linear-gradient(to_bottom,#00000008_1px,transparent_1px)] bg-[size:16px_16px]"></div>

                            @if ($isFound && $profile['profile_img'])
                                <img src="{{ $profile['profile_img'] }}" alt="{{ $profile['name'] }}"
                                    class="absolute inset-0 w-full h-full object-cover z-0">
                            @else
                                <i class="fa-solid fa-user-graduate text-6xl mb-3 text-slate-400 group-hover:scale-105 transition duration-300"></i>
                            @endif
                            <span class="text-sm font-semibold text-slate-500">
                                {{ $isFound ? 'Student Photo Placeholder' : 'No Photo Available' }}
                            </span>
                            <span class="text-xs text-slate-400 mt-1">3:4 Aspect Ratio</span>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Key-Value Profile Form / Grid -->
                <div class="lg:col-span-8 bg-white border border-slate-200/80 rounded-2xl p-6 sm:p-8 shadow-sm">
                    <div class="border-b border-slate-100 pb-5 mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <div>
                            <h2 class="text-2xl font-bold text-slate-900">{{ $profile['name'] }}</h2>
                            <p class="text-sm font-medium text-slate-500">{{ $profile['role'] }}</p>
                        </div>
                    </div>

                    <!-- Key-Value Display Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-y-6 gap-x-8">

                        <div class="flex items-start space-x-3">
                            <div class="w-10 h-10 rounded-lg bg-blue-50 text-its-accent flex items-center justify-center shrink-0">
                                <i class="fa-solid fa-id-card text-base"></i>
                            </div>
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">NRP</dt>
                                <dd class="text-sm font-semibold text-slate-800 mt-0.5">{{ $profile['nrp'] }}</dd>
                            </div>
                        </div>

                        <div class="flex items-start space-x-3">
                            <div class="w-10 h-10 rounded-lg bg-blue-50 text-its-accent flex items-center justify-center shrink-0">
                                <i class="fa-solid fa-graduation-cap text-base"></i>
                            </div>
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Major & Degree</dt>
                                <dd class="text-sm font-semibold text-slate-800 mt-0.5">{{ $profile['major'] }}</dd>
                            </div>
                        </div>

                        <div class="flex items-start space-x-3">
                            <div class="w-10 h-10 rounded-lg bg-blue-50 text-its-accent flex items-center justify-center shrink-0">
                                <i class="fa-solid fa-envelope text-base"></i>
                            </div>
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Institutional Email</dt>
                                <dd class="text-sm font-semibold text-slate-800 mt-0.5">{{ $profile['email'] }}</dd>
                            </div>
                        </div>

                        <div class="flex items-start space-x-3">
                            <div class="w-10 h-10 rounded-lg bg-blue-50 text-its-accent flex items-center justify-center shrink-0">
                                <i class="fa-solid fa-chart-line text-base"></i>
                            </div>
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Current Academic Standing</dt>
                                <dd class="text-sm font-semibold text-slate-800 mt-0.5">{{ $profile['standing'] }}</dd>
                            </div>
                        </div>

                        <div class="flex items-start space-x-3">
                            <div class="w-10 h-10 rounded-lg bg-blue-50 text-its-accent flex items-center justify-center shrink-0">
                                <i class="fa-solid fa-user-tie text-base"></i>
                            </div>
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Academic Advisor</dt>
                                <dd class="text-sm font-semibold text-slate-800 mt-0.5">{{ $profile['advisor'] }}</dd>
                            </div>
                        </div>

                        <div class="flex items-start space-x-3">
                            <div class="w-10 h-10 rounded-lg bg-blue-50 text-its-accent flex items-center justify-center shrink-0">
                                <i class="fa-solid fa-lightbulb text-base"></i>
                            </div>
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Focus & Research Interest</dt>
                                <dd class="text-sm font-semibold text-slate-800 mt-0.5">{{ $profile['focus'] }}</dd>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- Skills Section -->
    <section class="py-16 lg:py-24 bg-slate-50 relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- Section Title -->
            <div class="text-center max-w-3xl mx-auto mb-16">
                <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
                    Technical <span class="text-its-accent">Skills & Competencies</span>
                </h2>
                <p class="text-slate-600 mt-3 text-base sm:text-lg">
                    {{ $isFound ? 'Hover over any technology category below to inspect specific frameworks, tools, and technical proficiencies.' : 'No skill profile available for this student record.' }}
                </p>
            </div>

            <!-- Interactive Skills Grid / Fallback -->
            @if(count($skills) > 0)
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8">
                    @foreach($skills as $skill)
                        <div class="group relative h-52 bg-white rounded-2xl border border-slate-200 shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col items-center justify-center p-6 overflow-hidden">

                            <!-- Default Front View -->
                            <div class="flex flex-col items-center justify-center space-y-3 group-hover:opacity-0 transition-opacity duration-300">
                                <div class="w-16 h-16 rounded-2xl bg-its-light/60 text-its-accent flex items-center justify-center text-3xl shadow-inner group-hover:scale-90 transition duration-300">
                                    <i class="{{ $skill['icon'] }}"></i>
                                </div>
                                <h3 class="text-lg font-bold text-slate-800 tracking-wide text-center">
                                    {{ $skill['category'] }}
                                </h3>
                            </div>

                            <!-- Hover Overlay View -->
                            <div class="absolute inset-0 bg-its-blue text-white rounded-2xl p-6 flex flex-col items-center justify-center opacity-0 group-hover:opacity-100 transition-all duration-300 transform scale-95 group-hover:scale-100 z-10 shadow-2xl">
                                <h3 class="text-lg font-bold text-white mb-3 text-center border-b border-blue-400/30 pb-1 w-full">
                                    {{ $skill['category'] }}
                                </h3>

                                <div class="flex flex-wrap justify-center gap-2 overflow-y-auto max-h-32 no-scrollbar py-1">
                                    @foreach($skill['tools'] as $tool)
                                        <span class="bg-white/10 hover:bg-white/20 text-blue-100 text-xs font-semibold px-3 py-1 rounded-lg border border-white/15 transition-colors">
                                            {{ $tool }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>

                        </div>
                    @endforeach
                </div>
            @else
                <div class="max-w-md mx-auto bg-white rounded-2xl border border-slate-200 p-8 text-center shadow-sm">
                    <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
                        <i class="fa-solid fa-folder-open text-xl"></i>
                    </div>
                    <h4 class="text-base font-bold text-slate-700">No Skills Recorded</h4>
                    <p class="text-xs text-slate-500 mt-1">NRP {{ $nrp ?? 'Unspecified' }} does not have any skill categories associated with their profile.</p>
                </div>
            @endif

        </div>
    </section>

    <!-- Footer -->
    @include('partials.footer')

</x-layout>