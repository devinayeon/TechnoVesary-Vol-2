<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
</script>

<template>
  <div class="min-h-screen flex flex-col justify-center items-center bg-slate-50 px-4 py-12 sm:px-6 lg:px-8 dark:bg-slate-950 font-sans text-slate-900 dark:text-slate-100">
    <div class="w-full max-w-md">
      <!-- Header / Logo -->
      <div class="text-center mb-8">
        <Link href="/" class="inline-flex items-center gap-3 group focus:outline-none">
          <div class="h-12 w-12 rounded-2xl bg-emerald-600 flex items-center justify-center text-white shadow-lg shadow-emerald-600/20 group-hover:bg-emerald-500 transition-colors">
            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
          </div>
          <div class="text-left">
            <span class="block text-xl font-extrabold tracking-tight text-slate-900 dark:text-white">Bank Sampah</span>
            <span class="block text-xs font-semibold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Digital Ecosystem</span>
          </div>
        </Link>
      </div>

      <!-- Card Container -->
      <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 sm:p-8 shadow-sm">
        <slot />
      </div>

      <!-- Footer Info -->
      <p class="text-center text-xs text-slate-500 dark:text-slate-400 mt-6">
        &copy; {{ new Date().getFullYear() }} Bank Sampah Digital. Bersama wujudkan lingkungan bersih & bernilai ekonomis.
      </p>
    </div>
  </div>
</template>
