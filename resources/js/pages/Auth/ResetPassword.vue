<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import GuestLayout from '@/layouts/GuestLayout.vue';

const props = defineProps<{
  email: string;
  token: string;
}>();

const form = useForm({
  token: props.token,
  email: props.email,
  password: '',
  password_confirmation: '',
});

const submit = () => {
  form.post('/reset-password', {
    onFinish: () => {
      form.reset('password', 'password_confirmation');
    },
  });
};
</script>

<template>
  <GuestLayout>
    <Head title="Reset Kata Sandi - Bank Sampah Digital" />

    <div class="mb-6">
      <h2 class="text-xl font-bold tracking-tight text-slate-900 dark:text-white">Atur Ulang Kata Sandi</h2>
      <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">
        Buat kata sandi baru yang kuat dan unik untuk mengamankan akun Anda.
      </p>
    </div>

    <form @submit.prevent="submit" class="space-y-4">
      <!-- Email Field (Readonly) -->
      <div>
        <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300">
          Alamat Email Akun
        </label>
        <input
          id="email"
          type="email"
          v-model="form.email"
          readonly
          tabindex="-1"
          class="mt-1.5 block w-full rounded-xl border border-slate-200 bg-slate-100 px-3.5 py-2.5 text-sm text-slate-500 cursor-not-allowed select-none focus:outline-none dark:border-slate-800 dark:bg-slate-800/60 dark:text-slate-400 transition"
          :class="{ 'border-rose-500': form.errors.email }"
        />
        <p v-if="form.errors.email" class="mt-1.5 text-xs text-rose-600 dark:text-rose-400">
          {{ form.errors.email }}
        </p>
      </div>

      <!-- New Password -->
      <div>
        <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300">
          Kata Sandi Baru
        </label>
        <input
          id="password"
          type="password"
          v-model="form.password"
          required
          autofocus
          autocomplete="new-password"
          placeholder="Minimal 8 karakter"
          class="mt-1.5 block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-white transition"
          :class="{ 'border-rose-500 focus:border-rose-500 focus:ring-rose-500/20': form.errors.password }"
        />
        <p v-if="form.errors.password" class="mt-1.5 text-xs text-rose-600 dark:text-rose-400">
          {{ form.errors.password }}
        </p>
      </div>

      <!-- Confirm Password -->
      <div>
        <label for="password_confirmation" class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300">
          Konfirmasi Kata Sandi Baru
        </label>
        <input
          id="password_confirmation"
          type="password"
          v-model="form.password_confirmation"
          required
          autocomplete="new-password"
          placeholder="Ulangi kata sandi baru"
          class="mt-1.5 block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-white transition"
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
          <span v-if="form.processing">Menyimpan Sandi Baru...</span>
          <span v-else>Simpan Kata Sandi Baru</span>
        </button>
      </div>
    </form>
  </GuestLayout>
</template>
