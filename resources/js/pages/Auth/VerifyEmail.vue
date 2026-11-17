<script setup lang="ts">
import { computed } from "vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import AuthLayout from "@/layouts/AuthLayout.vue";
import { MailCheck } from "lucide-vue-next";

const props = defineProps<{
    status?: string;
}>();

const form = useForm({});

const submit = () => {
    form.post("/email/verification-notification");
};

const verificationLinkSent = computed(
    () => props.status === "verification-link-sent"
);
</script>

<template>
    <AuthLayout>
        <Head title="Verifikasi Email - ReValue" />

        <!-- Title & Subtitle -->
        <div class="mb-6 sm:mb-8 text-left">
            <div class="inline-flex size-12 items-center justify-center rounded-2xl bg-sprout-tint border-2 border-eco-green/40 text-eco-green-dark mb-4">
                <MailCheck class="size-6" />
            </div>
            <h1
                class="font-extrabold text-[30px] sm:text-[36px] text-night-ink leading-[1.2] tracking-tight mb-2"
            >
                Verifikasi email Anda.
            </h1>
            <p
                class="font-medium text-[15px] sm:text-[16px] text-charcoal leading-relaxed"
            >
                Terima kasih telah bergabung dengan ReValue! Sebelum mulai menyetor sampah, silakan klik tautan verifikasi yang telah kami kirimkan ke email Anda.
            </p>
        </div>

        <!-- Feedback Alert -->
        <div
            v-if="verificationLinkSent"
            class="mb-6 rounded-2xl bg-sprout-tint border-2 border-eco-green/40 p-4 text-[14px] font-bold text-night-ink"
        >
            Tautan verifikasi baru telah berhasil dikirim ke alamat email Anda. Silakan periksa kotak masuk atau folder spam.
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <!-- Tactile 3D Action Submit Button -->
            <div>
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="w-full h-[52px] bg-eco-green text-night-ink font-extrabold text-[15px] sm:text-[16px] rounded-2xl border-2 border-eco-green border-b-[5px] border-b-eco-green-dark shadow-xs hover:brightness-105 active:translate-y-0.5 active:border-b-2 active:shadow-none transition-all flex items-center justify-center cursor-pointer disabled:opacity-60 disabled:cursor-not-allowed"
                >
                    <span v-if="form.processing">Mengirim ulang...</span>
                    <span v-else>Kirim ulang email verifikasi</span>
                </button>
            </div>

            <div class="pt-2 text-center">
                <Link
                    href="/logout"
                    method="post"
                    as="button"
                    class="text-[14px] font-semibold text-charcoal hover:text-night-ink underline decoration-2 underline-offset-4 cursor-pointer transition-colors"
                >
                    Keluar dari akun
                </Link>
            </div>
        </form>
    </AuthLayout>
</template>
