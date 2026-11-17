<script setup lang="ts">
import { Head, Link, useForm } from "@inertiajs/vue3";
import AuthLayout from "@/layouts/AuthLayout.vue";

defineProps<{
    status?: string;
}>();

const form = useForm({
    email: "",
});

const submit = () => {
    form.post("/forgot-password");
};
</script>

<template>
    <AuthLayout>
        <Head title="Lupa Kata Sandi - ReValue" />

        <!-- Title & Subtitle -->
        <div class="mb-6 sm:mb-8 text-left">
            <h1
                class="font-extrabold text-[30px] sm:text-[36px] text-night-ink leading-[1.2] tracking-tight mb-2"
            >
                Lupa kata sandi?
            </h1>
            <p
                class="font-medium text-[15px] sm:text-[16px] text-charcoal leading-relaxed"
            >
                Jangan khawatir. Masukkan email terdaftar Anda dan kami akan mengirimkan tautan untuk mengatur ulang kata sandi.
            </p>
        </div>

        <!-- Feedback Status Alert -->
        <div
            v-if="status"
            class="mb-6 rounded-2xl bg-sprout-tint border-2 border-eco-green/40 p-4 text-[14px] font-bold text-night-ink"
        >
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="space-y-5">
            <!-- Email Address Field -->
            <div>
                <label
                    for="email"
                    class="block font-bold text-[14px] text-night-ink mb-1.5"
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
                    class="w-full h-[52px] px-4 rounded-xl border-2 bg-paper-white text-[15px] sm:text-[16px] font-semibold text-night-ink placeholder:text-charcoal/40 transition-all focus:outline-none"
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
                    class="w-full h-[52px] bg-eco-green text-night-ink font-extrabold text-[15px] sm:text-[16px] rounded-2xl border-2 border-eco-green border-b-[5px] border-b-eco-green-dark shadow-xs hover:brightness-105 active:translate-y-0.5 active:border-b-2 active:shadow-none transition-all flex items-center justify-center cursor-pointer disabled:opacity-60 disabled:cursor-not-allowed"
                >
                    <span v-if="form.processing">Mengirim tautan...</span>
                    <span v-else>Kirim tautan pemulihan</span>
                </button>
            </div>
        </form>

        <!-- Back to Login Link -->
        <div class="mt-8 text-center flex items-center justify-center gap-2 text-[14px] font-medium text-charcoal">
            <span>Ingat kata sandi Anda?</span>
            <Link
                href="/login"
                class="font-extrabold text-night-ink hover:text-eco-green-dark underline decoration-2 underline-offset-4 transition-colors"
            >
                Masuk
            </Link>
        </div>
    </AuthLayout>
</template>
