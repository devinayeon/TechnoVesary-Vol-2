<script setup lang="ts">
import { ref, onMounted, onUnmounted, nextTick } from "vue";
import gsap from "gsap";
import { ScrollTrigger } from "gsap/ScrollTrigger";

interface WasteCategory {
    id: number;
    name: string;
    description: string;
    points_per_kg: number;
}

const props = defineProps<{ categories: WasteCategory[] }>();

const sectionRef = ref<HTMLElement | null>(null);
let gsapCtx: gsap.Context | null = null;

const categoryIcons: Record<string, string> = {
    plastik: "/assets/illustration/Category=Plastik.png",
    kertas: "/assets/illustration/Category=Kardus.png",
    karton: "/assets/illustration/Category=Kardus.png",
    logam: "/assets/illustration/Category=Logam.png",
    kaleng: "/assets/illustration/Category=Plastik.png",
    kaca: "/assets/illustration/Category=Kaca.png",
    sachet: "/assets/illustration/Category=Sachet.png",
};

function getCategoryIcon(name: string): string {
    const lower = name.toLowerCase();
    for (const [key, icon] of Object.entries(categoryIcons)) {
        if (lower.includes(key)) return icon;
    }
    return "/images/landing/waste-plastic.svg";
}

onMounted(async () => {
    if (typeof window === "undefined" || !sectionRef.value) return;
    await nextTick();
    gsap.registerPlugin(ScrollTrigger);

    gsapCtx = gsap.context(() => {
        const root = sectionRef.value;
        if (!root) return;

        const cards = root.querySelectorAll(".waste-cat-card");
        if (cards.length > 0) {
            gsap.fromTo(
                cards,
                { scale: 0.82, y: 40, opacity: 0 },
                {
                    scale: 1,
                    y: 0,
                    opacity: 1,
                    duration: 0.6,
                    stagger: 0.08,
                    ease: "back.out(1.5)",
                    scrollTrigger: {
                        trigger: cards[0],
                        start: "top 85%",
                        toggleActions: "play none none none",
                    },
                },
            );
        }
    }, sectionRef.value);
});

onUnmounted(() => {
    gsapCtx?.revert();
});
</script>

<template>
    <section id="jenis-sampah" ref="sectionRef" class="bg-paper-white py-12 lg:py-16 overflow-hidden">
        <div class="max-w-[1200px] mx-auto px-6">
            <!-- Header -->
            <div
                class="flex flex-col sm:flex-row sm:items-center justify-between mb-8 gap-3"
            >
                <h2
                    class="font-bold text-2xl sm:text-[32px] text-night-ink tracking-tight"
                >
                    Beda jenis, sama berharganya.
                </h2>
                <a
                    href="#"
                    class="font-bold text-[15px] tracking-wider uppercase text-night-ink hover:text-eco-green transition-colors inline-flex items-center gap-1.5 group"
                >
                    Panduan memilah <span class="transition-transform group-hover:translate-x-1">→</span>
                </a>
            </div>

            <!-- Categories Grid -->
            <div class="waste-cat-grid grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <div
                    v-for="cat in categories"
                    :key="cat.id"
                    class="waste-cat-card bg-cloud-surface rounded-[20px] p-4 flex items-center gap-4 hover:-translate-y-1 hover:shadow-md hover:bg-white hover:border hover:border-border-gray transition-all duration-200 cursor-default"
                >
                    <!-- Squircle Sticker Icon -->
                    <div
                        class="size-[72px] shrink-0 flex items-center justify-center transition-transform duration-200 hover:rotate-6"
                    >
                        <img
                            :src="getCategoryIcon(cat.name)"
                            :alt="cat.name"
                            class="w-full h-full object-contain filter drop-shadow-xs"
                        />
                    </div>

                    <!-- Name & Points -->
                    <div>
                        <h3
                            class="font-bold text-[18px] sm:text-[19px] text-night-ink leading-tight"
                        >
                            {{ cat.name }}
                        </h3>
                        <p
                            class="font-extrabold text-[14px] text-eco-green-dark mt-1"
                        >
                            {{ cat.points_per_kg.toLocaleString("id-ID") }} Poin
                            / Kg
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
