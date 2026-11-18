<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthLayout from '@/layouts/AuthLayout.vue';

defineProps<{
    status?: string;
}>();

const form = useForm({
    email: '',
});

const submit = () => {
    form.post('/forgot-password');
};
</script>

<template>
    <AuthLayout>
        <Head title="Lupa Kata Sandi - ReValue" />

        <!-- Title & Subtitle -->
        <div class="mb-6 text-left sm:mb-8">
            <h1
                class="mb-2 text-[30px] leading-[1.2] font-extrabold tracking-tight text-night-ink sm:text-[36px]"
            >
                Lupa kata sandi?
            </h1>
            <p
                class="text-[15px] leading-relaxed font-medium text-charcoal sm:text-[16px]"
            >
                Jangan khawatir. Masukkan email terdaftar Anda dan kami akan
                mengirimkan tautan untuk mengatur ulang kata sandi.
            </p>
        </div>

        <!-- Feedback Status Alert -->
        <div
            v-if="status"
            class="mb-6 rounded-2xl border-2 border-eco-green/40 bg-sprout-tint p-4 text-[14px] font-bold text-night-ink"
        >
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="space-y-5">
            <!-- Email Address Field -->
            <div>
                <label
                    for="email"
                    class="mb-1.5 block text-[14px] font-bold text-night-ink"
                >
                    Alamat email terdaftar
                </label>
                <input
                    id="email"
                    type="email"
                    v-model="form.email"
                    required
                    autofocus
                    autocomplete="username"
                    placeholder="nama@email.com"
                    class="h-[52px] w-full rounded-xl border-2 bg-paper-white px-4 text-[15px] font-semibold text-night-ink transition-all placeholder:text-charcoal/40 focus:outline-none sm:text-[16px]"
                    :class="
                        form.errors.email
                            ? 'border-red-500 focus:border-red-500 focus:ring-4 focus:ring-red-500/15'
                            : 'border-border-gray focus:border-eco-green focus:ring-4 focus:ring-eco-green/15'
                    "
                />
                <p
                    v-if="form.errors.email"
                    class="mt-1.5 text-[13px] font-bold text-red-600"
                >
                    {{ form.errors.email }}
                </p>
            </div>

            <!-- Tactile 3D Action Submit Button -->
            <div class="pt-2">
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="flex h-[52px] w-full cursor-pointer items-center justify-center rounded-2xl border-2 border-b-[5px] border-eco-green border-b-eco-green-dark bg-eco-green text-[15px] font-extrabold text-night-ink shadow-xs transition-all hover:brightness-105 active:translate-y-0.5 active:border-b-2 active:shadow-none disabled:cursor-not-allowed disabled:opacity-60 sm:text-[16px]"
                >
                    <span v-if="form.processing">Mengirim tautan...</span>
                    <span v-else>Kirim tautan pemulihan</span>
                </button>
            </div>
        </form>

        <!-- Back to Login Link -->
        <div
            class="mt-8 flex items-center justify-center gap-2 text-center text-[14px] font-medium text-charcoal"
        >
            <span>Ingat kata sandi Anda?</span>
            <Link
                href="/login"
                class="font-extrabold text-night-ink underline decoration-2 underline-offset-4 transition-colors hover:text-eco-green-dark"
            >
                Masuk
            </Link>
        </div>
    </AuthLayout>
</template>
