<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { Eye, EyeOff } from 'lucide-vue-next';

const props = defineProps<{
    email: string;
    token: string;
}>();

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const showPassword = ref(false);
const showConfirmPassword = ref(false);

const submit = () => {
    form.post('/reset-password', {
        onFinish: () => {
            form.reset('password', 'password_confirmation');
        },
    });
};
</script>

<template>
    <AuthLayout>
        <Head title="Atur Ulang Kata Sandi - ReValue" />

        <!-- Title & Subtitle -->
        <div class="mb-6 text-left sm:mb-8">
            <h1
                class="mb-2 text-[30px] leading-[1.2] font-extrabold tracking-tight text-night-ink sm:text-[36px]"
            >
                Atur kata sandi baru.
            </h1>
            <p
                class="text-[15px] leading-relaxed font-medium text-charcoal sm:text-[16px]"
            >
                Buat kata sandi baru yang aman untuk melanjutkan langkah baik
                Anda.
            </p>
        </div>

        <form @submit.prevent="submit" class="space-y-4 sm:space-y-5">
            <!-- Email Field (Readonly) -->
            <div>
                <label
                    for="email"
                    class="mb-1.5 block text-[14px] font-bold text-night-ink"
                >
                    Alamat email akun
                </label>
                <input
                    id="email"
                    type="email"
                    v-model="form.email"
                    readonly
                    tabindex="-1"
                    class="h-[52px] w-full cursor-not-allowed rounded-xl border-2 border-border-gray/60 bg-cloud-surface px-4 text-[15px] font-semibold text-charcoal select-none focus:outline-none sm:text-[16px]"
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
                    class="mb-1.5 block text-[14px] font-bold text-night-ink"
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
                        class="h-[52px] w-full rounded-xl border-2 bg-paper-white pr-24 pl-4 text-[15px] font-semibold text-night-ink transition-all placeholder:text-charcoal/40 focus:outline-none sm:text-[16px]"
                        :class="
                            form.errors.password
                                ? 'border-red-500 focus:border-red-500 focus:ring-4 focus:ring-red-500/15'
                                : 'border-border-gray focus:border-eco-green focus:ring-4 focus:ring-eco-green/15'
                        "
                    />
                    <button
                        type="button"
                        class="absolute right-2 flex cursor-pointer items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-[13px] font-extrabold text-night-ink transition-colors hover:bg-cloud-surface hover:text-eco-green-dark"
                        @click="showPassword = !showPassword"
                    >
                        <component
                            :is="showPassword ? EyeOff : Eye"
                            class="size-4"
                        />
                        <span>{{ showPassword ? 'Tutup' : 'Lihat' }}</span>
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
                    class="mb-1.5 block text-[14px] font-bold text-night-ink"
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
                        class="h-[52px] w-full rounded-xl border-2 bg-paper-white pr-24 pl-4 text-[15px] font-semibold text-night-ink transition-all placeholder:text-charcoal/40 focus:outline-none sm:text-[16px]"
                        :class="
                            form.errors.password_confirmation
                                ? 'border-red-500 focus:border-red-500 focus:ring-4 focus:ring-red-500/15'
                                : 'border-border-gray focus:border-eco-green focus:ring-4 focus:ring-eco-green/15'
                        "
                    />
                    <button
                        type="button"
                        class="absolute right-2 flex cursor-pointer items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-[13px] font-extrabold text-night-ink transition-colors hover:bg-cloud-surface hover:text-eco-green-dark"
                        @click="showConfirmPassword = !showConfirmPassword"
                    >
                        <component
                            :is="showConfirmPassword ? EyeOff : Eye"
                            class="size-4"
                        />
                        <span>{{
                            showConfirmPassword ? 'Tutup' : 'Lihat'
                        }}</span>
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
                    class="flex h-[52px] w-full cursor-pointer items-center justify-center rounded-2xl border-2 border-b-[5px] border-eco-green border-b-eco-green-dark bg-eco-green text-[15px] font-extrabold text-night-ink shadow-xs transition-all hover:brightness-105 active:translate-y-0.5 active:border-b-2 active:shadow-none disabled:cursor-not-allowed disabled:opacity-60 sm:text-[16px]"
                >
                    <span v-if="form.processing">Menyimpan...</span>
                    <span v-else>Simpan kata sandi baru</span>
                </button>
            </div>
        </form>

        <!-- Back to Login Link -->
        <div
            class="mt-8 flex items-center justify-center gap-2 text-center text-[14px] font-medium text-charcoal"
        >
            <span>Batal atur ulang?</span>
            <Link
                href="/login"
                class="font-extrabold text-night-ink underline decoration-2 underline-offset-4 transition-colors hover:text-eco-green-dark"
            >
                Kembali ke Masuk
            </Link>
        </div>
    </AuthLayout>
</template>
