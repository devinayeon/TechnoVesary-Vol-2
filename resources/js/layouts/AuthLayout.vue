<script setup lang="ts">
import { Link } from "@inertiajs/vue3";
import { ArrowLeft } from "lucide-vue-next";
import AuthBrandPanel from "@/components/Auth/AuthBrandPanel.vue";
</script>

<template>
    <div
        class="min-h-screen w-full bg-paper-white flex flex-col lg:flex-row font-sans text-night-ink antialiased"
    >
        <!-- Left Side: Brand & Maskot Showcase (Desktop Full-Height Edge-to-Edge) -->
        <AuthBrandPanel />

        <!-- Right Side: Auth Form Panel -->
        <div
            class="flex-1 min-h-screen flex flex-col justify-between py-8 px-6 sm:px-12 xl:px-20 relative overflow-y-auto"
        >
            <!-- Top Header Navigation -->
            <div class="flex items-center justify-between w-full mb-6 sm:mb-8">
                <!-- Mobile Logo (Tampil jika layar < lg) -->
                <Link href="/" class="flex lg:hidden items-center gap-2 group">
                    <img
                        src="/assets/Logo/logo_hijau_biru.png"
                        alt="ReValue"
                        class="w-8 h-8 object-contain transition-transform duration-200 group-hover:scale-105"
                    />
                    <span
                        class="text-[22px] tracking-tight font-extrabold leading-none"
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
                    class="inline-flex items-center gap-2 text-[13px] sm:text-[14px] font-bold text-charcoal hover:text-night-ink transition-colors py-1.5 px-3 rounded-xl hover:bg-cloud-surface group"
                >
                    <ArrowLeft
                        class="size-4 transition-transform duration-200 group-hover:-translate-x-1"
                    />
                    <span>Beranda</span>
                </Link>
            </div>

            <!-- Form Center Region Slot -->
            <div class="w-full max-w-[440px] mx-auto my-auto py-4">
                <slot />
            </div>

            <!-- Bottom Footer Legal / Copyright Note -->
            <div class="mt-8 pt-4 border-t border-border-gray/50 text-center">
                <p class="text-[12px] font-medium text-charcoal/80">
                    © {{ new Date().getFullYear() }} ReValue. Langkah kecil
                    untuk masa depan besar.
                </p>
            </div>
        </div>
    </div>
</template>
