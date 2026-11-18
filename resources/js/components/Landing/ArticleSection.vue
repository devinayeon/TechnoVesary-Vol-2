<script setup lang="ts">
import { ref, onMounted, onUnmounted, nextTick } from 'vue';
import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

interface Article {
    id: number;
    title: string;
    excerpt?: string;
    content?: string;
    thumbnail?: string | null;
    photo?: string | null;
    slug: string;
    category?: { id: number; name: string } | null;
}

defineProps<{ articles: Article[] }>();

const sectionRef = ref<HTMLElement | null>(null);
let gsapCtx: gsap.Context | null = null;

onMounted(async () => {
    if (typeof window === 'undefined' || !sectionRef.value) return;
    await nextTick();
    gsap.registerPlugin(ScrollTrigger);

    gsapCtx = gsap.context(() => {
        const root = sectionRef.value;
        if (!root) return;

        const cards = root.querySelectorAll('.article-bento-card');
        if (cards.length > 0) {
            gsap.fromTo(
                cards,
                { y: 55, opacity: 0, scale: 0.95 },
                {
                    y: 0,
                    opacity: 1,
                    scale: 1,
                    duration: 0.7,
                    stagger: 0.12,
                    ease: 'power3.out',
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
        id="artikel"
        ref="sectionRef"
        class="scroll-mt-28 overflow-hidden bg-cloud-surface py-16 lg:py-20"
    >
        <div class="mx-auto max-w-[1200px] px-6">
            <!-- Header -->
            <div
                class="mb-4 flex flex-col justify-between gap-3 sm:flex-row sm:items-center"
            >
                <h2
                    class="text-3xl font-extrabold tracking-tight text-night-ink sm:text-5xl"
                >
                    Mulai dari tahu, jadi kebiasaan.
                </h2>
                <a
                    href="#"
                    class="group inline-flex items-center gap-1.5 text-[15px] font-bold tracking-wider text-night-ink uppercase transition-colors hover:text-eco-green"
                >
                    Semua artikel
                    <span class="transition-transform group-hover:translate-x-1"
                        >→</span
                    >
                </a>
            </div>
            <p
                class="mb-10 max-w-4xl text-[16px] leading-relaxed font-medium text-charcoal sm:text-[17px]"
            >
                Produk upcycle berawal dari bahan yang terpilah dengan baik.
                Kenali cara menyiapkan plastik, kardus, dan kaca agar setoran
                berikutnya lebih mudah dan aman.
            </p>

            <!-- Bento Grid (3 Columns) -->
            <div
                class="article-grid grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3"
            >
                <!-- Article 1 (Left on desktop) -->
                <article
                    v-if="articles.length > 0"
                    class="article-bento-card group flex cursor-default flex-col justify-between rounded-[20px] border-2 border-b-[5px] border-border-gray bg-paper-white p-5 transition-all duration-300 ease-out hover:-translate-y-2 hover:border-b-[5px] hover:border-eco-green hover:border-b-eco-green-dark hover:shadow-[0_16px_32px_-8px_rgba(88,204,2,0.22)]"
                >
                    <div>
                        <!-- Thumbnail / Image wrap -->
                        <div
                            class="mb-4 flex h-[160px] items-center justify-center overflow-hidden rounded-[14px] bg-sky-tint p-2"
                        >
                            <img
                                v-if="
                                    articles[0]?.thumbnail || articles[0]?.photo
                                "
                                :src="`/storage/${articles[0]?.thumbnail || articles[0]?.photo}`"
                                :alt="articles[0]?.title"
                                class="h-full w-full rounded-lg object-cover transition-transform duration-300 ease-out group-hover:scale-108"
                            />
                            <div
                                v-else
                                class="text-5xl transition-transform duration-300 group-hover:scale-110 group-hover:rotate-6"
                            >
                                🧴
                            </div>
                        </div>

                        <!-- Tag -->
                        <div class="mb-3">
                            <span
                                class="inline-block rounded-full bg-sprout-tint px-2.5 py-1 text-[11px] font-bold tracking-wider text-night-ink uppercase"
                            >
                                {{
                                    articles[0]?.category?.name ??
                                    'PANDUAN PILAH · 4 MNT'
                                }}
                            </span>
                        </div>

                        <h3
                            class="mb-2 text-[18px] leading-snug font-extrabold text-night-ink transition-colors duration-200 group-hover:text-eco-green-dark"
                        >
                            {{ articles[0]?.title }}
                        </h3>
                        <p
                            class="mb-4 line-clamp-3 text-[14px] leading-relaxed font-medium text-charcoal"
                        >
                            {{ articles[0]?.excerpt || articles[0]?.content }}
                        </p>
                    </div>

                    <a
                        :href="`/artikel/${articles[0]?.slug}`"
                        class="mt-auto inline-flex items-center gap-1 text-[14px] font-bold text-night-ink transition-colors group-hover:text-eco-green"
                    >
                        BACA ARTIKEL →
                    </a>
                </article>

                <!-- Bento Info Card 1: Pilah Lebih Tepat (Span 2 cols on desktop) -->
                <div
                    class="article-bento-card flex min-h-[200px] cursor-default flex-col justify-center rounded-[24px] border-2 border-b-[5px] border-[#bfe7fa] bg-sky-tint p-8 transition-all duration-300 ease-out hover:-translate-y-1 hover:border-sky-blue hover:border-b-sky-blue-dark hover:shadow-md lg:col-span-2"
                >
                    <h3
                        class="mb-3 text-2xl font-bold tracking-tight text-night-ink sm:text-[32px]"
                    >
                        Pilah lebih tepat, mulai dari tahu.
                    </h3>
                    <p
                        class="text-[16px] leading-relaxed font-medium text-charcoal sm:text-[17px]"
                    >
                        Mulai dari botol plastik yang paling sering dijumpai.
                        Lanjutkan ke kardus dan kaca untuk memahami cara
                        penyimpanan serta penanganan yang berbeda.
                    </p>
                </div>

                <!-- Bento Info Card 2: Tiga Kebiasaan Sebelum Setor -->
                <div
                    class="article-bento-card flex min-h-[200px] cursor-default flex-col justify-center rounded-[24px] border-2 border-b-[5px] border-border-gray/70 bg-paper-white p-8 transition-all duration-300 ease-out hover:-translate-y-1 hover:border-night-ink/20 hover:shadow-md"
                >
                    <p
                        class="mb-2 text-[13px] font-bold tracking-[1.6px] text-charcoal uppercase"
                    >
                        PRAKTIKKAN DARI RUMAH
                    </p>
                    <h3
                        class="mb-3 text-xl font-bold tracking-tight text-night-ink sm:text-[26px]"
                    >
                        Kecil, tapi konsisten.
                    </h3>
                    <div
                        class="space-y-1 text-[15px] leading-relaxed font-medium text-charcoal sm:text-[16px]"
                    >
                        <p>01 &nbsp;Bilas kemasan plastik</p>
                        <p>02 &nbsp;Simpan kardus tetap kering</p>
                        <p>03 &nbsp;Pisahkan kaca dengan aman</p>
                    </div>
                </div>

                <!-- Article 2 -->
                <article
                    v-if="articles.length > 1"
                    class="article-bento-card group flex cursor-default flex-col justify-between rounded-[20px] border-2 border-b-[5px] border-border-gray bg-paper-white p-5 transition-all duration-300 ease-out hover:-translate-y-2 hover:border-b-[5px] hover:border-eco-green hover:border-b-eco-green-dark hover:shadow-[0_16px_32px_-8px_rgba(88,204,2,0.22)]"
                >
                    <div>
                        <div
                            class="mb-4 flex h-[160px] items-center justify-center overflow-hidden rounded-[14px] bg-sky-tint p-2"
                        >
                            <img
                                v-if="
                                    articles[1]?.thumbnail || articles[1]?.photo
                                "
                                :src="`/storage/${articles[1]?.thumbnail || articles[1]?.photo}`"
                                :alt="articles[1]?.title"
                                class="h-full w-full rounded-lg object-cover transition-transform duration-300 ease-out group-hover:scale-108"
                            />
                            <div
                                v-else
                                class="text-5xl transition-transform duration-300 group-hover:scale-110 group-hover:rotate-6"
                            >
                                📦
                            </div>
                        </div>

                        <div class="mb-3">
                            <span
                                class="inline-block rounded-full bg-sprout-tint px-2.5 py-1 text-[11px] font-bold tracking-wider text-night-ink uppercase"
                            >
                                {{
                                    articles[1]?.category?.name ??
                                    'TIPS RUMAH · 3 MNT'
                                }}
                            </span>
                        </div>

                        <h3
                            class="mb-2 text-[18px] leading-snug font-extrabold text-night-ink transition-colors duration-200 group-hover:text-eco-green-dark"
                        >
                            {{ articles[1]?.title }}
                        </h3>
                        <p
                            class="mb-4 line-clamp-3 text-[14px] leading-relaxed font-medium text-charcoal"
                        >
                            {{ articles[1]?.excerpt || articles[1]?.content }}
                        </p>
                    </div>

                    <a
                        :href="`/artikel/${articles[1]?.slug}`"
                        class="mt-auto inline-flex items-center gap-1 text-[14px] font-bold text-night-ink transition-colors group-hover:text-eco-green"
                    >
                        BACA ARTIKEL →
                    </a>
                </article>

                <!-- Article 3 -->
                <article
                    v-if="articles.length > 2"
                    class="article-bento-card group flex cursor-default flex-col justify-between rounded-[20px] border-2 border-b-[5px] border-border-gray bg-paper-white p-5 transition-all duration-300 ease-out hover:-translate-y-2 hover:border-b-[5px] hover:border-eco-green hover:border-b-eco-green-dark hover:shadow-[0_16px_32px_-8px_rgba(88,204,2,0.22)]"
                >
                    <div>
                        <div
                            class="mb-4 flex h-[160px] items-center justify-center overflow-hidden rounded-[14px] bg-sky-tint p-2"
                        >
                            <img
                                v-if="
                                    articles[2]?.thumbnail || articles[2]?.photo
                                "
                                :src="`/storage/${articles[2]?.thumbnail || articles[2]?.photo}`"
                                :alt="articles[2]?.title"
                                class="h-full w-full rounded-lg object-cover transition-transform duration-300 ease-out group-hover:scale-108"
                            />
                            <div
                                v-else
                                class="text-5xl transition-transform duration-300 group-hover:scale-110 group-hover:rotate-6"
                            >
                                🍾
                            </div>
                        </div>

                        <div class="mb-3">
                            <span
                                class="inline-block rounded-full bg-sprout-tint px-2.5 py-1 text-[11px] font-bold tracking-wider text-night-ink uppercase"
                            >
                                {{
                                    articles[2]?.category?.name ??
                                    'DAUR ULANG · 5 MNT'
                                }}
                            </span>
                        </div>

                        <h3
                            class="mb-2 text-[18px] leading-snug font-extrabold text-night-ink transition-colors duration-200 group-hover:text-eco-green-dark"
                        >
                            {{ articles[2]?.title }}
                        </h3>
                        <p
                            class="mb-4 line-clamp-3 text-[14px] leading-relaxed font-medium text-charcoal"
                        >
                            {{ articles[2]?.excerpt || articles[2]?.content }}
                        </p>
                    </div>

                    <a
                        :href="`/artikel/${articles[2]?.slug}`"
                        class="mt-auto inline-flex items-center gap-1 text-[14px] font-bold text-night-ink transition-colors group-hover:text-eco-green"
                    >
                        BACA ARTIKEL →
                    </a>
                </article>
            </div>
        </div>
    </section>
</template>
