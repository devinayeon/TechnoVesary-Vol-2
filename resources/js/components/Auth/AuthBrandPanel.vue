<script setup lang="ts">
import { ref, onMounted, onUnmounted } from "vue";
import { Link } from "@inertiajs/vue3";
import gsap from "gsap";

const mascotRef = ref<HTMLElement | null>(null);
let gsapCtx: gsap.Context | null = null;

onMounted(() => {
    if (typeof window === "undefined" || !mascotRef.value) return;

    gsapCtx = gsap.context(() => {
        // Idle gentle floating animation untuk maskot Valu
        gsap.to(mascotRef.value, {
            y: "-=8",
            duration: 2.8,
            repeat: -1,
            yoyo: true,
            ease: "sine.inOut",
        });
    }, mascotRef.value);
});

onUnmounted(() => {
    gsapCtx?.revert();
});
</script>

<template>
    <div
        class="relative hidden lg:flex flex-col justify-between w-[480px] xl:w-[540px] 2xl:w-[580px] min-h-screen h-full bg-sky-tint p-8 xl:p-12 overflow-hidden shrink-0 select-none border-r-2 border-border-gray/50 lg:rounded-r-[36px] xl:rounded-r-[40px] shadow-xs"
    >
        <!-- Background Landscape Illustration (Full Edge-to-Edge) -->
        <img
            src="/assets/background/Landscape · Background.png"
            alt=""
            class="absolute inset-0 w-full h-full object-cover object-bottom pointer-events-none"
        />

        <!-- Top Section: Logo & Brand Story -->
        <div class="relative z-10">
            <!-- Brand Logo Lockup -->
            <Link href="/" class="inline-flex items-center gap-2.5 group">
                <img
                    src="/assets/Logo/logo_hijau_biru.png"
                    alt="ReValue"
                    class="w-11 h-11 object-contain transition-transform duration-200 group-hover:scale-105"
                />
                <span
                    class="text-[30px] xl:text-[32px] tracking-tight font-extrabold leading-none"
                >
                    <span class="text-night-ink">Re</span
                    ><span class="text-eco-green-dark">Value</span>
                </span>
            </Link>

            <!-- Brand Story Headline -->
            <div class="mt-6">
                <h2
                    class="font-extrabold text-[34px] xl:text-[40px] text-night-ink leading-[1.2] tracking-tight mb-3"
                >
                    Langkah kecilmu,<br />
                    berarti untuk bumi.
                </h2>
                <p
                    class="font-medium text-[15px] xl:text-[17px] text-charcoal leading-relaxed max-w-[360px]"
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
                class="w-[280px] xl:w-[320px] object-contain filter drop-shadow-[0_12px_24px_rgba(0,4,55,0.15)] transition-transform duration-300 hover:scale-105"
            />
        </div>

        <!-- Foreground Leaves Overlay -->
        <img
            src="/assets/background/Landscape · Foreground leaves.png"
            alt=""
            class="absolute bottom-0 left-0 right-0 w-full pointer-events-none z-15 object-contain object-bottom"
        />

        <!-- Bottom Promise Card -->
        <div
            class="relative z-20 bg-paper-white/95 backdrop-blur-xs border-2 border-b-[4px] border-border-gray rounded-2xl p-4 shadow-sm"
        >
            <p class="font-bold text-[14px] text-night-ink">
                Dijemput dari rumah. Ditimbang transparan.
            </p>
            <p class="font-medium text-[13px] text-charcoal mt-0.5">
                Kebiasaan baik dimulai dari satu langkah.
            </p>
        </div>
    </div>
</template>
