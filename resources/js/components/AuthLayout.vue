<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
defineProps<{ title: string; description: string }>();
</script>

<template>
    <Head :title="title" />
    <main
        class="flex min-h-screen items-center justify-center bg-[#090d16] px-5 py-12"
    >
        <div class="w-full max-w-md">
            <div class="mb-9 flex items-center gap-3">
                <span
                    class="flex h-11 w-11 items-center justify-center rounded-xl bg-indigo-600 text-xl text-white"
                    >⌘</span
                >
                <div>
                    <p class="text-lg font-bold tracking-wider">
                        MAGENTIC <span class="text-indigo-400">IDE</span>
                    </p>
                    <p class="text-xs text-slate-400">
                        Your workspace. Your ideas.
                    </p>
                </div>
            </div>
            <section
                class="rounded-2xl border border-slate-800 bg-[#0f1626] p-7 shadow-2xl"
            >
                <h1 class="text-2xl font-semibold text-white">{{ title }}</h1>
                <p class="mt-2 mb-7 text-sm leading-relaxed text-slate-400">
                    {{ description }}
                </p>
                <slot />
            </section>
        </div>
    </main>
</template>
