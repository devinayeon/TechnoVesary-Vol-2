<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import GuestLayout from '@/layouts/GuestLayout.vue';

const form = useForm({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
});

const submit = () => {
  form.post('/register', {
    onFinish: () => {
      form.reset('password', 'password_confirmation');
    },
  });
};
</script>

<template>
  <GuestLayout>
    <Head title="Pendaftaran Nasabah - Bank Sampah Digital" />

    <div class="mb-6">
      <h2 class="text-xl font-bold tracking-tight text-slate-900 dark:text-white">Daftar Nasabah Baru</h2>
      <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
        Buka rekening bank sampah dan mulai ubah sampah rumah tangga menjadi saldo bernilai.
      </p>
    </div>

    <form @submit.prevent="submit" class="space-y-4">
      <!-- Full Name -->
      <div>
        <label for="name" class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300">
          Nama Lengkap
        </label>
        <input
          id="name"
          type="text"
          v-model="form.name"
          required
          autofocus
          autocomplete="name"
          placeholder="Contoh: Siti Aminah"
          class="mt-1.5 block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:placeholder:text-slate-500 transition"
          :class="{ 'border-rose-500 focus:border-rose-500 focus:ring-rose-500/20': form.errors.name }"
        />
        <p v-if="form.errors.name" class="mt-1.5 text-xs text-rose-600 dark:text-rose-400">
          {{ form.errors.name }}
        </p>
      </div>

      <!-- Email Address -->
      <div>
        <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300">
          Alamat Email
        </label>
        <input
          id="email"
          type="email"
          v-model="form.email"
          required
          autocomplete="username"
          placeholder="nama@email.com"
          class="mt-1.5 block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:placeholder:text-slate-500 transition"
          :class="{ 'border-rose-500 focus:border-rose-500 focus:ring-rose-500/20': form.errors.email }"
        />
        <p v-if="form.errors.email" class="mt-1.5 text-xs text-rose-600 dark:text-rose-400">
          {{ form.errors.email }}
        </p>
      </div>

      <!-- Password -->
      <div>
        <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300">
          Kata Sandi
        </label>
        <input
          id="password"
          type="password"
          v-model="form.password"
          required
          autocomplete="new-password"
          placeholder="Minimal 8 karakter"
          class="mt-1.5 block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:placeholder:text-slate-500 transition"
          :class="{ 'border-rose-500 focus:border-rose-500 focus:ring-rose-500/20': form.errors.password }"
        />
        <p v-if="form.errors.password" class="mt-1.5 text-xs text-rose-600 dark:text-rose-400">
          {{ form.errors.password }}
        </p>
      </div>

      <!-- Confirm Password -->
      <div>
        <label for="password_confirmation" class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300">
          Konfirmasi Kata Sandi
        </label>
        <input
          id="password_confirmation"
          type="password"
          v-model="form.password_confirmation"
          required
          autocomplete="new-password"
          placeholder="Ulangi kata sandi"
          class="mt-1.5 block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:placeholder:text-slate-500 transition"
          :class="{ 'border-rose-500 focus:border-rose-500 focus:ring-rose-500/20': form.errors.password_confirmation }"
        />
        <p v-if="form.errors.password_confirmation" class="mt-1.5 text-xs text-rose-600 dark:text-rose-400">
          {{ form.errors.password_confirmation }}
        </p>
      </div>

      <!-- Submit Button -->
      <div class="pt-2">
        <button
          type="submit"
          :disabled="form.processing"
          class="w-full cursor-pointer flex justify-center items-center rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-bold text-white shadow-md shadow-emerald-600/20 hover:bg-emerald-500 active:scale-98 disabled:opacity-60 transition"
        >
          <span v-if="form.processing">Mendaftarkan Akun...</span>
          <span v-else>Daftar Rekening Nasabah</span>
        </button>
      </div>
    </form>

    <div class="mt-6 border-t border-slate-200 dark:border-slate-800 pt-5 text-center text-xs text-slate-600 dark:text-slate-400">
      Sudah memiliki akun?
      <Link href="/login" class="font-bold text-emerald-600 hover:text-emerald-500 dark:text-emerald-400 ml-1">
        Masuk di Sini
      </Link>
    </div>
  </GuestLayout>
</template>
