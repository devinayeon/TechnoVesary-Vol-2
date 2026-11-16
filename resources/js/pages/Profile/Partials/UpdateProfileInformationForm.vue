<script setup lang="ts">
import { Link, useForm, usePage } from '@inertiajs/vue3';
import type { User } from '@/types/auth';

defineProps<{
  mustVerifyEmail?: boolean;
  status?: string;
}>();

const user = usePage().props.auth.user as User;

const form = useForm({
  name: user.name,
  email: user.email,
});
</script>

<template>
  <section>
    <header>
      <h2 class="text-lg font-bold text-slate-900 dark:text-white">
        Informasi Profil
      </h2>
      <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
        Perbarui nama tampilan dan alamat email yang terhubung dengan akun Bank Sampah Anda.
      </p>
    </header>

    <form @submit.prevent="form.patch('/profile')" class="mt-6 space-y-5">
      <!-- Name -->
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
          class="mt-1.5 block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-white transition"
          :class="{ 'border-rose-500 focus:border-rose-500 focus:ring-rose-500/20': form.errors.name }"
        />
        <p v-if="form.errors.name" class="mt-1.5 text-xs text-rose-600 dark:text-rose-400">
          {{ form.errors.name }}
        </p>
      </div>

      <!-- Email -->
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
          class="mt-1.5 block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-white transition"
          :class="{ 'border-rose-500 focus:border-rose-500 focus:ring-rose-500/20': form.errors.email }"
        />
        <p v-if="form.errors.email" class="mt-1.5 text-xs text-rose-600 dark:text-rose-400">
          {{ form.errors.email }}
        </p>
      </div>

      <!-- Verification Notice If Email Unverified -->
      <div v-if="mustVerifyEmail && user.email_verified_at === null" class="rounded-xl border border-amber-200 bg-amber-50 p-4 dark:border-amber-900/50 dark:bg-amber-950/40">
        <p class="text-xs text-amber-800 dark:text-amber-300">
          Alamat email Anda belum diverifikasi.
          <Link
            href="/email/verification-notification"
            method="post"
            as="button"
            class="font-bold underline hover:text-amber-900 dark:hover:text-amber-200 ml-1"
          >
            Klik di sini untuk mengirim ulang tautan verifikasi.
          </Link>
        </p>

        <p
          v-show="status === 'verification-link-sent'"
          class="mt-2 text-xs font-semibold text-emerald-700 dark:text-emerald-400"
        >
          Tautan verifikasi baru telah dikirimkan ke email Anda.
        </p>
      </div>

      <!-- Action & Feedback -->
      <div class="flex items-center gap-4 pt-2">
        <button
          type="submit"
          :disabled="form.processing"
          class="cursor-pointer rounded-xl bg-emerald-600 px-5 py-2.5 text-xs font-bold text-white shadow-md shadow-emerald-600/20 hover:bg-emerald-500 active:scale-98 disabled:opacity-60 transition"
        >
          Simpan Perubahan
        </button>

        <Transition
          enter-active-class="transition ease-in-out"
          enter-from-class="opacity-0"
          leave-active-class="transition ease-in-out"
          leave-to-class="opacity-0"
        >
          <p v-if="form.recentlySuccessful" class="text-xs font-semibold text-emerald-600 dark:text-emerald-400">
            Tersimpan.
          </p>
        </Transition>
      </div>
    </form>
  </section>
</template>
