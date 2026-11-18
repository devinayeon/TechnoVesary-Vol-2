<script setup lang="ts">
import { ref, onMounted, onUnmounted, nextTick } from 'vue';
import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

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
    plastik: '/assets/illustration/Category=Plastik.png',
    kertas: '/assets/illustration/Category=Kardus.png',
    karton: '/assets/illustration/Category=Kardus.png',
    logam: '/assets/illustration/Category=Logam.png',
    kaleng: '/assets/illustration/Category=Plastik.png',
    kaca: '/assets/illustration/Category=Kaca.png',
    sachet: '/assets/illustration/Category=Sachet.png',
};

function getCategoryIcon(name: string): string {
    const lower = name.toLowerCase();
    for (const [key, icon] of Object.entries(categoryIcons)) {
        if (lower.includes(key)) return icon;
    }
    return '/images/landing/waste-plastic.svg';
}

onMounted(async () => {
    if (typeof window === 'undefined' || !sectionRef.value) return;
    await nextTick();
    gsap.registerPlugin(ScrollTrigger);

    gsapCtx = gsap.context(() => {
        const root = sectionRef.value;
        if (!root) return;

        const cards = root.querySelectorAll('.waste-cat-card');
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
                    ease: 'back.out(1.5)',
                    scrollTrigger: {
                        trigger: cards[0],
                        start: 'top 85%',
                        toggleActions: 'play none none none',
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
    <section
        id="jenis-sampah"
        ref="sectionRef"
        class="overflow-hidden bg-paper-white py-12 lg:py-16"
    >
        <div class="mx-auto max-w-[1200px] px-6">
            <!-- Header -->
            <div
                class="mb-8 flex flex-col justify-between gap-3 sm:flex-row sm:items-center"
            >
                <h2
                    class="text-2xl font-bold tracking-tight text-night-ink sm:text-[32px]"
                >
                    Beda jenis, sama berharganya.
                </h2>
                <a
                    href="#"
                    class="group inline-flex items-center gap-1.5 text-[15px] font-bold tracking-wider text-night-ink uppercase transition-colors hover:text-eco-green"
                >
                    Panduan memilah
                    <span class="transition-transform group-hover:translate-x-1"
                        >→</span
                    >
                </a>
            </div>

            <!-- Categories Grid -->
            <div
                class="waste-cat-grid grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4"
            >
                <div
                    v-for="cat in categories"
                    :key="cat.id"
                    class="waste-cat-card group flex cursor-default items-center gap-4 rounded-[20px] border-2 border-b-[4px] border-border-gray/80 bg-cloud-surface p-4 transition-all duration-300 ease-out hover:-translate-y-1.5 hover:border-b-[4px] hover:border-eco-green hover:border-b-eco-green-dark hover:bg-paper-white hover:shadow-[0_12px_24px_-6px_rgba(88,204,2,0.25)]"
                >
                    <!-- Squircle Sticker Icon -->
                    <div
                        class="flex size-[72px] shrink-0 items-center justify-center transition-transform duration-300 ease-out group-hover:scale-110 group-hover:rotate-6"
                    >
                        <img
                            :src="getCategoryIcon(cat.name)"
                            :alt="cat.name"
                            class="h-full w-full object-contain drop-shadow-xs filter"
                        />
                    </div>

                    <!-- Name & Points -->
                    <div>
                        <h3
                            class="text-[18px] leading-tight font-bold text-night-ink transition-colors duration-200 sm:text-[19px]"
                        >
                            {{ cat.name }}
                        </h3>
                        <p
                            class="mt-1 text-[14px] font-extrabold text-eco-green-dark transition-transform duration-200 group-hover:translate-x-0.5"
                        >
                            {{ cat.points_per_kg.toLocaleString('id-ID') }} Poin
                            / Kg
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
