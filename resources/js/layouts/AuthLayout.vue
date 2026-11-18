<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';
import AuthBrandPanel from '@/components/Auth/AuthBrandPanel.vue';
</script>

<template>
    <div
        class="flex min-h-screen w-full flex-col bg-paper-white font-sans text-night-ink antialiased lg:flex-row"
    >
        <!-- Left Side: Brand & Maskot Showcase (Desktop Full-Height Edge-to-Edge) -->
        <AuthBrandPanel />

        <!-- Right Side: Auth Form Panel -->
        <div
            class="relative flex min-h-screen flex-1 flex-col justify-between overflow-y-auto px-6 py-8 sm:px-12 xl:px-20"
        >
            <!-- Top Header Navigation -->
            <div class="mb-6 flex w-full items-center justify-between sm:mb-8">
                <!-- Mobile Logo (Tampil jika layar < lg) -->
                <Link href="/" class="group flex items-center gap-2 lg:hidden">
                    <img
                        src="/assets/Logo/logo_hijau_biru.png"
                        alt="ReValue"
                        class="h-8 w-8 object-contain transition-transform duration-200 group-hover:scale-105"
                    />
                    <span
                        class="text-[22px] leading-none font-extrabold tracking-tight"
                    >
                        <span class="text-night-ink">Re</span
                        ><span class="text-eco-green-dark">Value</span>
                    </span>
                </Link>

                <div class="hidden lg:block"></div>

                <!-- Kembali ke Beranda Link -->
            </div>
            <div>
                <Link
                    href="/"
                    class="group ml-auto inline-flex items-center gap-2 rounded-xl px-3 py-1.5 text-[13px] font-bold text-charcoal transition-colors hover:bg-cloud-surface hover:text-night-ink sm:text-[14px]"
                >
                    <ArrowLeft
                        class="size-4 transition-transform duration-200 group-hover:-translate-x-1"
                    />
                    <span>Beranda</span>
                </Link>
            </div>

            <!-- Form Center Region Slot -->
            <div class="mx-auto my-auto w-full max-w-[440px] py-4">
                <slot />
            </div>

            <!-- Bottom Footer Legal / Copyright Note -->
            <div class="mt-8 border-t border-border-gray/50 pt-4 text-center">
                <p class="text-[12px] font-medium text-charcoal/80">
                    © {{ new Date().getFullYear() }} ReValue. Langkah kecil
                    untuk masa depan besar.
                </p>
            </div>
        </div>
    </div>
</template>
