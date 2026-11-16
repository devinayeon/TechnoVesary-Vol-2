<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import GuestLayout from '@/layouts/GuestLayout.vue';

defineProps<{
  canResetPassword?: boolean;
  status?: string;
}>();

const form = useForm({
  email: '',
  password: '',
  remember: false,
});

const submit = () => {
  form.post('/login', {
    onFinish: () => {
      form.reset('password');
    },
  });
};
</script>

<template>
  <GuestLayout>
    <Head title="Masuk - Bank Sampah Digital" />

    <div class="mb-6">
      <h2 class="text-xl font-bold tracking-tight text-slate-900 dark:text-white">Selamat Datang Kembali</h2>
      <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
        Masuk ke akun Anda untuk mengelola setoran atau layanan sampah.
      </p>
    </div>

    <!-- Status Alert -->
    <div v-if="status" class="mb-4 rounded-xl bg-emerald-50 p-4 text-sm font-medium text-emerald-800 dark:bg-emerald-950/50 dark:text-emerald-300">
      {{ status }}
    </div>

    <form @submit.prevent="submit" class="space-y-4">
      <!-- Email Field -->
      <div>
        <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300">
          Alamat Email
        </label>
        <input
          id="email"
          type="email"
          v-model="form.email"
          required
          autofocus
          autocomplete="username"
          placeholder="nama@email.com"
          class="mt-1.5 block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:placeholder:text-slate-500 transition"
          :class="{ 'border-rose-500 focus:border-rose-500 focus:ring-rose-500/20': form.errors.email }"
        />
        <p v-if="form.errors.email" class="mt-1.5 text-xs text-rose-600 dark:text-rose-400">
          {{ form.errors.email }}
        </p>
      </div>

      <!-- Password Field -->
      <div>
        <div class="flex items-center justify-between">
          <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300">
            Kata Sandi
          </label>
          <Link
            href="/forgot-password"
            class="text-xs font-semibold text-emerald-600 hover:text-emerald-500 dark:text-emerald-400"
          >
            Lupa kata sandi?
          </Link>
        </div>
        <input
          id="password"
          type="password"
          v-model="form.password"
          required
          autocomplete="current-password"
          placeholder="••••••••"
          class="mt-1.5 block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:placeholder:text-slate-500 transition"
          :class="{ 'border-rose-500 focus:border-rose-500 focus:ring-rose-500/20': form.errors.password }"
        />
        <p v-if="form.errors.password" class="mt-1.5 text-xs text-rose-600 dark:text-rose-400">
          {{ form.errors.password }}
        </p>
      </div>

      <!-- Remember Me -->
      <div class="flex items-center justify-between pt-1">
        <label class="flex items-center gap-2 cursor-pointer">
          <input
            type="checkbox"
            v-model="form.remember"
            class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500 dark:border-slate-700 dark:bg-slate-800"
          />
          <span class="text-xs text-slate-600 dark:text-slate-400">Ingat saya</span>
        </label>
      </div>

      <!-- Submit Button -->
      <div class="pt-2">
        <button
          type="submit"
          :disabled="form.processing"
          class="w-full cursor-pointer flex justify-center items-center rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-bold text-white shadow-md shadow-emerald-600/20 hover:bg-emerald-500 active:scale-98 disabled:opacity-60 transition"
        >
          <span v-if="form.processing">Memproses...</span>
          <span v-else>Masuk ke Akun</span>
        </button>
      </div>
    </form>

    <div class="mt-6 border-t border-slate-200 dark:border-slate-800 pt-5 text-center text-xs text-slate-600 dark:text-slate-400">
      Belum memiliki akun nasabah?
      <Link href="/register" class="font-bold text-emerald-600 hover:text-emerald-500 dark:text-emerald-400 ml-1">
        Daftar Sekarang
      </Link>
    </div>
  </GuestLayout>
</template>
