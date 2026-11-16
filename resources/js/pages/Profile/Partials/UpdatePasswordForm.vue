<script setup lang="ts">
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';

const passwordInput = ref<HTMLInputElement | null>(null);
const currentPasswordInput = ref<HTMLInputElement | null>(null);

const form = useForm({
  current_password: '',
  password: '',
  password_confirmation: '',
});

const updatePassword = () => {
  form.put('/password', {
    preserveScroll: true,
    onSuccess: () => form.reset(),
    onError: () => {
      if (form.errors.password) {
        form.reset('password', 'password_confirmation');
        passwordInput.value?.focus();
      }
      if (form.errors.current_password) {
        form.reset('current_password');
        currentPasswordInput.value?.focus();
      }
    },
  });
};
</script>

<template>
  <section>
    <header>
      <h2 class="text-lg font-bold text-slate-900 dark:text-white">
        Perbarui Kata Sandi
      </h2>
      <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
        Pastikan akun Anda tetap aman dengan menggunakan kata sandi acak dan panjang.
      </p>
    </header>

    <form @submit.prevent="updatePassword" class="mt-6 space-y-5">
      <!-- Current Password -->
      <div>
        <label for="current_password" class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300">
          Kata Sandi Saat Ini
        </label>
        <input
          id="current_password"
          ref="currentPasswordInput"
          type="password"
          v-model="form.current_password"
          autocomplete="current-password"
          placeholder="••••••••"
          class="mt-1.5 block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-white transition"
          :class="{ 'border-rose-500 focus:border-rose-500 focus:ring-rose-500/20': form.errors.current_password }"
        />
        <p v-if="form.errors.current_password" class="mt-1.5 text-xs text-rose-600 dark:text-rose-400">
          {{ form.errors.current_password }}
        </p>
      </div>

      <!-- New Password -->
      <div>
        <label for="new_password" class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300">
          Kata Sandi Baru
        </label>
        <input
          id="new_password"
          ref="passwordInput"
          type="password"
          v-model="form.password"
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
        <label for="new_password_confirmation" class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300">
          Konfirmasi Kata Sandi Baru
        </label>
        <input
          id="new_password_confirmation"
          type="password"
          v-model="form.password_confirmation"
          autocomplete="new-password"
          placeholder="Ulangi kata sandi baru"
          class="mt-1.5 block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-white transition"
          :class="{ 'border-rose-500 focus:border-rose-500 focus:ring-rose-500/20': form.errors.password_confirmation }"
        />
        <p v-if="form.errors.password_confirmation" class="mt-1.5 text-xs text-rose-600 dark:text-rose-400">
          {{ form.errors.password_confirmation }}
        </p>
      </div>

      <!-- Submit & Feedback -->
      <div class="flex items-center gap-4 pt-2">
        <button
          type="submit"
          :disabled="form.processing"
          class="cursor-pointer rounded-xl bg-emerald-600 px-5 py-2.5 text-xs font-bold text-white shadow-md shadow-emerald-600/20 hover:bg-emerald-500 active:scale-98 disabled:opacity-60 transition"
        >
          Perbarui Kata Sandi
        </button>

        <Transition
          enter-active-class="transition ease-in-out"
          enter-from-class="opacity-0"
          leave-active-class="transition ease-in-out"
          leave-to-class="opacity-0"
        >
          <p v-if="form.recentlySuccessful" class="text-xs font-semibold text-emerald-600 dark:text-emerald-400">
            Kata sandi berhasil diperbarui.
          </p>
        </Transition>
      </div>
    </form>
  </section>
</template>
