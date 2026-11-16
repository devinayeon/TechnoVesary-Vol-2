<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { computed } from 'vue';
import type { User } from '@/types/auth';

const props = defineProps<{
  auth: {
    user: User;
  };
}>();

const userName = computed(() => props.auth?.user?.name ?? 'Driver');

const stats = [
  { label: 'Tugas Jemput Hari Ini', value: '8 Lokasi', change: '3 lokasi selesai', color: 'text-sky-600 dark:text-sky-400' },
  { label: 'Total Muatan Masuk', value: '340 Kg', change: 'Kapasitas armada 500 Kg', color: 'text-emerald-600 dark:text-emerald-400' },
  { label: 'Status Armada', value: 'Aktif (Truk 02)', change: 'Rute Wilayah Barat', color: 'text-indigo-600 dark:text-indigo-400' },
];

const handleLogout = () => {
  router.post('/logout');
};
</script>

<template>
  <Head title="Driver Dashboard - Bank Sampah Digital" />

  <div class="min-h-screen bg-slate-50 text-slate-800 dark:bg-slate-950 dark:text-slate-100 font-sans">
    <!-- Navbar -->
    <header class="sticky top-0 z-40 border-b border-slate-200 bg-white/80 backdrop-blur-md dark:border-slate-800 dark:bg-slate-900/80">
      <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
        <div class="flex items-center gap-3">
          <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-sky-600 text-white shadow-sm shadow-sky-500/20">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
            </svg>
          </div>
          <div>
            <h1 class="text-base font-bold tracking-tight text-slate-900 dark:text-white">Bank Sampah Digital</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400">Portal Driver Armada</p>
          </div>
        </div>

        <div class="flex items-center gap-3">
          <div class="text-right hidden sm:block">
            <p class="text-sm font-semibold text-slate-800 dark:text-slate-200">{{ userName }}</p>
            <span class="inline-flex items-center rounded-full bg-sky-100 px-2 py-0.5 text-xs font-medium text-sky-800 dark:bg-sky-950/60 dark:text-sky-300">
              Driver Lapangan
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
      <section class="mb-8 rounded-2xl bg-gradient-to-r from-sky-700 via-sky-600 to-teal-600 p-6 sm:p-8 text-white shadow-lg shadow-sky-950/10">
        <h2 class="text-2xl font-bold tracking-tight">Halo, Pak {{ userName }}! 🚛</h2>
        <p class="mt-2 max-w-2xl text-sky-100 text-sm leading-relaxed">
          Berikut adalah jadwal dan rute penjemputan sampah anorganik nasabah hari ini. Pastikan timbangan fisik sesuai saat input konfirmasi penimbangan.
        </p>
      </section>

      <!-- Stats Grid -->
      <section class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
        <div
          v-for="(item, index) in stats"
          :key="index"
          class="rounded-xl border border-slate-200 bg-white p-6 shadow-xs dark:border-slate-800 dark:bg-slate-900"
        >
          <dt class="truncate text-xs font-semibold text-slate-500 uppercase tracking-wider dark:text-slate-400">
            {{ item.label }}
          </dt>
          <dd class="mt-2 text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">
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
