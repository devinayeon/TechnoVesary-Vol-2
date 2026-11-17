<script setup lang="ts">
import { Link } from "@inertiajs/vue3";
import AppButton from "./AppButton.vue";

const benefits = [
    "✓  Dijemput dari rumah",
    "✓  Ditimbang transparan",
    "✓  Poin bisa dipakai lagi",
];
</script>

<template>
    <section class="relative bg-paper-white pt-32 pb-8 overflow-hidden">
        <!-- Center Content -->
        <div class="relative max-w-[800px] mx-auto px-6 text-center z-10">
            <!-- Tag / Caption -->
            <p
                class="font-bold text-[13px] tracking-[1.6px] text-charcoal uppercase mb-6"
            >
                KECIL AKSINYA. BESAR ARTINYA.
            </p>

            <!-- Headline -->
            <h1
                class="text-5xl sm:text-6xl lg:text-[76px] font-extrabold text-night-ink leading-[1.06] tracking-[-2.6px] mb-6"
            >
                Sampahmu punya<br />
                masa depan baru.
            </h1>

            <!-- Subheadline -->
            <p
                class="text-lg sm:text-[20px] font-medium leading-[1.5] text-charcoal max-w-[570px] mx-auto mb-8"
            >
                Pilah dari rumah, kami yang jemput. Ubah sampahmu jadi poin,
                hadiah, dan langkah baik untuk bumi.
            </p>

            <!-- CTA Button -->
            <div class="flex flex-col items-center gap-3 mb-4">
                <Link href="/register">
                    <AppButton
                        variant="primary"
                        size="lg"
                        class="h-12 px-8 text-[14px]"
                    >
                        MULAI LANGKAH BAIK
                    </AppButton>
                </Link>
                <p class="text-[13px] font-bold text-charcoal tracking-wide">
                    Daftar gratis. Mulai kebiasaan baikmu hari ini.
                </p>
            </div>
        </div>

        <!-- Floating Orb Kiri (Pilah Master - Emerald Tier) -->
        <div
            class="hidden xl:block absolute left-[80px] 2xl:left-[110px] top-[140px] z-10 pointer-events-none select-none"
        >
            <div
                class="transform -rotate-[8.85deg] transition-transform duration-300 hover:rotate-0 pointer-events-auto"
            >
                <div class="w-[240px] flex flex-col items-center p-4">
                    <!-- Duolingo Shield Badge -->
                    <div class="relative w-[140px] h-[160px] mb-4">
                        <img
                            src="/assets/Badge/Badge=Pilah Master, Tier=Emerald.png"
                            alt=""
                            class="absolute inset-0 w-full h-full"
                        />
                    </div>
                    <p
                        class="font-bold text-[19px] text-night-ink leading-[1.3] text-center w-[200px]"
                    >
                        Pilah sedikit,<br />berarti banyak.
                    </p>
                </div>
            </div>
        </div>

        <!-- Floating Orb Kanan (Pejuang 25 Order - Gold Tier) -->
        <div
            class="hidden xl:block absolute right-[80px] 2xl:right-[110px] top-[180px] z-10 pointer-events-none select-none"
        >
            <div
                class="transform rotate-[14.14deg] transition-transform duration-300 hover:rotate-0 pointer-events-auto"
            >
                <div class="w-[220px] flex flex-col items-center p-4">
                    <!-- Duolingo Shield Badge -->
                    <div class="relative w-[140px] h-[160px] mb-4">
                        <img
                            src="/assets/Badge/Badge=Pejuang 25 Order, Tier=Gold.png"
                            alt=""
                            class="absolute inset-0 w-full h-full"
                        />
                    </div>
                    <p
                        class="font-bold text-[19px] text-night-ink leading-[1.3] text-center w-[190px]"
                    >
                        Setor. Kumpulkan.<br />Rayakan!
                    </p>
                </div>
            </div>
        </div>

        <!-- Benefits Bar -->
        <div class="max-w-[1200px] mx-auto px-6 mt-16">
            <div
                class="bg-cloud-surface rounded-3xl h-auto sm:h-16 px-6 sm:px-10 py-4 sm:py-0 flex flex-col sm:flex-row items-center justify-between gap-3 text-[17px] sm:text-[19px] font-bold text-night-ink"
            >
                <span
                    v-for="b in benefits"
                    :key="b"
                    class="hover:text-eco-green transition-colors"
                >
                    {{ b }}
                </span>
            </div>
        </div>
    </section>
</template>
