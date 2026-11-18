<script setup lang="ts">
import { ref, onMounted, onUnmounted, nextTick } from 'vue';
import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import AppButton from './AppButton.vue';

interface Product {
    id: number;
    name: string;
    description: string | null;
    price_rupiah: number;
    price_points: number;
    photo: string | null;
    nama_produk?: string;
    deskripsi?: string;
    harga_rupiah?: number;
    harga_poin?: number;
    foto?: string | null;
}

defineProps<{ products: Product[] }>();

const sectionRef = ref<HTMLElement | null>(null);
let gsapCtx: gsap.Context | null = null;

function formatRupiah(amount: number): string {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(amount);
}

onMounted(async () => {
    if (typeof window === 'undefined' || !sectionRef.value) return;
    await nextTick();
    gsap.registerPlugin(ScrollTrigger);

    gsapCtx = gsap.context(() => {
        const root = sectionRef.value;
        if (!root) return;

        const cards = root.querySelectorAll('.upcycle-bento-card');
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
        id="upcycle"
        ref="sectionRef"
        class="scroll-mt-28 overflow-hidden bg-paper-white py-16 lg:py-20"
    >
        <div class="mx-auto max-w-[1200px] px-6">
            <!-- Header -->
            <div
                class="mb-4 flex flex-col justify-between gap-3 sm:flex-row sm:items-center"
            >
                <h2
                    class="text-3xl font-extrabold tracking-tight text-night-ink sm:text-5xl"
                >
                    Nilai baru, cerita baru.
                </h2>
                <a
                    href="#"
                    class="group inline-flex items-center gap-1.5 text-[15px] font-bold tracking-wider text-night-ink uppercase transition-colors hover:text-eco-green"
                >
                    Jelajahi toko
                    <span class="transition-transform group-hover:translate-x-1"
                        >→</span
                    >
                </a>
            </div>
            <p
                class="mb-10 max-w-4xl text-[16px] leading-relaxed font-medium text-charcoal sm:text-[17px]"
            >
                Dari bahan terbuang jadi barang tersayang. Belanja dengan poin,
                Rupiah, atau keduanya. Pilih karya yang berguna, lalu teruskan
                cerita baik dari setiap setoranmu.
            </p>

            <!-- Bento Grid (3 Columns) -->
            <div
                class="upcycle-grid grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3"
            >
                <!-- Bento Top Left: Info Card (Span 2 cols on desktop) -->
                <div
                    class="upcycle-bento-card flex min-h-[200px] cursor-default flex-col justify-center rounded-[24px] border-2 border-b-[5px] border-border-gray/70 bg-cloud-surface p-8 transition-all duration-300 ease-out hover:-translate-y-1 hover:border-night-ink/20 hover:shadow-md lg:col-span-2"
                >
                    <p
                        class="mb-2 text-[13px] font-bold tracking-[1.6px] text-charcoal uppercase"
                    >
                        PILIH KARYANYA &nbsp;/&nbsp; GUNAKAN POINMU
                        &nbsp;/&nbsp; PAKAI KEMBALI
                    </p>
                    <h3
                        class="mb-3 text-2xl font-bold tracking-tight text-night-ink sm:text-[32px]"
                    >
                        Setoranmu tidak berhenti di poin.
                    </h3>
                    <p
                        class="text-[16px] leading-relaxed font-medium text-charcoal sm:text-[17px]"
                    >
                        Poin yang kamu kumpulkan bisa kembali menjadi produk
                        upcycle. Pilih barang yang berguna, dukung karya lokal,
                        dan teruskan usia pakai bahan.
                    </p>
                </div>

                <!-- Product 1 -->
                <div
                    v-if="products.length > 0"
                    class="upcycle-bento-card group flex cursor-default flex-col justify-between rounded-[20px] border-2 border-b-[5px] border-border-gray bg-paper-white p-5 transition-all duration-300 ease-out hover:-translate-y-2 hover:border-b-[5px] hover:border-sky-blue hover:border-b-sky-blue-dark hover:shadow-[0_16px_32px_-8px_rgba(28,176,246,0.25)]"
                >
                    <div
                        class="mb-4 flex h-[140px] items-center justify-center overflow-hidden rounded-[14px] bg-gold-tint p-2"
                    >
                        <img
                            v-if="products[0]?.photo || products[0]?.foto"
                            :src="`/storage/${products[0]?.photo || products[0]?.foto}`"
                            :alt="
                                products[0]?.name ||
                                products[0]?.nama_produk ||
                                'Produk'
                            "
                            class="h-full w-full rounded-lg object-cover transition-transform duration-300 ease-out group-hover:scale-108"
                        />
                        <div
                            v-else
                            class="text-5xl transition-transform duration-300 group-hover:scale-110 group-hover:rotate-6"
                        >
                            🛍️
                        </div>
                    </div>
                    <div>
                        <h4
                            class="mb-1 text-[20px] leading-snug font-extrabold text-night-ink transition-colors duration-200 group-hover:text-sky-blue-dark"
                        >
                            {{ products[0]?.name ?? products[0]?.nama_produk }}
                        </h4>
                        <p
                            class="mb-4 line-clamp-2 text-[14px] leading-relaxed text-night-ink/80"
                        >
                            {{
                                products[0]?.description ??
                                products[0]?.deskripsi
                            }}
                        </p>
                    </div>
                    <div class="mt-auto">
                        <div class="mb-4 flex items-center justify-between">
                            <span
                                class="text-[18px] font-extrabold text-night-ink"
                            >
                                {{
                                    formatRupiah(
                                        products[0]?.price_rupiah ??
                                            products[0]?.harga_rupiah ??
                                            0,
                                    )
                                }}
                            </span>
                            <span
                                v-if="
                                    products[0]?.price_points ??
                                    products[0]?.harga_poin
                                "
                                class="rounded-full bg-sprout-tint px-2.5 py-1 text-xs font-bold text-night-ink"
                            >
                                {{
                                    (
                                        products[0]?.price_points ??
                                        products[0]?.harga_poin ??
                                        0
                                    ).toLocaleString('id-ID')
                                }}
                                Poin
                            </span>
                        </div>
                        <AppButton
                            variant="blue"
                            size="md"
                            class="h-11 w-full text-[14px]"
                        >
                            LIHAT PRODUK →
                        </AppButton>
                    </div>
                </div>

                <!-- Product 2 -->
                <div
                    v-if="products.length > 1"
                    class="upcycle-bento-card group flex cursor-default flex-col justify-between rounded-[20px] border-2 border-b-[5px] border-border-gray bg-paper-white p-5 transition-all duration-300 ease-out hover:-translate-y-2 hover:border-b-[5px] hover:border-sky-blue hover:border-b-sky-blue-dark hover:shadow-[0_16px_32px_-8px_rgba(28,176,246,0.25)]"
                >
                    <div
                        class="mb-4 flex h-[140px] items-center justify-center overflow-hidden rounded-[14px] bg-gold-tint p-2"
                    >
                        <img
                            v-if="products[1]?.photo || products[1]?.foto"
                            :src="`/storage/${products[1]?.photo || products[1]?.foto}`"
                            :alt="
                                products[1]?.name ||
                                products[1]?.nama_produk ||
                                'Produk'
                            "
                            class="h-full w-full rounded-lg object-cover transition-transform duration-300 ease-out group-hover:scale-108"
                        />
                        <div
                            v-else
                            class="text-5xl transition-transform duration-300 group-hover:scale-110 group-hover:rotate-6"
                        >
                            🪴
                        </div>
                    </div>
                    <div>
                        <h4
                            class="mb-1 text-[20px] leading-snug font-extrabold text-night-ink transition-colors duration-200 group-hover:text-sky-blue-dark"
                        >
                            {{ products[1]?.name ?? products[1]?.nama_produk }}
                        </h4>
                        <p
                            class="mb-4 line-clamp-2 text-[14px] leading-relaxed text-night-ink/80"
                        >
                            {{
                                products[1]?.description ??
                                products[1]?.deskripsi
                            }}
                        </p>
                    </div>
                    <div class="mt-auto">
                        <div class="mb-4 flex items-center justify-between">
                            <span
                                class="text-[18px] font-extrabold text-night-ink"
                            >
                                {{
                                    formatRupiah(
                                        products[1]?.price_rupiah ??
                                            products[1]?.harga_rupiah ??
                                            0,
                                    )
                                }}
                            </span>
                            <span
                                v-if="
                                    products[1]?.price_points ??
                                    products[1]?.harga_poin
                                "
                                class="rounded-full bg-sprout-tint px-2.5 py-1 text-xs font-bold text-night-ink"
                            >
                                {{
                                    (
                                        products[1]?.price_points ??
                                        products[1]?.harga_poin ??
                                        0
                                    ).toLocaleString('id-ID')
                                }}
                                Poin
                            </span>
                        </div>
                        <AppButton
                            variant="blue"
                            size="md"
                            class="h-11 w-full text-[14px]"
                        >
                            LIHAT PRODUK →
                        </AppButton>
                    </div>
                </div>

                <!-- Product 3 -->
                <div
                    v-if="products.length > 2"
                    class="upcycle-bento-card group flex cursor-default flex-col justify-between rounded-[20px] border-2 border-b-[5px] border-border-gray bg-paper-white p-5 transition-all duration-300 ease-out hover:-translate-y-2 hover:border-b-[5px] hover:border-sky-blue hover:border-b-sky-blue-dark hover:shadow-[0_16px_32px_-8px_rgba(28,176,246,0.25)]"
                >
                    <div
                        class="mb-4 flex h-[140px] items-center justify-center overflow-hidden rounded-[14px] bg-gold-tint p-2"
                    >
                        <img
                            v-if="products[2]?.photo || products[2]?.foto"
                            :src="`/storage/${products[2]?.photo || products[2]?.foto}`"
                            :alt="
                                products[2]?.name ||
                                products[2]?.nama_produk ||
                                'Produk'
                            "
                            class="h-full w-full rounded-lg object-cover transition-transform duration-300 ease-out group-hover:scale-108"
                        />
                        <div
                            v-else
                            class="text-5xl transition-transform duration-300 group-hover:scale-110 group-hover:rotate-6"
                        >
                            💡
                        </div>
                    </div>
                    <div>
                        <h4
                            class="mb-1 text-[20px] leading-snug font-extrabold text-night-ink transition-colors duration-200 group-hover:text-sky-blue-dark"
                        >
                            {{ products[2]?.name ?? products[2]?.nama_produk }}
                        </h4>
                        <p
                            class="mb-4 line-clamp-2 text-[14px] leading-relaxed text-night-ink/80"
                        >
                            {{
                                products[2]?.description ??
                                products[2]?.deskripsi
                            }}
                        </p>
                    </div>
                    <div class="mt-auto">
                        <div class="mb-4 flex items-center justify-between">
                            <span
                                class="text-[18px] font-extrabold text-night-ink"
                            >
                                {{
                                    formatRupiah(
                                        products[2]?.price_rupiah ??
                                            products[2]?.harga_rupiah ??
                                            0,
                                    )
                                }}
                            </span>
                            <span
                                v-if="
                                    products[2]?.price_points ??
                                    products[2]?.harga_poin
                                "
                                class="rounded-full bg-sprout-tint px-2.5 py-1 text-xs font-bold text-night-ink"
                            >
                                {{
                                    (
                                        products[2]?.price_points ??
                                        products[2]?.harga_poin ??
                                        0
                                    ).toLocaleString('id-ID')
                                }}
                                Poin
                            </span>
                        </div>
                        <AppButton
                            variant="blue"
                            size="md"
                            class="h-11 w-full text-[14px]"
                        >
                            LIHAT PRODUK →
                        </AppButton>
                    </div>
                </div>

                <!-- Bento Bottom Right: Circular Banner -->
                <div
                    class="upcycle-bento-card flex min-h-[200px] cursor-default flex-col justify-center rounded-[24px] border-2 border-b-[5px] border-night-ink bg-night-ink p-8 text-paper-white transition-all duration-300 ease-out hover:-translate-y-1.5 hover:shadow-[0_16px_36px_-6px_rgba(0,4,55,0.45)]"
                >
                    <p
                        class="mb-2 text-[13px] font-bold tracking-[1.6px] text-sprout-tint uppercase"
                    >
                        SATU PUTARAN BARU
                    </p>
                    <h3
                        class="mb-3 text-2xl font-bold tracking-tight text-paper-white sm:text-[26px]"
                    >
                        Pakai. Hargai. Ulangi.
                    </h3>
                    <p
                        class="text-[16px] leading-relaxed font-medium text-paper-white/80 sm:text-[17px]"
                    >
                        Rawat barang pilihanmu. Lalu, mulai lagi dari memilah
                        sampah di rumah.
                    </p>
                </div>
            </div>
        </div>
    </section>
</template>
