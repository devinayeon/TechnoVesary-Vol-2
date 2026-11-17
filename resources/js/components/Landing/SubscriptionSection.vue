<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, nextTick } from "vue";
import gsap from "gsap";
import { ScrollTrigger } from "gsap/ScrollTrigger";
import AppButton from "./AppButton.vue";

interface Subscription {
    id: number;
    name: string;
    price: number;
    description: string;
    nama?: string;
    harga?: number;
}

const props = defineProps<{ subscriptions?: Subscription[] }>();
const sectionRef = ref<HTMLElement | null>(null);
let gsapCtx: gsap.Context | null = null;

// Ambil paket dasar (hanya 1 paket di database: Paket Revalue +)
const baseSubscription = computed(() => {
    if (props.subscriptions && props.subscriptions.length > 0) {
        return props.subscriptions[0];
    }
    return {
        id: 1,
        name: "Paket Revalue +",
        price: 25000,
        description: "Beli Revalue+ untuk melakukan penjemputan dan mendapatkan poin",
    };
});

// Dari 1 paket tersebut, buat 3 varian berdasarkan durasi: 1 bulan, 3 bulan, dan 1 tahun (12 bulan)
const tiers = computed(() => {
    const base = baseSubscription.value;
    const basePrice = Number(base.price ?? base.harga ?? 25000);
    const baseName = base.name ?? base.nama ?? "Paket Revalue +";

    return [
        {
            key: "1-bulan",
            tag: "UNTUK LANGKAH PERTAMA",
            name: baseName,
            durationLabel: "/ 1 bulan",
            months: 1,
            price: basePrice * 1,
            points: basePrice * 1,
            isFeatured: false,
        },
        {
            key: "3-bulan",
            tag: "UNTUK KEBIASAAN BAIK",
            name: baseName,
            durationLabel: "/ 3 bulan",
            months: 3,
            price: basePrice * 3,
            points: basePrice * 3,
            isFeatured: true, // Kartu utama berlatar night-ink
        },
        {
            key: "1-tahun",
            tag: "UNTUK KEBIASAAN BERKELANJUTAN",
            name: baseName,
            durationLabel: "/ 1 tahun",
            months: 12,
            price: basePrice * 12,
            points: basePrice * 12,
            isFeatured: false,
        },
    ];
});

const benefitsList = [
    "✓  Jemput sampah terpilah",
    "✓  Rincian berat per kategori",
    "✓  Poin dari setiap setoran",
    "✓  Akses Upcycle Store",
];

function formatRupiah(amount: number): string {
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
    }).format(amount);
}

onMounted(async () => {
    if (typeof window === "undefined" || !sectionRef.value) return;
    await nextTick();
    gsap.registerPlugin(ScrollTrigger);

    gsapCtx = gsap.context(() => {
        const root = sectionRef.value;
        if (!root) return;

        const cards = root.querySelectorAll(".sub-card");
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
    }, sectionRef.value);
});

onUnmounted(() => {
    gsapCtx?.revert();
});
</script>

<template>
    <section id="langganan" ref="sectionRef" class="bg-paper-white py-16 lg:py-20 overflow-hidden scroll-mt-28">
        <div class="max-w-[1200px] mx-auto px-6">
            <!-- Header -->
            <div class="text-center mb-12">
                <p
                    class="font-bold text-[13px] tracking-[1.6px] uppercase text-charcoal mb-3"
                >
                    LANGGANAN REVALUE+
                </p>
                <h2
                    class="font-extrabold text-3xl sm:text-5xl text-night-ink tracking-tight mb-4"
                >
                    Pilih langkah baik yang pas.
                </h2>
                <p
                    class="font-medium text-[16px] sm:text-[17px] text-charcoal max-w-2xl mx-auto"
                >
                    Langganan aktif setelah pembayaran. Bayar dengan Rupiah atau
                    poin hasil setoranmu.
                </p>
            </div>

            <!-- Pricing Cards (3 durasi: 1 Bulan, 3 Bulan, 1 Tahun) -->
            <div class="sub-cards-grid grid grid-cols-1 md:grid-cols-3 gap-6">
                <div
                    v-for="tier in tiers"
                    :key="tier.key"
                    class="sub-card rounded-[28px] p-7 min-h-[500px] flex flex-col justify-between transition-all duration-200 hover:-translate-y-2 cursor-default"
                    :class="
                        tier.isFeatured
                            ? 'bg-night-ink text-paper-white shadow-[0px_6px_0px_0px_#00021c] hover:shadow-[0px_10px_0px_0px_#00021c] border-2 border-night-ink'
                            : 'bg-paper-white text-night-ink border-2 border-border-gray shadow-[0px_4px_0px_0px_var(--color-border-gray)] hover:shadow-[0px_8px_0px_0px_var(--color-border-gray)]'
                    "
                >
                    <!-- Top Info -->
                    <div>
                        <p
                            class="font-bold text-[13px] tracking-[1.6px] uppercase mb-4"
                            :class="
                                tier.isFeatured
                                    ? 'text-sprout-tint'
                                    : 'text-charcoal'
                            "
                        >
                            {{ tier.tag }}
                        </p>

                        <h3
                            class="font-bold text-2xl sm:text-[26px] tracking-tight mb-6"
                            :class="
                                tier.isFeatured
                                    ? 'text-paper-white'
                                    : 'text-night-ink'
                            "
                        >
                            {{ tier.name }}
                        </h3>

                        <!-- Price (Harga dikali lama bulan) -->
                        <div class="mb-6">
                            <div
                                class="font-extrabold text-3xl sm:text-[40px] tracking-tight leading-tight"
                                :class="
                                    tier.isFeatured
                                        ? 'text-paper-white'
                                        : 'text-night-ink'
                                "
                            >
                                {{ formatRupiah(tier.price) }}
                            </div>
                            <div
                                class="font-bold text-[15px] tracking-wide uppercase mt-1"
                                :class="
                                    tier.isFeatured
                                        ? 'text-paper-white/80'
                                        : 'text-night-ink'
                                "
                            >
                                {{ tier.durationLabel }}
                            </div>
                            <div
                                class="font-bold text-[15px] tracking-wide uppercase mt-1"
                                :class="
                                    tier.isFeatured
                                        ? 'text-sprout-tint'
                                        : 'text-charcoal'
                                "
                            >
                                atau
                                {{ tier.points.toLocaleString("id-ID") }}
                                poin
                            </div>
                        </div>

                        <!-- Benefits -->
                        <div
                            class="flex flex-col gap-3 font-medium text-[16px] sm:text-[17px] mb-8"
                        >
                            <p
                                v-for="benefit in benefitsList"
                                :key="benefit"
                                :class="
                                    tier.isFeatured
                                        ? 'text-paper-white/90'
                                        : 'text-night-ink'
                                "
                            >
                                {{ benefit }}
                            </p>
                        </div>
                    </div>

                    <!-- Tactile Action Button -->
                    <AppButton
                        variant="primary"
                        size="lg"
                        href="/register"
                        class="w-full h-12 text-[14px]"
                    >
                        PILIH PAKET
                    </AppButton>
                </div>
            </div>

            <p
                class="font-bold text-[13px] tracking-[1.6px] text-charcoal text-center mt-8"
            >
                Contoh paket dan harga untuk rancangan. Tarif final mengikuti
                layanan ReValue.
            </p>
        </div>
    </section>
</template>
