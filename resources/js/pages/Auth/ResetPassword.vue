<script setup lang="ts">
import { ref } from "vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import AuthLayout from "@/layouts/AuthLayout.vue";
import { Eye, EyeOff } from "lucide-vue-next";

const props = defineProps<{
    email: string;
    token: string;
}>();

const form = useForm({
    token: props.token,
    email: props.email,
    password: "",
    password_confirmation: "",
});

const showPassword = ref(false);
const showConfirmPassword = ref(false);

const submit = () => {
    form.post("/reset-password", {
        onFinish: () => {
            form.reset("password", "password_confirmation");
        },
    });
};
</script>

<template>
    <AuthLayout>
        <Head title="Atur Ulang Kata Sandi - ReValue" />

        <!-- Title & Subtitle -->
        <div class="mb-6 sm:mb-8 text-left">
            <h1
                class="font-extrabold text-[30px] sm:text-[36px] text-night-ink leading-[1.2] tracking-tight mb-2"
            >
                Atur kata sandi baru.
            </h1>
            <p
                class="font-medium text-[15px] sm:text-[16px] text-charcoal leading-relaxed"
            >
                Buat kata sandi baru yang aman untuk melanjutkan langkah baik Anda.
            </p>
        </div>

        <form @submit.prevent="submit" class="space-y-4 sm:space-y-5">
            <!-- Email Field (Readonly) -->
            <div>
                <label
                    for="email"
                    class="block font-bold text-[14px] text-night-ink mb-1.5"
                >
                    Alamat email akun
                </label>
                <input
                    id="email"
                    type="email"
                    v-model="form.email"
                    readonly
                    tabindex="-1"
                    class="w-full h-[52px] px-4 rounded-xl border-2 border-border-gray/60 bg-cloud-surface text-[15px] sm:text-[16px] font-semibold text-charcoal cursor-not-allowed select-none focus:outline-none"
                    :class="{ 'border-red-500': form.errors.email }"
                />
                <p
                    v-if="form.errors.email"
                    class="mt-1.5 text-[13px] font-bold text-red-600"
                >
                    {{ form.errors.email }}
                </p>
            </div>

            <!-- New Password Field -->
            <div>
                <label
                    for="password"
                    class="block font-bold text-[14px] text-night-ink mb-1.5"
                >
                    Kata sandi baru
                </label>
                <div class="relative flex items-center">
                    <input
                        id="password"
                        :type="showPassword ? 'text' : 'password'"
                        v-model="form.password"
                        required
                        autofocus
                        autocomplete="new-password"
                        placeholder="Minimal 8 karakter"
                        class="w-full h-[52px] pl-4 pr-24 rounded-xl border-2 bg-paper-white text-[15px] sm:text-[16px] font-semibold text-night-ink placeholder:text-charcoal/40 transition-all focus:outline-none"
                        :class="
                            form.errors.password
                                ? 'border-red-500 focus:border-red-500 focus:ring-4 focus:ring-red-500/15'
                                : 'border-border-gray focus:border-eco-green focus:ring-4 focus:ring-eco-green/15'
                        "
                    />
                    <button
                        type="button"
                        class="absolute right-2 px-2.5 py-1.5 text-[13px] font-extrabold text-night-ink hover:text-eco-green-dark flex items-center gap-1.5 cursor-pointer rounded-lg hover:bg-cloud-surface transition-colors"
                        @click="showPassword = !showPassword"
                    >
                        <component
                            :is="showPassword ? EyeOff : Eye"
                            class="size-4"
                        />
                        <span>{{ showPassword ? "Tutup" : "Lihat" }}</span>
                    </button>
                </div>
                <p
                    v-if="form.errors.password"
                    class="mt-1.5 text-[13px] font-bold text-red-600"
                >
                    {{ form.errors.password }}
                </p>
            </div>

            <!-- Confirm Password Field -->
            <div>
                <label
                    for="password_confirmation"
                    class="block font-bold text-[14px] text-night-ink mb-1.5"
                >
                    Konfirmasi kata sandi baru
                </label>
                <div class="relative flex items-center">
                    <input
                        id="password_confirmation"
                        :type="showConfirmPassword ? 'text' : 'password'"
                        v-model="form.password_confirmation"
                        required
                        autocomplete="new-password"
                        placeholder="Ulangi kata sandi baru"
                        class="w-full h-[52px] pl-4 pr-24 rounded-xl border-2 bg-paper-white text-[15px] sm:text-[16px] font-semibold text-night-ink placeholder:text-charcoal/40 transition-all focus:outline-none"
                        :class="
                            form.errors.password_confirmation
                                ? 'border-red-500 focus:border-red-500 focus:ring-4 focus:ring-red-500/15'
                                : 'border-border-gray focus:border-eco-green focus:ring-4 focus:ring-eco-green/15'
                        "
                    />
                    <button
                        type="button"
                        class="absolute right-2 px-2.5 py-1.5 text-[13px] font-extrabold text-night-ink hover:text-eco-green-dark flex items-center gap-1.5 cursor-pointer rounded-lg hover:bg-cloud-surface transition-colors"
                        @click="showConfirmPassword = !showConfirmPassword"
                    >
                        <component
                            :is="showConfirmPassword ? EyeOff : Eye"
                            class="size-4"
                        />
                        <span>{{ showConfirmPassword ? "Tutup" : "Lihat" }}</span>
                    </button>
                </div>
                <p
                    v-if="form.errors.password_confirmation"
                    class="mt-1.5 text-[13px] font-bold text-red-600"
                >
                    {{ form.errors.password_confirmation }}
                </p>
            </div>

            <!-- Tactile 3D Action Submit Button -->
            <div class="pt-3">
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="w-full h-[52px] bg-eco-green text-night-ink font-extrabold text-[15px] sm:text-[16px] rounded-2xl border-2 border-eco-green border-b-[5px] border-b-eco-green-dark shadow-xs hover:brightness-105 active:translate-y-0.5 active:border-b-2 active:shadow-none transition-all flex items-center justify-center cursor-pointer disabled:opacity-60 disabled:cursor-not-allowed"
                >
                    <span v-if="form.processing">Menyimpan...</span>
                    <span v-else>Simpan kata sandi baru</span>
                </button>
            </div>
        </form>

        <!-- Back to Login Link -->
        <div class="mt-8 text-center flex items-center justify-center gap-2 text-[14px] font-medium text-charcoal">
            <span>Batal atur ulang?</span>
            <Link
                href="/login"
                class="font-extrabold text-night-ink hover:text-eco-green-dark underline decoration-2 underline-offset-4 transition-colors"
            >
                Kembali ke Masuk
            </Link>
        </div>
    </AuthLayout>
</template>
