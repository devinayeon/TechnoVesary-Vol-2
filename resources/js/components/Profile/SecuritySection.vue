<script setup lang="ts">
import { computed, ref } from "vue";
import { useForm } from "@inertiajs/vue3";
import type { User } from "@/types/auth";
import AppButton from "@/components/Landing/AppButton.vue";
import StatusChip from "@/components/Dashboard/StatusChip.vue";
import DetailField from "./DetailField.vue";

const props = defineProps<{
    user: User;
    mustVerifyEmail?: boolean;
    status?: string;
}>();

const emit = defineEmits<{
    (e: "goToEditEmail"): void;
}>();

const isChangingPassword = ref(false);
const passwordSuccess = ref(false);

const passwordForm = useForm({
    current_password: "",
    password: "",
    password_confirmation: "",
});

const verificationForm = useForm({});

const isEmailVerified = computed(() => !!props.user.email_verified_at);

const updatePassword = () => {
    passwordForm.put("/password", {
        preserveScroll: true,
        onSuccess: () => {
            passwordForm.reset();
            isChangingPassword.value = false;
            passwordSuccess.value = true;
            setTimeout(() => {
                passwordSuccess.value = false;
            }, 4000);
        },
    });
};

const resendVerification = () => {
    verificationForm.post("/email/verification-notification", {
        preserveScroll: true,
    });
};
</script>

<template>
    <div class="flex flex-col gap-6 w-full">
        <!-- Heading -->
        <div class="flex flex-col gap-1">
            <h1 class="text-[28px] font-extrabold text-night-ink tracking-tight">
                Keamanan akun
            </h1>
            <p class="text-[14px] text-charcoal">
                Kelola kata sandi dan status email akunmu.
            </p>
        </div>

        <!-- Success Alert if password changed -->
        <div
            v-if="passwordSuccess || status === 'password-updated'"
            class="bg-sprout-tint/80 border border-eco-green/40 p-4 rounded-xl text-night-ink font-bold text-sm shadow-xs animate-in fade-in duration-300"
        >
            Kata sandi berhasil diperbarui!
        </div>

        <!-- Verification sent alert -->
        <div
            v-if="status === 'verification-link-sent'"
            class="bg-sky-tint/80 border border-sky-blue/40 p-4 rounded-xl text-night-ink font-bold text-sm shadow-xs animate-in fade-in duration-300"
        >
            Tautan verifikasi baru telah dikirimkan ke alamat email kamu. Silakan periksa kotak masuk email.
        </div>

        <!-- 2 Columns Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            <!-- Left Card: Password -->
            <div
                class="lg:col-span-7 bg-paper-white border border-border-gray rounded-[20px] p-6 sm:p-8 flex flex-col gap-5 shadow-xs"
            >
                <h3 class="text-[21px] font-extrabold text-night-ink leading-snug">
                    Kata sandi
                </h3>

                <p class="text-[13px] text-charcoal leading-relaxed font-normal">
                    Kata sandi tidak ditampilkan. Gunakan kata sandi yang unik untuk akun ReValue.
                </p>

                <!-- Normal view: Trigger button -->
                <div v-if="!isChangingPassword" class="flex flex-col items-start gap-3 pt-2">
                    <AppButton
                        variant="primary"
                        size="md"
                        @click="isChangingPassword = true"
                    >
                        Ubah kata sandi
                    </AppButton>
                    <span class="text-[12px] text-charcoal font-normal">
                        Kata sandi lama diperlukan untuk menyimpan perubahan.
                    </span>
                </div>

                <!-- Form view: Change password inline -->
                <form
                    v-else
                    @submit.prevent="updatePassword"
                    class="flex flex-col gap-4 pt-2 border-t border-border-gray/70"
                >
                    <div class="flex flex-col gap-1.5">
                        <label class="text-[13px] font-bold text-night-ink">
                            Kata sandi saat ini *
                        </label>
                        <input
                            v-model="passwordForm.current_password"
                            type="password"
                            required
                            class="h-[48px] px-4 rounded-xl border border-border-gray bg-paper-white text-[15px] text-night-ink focus:outline-none focus:border-eco-green focus:ring-2 focus:ring-eco-green/20 transition"
                            :class="{ 'border-rose-500': passwordForm.errors.current_password }"
                        />
                        <span
                            v-if="passwordForm.errors.current_password"
                            class="text-xs text-rose-500 font-semibold"
                        >
                            {{ passwordForm.errors.current_password }}
                        </span>
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label class="text-[13px] font-bold text-night-ink">
                            Kata sandi baru *
                        </label>
                        <input
                            v-model="passwordForm.password"
                            type="password"
                            required
                            class="h-[48px] px-4 rounded-xl border border-border-gray bg-paper-white text-[15px] text-night-ink focus:outline-none focus:border-eco-green focus:ring-2 focus:ring-eco-green/20 transition"
                            :class="{ 'border-rose-500': passwordForm.errors.password }"
                        />
                        <span
                            v-if="passwordForm.errors.password"
                            class="text-xs text-rose-500 font-semibold"
                        >
                            {{ passwordForm.errors.password }}
                        </span>
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label class="text-[13px] font-bold text-night-ink">
                            Konfirmasi kata sandi baru *
                        </label>
                        <input
                            v-model="passwordForm.password_confirmation"
                            type="password"
                            required
                            class="h-[48px] px-4 rounded-xl border border-border-gray bg-paper-white text-[15px] text-night-ink focus:outline-none focus:border-eco-green focus:ring-2 focus:ring-eco-green/20 transition"
                            :class="{ 'border-rose-500': passwordForm.errors.password_confirmation }"
                        />
                        <span
                            v-if="passwordForm.errors.password_confirmation"
                            class="text-xs text-rose-500 font-semibold"
                        >
                            {{ passwordForm.errors.password_confirmation }}
                        </span>
                    </div>

                    <div class="pt-3 flex items-center justify-end gap-3 border-t border-border-gray/70">
                        <AppButton
                            variant="outline"
                            size="md"
                            type="button"
                            @click="isChangingPassword = false"
                        >
                            Batal
                        </AppButton>
                        <AppButton
                            variant="primary"
                            size="md"
                            type="submit"
                            :disabled="passwordForm.processing"
                        >
                            {{ passwordForm.processing ? "Menyimpan..." : "Simpan kata sandi" }}
                        </AppButton>
                    </div>
                </form>
            </div>

            <!-- Right Card: Email Security -->
            <div
                class="lg:col-span-5 bg-paper-white border border-border-gray rounded-[20px] p-6 sm:p-8 flex flex-col gap-5 shadow-xs"
            >
                <h3 class="text-[21px] font-extrabold text-night-ink leading-snug">
                    Email akun
                </h3>

                <DetailField label="Alamat email" :value="user.email" />

                <!-- Status & Resend -->
                <div class="flex flex-col gap-3 pt-1">
                    <div class="flex items-center gap-2">
                        <StatusChip
                            :label="isEmailVerified ? 'Email terverifikasi' : 'Belum diverifikasi'"
                            :tone="isEmailVerified ? 'Green' : 'Amber'"
                        />
                    </div>

                    <button
                        v-if="!isEmailVerified"
                        type="button"
                        :disabled="verificationForm.processing"
                        @click="resendVerification"
                        class="text-left text-xs font-bold text-eco-green hover:underline cursor-pointer"
                    >
                        {{ verificationForm.processing ? "Mengirim ulang..." : "Kirim ulang email verifikasi" }}
                    </button>
                </div>

                <div class="pt-3 border-t border-border-gray/50">
                    <button
                        type="button"
                        @click="emit('goToEditEmail')"
                        class="text-[13px] font-extrabold text-night-ink hover:text-eco-green transition cursor-pointer select-none"
                    >
                        Ubah alamat email
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
