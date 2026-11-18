<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import gsap from 'gsap';

const mascotRef = ref<HTMLElement | null>(null);
let gsapCtx: gsap.Context | null = null;

onMounted(() => {
    if (typeof window === 'undefined' || !mascotRef.value) return;

    gsapCtx = gsap.context(() => {
        // Idle gentle floating animation untuk maskot Valu
        gsap.to(mascotRef.value, {
            y: '-=8',
            duration: 2.8,
            repeat: -1,
            yoyo: true,
            ease: 'sine.inOut',
        });
    }, mascotRef.value);
});

onUnmounted(() => {
    gsapCtx?.revert();
});
</script>

<template>
    <div
        class="relative hidden h-full min-h-screen w-[480px] shrink-0 flex-col justify-between overflow-hidden border-r-2 border-border-gray/50 bg-sky-tint p-8 shadow-xs select-none lg:flex lg:rounded-r-[36px] xl:w-[540px] xl:rounded-r-[40px] xl:p-12 2xl:w-[580px]"
    >
        <!-- Background Landscape Illustration (Full Edge-to-Edge) -->
        <img
            src="/assets/background/Landscape · Background.png"
            alt=""
            class="pointer-events-none absolute inset-0 h-full w-full object-cover object-bottom"
        />

        <!-- Top Section: Logo & Brand Story -->
        <div class="relative z-10">
            <!-- Brand Logo Lockup -->
            <Link href="/" class="group inline-flex items-center gap-2.5">
                <img
                    src="/assets/Logo/logo_hijau_biru.png"
                    alt="ReValue"
                    class="h-11 w-11 object-contain transition-transform duration-200 group-hover:scale-105"
                />
                <span
                    class="text-[30px] leading-none font-extrabold tracking-tight xl:text-[32px]"
                >
                    <span class="text-night-ink">Re</span
                    ><span class="text-eco-green-dark">Value</span>
                </span>
            </Link>

            <!-- Brand Story Headline -->
            <div class="mt-6">
                <h2
                    class="mb-3 text-[34px] leading-[1.2] font-extrabold tracking-tight text-night-ink xl:text-[40px]"
                >
                    Langkah kecilmu,<br />
                    berarti untuk bumi.
                </h2>
                <p
                    class="max-w-[360px] text-[15px] leading-relaxed font-medium text-charcoal xl:text-[17px]"
                >
                    Pilah dari rumah. Kami jemput,<br />
                    lalu sampahmu jadi lebih berarti.
                </p>
            </div>
        </div>

        <!-- Center Mascot: Valu in the neighborhood with gentle idle floating -->
        <div
            ref="mascotRef"
            class="relative z-10 my-auto flex items-center justify-center py-4"
        >
            <img
                src="/assets/Maskot/Valu · In the neighborhood.png"
                alt="Valu Mascot"
                class="w-[280px] object-contain drop-shadow-[0_12px_24px_rgba(0,4,55,0.15)] filter transition-transform duration-300 hover:scale-105 xl:w-[320px]"
            />
        </div>

        <!-- Foreground Leaves Overlay -->
        <img
            src="/assets/background/Landscape · Foreground leaves.png"
            alt=""
            class="pointer-events-none absolute right-0 bottom-0 left-0 z-15 w-full object-contain object-bottom"
        />

        <!-- Bottom Promise Card -->
        <div
            class="relative z-20 rounded-2xl border-2 border-b-[4px] border-border-gray bg-paper-white/95 p-4 shadow-sm backdrop-blur-xs"
        >
            <p class="text-[14px] font-bold text-night-ink">
                Dijemput dari rumah. Ditimbang transparan.
            </p>
            <p class="mt-0.5 text-[13px] font-medium text-charcoal">
                Kebiasaan baik dimulai dari satu langkah.
            </p>
        </div>
    </div>
</template>
