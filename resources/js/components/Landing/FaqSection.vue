<script setup lang="ts">
import { ref, onMounted, onUnmounted, nextTick } from 'vue';
import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

const openIndex = ref<number | null>(0);
const sectionRef = ref<HTMLElement | null>(null);
let gsapCtx: gsap.Context | null = null;

const faqs = [
    {
        question: 'Bagaimana poin saya dihitung?',
        answer: 'Berat sampah per kategori dikalikan nilai poin per kilogram. Kamu bisa melihat rincian hasil timbang setelah penjemputan selesai.',
    },
    {
        question: 'Apakah harus berlangganan?',
        answer: 'Berlangganan diperlukan untuk layanan penjemputan berkala dari rumah. Kamu bisa mulai dari paket 1 bulan untuk mencoba kepraktisannya.',
    },
    {
        question: 'Poin bisa dipakai untuk apa?',
        answer: 'Poin bisa ditukarkan dengan produk karya pengrajin lokal di Upcycle Store atau digunakan untuk memperpanjang paket langgananmu.',
    },
    {
        question: 'Kapan poin masuk ke saldo?',
        answer: 'Poin langsung masuk otomatis ke akunmu begitu driver menyelesaikan penimbangan di tempat dan mengonfirmasi setoran.',
    },
];

function toggle(index: number) {
    openIndex.value = openIndex.value === index ? null : index;
}

onMounted(async () => {
    if (typeof window === 'undefined' || !sectionRef.value) return;
    await nextTick();
    gsap.registerPlugin(ScrollTrigger);

    gsapCtx = gsap.context(() => {
        const root = sectionRef.value;
        if (!root) return;

        const items = root.querySelectorAll('.faq-item');
        if (items.length > 0) {
            gsap.fromTo(
                items,
                { y: 35, opacity: 0 },
                {
                    y: 0,
                    opacity: 1,
                    duration: 0.6,
                    stagger: 0.1,
                    ease: 'power3.out',
                    scrollTrigger: {
                        trigger: items[0],
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
        id="faq"
        ref="sectionRef"
        class="scroll-mt-28 overflow-hidden bg-paper-white py-16 lg:py-20"
    >
        <div class="mx-auto max-w-[1200px] px-6">
            <div class="grid grid-cols-1 items-start gap-12 lg:grid-cols-12">
                <!-- Left Side -->
                <div class="lg:col-span-5">
                    <p
                        class="mb-3 text-[13px] font-bold tracking-[1.6px] text-charcoal uppercase"
                    >
                        TANYA REVALUE
                    </p>
                    <h2
                        class="mb-5 text-3xl leading-[1.2] font-extrabold tracking-tight text-night-ink sm:text-5xl"
                    >
                        Masih<br />penasaran?
                    </h2>
                    <p
                        class="mb-6 text-[16px] font-medium text-charcoal sm:text-[17px]"
                    >
                        Kami bantu mulai dari hal kecil.
                    </p>
                    <a
                        href="#"
                        class="group inline-flex items-center gap-2 text-[15px] font-bold tracking-wider text-night-ink uppercase transition-colors hover:text-eco-green"
                    >
                        Hubungi kami
                        <span
                            class="transition-transform group-hover:translate-x-1"
                            >→</span
                        >
                    </a>
                </div>

                <!-- Right Side: Accordion -->
                <div class="faq-list flex flex-col gap-3.5 lg:col-span-7">
                    <div
                        v-for="(faq, index) in faqs"
                        :key="faq.question"
                        class="faq-item group cursor-pointer overflow-hidden rounded-2xl border-2 border-b-[4px] border-border-gray bg-cloud-surface transition-all duration-300 ease-out hover:-translate-y-0.5 hover:border-night-ink/30 hover:border-b-night-ink/50 hover:shadow-md"
                        :class="
                            openIndex === index
                                ? 'border-eco-green border-b-eco-green-dark bg-paper-white shadow-sm'
                                : ''
                        "
                    >
                        <button
                            type="button"
                            class="flex w-full cursor-pointer items-center justify-between gap-4 px-6 py-5 text-left text-[17px] font-bold text-night-ink select-none sm:text-[18px]"
                            @click="toggle(index)"
                        >
                            <span
                                class="transition-colors duration-200 group-hover:text-night-ink"
                                >{{ faq.question }}</span
                            >
                            <span
                                class="flex size-8.5 shrink-0 items-center justify-center rounded-full border-2 border-b-[3px] border-border-gray bg-paper-white text-lg font-bold transition-all duration-300 group-hover:border-eco-green"
                                :class="
                                    openIndex === index
                                        ? 'rotate-45 border-eco-green bg-sprout-tint text-eco-green-dark'
                                        : 'group-hover:bg-sprout-tint/50'
                                "
                            >
                                +
                            </span>
                        </button>
                        <div
                            v-show="openIndex === index"
                            class="animate-in fade-in border-t border-border-gray/40 px-6 pt-1 pb-5 text-[15px] leading-relaxed font-medium text-charcoal duration-200 sm:text-[16px]"
                        >
                            {{ faq.answer }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
