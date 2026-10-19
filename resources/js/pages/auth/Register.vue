<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import AuthLayout from '@/components/AuthLayout.vue';
const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});
function submit() {
    form.post('/register', {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
}
</script>

<template>
    <AuthLayout
        title="Create your account"
        description="A private workspace for your files, code, and AI conversations."
    >
        <form class="space-y-4" @submit.prevent="submit">
            <div>
                <label for="name" class="mb-2 block text-sm">Name</label>
                <input
                    id="name"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
                    maxlength="100"
                    class="auth-input"
                />
                <p
                    v-if="form.errors.name"
                    class="mt-2 text-sm text-rose-400"
                    role="alert"
                >
                    {{ form.errors.name }}
                </p>
            </div>
            <div>
                <label for="email" class="mb-2 block text-sm">Email</label>
                <input
                    id="email"
                    v-model="form.email"
                    type="email"
                    required
                    autocomplete="username"
                    class="auth-input"
                    @input="form.email = form.email.toLowerCase()"
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
                    >Password
                    <span class="text-xs text-slate-400"
                        >(at least 8 characters)</span
                    ></label
                >
                <input
                    id="password"
                    v-model="form.password"
                    type="password"
                    required
                    minlength="8"
                    autocomplete="new-password"
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
            <div>
                <label for="confirmation" class="mb-2 block text-sm"
                    >Confirm password</label
                >
                <input
                    id="confirmation"
                    v-model="form.password_confirmation"
                    type="password"
                    required
                    autocomplete="new-password"
                    class="auth-input"
                />
            </div>
            <button :disabled="form.processing" class="auth-submit">
                {{ form.processing ? 'Creating account…' : 'Create account' }}
            </button>
            <p class="text-center text-sm text-slate-400">
                Already have an account?
                <Link href="/login" class="text-indigo-300 hover:underline"
                    >Sign in</Link
                >
            </p>
        </form>
    </AuthLayout>
</template>
