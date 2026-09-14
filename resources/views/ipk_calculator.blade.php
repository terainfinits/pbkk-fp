<x-layout>

    <!-- Navbar -->
    @include('partials.navbar')

    <!-- Hero / Calculator Section -->
    <section class="relative bg-gradient-to-b from-blue-50/50 via-white to-slate-50 py-12 lg:py-20">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- Section Header -->
            <div class="text-center max-w-2xl mx-auto mb-10">
                <span class="text-xs font-semibold tracking-wider text-its-accent uppercase bg-its-light px-3 py-1 rounded-full">Academic Tool</span>
                <h1 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight mt-3">
                    IPK <span class="text-its-accent">Calculator</span>
                </h1>
                <p class="text-slate-600 mt-2 text-sm sm:text-base">
                    Calculate your 2-semester cumulative Grade Point Average (IPK) instantly.
                </p>
            </div>

            <!-- Main Calculator Card -->
            <div class="bg-white border border-slate-200/80 rounded-2xl p-6 sm:p-10 shadow-sm">
                
                <!-- Controller Error Alert -->
                <div id="error-container" class="{{ !empty($initialError) ? '' : 'hidden' }} mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm flex items-center gap-3">
                    <i class="fa-solid fa-triangle-exclamation text-base text-red-500 shrink-0"></i>
                    <span id="error-message">{{ $initialError }}</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-12 gap-8 items-center">
                    
                    <!-- Input Form Column -->
                    <div class="md:col-span-7 space-y-5">
                        
                        <!-- IP Semester 1 -->
                        <div>
                            <label for="ip1" class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-2">
                                Semester 1 IP (IP 1)
                            </label>
                            <div class="relative rounded-xl shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                    <i class="fa-solid fa-chart-line text-sm"></i>
                                </div>
                                <input 
                                    type="number" 
                                    id="ip1" 
                                    name="ip1" 
                                    step="0.01" 
                                    min="0" 
                                    max="4" 
                                    maxlength="5"
                                    value="{{ $initialIP1 }}"
                                    placeholder="e.g. 3.75"
                                    class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 font-semibold focus:outline-none focus:ring-2 focus:ring-its-accent focus:bg-white transition"
                                >
                            </div>
                        </div>

                        <!-- IP Semester 2 -->
                        <div>
                            <label for="ip2" class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-2">
                                Semester 2 IP (IP 2)
                            </label>
                            <div class="relative rounded-xl shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                    <i class="fa-solid fa-chart-line text-sm"></i>
                                </div>
                                <input 
                                    type="number" 
                                    id="ip2" 
                                    name="ip2" 
                                    step="0.01" 
                                    min="0" 
                                    max="4" 
                                    maxlength="5"
                                    value="{{ $initialIP2 }}"
                                    placeholder="e.g. 3.85"
                                    class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 font-semibold focus:outline-none focus:ring-2 focus:ring-its-accent focus:bg-white transition"
                                >
                            </div>
                        </div>

                        <!-- Reset / Clear Button -->
                        <div class="pt-2 flex gap-3">
                            <button 
                                type="button" 
                                id="btn-reset" 
                                class="w-full py-2.5 rounded-xl border border-slate-200 text-slate-600 font-semibold text-xs hover:bg-slate-100 transition flex items-center justify-center gap-2"
                            >
                                <i class="fa-solid fa-rotate-left"></i> Reset Inputs
                            </button>
                        </div>

                    </div>

                    <!-- Result Display Column -->
                    <div class="md:col-span-5">
                        <div class="bg-gradient-to-br from-its-blue to-its-accent text-white rounded-2xl p-6 text-center shadow-lg relative overflow-hidden flex flex-col items-center justify-center min-h-[220px]">
                            <!-- Background Decor -->
                            <div class="absolute inset-0 bg-[linear-gradient(to_right,#ffffff08_1px,transparent_1px),linear-gradient(to_bottom,#ffffff08_1px,transparent_1px)] bg-[size:16px_16px]"></div>

                            <span class="text-xs uppercase font-bold tracking-wider text-blue-200 mb-1 z-10">Calculated IPK</span>
                            
                            <div class="my-2 z-10">
                                <span id="result-display" class="text-4xl sm:text-5xl font-extrabold tracking-tight">
                                    {{ $initialResult !== null ? number_format($initialResult, 2) : '0.00' }}
                                </span>
                                <span class="text-sm text-blue-200 block mt-1 font-medium">/ 4.00</span>
                            </div>

                            <div id="status-badge" class="mt-2 z-10 inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-white/10 border border-white/20 text-white">
                                <i class="fa-solid fa-graduation-cap text-xs"></i>
                                <span id="status-text">
                                    @if($initialResult !== null)
                                        Result Loaded
                                    @else
                                        Enter both IPs
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </section>

    <!-- Footer -->
    @include('partials.footer')

    <!-- Real-Time Interactive Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ip1Input = document.getElementById('ip1');
            const ip2Input = document.getElementById('ip2');
            const resultDisplay = document.getElementById('result-display');
            const statusText = document.getElementById('status-text');
            const errorContainer = document.getElementById('error-container');
            const errorMessage = document.getElementById('error-message');
            const btnReset = document.getElementById('btn-reset');

            function calculateRealtime() {
                const val1 = ip1Input.value.trim();
                const val2 = ip2Input.value.trim();

                // Clear client side error
                errorContainer.classList.add('hidden');
                errorMessage.innerText = '';

                if (val1 === '' || val2 === '') {
                    resultDisplay.innerText = '0.00';
                    statusText.innerText = 'Enter both IPs';
                    return;
                }

                const num1 = parseFloat(val1);
                const num2 = parseFloat(val2);

                if (isNaN(num1) || isNaN(num2)) {
                    showError('Please enter valid numeric values.');
                    return;
                }

                if (val1.length > 5 || val2.length > 5) {
                    showError('Number length must not exceed 5 characters.');
                    return;
                }

                if (num1 < 0 || num1 > 4 || num2 < 0 || num2 > 4) {
                    showError('IP value must be between 0.00 and 4.00.');
                    return;
                }

                const avg = (num1 + num2) / 2;
                resultDisplay.innerText = avg.toFixed(2);
                statusText.innerText = 'Calculated';
            }

            function showError(msg) {
                errorMessage.innerText = msg;
                errorContainer.classList.remove('hidden');
                resultDisplay.innerText = '0.00';
                statusText.innerText = 'Invalid Input';
            }

            ip1Input.addEventListener('input', calculateRealtime);
            ip2Input.addEventListener('input', calculateRealtime);

            btnReset.addEventListener('click', function () {
                ip1Input.value = '';
                ip2Input.value = '';
                errorContainer.classList.add('hidden');
                resultDisplay.innerText = '0.00';
                statusText.innerText = 'Enter both IPs';
            });
        });
    </script>

</x-layout>