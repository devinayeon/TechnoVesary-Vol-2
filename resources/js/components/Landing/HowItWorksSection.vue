<script setup lang="ts">
import { ref, onMounted, onUnmounted, nextTick } from "vue";
import gsap from "gsap";
import { ScrollTrigger } from "gsap/ScrollTrigger";

const sectionRef = ref<HTMLElement | null>(null);
let gsapCtx: gsap.Context | null = null;

const steps = [
    {
        num: "01",
        title: "Pilah sampahmu",
        desc: "Pisahkan plastik, kertas, logam, dan kaca. Pastikan bersih dan kering, ya.",
        bg: "bg-night-ink",
        numColor: "text-paper-white",
        titleColor: "text-paper-white",
        descColor: "text-paper-white/90",
        illustration: "/assets/Maskot/Pose=Sorting-Helper.png",
    },
    {
        num: "02",
        title: "Kami jemput ke rumah",
        desc: "Aktifkan langganan, tentukan titik jemput, lalu temukan driver terdekat.",
        bg: "bg-silver-tier",
        numColor: "text-night-ink",
        titleColor: "text-night-ink",
        descColor: "text-charcoal",
        illustration: "/assets/Maskot/Pose=Pickup-Sprint.png",
    },
    {
        num: "03",
        title: "Jadi poin, jadi berarti",
        desc: "Sampah ditimbang per kategori. Poin masuk setelah penjemputan selesai.",
        bg: "bg-sunlight-gold",
        numColor: "text-night-ink",
        titleColor: "text-night-ink",
        descColor: "text-charcoal",
        illustration: "/assets/Maskot/Pose=Celebrate-Coins.png",
    },
];

onMounted(async () => {
    if (typeof window === "undefined" || !sectionRef.value) return;
    await nextTick();
    gsap.registerPlugin(ScrollTrigger);

    gsapCtx = gsap.context(() => {
        const root = sectionRef.value;
        if (!root) return;

        // Entrance animation untuk Header
        const header = root.querySelector(".how-header");
        if (header) {
            gsap.fromTo(
                header,
                { y: 30, opacity: 0 },
                {
                    y: 0,
                    opacity: 1,
                    duration: 0.7,
                    ease: "power3.out",
                    scrollTrigger: {
                        trigger: header,
                        start: "top 85%",
                        toggleActions: "play none none none",
                    },
                },
            );
        }

        // Entrance animation untuk 3 Kartu Langkah
        const cards = root.querySelectorAll(".how-step-card");
        if (cards.length > 0) {
            gsap.fromTo(
                cards,
                { y: 55, opacity: 0, scale: 0.94 },
                {
                    y: 0,
                    opacity: 1,
                    scale: 1,
                    duration: 0.8,
                    stagger: 0.16,
                    ease: "back.out(1.3)",
                    scrollTrigger: {
                        trigger: cards[0],
                        start: "top 85%",
                        toggleActions: "play none none none",
                    },
                },
            );
        }

        // Idle floating/breathing animation for mascots
        const mascots = root.querySelectorAll(".how-mascot-img");
        if (mascots.length > 0) {
            gsap.to(mascots, {
                y: -10,
                duration: 2.2,
                repeat: -1,
                yoyo: true,
                stagger: 0.25,
                ease: "sine.inOut",
            });
        }
    }, sectionRef.value); // Gunakan sectionRef.value (DOM element nyata)
});

onUnmounted(() => {
    gsapCtx?.revert();
});
</script>

<template>
    <section
        id="cara-kerja"
        ref="sectionRef"
        class="bg-paper-white py-16 lg:py-20 overflow-hidden scroll-mt-28"
    >
        <div class="max-w-[1200px] mx-auto px-6">
            <!-- Header -->
            <div class="how-header text-center mb-12">
                <p
                    class="font-bold text-[13px] tracking-[1.6px] uppercase text-charcoal mb-3"
                >
                    DARI RUMAH, UNTUK BUMI
                </p>
                <h2
                    class="font-extrabold text-3xl sm:text-5xl text-night-ink tracking-tight"
                >
                    Berbuat baik, nggak perlu ribet.
                </h2>
            </div>

            <!-- 3 Step Cards Grid -->
            <div class="how-cards-grid grid grid-cols-1 md:grid-cols-3 gap-6">
                <div
                    v-for="step in steps"
                    :key="step.num"
                    class="how-step-card group rounded-[28px] p-7 flex flex-col min-h-[400px] border-2 border-transparent transition-all duration-300 ease-out hover:-translate-y-2.5 cursor-default"
                    :class="[
                        step.bg,
                        step.num === '01' ? 'hover:shadow-[0_20px_40px_-10px_rgba(0,4,55,0.4)] hover:border-night-ink' : '',
                        step.num === '02' ? 'hover:shadow-[0_20px_40px_-10px_rgba(184,196,208,0.7)] hover:border-[#9ba9b7]' : '',
                        step.num === '03' ? 'hover:shadow-[0_20px_40px_-10px_rgba(255,200,0,0.5)] hover:border-[#e0b000]' : '',
                    ]"
                >
                    <!-- Step Number -->
                    <div
                        class="font-bold text-[19px] mb-4 transition-transform duration-300 group-hover:scale-105"
                        :class="step.numColor"
                    >
                        {{ step.num }}
                    </div>

                    <!-- Valu Mascot Illustration -->
                    <div
                        class="size-40 mx-auto my-auto flex items-center justify-center"
                    >
                        <img
                            :src="step.illustration"
                            :alt="step.title"
                            class="how-mascot-img w-full h-full object-contain filter drop-shadow-md transition-all duration-300 ease-out group-hover:scale-110 group-hover:-rotate-3"
                        />
                    </div>

                    <!-- Content -->
                    <div class="mt-auto pt-4">
                        <h3
                            class="font-bold text-[24px] sm:text-[26px] tracking-tight mb-2 leading-tight transition-transform duration-300 group-hover:translate-x-0.5"
                            :class="step.titleColor"
                        >
                            {{ step.title }}
                        </h3>
                        <p
                            class="font-medium text-[16px] sm:text-[17px] leading-relaxed"
                            :class="step.descColor"
                        >
                            {{ step.desc }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
