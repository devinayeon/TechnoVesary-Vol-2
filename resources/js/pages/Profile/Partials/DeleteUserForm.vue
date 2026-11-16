<script setup lang="ts">
import { nextTick, ref } from 'vue';
import { useForm } from '@inertiajs/vue3';

const confirmingUserDeletion = ref(false);
const passwordInput = ref<HTMLInputElement | null>(null);

const form = useForm({
  password: '',
});

const confirmUserDeletion = () => {
  confirmingUserDeletion.value = true;
  nextTick(() => passwordInput.value?.focus());
};

const deleteUser = () => {
  form.delete('/profile', {
    preserveScroll: true,
    onSuccess: () => closeModal(),
    onError: () => passwordInput.value?.focus(),
    onFinish: () => form.reset(),
  });
};

const closeModal = () => {
  confirmingUserDeletion.value = false;
  form.clearErrors();
  form.reset();
};
</script>

<template>
  <section class="space-y-6">
    <header>
      <h2 class="text-lg font-bold text-rose-600 dark:text-rose-400">
        Hapus Akun Pengguna
      </h2>
      <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
        Setelah akun Anda dihapus, semua riwayat setoran sampah, tabungan saldo, dan data terkait akan dihapus secara permanen.
      </p>
    </header>

    <div>
      <button
        @click="confirmUserDeletion"
        class="cursor-pointer rounded-xl bg-rose-600 px-5 py-2.5 text-xs font-bold text-white shadow-md shadow-rose-600/20 hover:bg-rose-500 active:scale-98 transition"
      >
        Hapus Akun Saya
      </button>
    </div>

    <!-- Confirmation Modal Dialog -->
    <div
      v-if="confirmingUserDeletion"
      class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4"
    >
      <div class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-xl dark:border-slate-800 dark:bg-slate-900">
        <h3 class="text-lg font-bold text-slate-900 dark:text-white">
          Apakah Anda yakin ingin menghapus akun?
        </h3>
        <p class="mt-2 text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
          Tindakan ini tidak dapat dibatalkan. Masukkan kata sandi akun Anda untuk mengonfirmasi penghapusan secara permanen.
        </p>

        <form @submit.prevent="deleteUser" class="mt-5">
          <div>
            <label for="password_delete" class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300">
              Kata Sandi Konfirmasi
            </label>
            <input
              id="password_delete"
              ref="passwordInput"
              type="password"
              v-model="form.password"
              placeholder="••••••••"
              class="mt-1.5 block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-white transition"
              :class="{ 'border-rose-500': form.errors.password }"
            />
            <p v-if="form.errors.password" class="mt-1.5 text-xs text-rose-600 dark:text-rose-400">
              {{ form.errors.password }}
            </p>
          </div>

          <div class="mt-6 flex justify-end gap-3">
            <button
              type="button"
              @click="closeModal"
              class="cursor-pointer rounded-xl border border-slate-300 bg-white px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-100 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700 transition"
            >
              Batal
            </button>
            <button
              type="submit"
              :disabled="form.processing"
              class="cursor-pointer rounded-xl bg-rose-600 px-4 py-2 text-xs font-bold text-white shadow-md shadow-rose-600/20 hover:bg-rose-500 active:scale-98 disabled:opacity-60 transition"
            >
              Hapus Permanen
            </button>
          </div>
        </form>
      </div>
    </div>
  </section>
</template>
