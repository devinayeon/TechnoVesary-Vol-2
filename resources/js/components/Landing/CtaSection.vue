<script setup lang="ts">
import { ref, onMounted, onUnmounted, nextTick } from "vue";
import { Link } from "@inertiajs/vue3";
import gsap from "gsap";
import { ScrollTrigger } from "gsap/ScrollTrigger";
import AppButton from "./AppButton.vue";

const sectionRef = ref<HTMLElement | null>(null);
const mascotRef = ref<HTMLElement | null>(null);
let gsapCtx: gsap.Context | null = null;

onMounted(async () => {
    if (typeof window === "undefined" || !sectionRef.value) return;
    await nextTick();
    gsap.registerPlugin(ScrollTrigger);

    gsapCtx = gsap.context(() => {
        // Entrance on scroll with fromTo
        const tl = gsap.timeline({
            scrollTrigger: {
                trigger: ".cta-banner",
                start: "top 85%",
            },
        });

        tl.fromTo(
            ".cta-banner",
            { scale: 0.92, y: 40, opacity: 0 },
            {
                scale: 1,
                y: 0,
                opacity: 1,
                duration: 0.7,
                ease: "back.out(1.4)",
            },
        ).fromTo(
            mascotRef.value,
            { x: 50, scale: 0.8, opacity: 0 },
            {
                x: 0,
                scale: 1,
                opacity: 1,
                duration: 0.8,
                ease: "back.out(1.6)",
            },
            "-=0.4",
        );

        // Idle floating/cheering animation for mascot
        if (mascotRef.value) {
            gsap.to(mascotRef.value, {
                y: -12,
                duration: 2.2,
                repeat: -1,
                yoyo: true,
                ease: "sine.inOut",
            });
        }
    }, sectionRef.value);
});

onUnmounted(() => {
    gsapCtx?.revert();
});
</script>

<template>
    <section id="cta" ref="sectionRef" class="bg-paper-white py-12 lg:py-16 overflow-hidden">
        <div class="max-w-[1200px] mx-auto px-6">
            <div
                class="cta-banner bg-sprout-tint rounded-[32px] p-8 lg:p-12 relative overflow-hidden flex flex-col lg:flex-row items-center justify-between gap-8 min-h-[330px] hover:shadow-xl transition-all duration-300"
            >
                <!-- Text & Action -->
                <div class="max-w-[650px] z-10">
                    <h2
                        class="font-extrabold text-3xl sm:text-[48px] text-night-ink leading-[1.2] tracking-tight mb-4"
                    >
                        Bumi lebih baik.<br />Dimulai dari rumahmu.
                    </h2>
                    <p
                        class="font-medium text-[16px] sm:text-[17px] text-charcoal mb-8"
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
                    class="relative w-[280px] sm:w-[360px] lg:w-[408px] shrink-0 z-10 flex items-center justify-center cursor-default select-none"
                >
                    <img
                        src="/assets/Maskot/Pose=Instant-ReValuePlus.png"
                        alt="Valu Plus"
                        class="w-full h-auto object-contain filter drop-shadow-xl transition-transform duration-300 hover:scale-105"
                    />
                </div>
            </div>
        </div>
    </section>
</template>
