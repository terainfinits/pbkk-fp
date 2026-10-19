<script setup lang="ts">
import { Link, Head } from '@inertiajs/vue3';
defineProps<{ status: number }>();
</script>

<template>
    <Head :title="String(status)" />
    <main class="grid min-h-screen place-items-center px-6 text-center">
        <div>
            <p class="text-sm font-semibold tracking-widest text-indigo-400">
                MAGENTIC IDE
            </p>
            <h1 class="mt-5 text-6xl font-bold">{{ status }}</h1>
            <p class="mt-4 text-slate-400">
                {{
                    status === 404
                        ? 'This page could not be found.'
                        : 'We could not complete that request.'
                }}
            </p>
            <Link
                href="/"
                class="mt-8 inline-block rounded-lg bg-indigo-600 px-5 py-3 text-sm font-medium"
                >Return to workspace</Link
            >
        </div>
    </main>
</template>
