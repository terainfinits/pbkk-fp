<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({ name: '', email: '', password: '', password_confirmation: '' });

function submit() {
  form.post('/register', { onFinish: () => form.reset('password', 'password_confirmation') });
}
</script>

<template>
  <Head title="Register" />
  <main class="min-h-screen bg-[#090d16] text-slate-100 flex items-center justify-center p-5">
    <div class="w-full max-w-4xl overflow-hidden rounded-2xl border border-slate-800 bg-[#0e1424] shadow-2xl md:grid md:grid-cols-2">
      <section class="bg-gradient-to-br from-indigo-950 via-[#131c38] to-[#0e1424] p-8 md:p-12 flex flex-col justify-between">
        <div class="flex items-center gap-3">
          <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-600 text-xl font-bold">M</div>
          <span class="font-bold tracking-widest">MAGENTIC <span class="text-indigo-300">IDE</span></span>
        </div>
        <div class="mt-16 md:mt-24">
          <p class="mb-3 text-sm font-semibold uppercase tracking-widest text-indigo-300">Agentic AI Code Studio</p>
          <h1 class="text-3xl font-bold leading-tight">Mulai dengan Magentic IDE.</h1>
          <p class="mt-4 text-sm leading-6 text-slate-400">Buat akun untuk masuk ke workspace dan menyimpan riwayat percakapan Anda.</p>
        </div>
      </section>

      <section class="p-8 md:p-12">
        <h2 class="text-2xl font-bold">Register</h2>
        <p class="mt-2 text-sm text-slate-400">Isi data berikut untuk membuat akun.</p>

        <form class="mt-8 space-y-4" @submit.prevent="submit">
          <div>
            <label for="name" class="mb-2 block text-sm font-medium">Nama</label>
            <input id="name" v-model="form.name" type="text" autocomplete="name" required class="w-full rounded-lg border border-slate-700 bg-slate-900 px-3 py-2.5 text-white outline-none focus:border-indigo-400" />
            <p v-if="form.errors.name" class="mt-1 text-sm text-red-400">{{ form.errors.name }}</p>
          </div>
          <div>
            <label for="email" class="mb-2 block text-sm font-medium">Email</label>
            <input id="email" v-model="form.email" type="email" autocomplete="email" required class="w-full rounded-lg border border-slate-700 bg-slate-900 px-3 py-2.5 text-white outline-none focus:border-indigo-400" />
            <p v-if="form.errors.email" class="mt-1 text-sm text-red-400">{{ form.errors.email }}</p>
          </div>
          <div>
            <label for="password" class="mb-2 block text-sm font-medium">Password</label>
            <input id="password" v-model="form.password" type="password" autocomplete="new-password" required minlength="8" class="w-full rounded-lg border border-slate-700 bg-slate-900 px-3 py-2.5 text-white outline-none focus:border-indigo-400" />
            <p v-if="form.errors.password" class="mt-1 text-sm text-red-400">{{ form.errors.password }}</p>
          </div>
          <div>
            <label for="password_confirmation" class="mb-2 block text-sm font-medium">Konfirmasi password</label>
            <input id="password_confirmation" v-model="form.password_confirmation" type="password" autocomplete="new-password" required class="w-full rounded-lg border border-slate-700 bg-slate-900 px-3 py-2.5 text-white outline-none focus:border-indigo-400" />
          </div>
          <button type="submit" :disabled="form.processing" class="w-full rounded-lg bg-indigo-600 px-4 py-2.5 font-semibold hover:bg-indigo-500 disabled:cursor-wait disabled:opacity-60">{{ form.processing ? 'Memproses...' : 'Buat akun' }}</button>
        </form>

        <p class="mt-6 text-center text-sm text-slate-400">Sudah punya akun? <Link href="/login" class="font-semibold text-indigo-300 hover:text-indigo-200">Login</Link></p>
      </section>
    </div>
  </main>
</template>
