<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import GuestLayout from '@/layouts/GuestLayout.vue';

const props = defineProps<{
  status?: string;
}>();

const form = useForm({});

const submit = () => {
  form.post('/email/verification-notification');
};

const verificationLinkSent = computed(
  () => props.status === 'verification-link-sent',
);
</script>

<template>
  <GuestLayout>
    <Head title="Verifikasi Email - Bank Sampah Digital" />

    <div class="mb-6">
      <div class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300 mb-3">
        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
        </svg>
      </div>
      <h2 class="text-xl font-bold tracking-tight text-slate-900 dark:text-white">Verifikasi Alamat Email</h2>
      <p class="text-sm text-slate-600 dark:text-slate-400 mt-2 leading-relaxed">
        Terima kasih telah bergabung di Bank Sampah Digital! Sebelum memulai transaksi atau penjemputan sampah, silakan verifikasi alamat email Anda melalui tautan yang baru saja kami kirimkan.
      </p>
    </div>

    <!-- Feedback alert jika link baru saja dikirim ulang -->
    <div
      v-if="verificationLinkSent"
      class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-xs font-semibold text-emerald-800 dark:border-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300"
    >
      Tautan verifikasi baru telah berhasil dikirim ke alamat email yang Anda daftarkan. Silakan periksa kotak masuk atau folder spam Anda.
    </div>

    <form @submit.prevent="submit" class="space-y-4">
      <div>
        <button
          type="submit"
          :disabled="form.processing"
          class="w-full cursor-pointer flex justify-center items-center rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-bold text-white shadow-md shadow-emerald-600/20 hover:bg-emerald-500 active:scale-98 disabled:opacity-60 transition"
        >
          <span v-if="form.processing">Mengirim Ulang...</span>
          <span v-else>Kirim Ulang Email Verifikasi</span>
        </button>
      </div>

      <div class="flex items-center justify-between pt-2">
        <Link
          href="/logout"
          method="post"
          as="button"
          class="text-xs font-medium text-slate-500 underline hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200 transition"
        >
          Keluar dari Sesi
        </Link>
      </div>
    </form>
  </GuestLayout>
</template>
