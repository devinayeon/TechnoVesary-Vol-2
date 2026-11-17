<script setup lang="ts">
import { ref, onMounted, onUnmounted, nextTick } from "vue";
import gsap from "gsap";
import { ScrollTrigger } from "gsap/ScrollTrigger";

const sectionRef = ref<HTMLElement | null>(null);
const mascotRef = ref<HTMLElement | null>(null);
const leftBadgeRef = ref<HTMLElement | null>(null);
const rightBadgeRef = ref<HTMLElement | null>(null);
const overlayPillRef = ref<HTMLElement | null>(null);
let gsapCtx: gsap.Context | null = null;

onMounted(async () => {
    if (typeof window === "undefined" || !sectionRef.value) return;
    await nextTick();
    gsap.registerPlugin(ScrollTrigger);

    gsapCtx = gsap.context(() => {
        // Timeline dengan fromTo untuk entrance terjamin
        const tl = gsap.timeline({
            scrollTrigger: {
                trigger: ".points-container",
                start: "top 82%",
            },
        });

        tl.fromTo(
            ".points-left-content",
            { x: -50, opacity: 0 },
            { x: 0, opacity: 1, duration: 0.8, ease: "power3.out" },
        )
            .fromTo(
                mascotRef.value,
                { scale: 0.65, opacity: 0, y: 30 },
                { scale: 1, opacity: 1, y: 0, duration: 0.8, ease: "back.out(1.8)" },
                "-=0.5",
            )
            .fromTo(
                [leftBadgeRef.value, rightBadgeRef.value],
                { scale: 0, opacity: 0, rotation: -12 },
                {
                    scale: 1,
                    opacity: 1,
                    rotation: 0,
                    duration: 0.6,
                    stagger: 0.15,
                    ease: "back.out(2)",
                },
                "-=0.4",
            )
            .fromTo(
                overlayPillRef.value,
                { y: 35, scale: 0.88, opacity: 0 },
                {
                    y: 0,
                    scale: 1,
                    opacity: 1,
                    duration: 0.6,
                    ease: "back.out(1.5)",
                },
                "-=0.3",
            );

        // Idle bobbing / celebratory animations
        if (mascotRef.value) {
            gsap.to(mascotRef.value, {
                y: -12,
                duration: 2.2,
                repeat: -1,
                yoyo: true,
                ease: "sine.inOut",
            });
        }

        if (leftBadgeRef.value) {
            gsap.to(leftBadgeRef.value, {
                y: -8,
                rotation: "+=3",
                duration: 2.8,
                repeat: -1,
                yoyo: true,
                ease: "sine.inOut",
            });
        }

        if (rightBadgeRef.value) {
            gsap.to(rightBadgeRef.value, {
                y: 10,
                rotation: "-=3",
                duration: 3.1,
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
    <section ref="sectionRef" class="bg-paper-white py-12 lg:py-16 overflow-hidden">
        <div class="max-w-[1200px] mx-auto px-6">
            <div
                class="points-container bg-lavender-tint rounded-[32px] p-8 lg:p-12 min-h-[470px] relative overflow-hidden flex flex-col lg:flex-row items-center justify-between gap-10 hover:shadow-md transition-shadow"
            >
                <!-- Left Content -->
                <div class="points-left-content max-w-[570px] z-10">
                    <p
                        class="font-bold text-[13px] tracking-[1.6px] uppercase text-night-ink mb-4"
                    >
                        POIN &amp; PENCAPAIAN
                    </p>
                    <h2
                        class="font-extrabold text-3xl sm:text-[48px] text-night-ink leading-[1.2] tracking-tight mb-5"
                    >
                        Kebiasaan baikmu<br />layak dirayakan.
                    </h2>
                    <p
                        class="font-medium text-[16px] sm:text-[17px] text-charcoal leading-relaxed mb-6"
                    >
                        Setiap sampah yang ditimbang punya nilai. Kumpulkan poin
                        dari setoranmu dan buka badge di setiap pencapaian.
                    </p>
                    <p
                        class="font-bold text-[18px] sm:text-[19px] text-night-ink mb-6"
                    >
                        5, 10, 25 kali setor. Terus bertumbuh!
                    </p>
                    <a
                        href="#"
                        class="font-bold text-[15px] tracking-wider uppercase text-night-ink hover:text-eco-green transition-colors inline-flex items-center gap-2 group"
                    >
                        Kenali hadiahmu <span class="transition-transform group-hover:translate-x-1">→</span>
                    </a>
                </div>

                <!-- Right Illustration & Badges -->
                <div
                    class="relative w-full lg:w-[500px] h-[380px] sm:h-[400px] flex items-center justify-center select-none"
                >
                    <!-- Shield Badge Milestone 1 (Rotated) -->
                    <div
                        ref="leftBadgeRef"
                        class="absolute left-4 top-2 transform rotate-[9.15deg] transition-transform duration-300 hover:rotate-0 z-10 cursor-default"
                    >
                        <div class="relative w-[112px] h-[128px]">
                            <img
                                src="/assets/Badge/Badge=Sultan Mager, Tier=Sky.png"
                                alt="Sultan Mager"
                                class="absolute inset-0 w-full h-full object-contain filter drop-shadow-md"
                            />
                        </div>
                    </div>

                    <!-- Central Mascot Valu Celebrate Coins -->
                    <div ref="mascotRef" class="relative size-[280px] sm:size-[308px] z-20 cursor-default">
                        <img
                            src="/assets/Maskot/Pose=Celebrate-Coins.png"
                            alt="Valu Celebrate"
                            class="w-full h-full object-contain filter drop-shadow-xl transition-transform duration-300 hover:scale-105"
                        />
                    </div>

                    <!-- Shield Badge Milestone 25 (Gold) -->
                    <div
                        ref="rightBadgeRef"
                        class="absolute right-4 top-20 transform -rotate-[6deg] transition-transform duration-300 hover:rotate-0 z-10 cursor-default"
                    >
                        <div class="relative w-[112px] h-[126px]">
                            <img
                                src="/assets/Badge/Badge=Pejuang 25 Order, Tier=Gold.png"
                                alt="Pejuang 25 Order"
                                class="absolute inset-0 w-full h-full object-contain filter drop-shadow-md"
                            />
                        </div>
                    </div>

                    <!-- Overlay Pill: SETORAN SELESAI -->
                    <div
                        ref="overlayPillRef"
                        class="absolute bottom-2 left-1/2 -translate-x-1/2 z-30 w-[280px] sm:w-[292px] bg-paper-white rounded-2xl p-4 shadow-md border border-border-gray/50 hover:shadow-xl transition-all cursor-default"
                    >
                        <p
                            class="font-bold text-[12px] sm:text-[13px] tracking-wide text-charcoal mb-0.5"
                        >
                            SETORAN SELESAI
                        </p>
                        <p
                            class="font-bold text-[17px] sm:text-[19px] text-night-ink leading-tight"
                        >
                            Poin masuk ke saldomu!
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
