<x-layout>

    <!-- Navbar -->
    @include('partials.navbar')

    <!-- Hero / 404 Error Section -->
    <section
        class="relative bg-gradient-to-b from-blue-50/50 via-white to-slate-50 py-16 lg:py-24 min-h-[75vh] flex items-center justify-center overflow-hidden">

        <!-- Background Grid Pattern Decor -->
        <div
            class="absolute inset-0 bg-[linear-gradient(to_right,#0055b808_1px,transparent_1px),linear-gradient(to_bottom,#0055b808_1px,transparent_1px)] bg-[size:24px_24px] pointer-events-none">
        </div>

        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10">

            <!-- Badge -->
            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full border border-blue-100">
                <span class="w-2 h-2 rounded-full bg-its-accent animate-pulse"></span>
                <span class="text-xl font-semibold uppercase tracking-wider text-its-accent">Error 404 :(</span>
            </div>


            <!-- Heading & Message -->
            <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight mt-6">
                Page Not Found
            </h2>

            <p class="text-slate-600 text-sm sm:text-base max-w-lg mx-auto mt-3 leading-relaxed">
                Oops! The page you are looking for is not available
            </p>

            <!-- Action Buttons -->
            <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-3">
                <a href="{{ url('/') }}"
                    class="w-full sm:w-auto px-6 py-3 rounded-xl bg-gradient-to-r from-its-blue to-its-accent text-white font-semibold text-sm shadow-md hover:opacity-95 transition flex items-center justify-center gap-2">
                    <i class="fa-solid fa-house text-xs"></i>
                    Back to Home
                </a>

                <button onclick="window.history.back()"
                    class="w-full sm:w-auto px-6 py-3 rounded-xl border border-slate-200 bg-white text-slate-700 font-semibold text-sm hover:bg-slate-50 transition flex items-center justify-center gap-2 shadow-sm">
                    <i class="fa-solid fa-arrow-left text-xs"></i>
                    Go Back
                </button>
            </div>

        </div>
    </section>

    <!-- Footer -->
    @include('partials.footer')

</x-layout>
