<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import AuthLayout from '@/components/AuthLayout.vue';
const form = useForm({ email: '', password: '', remember: false });
function submit() {
    form.post('/login', { onFinish: () => form.reset('password') });
}
</script>

<template>
    <AuthLayout
        title="Welcome back"
        description="Sign in to continue in your private coding workspace."
    >
        <form class="space-y-5" @submit.prevent="submit">
            <div>
                <label for="email" class="mb-2 block text-sm">Email</label>
                <input
                    id="email"
                    v-model="form.email"
                    type="email"
                    required
                    autofocus
                    autocomplete="username"
                    class="auth-input"
                    :aria-invalid="!!form.errors.email"
                />
                <p
                    v-if="form.errors.email"
                    class="mt-2 text-sm text-rose-400"
                    role="alert"
                >
                    {{ form.errors.email }}
                </p>
            </div>
            <div>
                <label for="password" class="mb-2 block text-sm"
                    >Password</label
                >
                <input
                    id="password"
                    v-model="form.password"
                    type="password"
                    required
                    autocomplete="current-password"
                    class="auth-input"
                />
                <p
                    v-if="form.errors.password"
                    class="mt-2 text-sm text-rose-400"
                    role="alert"
                >
                    {{ form.errors.password }}
                </p>
            </div>
            <label class="flex items-center gap-2 text-sm text-slate-400"
                ><input v-model="form.remember" type="checkbox" /> Remember
                me</label
            >
            <button :disabled="form.processing" class="auth-submit">
                {{ form.processing ? 'Signing in…' : 'Sign in' }}
            </button>
            <p class="text-center text-sm text-slate-400">
                New to Magentic?
                <Link href="/register" class="text-indigo-300 hover:underline"
                    >Create an account</Link
                >
            </p>
        </form>
    </AuthLayout>
</template>
