<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import type { User } from '@/types/auth';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';

defineProps<{
  mustVerifyEmail?: boolean;
  status?: string;
}>();

const user = usePage().props.auth.user as User;

const dashboardRoute = computed(() => {
  if (user?.role === 'admin') return '/admin/dashboard';
  if (user?.role === 'driver') return '/driver/dashboard';
  return '/customer/dashboard';
});
</script>

<template>
  <Head title="Pengaturan Profil - Bank Sampah Digital" />

  <div class="min-h-screen bg-slate-50 text-slate-800 dark:bg-slate-950 dark:text-slate-100 font-sans">
    <!-- Navbar -->
    <header class="sticky top-0 z-40 border-b border-slate-200 bg-white/80 backdrop-blur-md dark:border-slate-800 dark:bg-slate-900/80">
      <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
        <div class="flex items-center gap-3">
          <Link :href="dashboardRoute" class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-600 text-white shadow-sm shadow-emerald-500/20 hover:bg-emerald-500 transition">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
          </Link>
          <div>
            <h1 class="text-base font-bold tracking-tight text-slate-900 dark:text-white">Pengaturan Akun</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400">Bank Sampah Digital</p>
          </div>
        </div>

        <div class="flex items-center gap-3">
          <Link
            :href="dashboardRoute"
            class="rounded-xl border border-slate-300 bg-white px-3.5 py-1.5 text-xs font-semibold text-slate-700 shadow-xs hover:bg-slate-100 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700 transition"
          >
            Kembali ke Dashboard
          </Link>
        </div>
      </div>
    </header>

    <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8 space-y-6">
      <!-- 1. Profile Information -->
      <div class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 shadow-xs dark:border-slate-800 dark:bg-slate-900">
        <UpdateProfileInformationForm
          :must-verify-email="mustVerifyEmail"
          :status="status"
        />
      </div>

      <!-- 2. Update Password -->
      <div class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 shadow-xs dark:border-slate-800 dark:bg-slate-900">
        <UpdatePasswordForm />
      </div>

      <!-- 3. Delete Account -->
      <div class="rounded-2xl border border-rose-200 bg-rose-50/30 p-6 sm:p-8 shadow-xs dark:border-rose-950 dark:bg-rose-950/20">
        <DeleteUserForm />
      </div>
    </main>
  </div>
</template>
