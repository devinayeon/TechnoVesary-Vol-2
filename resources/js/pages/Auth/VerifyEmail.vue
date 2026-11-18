<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { MailCheck } from 'lucide-vue-next';

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
    <AuthLayout>
        <Head title="Verifikasi Email - ReValue" />

        <!-- Title & Subtitle -->
        <div class="mb-6 text-left sm:mb-8">
            <div
                class="mb-4 inline-flex size-12 items-center justify-center rounded-2xl border-2 border-eco-green/40 bg-sprout-tint text-eco-green-dark"
            >
                <MailCheck class="size-6" />
            </div>
            <h1
                class="mb-2 text-[30px] leading-[1.2] font-extrabold tracking-tight text-night-ink sm:text-[36px]"
            >
                Verifikasi email Anda.
            </h1>
            <p
                class="text-[15px] leading-relaxed font-medium text-charcoal sm:text-[16px]"
            >
                Terima kasih telah bergabung dengan ReValue! Sebelum mulai
                menyetor sampah, silakan klik tautan verifikasi yang telah kami
                kirimkan ke email Anda.
            </p>
        </div>

        <!-- Feedback Alert -->
        <div
            v-if="verificationLinkSent"
            class="mb-6 rounded-2xl border-2 border-eco-green/40 bg-sprout-tint p-4 text-[14px] font-bold text-night-ink"
        >
            Tautan verifikasi baru telah berhasil dikirim ke alamat email Anda.
            Silakan periksa kotak masuk atau folder spam.
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <!-- Tactile 3D Action Submit Button -->
            <div>
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="flex h-[52px] w-full cursor-pointer items-center justify-center rounded-2xl border-2 border-b-[5px] border-eco-green border-b-eco-green-dark bg-eco-green text-[15px] font-extrabold text-night-ink shadow-xs transition-all hover:brightness-105 active:translate-y-0.5 active:border-b-2 active:shadow-none disabled:cursor-not-allowed disabled:opacity-60 sm:text-[16px]"
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
                    class="cursor-pointer text-[14px] font-semibold text-charcoal underline decoration-2 underline-offset-4 transition-colors hover:text-night-ink"
                >
                    Keluar dari akun
                </Link>
            </div>
        </form>
    </AuthLayout>
</template>
