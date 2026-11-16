<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { computed } from 'vue';
import type { User } from '@/types/auth';

const props = defineProps<{
  auth: {
    user: User;
  };
}>();

const userName = computed(() => props.auth?.user?.name ?? 'Nasabah');

const stats = [
  { label: 'Saldo Tabungan Sampah', value: 'Rp 450.000', change: '+Rp 75.000 bulan ini', color: 'text-emerald-600 dark:text-emerald-400' },
  { label: 'Total Sampah Disetor', value: '84.5 Kg', change: '+12.3 Kg bulan ini', color: 'text-teal-600 dark:text-teal-400' },
  { label: 'Poin Eco-Reward', value: '1,250 Poin', change: 'Dapat ditukar voucher sembako', color: 'text-amber-600 dark:text-amber-400' },
];

const handleLogout = () => {
  router.post('/logout');
};
</script>

<template>
  <Head title="Customer Dashboard - Bank Sampah Digital" />

  <div class="min-h-screen bg-slate-50 text-slate-800 dark:bg-slate-950 dark:text-slate-100 font-sans">
    <!-- Navbar -->
    <header class="sticky top-0 z-40 border-b border-slate-200 bg-white/80 backdrop-blur-md dark:border-slate-800 dark:bg-slate-900/80">
      <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
        <div class="flex items-center gap-3">
          <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-600 text-white shadow-sm shadow-emerald-500/20">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
          </div>
          <div>
            <h1 class="text-base font-bold tracking-tight text-slate-900 dark:text-white">Bank Sampah Digital</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400">Portal Nasabah</p>
          </div>
        </div>

        <div class="flex items-center gap-3">
          <div class="text-right hidden sm:block">
            <p class="text-sm font-semibold text-slate-800 dark:text-slate-200">{{ userName }}</p>
            <span class="inline-flex items-center rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">
              Nasabah Aktif
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
      <!-- Greeting Banner -->
      <section class="mb-8 rounded-2xl bg-gradient-to-r from-emerald-700 via-emerald-600 to-teal-600 p-6 sm:p-8 text-white shadow-lg shadow-emerald-950/10">
        <h2 class="text-2xl font-bold tracking-tight">Halo, {{ userName }}! 🌱</h2>
        <p class="mt-2 max-w-2xl text-emerald-100 text-sm leading-relaxed">
          Terima kasih telah berkontribusi menjaga kelestarian lingkungan. Jadwalkan penjemputan sampah anorganik Anda dan pantau pertambahan saldo secara berkala.
        </p>
        <div class="mt-6 flex flex-wrap gap-3">
          <button class="cursor-pointer inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-bold text-emerald-800 shadow-md hover:bg-emerald-50 active:scale-98 transition">
            <svg class="h-5 w-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Request Jemput Sampah
          </button>
          <button class="cursor-pointer inline-flex items-center gap-2 rounded-xl border border-emerald-300/40 bg-emerald-800/40 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-800/60 transition">
            Tarik Tunai Saldo
          </button>
        </div>
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
