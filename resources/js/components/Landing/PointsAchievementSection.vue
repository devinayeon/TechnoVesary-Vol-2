<script setup lang="ts">
import { ref, onMounted, onUnmounted, nextTick } from 'vue';
import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

const sectionRef = ref<HTMLElement | null>(null);
const mascotRef = ref<HTMLElement | null>(null);
const leftBadgeRef = ref<HTMLElement | null>(null);
const rightBadgeRef = ref<HTMLElement | null>(null);
const overlayPillRef = ref<HTMLElement | null>(null);
let gsapCtx: gsap.Context | null = null;

onMounted(async () => {
    if (typeof window === 'undefined' || !sectionRef.value) return;
    await nextTick();
    gsap.registerPlugin(ScrollTrigger);

    gsapCtx = gsap.context(() => {
        // Timeline dengan fromTo untuk entrance terjamin
        const tl = gsap.timeline({
            scrollTrigger: {
                trigger: '.points-container',
                start: 'top 82%',
            },
        });

        tl.fromTo(
            '.points-left-content',
            { x: -50, opacity: 0 },
            { x: 0, opacity: 1, duration: 0.8, ease: 'power3.out' },
        )
            .fromTo(
                mascotRef.value,
                { scale: 0.65, opacity: 0, y: 30 },
                {
                    scale: 1,
                    opacity: 1,
                    y: 0,
                    duration: 0.8,
                    ease: 'back.out(1.8)',
                },
                '-=0.5',
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
                    ease: 'back.out(2)',
                },
                '-=0.4',
            )
            .fromTo(
                overlayPillRef.value,
                { y: 35, scale: 0.88, opacity: 0 },
                {
                    y: 0,
                    scale: 1,
                    opacity: 1,
                    duration: 0.6,
                    ease: 'back.out(1.5)',
                },
                '-=0.3',
            );

        // Idle bobbing / celebratory animations
        if (mascotRef.value) {
            gsap.to(mascotRef.value, {
                y: -12,
                duration: 2.2,
                repeat: -1,
                yoyo: true,
                ease: 'sine.inOut',
            });
        }

        if (leftBadgeRef.value) {
            gsap.to(leftBadgeRef.value, {
                y: -8,
                rotation: '+=3',
                duration: 2.8,
                repeat: -1,
                yoyo: true,
                ease: 'sine.inOut',
            });
        }

        if (rightBadgeRef.value) {
            gsap.to(rightBadgeRef.value, {
                y: 10,
                rotation: '-=3',
                duration: 3.1,
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
        ref="sectionRef"
        class="overflow-hidden bg-paper-white py-12 lg:py-16"
    >
        <div class="mx-auto max-w-[1200px] px-6">
            <div
                class="points-container relative flex min-h-[470px] flex-col items-center justify-between gap-10 overflow-hidden rounded-[32px] border-2 border-b-[6px] border-[#dfcdfa] bg-lavender-tint p-8 transition-all duration-300 ease-out hover:border-[#ceb4f7] hover:shadow-[0_20px_40px_-10px_rgba(147,51,234,0.18)] lg:flex-row lg:p-12"
            >
                <!-- Left Content -->
                <div class="points-left-content z-10 max-w-[570px]">
                    <p
                        class="mb-4 text-[13px] font-bold tracking-[1.6px] text-night-ink uppercase"
                    >
                        POIN &amp; PENCAPAIAN
                    </p>
                    <h2
                        class="mb-5 text-3xl leading-[1.2] font-extrabold tracking-tight text-night-ink sm:text-[48px]"
                    >
                        Kebiasaan baikmu<br />layak dirayakan.
                    </h2>
                    <p
                        class="mb-6 text-[16px] leading-relaxed font-medium text-charcoal sm:text-[17px]"
                    >
                        Setiap sampah yang ditimbang punya nilai. Kumpulkan poin
                        dari setoranmu dan buka badge di setiap pencapaian.
                    </p>
                    <p
                        class="mb-6 text-[18px] font-bold text-night-ink sm:text-[19px]"
                    >
                        5, 10, 25 kali setor. Terus bertumbuh!
                    </p>
                    <a
                        href="#"
                        class="group inline-flex items-center gap-2 text-[15px] font-bold tracking-wider text-night-ink uppercase transition-colors hover:text-eco-green"
                    >
                        Kenali hadiahmu
                        <span
                            class="transition-transform group-hover:translate-x-1"
                            >→</span
                        >
                    </a>
                </div>

                <!-- Right Illustration & Badges -->
                <div
                    class="relative flex h-[380px] w-full items-center justify-center select-none sm:h-[400px] lg:w-[500px]"
                >
                    <!-- Shield Badge Milestone 1 (Rotated) -->
                    <div
                        ref="leftBadgeRef"
                        class="absolute top-2 left-4 z-10 rotate-[9.15deg] transform cursor-default transition-transform duration-300 hover:rotate-0"
                    >
                        <div class="relative h-[128px] w-[112px]">
                            <img
                                src="/assets/Badge/Badge=Sultan Mager, Tier=Sky.png"
                                alt="Sultan Mager"
                                class="absolute inset-0 h-full w-full object-contain drop-shadow-md filter"
                            />
                        </div>
                    </div>

                    <!-- Central Mascot Valu Celebrate Coins -->
                    <div
                        ref="mascotRef"
                        class="relative z-20 size-[280px] cursor-default sm:size-[308px]"
                    >
                        <img
                            src="/assets/Maskot/Pose=Celebrate-Coins.png"
                            alt="Valu Celebrate"
                            class="h-full w-full object-contain drop-shadow-xl filter transition-transform duration-300 hover:scale-105"
                        />
                    </div>

                    <!-- Shield Badge Milestone 25 (Gold) -->
                    <div
                        ref="rightBadgeRef"
                        class="absolute top-20 right-4 z-10 -rotate-[6deg] transform cursor-default transition-transform duration-300 hover:rotate-0"
                    >
                        <div class="relative h-[126px] w-[112px]">
                            <img
                                src="/assets/Badge/Badge=Pejuang 25 Order, Tier=Gold.png"
                                alt="Pejuang 25 Order"
                                class="absolute inset-0 h-full w-full object-contain drop-shadow-md filter"
                            />
                        </div>
                    </div>

                    <!-- Overlay Pill: SETORAN SELESAI -->
                    <div
                        ref="overlayPillRef"
                        class="absolute bottom-2 left-1/2 z-30 w-[280px] -translate-x-1/2 cursor-default rounded-2xl border-2 border-b-[4px] border-border-gray bg-paper-white p-4 transition-all duration-300 ease-out hover:-translate-y-1 hover:border-b-[4px] hover:border-eco-green hover:border-b-eco-green-dark hover:shadow-lg sm:w-[292px]"
                    >
                        <p
                            class="mb-0.5 text-[12px] font-bold tracking-wide text-charcoal sm:text-[13px]"
                        >
                            SETORAN SELESAI
                        </p>
                        <p
                            class="text-[17px] leading-tight font-bold text-night-ink sm:text-[19px]"
                        >
                            Poin masuk ke saldomu!
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
