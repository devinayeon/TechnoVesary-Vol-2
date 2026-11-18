<script setup lang="ts">
import { ref, onMounted, onUnmounted, nextTick } from 'vue';
import { Link } from '@inertiajs/vue3';
import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import AppButton from './AppButton.vue';

const sectionRef = ref<HTMLElement | null>(null);
const mascotRef = ref<HTMLElement | null>(null);
let gsapCtx: gsap.Context | null = null;

onMounted(async () => {
    if (typeof window === 'undefined' || !sectionRef.value) return;
    await nextTick();
    gsap.registerPlugin(ScrollTrigger);

    gsapCtx = gsap.context(() => {
        // Entrance on scroll with fromTo
        const tl = gsap.timeline({
            scrollTrigger: {
                trigger: '.cta-banner',
                start: 'top 85%',
            },
        });

        tl.fromTo(
            '.cta-banner',
            { scale: 0.92, y: 40, opacity: 0 },
            {
                scale: 1,
                y: 0,
                opacity: 1,
                duration: 0.7,
                ease: 'back.out(1.4)',
            },
        ).fromTo(
            mascotRef.value,
            { x: 50, scale: 0.8, opacity: 0 },
            {
                x: 0,
                scale: 1,
                opacity: 1,
                duration: 0.8,
                ease: 'back.out(1.6)',
            },
            '-=0.4',
        );

        // Idle floating/cheering animation for mascot
        if (mascotRef.value) {
            gsap.to(mascotRef.value, {
                y: -12,
                duration: 2.2,
                repeat: -1,
                yoyo: true,
                ease: 'sine.inOut',
            });
        }
    }, sectionRef.value);
});

onUnmounted(() => {
    gsapCtx?.revert();
});
</script>

<template>
    <section
        id="cta"
        ref="sectionRef"
        class="overflow-hidden bg-paper-white py-12 lg:py-16"
    >
        <div class="mx-auto max-w-[1200px] px-6">
            <div
                class="cta-banner relative flex min-h-[330px] flex-col items-center justify-between gap-8 overflow-hidden rounded-[32px] border-2 border-b-[6px] border-[#b5f084] bg-sprout-tint p-8 transition-all duration-300 ease-out hover:border-eco-green hover:border-b-eco-green-dark hover:shadow-[0_20px_40px_-10px_rgba(88,204,2,0.3)] lg:flex-row lg:p-12"
            >
                <!-- Text & Action -->
                <div class="z-10 max-w-[650px]">
                    <h2
                        class="mb-4 text-3xl leading-[1.2] font-extrabold tracking-tight text-night-ink sm:text-[48px]"
                    >
                        Bumi lebih baik.<br />Dimulai dari rumahmu.
                    </h2>
                    <p
                        class="mb-8 text-[16px] font-medium text-charcoal sm:text-[17px]"
                    >
                        Yuk, kasih kesempatan kedua untuk sampahmu.
                    </p>
                    <div>
                        <Link href="/register">
                            <AppButton
                                variant="primary"
                                size="lg"
                                class="h-12 px-8 text-[14px]"
                            >
                                DAFTAR GRATIS
                            </AppButton>
                        </Link>
                    </div>
                </div>

                <!-- Mascot Valu Instant ReValue Plus -->
                <div
                    ref="mascotRef"
                    class="relative z-10 flex w-[280px] shrink-0 cursor-default items-center justify-center select-none sm:w-[360px] lg:w-[408px]"
                >
                    <img
                        src="/assets/Maskot/Pose=Instant-ReValuePlus.png"
                        alt="Valu Plus"
                        class="h-auto w-full object-contain drop-shadow-xl filter transition-transform duration-300 hover:scale-105"
                    />
                </div>
            </div>
        </div>
    </section>
</template>
