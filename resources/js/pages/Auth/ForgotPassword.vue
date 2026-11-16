<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import GuestLayout from '@/layouts/GuestLayout.vue';

defineProps<{
  status?: string;
}>();

const form = useForm({
  email: '',
});

const submit = () => {
  form.post('/forgot-password');
};
</script>

<template>
  <GuestLayout>
    <Head title="Lupa Kata Sandi - Bank Sampah Digital" />

    <div class="mb-6">
      <h2 class="text-xl font-bold tracking-tight text-slate-900 dark:text-white">Lupa Kata Sandi?</h2>
      <p class="text-sm text-slate-600 dark:text-slate-400 mt-2 leading-relaxed">
        Masukkan alamat email yang terdaftar pada akun Anda. Kami akan mengirimkan tautan reset kata sandi agar Anda dapat mengatur ulang kata sandi dengan aman.
      </p>
    </div>

    <!-- Feedback Status -->
    <div
      v-if="status"
      class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-xs font-semibold text-emerald-800 dark:border-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300"
    >
      {{ status }}
    </div>

    <form @submit.prevent="submit" class="space-y-4">
      <div>
        <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300">
          Alamat Email Akun
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

      <div class="pt-2">
        <button
          type="submit"
          :disabled="form.processing"
          class="w-full cursor-pointer flex justify-center items-center rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-bold text-white shadow-md shadow-emerald-600/20 hover:bg-emerald-500 active:scale-98 disabled:opacity-60 transition"
        >
          <span v-if="form.processing">Mengirim Tautan...</span>
          <span v-else>Kirim Tautan Reset Sandi</span>
        </button>
      </div>
    </form>

    <div class="mt-6 border-t border-slate-200 dark:border-slate-800 pt-5 text-center text-xs text-slate-600 dark:text-slate-400">
      Ingat kata sandi Anda?
      <Link href="/login" class="font-bold text-emerald-600 hover:text-emerald-500 dark:text-emerald-400 ml-1">
        Kembali ke Login
      </Link>
    </div>
  </GuestLayout>
</template>
