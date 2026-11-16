<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { computed } from 'vue';
import type { User } from '@/types/auth';

const props = defineProps<{
  auth: {
    user: User;
  };
}>();

const userName = computed(() => props.auth?.user?.name ?? 'Admin');

const stats = [
  { label: 'Total Nasabah Terdaftar', value: '1,420 Jiwa', change: '+24 minggu ini', color: 'text-emerald-600 dark:text-emerald-400' },
  { label: 'Total Saldo Kas Beredar', value: 'Rp 64.250.000', change: 'Likuiditas aman', color: 'text-indigo-600 dark:text-indigo-400' },
  { label: 'Total Tonase Sampah', value: '18.4 Ton', change: '8.2 Ton plastik & kardus', color: 'text-teal-600 dark:text-teal-400' },
  { label: 'Antrean Tarik Saldo', value: '5 Pengajuan', change: 'Menunggu persetujuan admin', color: 'text-amber-600 dark:text-amber-400' },
];

const handleLogout = () => {
  router.post('/logout');
};
</script>

<template>
  <Head title="Admin Dashboard - Bank Sampah Digital" />

  <div class="min-h-screen bg-slate-50 text-slate-800 dark:bg-slate-950 dark:text-slate-100 font-sans">
    <!-- Navbar -->
    <header class="sticky top-0 z-40 border-b border-slate-200 bg-white/80 backdrop-blur-md dark:border-slate-800 dark:bg-slate-900/80">
      <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
        <div class="flex items-center gap-3">
          <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-900 text-white dark:bg-emerald-600 shadow-sm">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
            </svg>
          </div>
          <div>
            <h1 class="text-base font-bold tracking-tight text-slate-900 dark:text-white">Bank Sampah Digital</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400">Pusat Kendali Administrator</p>
          </div>
        </div>

        <div class="flex items-center gap-3">
          <div class="text-right hidden sm:block">
            <p class="text-sm font-semibold text-slate-800 dark:text-slate-200">{{ userName }}</p>
            <span class="inline-flex items-center rounded-full bg-slate-200 px-2 py-0.5 text-xs font-semibold text-slate-800 dark:bg-slate-800 dark:text-slate-200">
              Super Admin
            </span>
          </div>

          <Link
            href="/profile"
            class="rounded-lg border border-slate-300 bg-white px-3.5 py-1.5 text-xs font-semibold text-slate-700 shadow-xs hover:bg-slate-100 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700 transition"
          >
            Profil
          </Link>

          <button
            @click="handleLogout"
            class="cursor-pointer rounded-lg border border-slate-300 bg-white px-3.5 py-1.5 text-xs font-semibold text-slate-700 shadow-xs hover:bg-slate-100 active:bg-slate-200 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700 transition"
          >
            Keluar
          </button>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
      <section class="mb-8 rounded-2xl bg-gradient-to-r from-slate-900 via-slate-800 to-emerald-900 p-6 sm:p-8 text-white shadow-lg">
        <h2 class="text-2xl font-bold tracking-tight">Executive Overview 📊</h2>
        <p class="mt-2 max-w-2xl text-slate-300 text-sm leading-relaxed">
          Monitoring peredaran saldo tabungan nasabah, operasional armada logistik penjemputan sampah, dan status inventori gudang daur ulang.
        </p>
      </section>

      <!-- Stats Grid -->
      <section class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <div
          v-for="(item, index) in stats"
          :key="index"
          class="rounded-xl border border-slate-200 bg-white p-6 shadow-xs dark:border-slate-800 dark:bg-slate-900"
        >
          <dt class="truncate text-xs font-semibold text-slate-500 uppercase tracking-wider dark:text-slate-400">
            {{ item.label }}
          </dt>
          <dd class="mt-2 text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">
            {{ item.value }}
          </dd>
          <dd class="mt-2 text-xs font-medium" :class="item.color">
            {{ item.change }}
          </dd>
        </div>
      </section>
    </main>
  </div>
</template>
