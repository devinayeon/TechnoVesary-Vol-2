<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import gsap from 'gsap';
import AppButton from './AppButton.vue';

const benefits = [
    '✓  Dijemput dari rumah',
    '✓  Ditimbang transparan',
    '✓  Poin bisa dipakai lagi',
];

// Split headline per karakter untuk animasi letter-by-letter
const line1 = 'Sampahmu punya';
const line2 = 'masa depan baru.';
const line1Chars = line1.split('');
const line2Chars = line2.split('');

const heroContainerRef = ref<HTMLElement | null>(null);
const leftBadgeRef = ref<HTMLElement | null>(null);
const rightBadgeRef = ref<HTMLElement | null>(null);
let gsapCtx: gsap.Context | null = null;

onMounted(() => {
    if (typeof window === 'undefined' || !heroContainerRef.value) return;

    gsapCtx = gsap.context(() => {
        const tl = gsap.timeline({ defaults: { ease: 'power3.out' } });

        // 1. Tag caption masuk dari atas
        tl.fromTo(
            '.hero-tag',
            { y: -20, opacity: 0 },
            { y: 0, opacity: 1, duration: 0.5, delay: 0.1 },
        );

        // 2. Letter-by-letter untuk headline h1
        const chars = heroContainerRef.value!.querySelectorAll('.hero-char');
        tl.fromTo(
            chars,
            {
                y: 50,
                opacity: 0,
                rotateX: -90,
                transformOrigin: '0% 50% -10px',
            },
            {
                y: 0,
                opacity: 1,
                rotateX: 0,
                duration: 0.045,
                stagger: 0.028,
                ease: 'back.out(1.4)',
            },
            '-=0.1',
        );

        // 3. Subheadline slide up
        tl.fromTo(
            '.hero-subheadline',
            { y: 25, opacity: 0 },
            { y: 0, opacity: 1, duration: 0.6 },
            '-=0.1',
        );

        // 4. CTA button pop in
        tl.fromTo(
            '.hero-cta',
            { scale: 0.85, y: 25, opacity: 0 },
            {
                scale: 1,
                y: 0,
                opacity: 1,
                duration: 0.6,
                ease: 'back.out(1.8)',
            },
            '-=0.3',
        );

        // 5. Benefits bar slide up
        tl.fromTo(
            '.hero-benefits',
            { y: 30, opacity: 0 },
            { y: 0, opacity: 1, duration: 0.7 },
            '-=0.3',
        );

        // Entrance untuk floating badges dengan fromTo
        if (leftBadgeRef.value) {
            gsap.fromTo(
                leftBadgeRef.value,
                { x: -50, scale: 0.6, opacity: 0 },
                {
                    x: 0,
                    scale: 1,
                    opacity: 1,
                    duration: 1,
                    delay: 0.3,
                    ease: 'back.out(1.5)',
                },
            );

            // Idle floating animation (sinusoidal bobbing)
            gsap.to(leftBadgeRef.value, {
                y: '-=14',
                rotation: '-=3',
                duration: 3,
                repeat: -1,
                yoyo: true,
                ease: 'sine.inOut',
            });
        }

        if (rightBadgeRef.value) {
            gsap.fromTo(
                rightBadgeRef.value,
                { x: 50, scale: 0.6, opacity: 0 },
                {
                    x: 0,
                    scale: 1,
                    opacity: 1,
                    duration: 1,
                    delay: 0.4,
                    ease: 'back.out(1.5)',
                },
            );

            // Idle floating animation (counter-phase)
            gsap.to(rightBadgeRef.value, {
                y: '+=16',
                rotation: '+=3.5',
                duration: 3.4,
                repeat: -1,
                yoyo: true,
                ease: 'sine.inOut',
            });
        }
    }, heroContainerRef.value);
});

onUnmounted(() => {
    gsapCtx?.revert();
});
</script>

<template>
    <section
        id="beranda"
        ref="heroContainerRef"
        class="relative scroll-mt-28 overflow-hidden bg-paper-white pt-32 pb-8"
    >
        <!-- Center Content -->
        <div class="relative z-10 mx-auto max-w-[800px] px-6 text-center">
            <!-- Tag / Caption -->
            <p
                class="hero-tag mb-6 text-[13px] font-bold tracking-[1.6px] text-charcoal uppercase"
            >
                KECIL AKSINYA. BESAR ARTINYA.
            </p>

            <!-- Headline — letter-by-letter animation via v-for -->
            <h1
                class="mb-6 text-5xl leading-[1.06] font-extrabold tracking-[-2.6px] text-night-ink sm:text-6xl lg:text-[76px]"
                style="perspective: 500px"
            >
                <!-- Baris 1 -->
                <span class="block">
                    <span
                        v-for="(char, i) in line1Chars"
                        :key="'l1-' + i"
                        class="hero-char inline-block"
                        :style="char === ' ' ? 'width: 0.35em' : ''"
                        >{{ char === ' ' ? '\u00A0' : char }}</span
                    >
                </span>
                <!-- Baris 2 -->
                <span class="block">
                    <span
                        v-for="(char, i) in line2Chars"
                        :key="'l2-' + i"
                        class="hero-char inline-block"
                        :style="char === ' ' ? 'width: 0.35em' : ''"
                        >{{ char === ' ' ? '\u00A0' : char }}</span
                    >
                </span>
            </h1>

            <!-- Subheadline -->
            <p
                class="hero-subheadline mx-auto mb-8 max-w-[570px] text-lg leading-[1.5] font-medium text-charcoal sm:text-[20px]"
            >
                Pilah dari rumah, kami yang jemput. Ubah sampahmu jadi poin,
                hadiah, dan langkah baik untuk bumi.
            </p>

            <!-- CTA Button -->
            <div class="hero-cta mb-4 flex flex-col items-center gap-3">
                <Link href="/register">
                    <AppButton
                        variant="primary"
                        size="lg"
                        class="h-12 px-8 text-[14px]"
                    >
                        MULAI LANGKAH BAIK
                    </AppButton>
                </Link>
                <p class="text-[13px] font-bold tracking-wide text-charcoal">
                    Daftar gratis. Mulai kebiasaan baikmu hari ini.
                </p>
            </div>
        </div>

        <!-- Floating Orb Kiri (Pilah Master - Emerald Tier) -->
        <div
            ref="leftBadgeRef"
            class="pointer-events-none absolute top-[140px] left-[80px] z-10 hidden select-none xl:block 2xl:left-[110px]"
        >
            <div
                class="pointer-events-auto -rotate-[8.85deg] transform cursor-default transition-transform duration-300 hover:rotate-0"
            >
                <div class="flex w-[240px] flex-col items-center p-4">
                    <!-- Duolingo Shield Badge -->
                    <div class="relative mb-4 h-[160px] w-[140px]">
                        <img
                            src="/assets/Badge/Badge=Pilah Master, Tier=Emerald.png"
                            alt="Pilah Master"
                            class="absolute inset-0 h-full w-full object-contain drop-shadow-[0_8px_16px_rgba(88,204,2,0.25)] filter"
                        />
                    </div>
                    <p
                        class="w-[200px] text-center text-[19px] leading-[1.3] font-bold text-night-ink"
                    >
                        Pilah sedikit,<br />berarti banyak.
                    </p>
                </div>
            </div>
        </div>

        <!-- Floating Orb Kanan (Pejuang 25 Order - Gold Tier) -->
        <div
            ref="rightBadgeRef"
            class="pointer-events-none absolute top-[180px] right-[80px] z-10 hidden select-none xl:block 2xl:right-[110px]"
        >
            <div
                class="pointer-events-auto rotate-[14.14deg] transform cursor-default transition-transform duration-300 hover:rotate-0"
            >
                <div class="flex w-[220px] flex-col items-center p-4">
                    <!-- Duolingo Shield Badge -->
                    <div class="relative mb-4 h-[160px] w-[140px]">
                        <img
                            src="/assets/Badge/Badge=Pejuang 25 Order, Tier=Gold.png"
                            alt="Pejuang 25 Order"
                            class="absolute inset-0 h-full w-full object-contain drop-shadow-[0_8px_16px_rgba(255,200,0,0.3)] filter"
                        />
                    </div>
                    <p
                        class="w-[190px] text-center text-[19px] leading-[1.3] font-bold text-night-ink"
                    >
                        Setor. Kumpulkan.<br />Rayakan!
                    </p>
                </div>
            </div>
        </div>

        <!-- Benefits Bar -->
        <div class="hero-benefits mx-auto mt-16 max-w-[1200px] px-6">
            <div
                class="flex h-auto flex-col items-center justify-between gap-3 rounded-3xl border-2 border-b-[4px] border-border-gray/70 bg-cloud-surface px-6 py-4 text-[17px] font-bold text-night-ink sm:h-16 sm:flex-row sm:px-10 sm:py-0 sm:text-[19px]"
            >
                <span
                    v-for="b in benefits"
                    :key="b"
                    class="inline-block cursor-default py-1 transition-all duration-200 hover:scale-105 hover:text-eco-green"
                >
                    {{ b }}
                </span>
            </div>
        </div>
    </section>
</template>
