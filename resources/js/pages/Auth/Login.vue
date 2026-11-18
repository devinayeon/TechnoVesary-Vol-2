<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { Eye, EyeOff } from 'lucide-vue-next';

defineProps<{
    canResetPassword?: boolean;
    status?: string;
}>();

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const showPassword = ref(false);

const submit = () => {
    form.post('/login', {
        onFinish: () => {
            form.reset('password');
        },
    });
};
</script>

<template>
    <AuthLayout>
        <Head title="Masuk ke Akun - ReValue" />

        <!-- Title & Subtitle -->
        <div class="mb-6 text-left sm:mb-8">
            <h1
                class="mb-2 text-[30px] leading-[1.2] font-extrabold tracking-tight text-night-ink sm:text-[36px]"
            >
                Senang bertemu lagi.
            </h1>
            <p
                class="text-[15px] leading-relaxed font-medium text-charcoal sm:text-[16px]"
            >
                Masuk untuk melanjutkan langkah baik Anda.
            </p>
        </div>

        <!-- Session Status Alert -->
        <div
            v-if="status"
            class="mb-6 rounded-2xl border-2 border-eco-green/40 bg-sprout-tint p-4 text-[14px] font-bold text-night-ink"
        >
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="space-y-5">
            <!-- Email Field -->
            <div>
                <label
                    for="email"
                    class="mb-1.5 block text-[14px] font-bold text-night-ink"
                >
                    Alamat email
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

            <!-- Password Field -->
            <div>
                <label
                    for="password"
                    class="mb-1.5 block text-[14px] font-bold text-night-ink"
                >
                    Kata sandi
                </label>
                <div class="relative flex items-center">
                    <input
                        id="password"
                        :type="showPassword ? 'text' : 'password'"
                        v-model="form.password"
                        required
                        autocomplete="current-password"
                        placeholder="••••••••••••"
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

            <!-- Login Options: Remember Me & Forgot Password -->
            <div class="flex items-center justify-between pt-1">
                <label
                    class="flex cursor-pointer items-center gap-2.5 select-none"
                >
                    <input
                        type="checkbox"
                        v-model="form.remember"
                        class="size-5 cursor-pointer rounded-[6px] border-2 border-border-gray text-eco-green focus:ring-eco-green focus:ring-offset-0"
                    />
                    <span class="text-[14px] font-semibold text-night-ink">
                        Ingat saya
                    </span>
                </label>

                <Link
                    v-if="canResetPassword !== false"
                    href="/forgot-password"
                    class="text-[14px] font-extrabold text-night-ink transition-colors hover:text-eco-green-dark"
                >
                    Lupa kata sandi?
                </Link>
            </div>

            <!-- Tactile 3D Action Submit Button (Duolingo Style) -->
            <div class="pt-2">
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="flex h-[52px] w-full cursor-pointer items-center justify-center rounded-2xl border-2 border-b-[5px] border-eco-green border-b-eco-green-dark bg-eco-green text-[15px] font-extrabold text-night-ink shadow-xs transition-all hover:brightness-105 active:translate-y-0.5 active:border-b-2 active:shadow-none disabled:cursor-not-allowed disabled:opacity-60 sm:text-[16px]"
                >
                    <span v-if="form.processing">Memproses...</span>
                    <span v-else>Masuk ke akun</span>
                </button>
            </div>
        </form>

        <!-- Account Switcher Link -->
        <div
            class="mt-8 flex items-center justify-center gap-2 text-center text-[14px] font-medium text-charcoal"
        >
            <span>Belum punya akun?</span>
            <Link
                href="/register"
                class="font-extrabold text-night-ink underline decoration-2 underline-offset-4 transition-colors hover:text-eco-green-dark"
            >
                Daftar
            </Link>
        </div>
    </AuthLayout>
</template>
