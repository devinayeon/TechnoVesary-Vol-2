<script setup lang="ts">
import { ref, onMounted, onUnmounted, nextTick } from 'vue';
import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

const sectionRef = ref<HTMLElement | null>(null);
let gsapCtx: gsap.Context | null = null;

const testimonials = [
    {
        id: 1,
        avatar: '/assets/placeholder/Ilustrasi Bima.png',
        name: 'Bima',
        role: 'Rumah tangga',
        quote: 'Dulu botol dan kardus menumpuk di rumah. Sekarang tiap minggu disetorkan dan saldo poin terus bertambah. Nggak nyangka bisa sebermanfaat ini.',
    },
    {
        id: 2,
        avatar: '/assets/placeholder/Ilustrasi Ibu Sari.png',
        name: 'Ibu Sari',
        role: 'Pengrajin lokal',
        quote: 'Senang melihat bahan bekas punya cerita baru. Produk upcycle saya laris, dan sampah berkurang dari sumbernya.',
    },
    {
        id: 3,
        avatar: '/assets/placeholder/Ilustrasi Maya.png',
        name: 'Maya',
        role: 'Kebiasaan berkelanjutan',
        quote: 'Setiap setoran terasa lebih berarti. Ada angka nyata di saldo poin yang bisa saya tukarkan kapan saja.',
    },
];

onMounted(async () => {
    if (typeof window === 'undefined' || !sectionRef.value) return;
    await nextTick();
    gsap.registerPlugin(ScrollTrigger);

    gsapCtx = gsap.context(() => {
        const root = sectionRef.value;
        if (!root) return;

        const cards = root.querySelectorAll('.testi-card');
        if (cards.length > 0) {
            gsap.fromTo(
                cards,
                { y: 55, opacity: 0, scale: 0.94 },
                {
                    y: 0,
                    opacity: 1,
                    scale: 1,
                    duration: 0.7,
                    stagger: 0.16,
                    ease: 'back.out(1.3)',
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
        id="testimoni"
        ref="sectionRef"
        class="overflow-hidden bg-paper-white py-16 lg:py-20"
    >
        <div class="mx-auto max-w-[1200px] px-6">
            <!-- Header -->
            <div class="mb-12 text-center">
                <p
                    class="mb-3 text-[13px] font-bold tracking-[1.6px] text-charcoal uppercase"
                >
                    CERITA TEMAN REVALUE
                </p>
                <h2
                    class="mb-4 text-3xl font-extrabold tracking-tight text-night-ink sm:text-5xl"
                >
                    Cerita baik, berawal dari rumah.
                </h2>
                <p
                    class="mx-auto max-w-2xl text-[16px] font-medium text-charcoal sm:text-[17px]"
                >
                    Contoh testimoni untuk menggambarkan pengalaman customer
                    kami.
                </p>
            </div>

            <!-- Testimonial Cards -->
            <div class="testi-grid grid grid-cols-1 gap-6 md:grid-cols-3">
                <div
                    v-for="t in testimonials"
                    :key="t.id"
                    class="testi-card group flex cursor-default flex-col justify-between rounded-[28px] border-2 border-b-[5px] border-border-gray bg-paper-white p-7 transition-all duration-300 ease-out hover:-translate-y-2 hover:border-b-[5px] hover:border-sunlight-gold hover:border-b-[#d9a700] hover:shadow-[0_16px_36px_-8px_rgba(255,200,0,0.28)]"
                >
                    <div>
                        <span
                            class="mb-4 inline-block text-[12px] font-bold tracking-[1.6px] text-charcoal uppercase transition-colors group-hover:text-night-ink sm:text-[13px]"
                        >
                            ILUSTRASI PENGALAMAN
                        </span>
                        <p
                            class="mb-6 text-[16px] leading-relaxed font-medium text-charcoal italic sm:text-[17px]"
                        >
                            “{{ t.quote }}”
                        </p>
                    </div>

                    <div
                        class="flex items-center gap-3 border-t border-border-gray/70 pt-4"
                    >
                        <img
                            :src="t.avatar"
                            :alt="t.name"
                            class="h-12 w-12 shrink-0 rounded-full border border-border-gray bg-cloud-surface object-cover transition-transform duration-300 ease-out group-hover:scale-110 group-hover:rotate-6"
                        />

                        <div>
                            <p
                                class="text-[16px] leading-tight font-bold text-night-ink"
                            >
                                {{ t.name }}
                            </p>
                            <p class="text-[13px] font-medium text-charcoal">
                                {{ t.role }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
