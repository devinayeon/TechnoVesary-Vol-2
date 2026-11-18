<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue';
import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import AppButton from './AppButton.vue';

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
        name: 'Paket Revalue +',
        price: 25000,
        description:
            'Beli Revalue+ untuk melakukan penjemputan dan mendapatkan poin',
    };
});

// Dari 1 paket tersebut, buat 3 varian berdasarkan durasi: 1 bulan, 3 bulan, dan 1 tahun (12 bulan)
const tiers = computed(() => {
    const base = baseSubscription.value;
    const basePrice = Number(base.price ?? base.harga ?? 25000);
    const baseName = base.name ?? base.nama ?? 'Paket Revalue +';

    return [
        {
            key: '1-bulan',
            tag: 'UNTUK LANGKAH PERTAMA',
            name: baseName,
            durationLabel: '/ 1 bulan',
            months: 1,
            price: basePrice * 1,
            points: basePrice * 1,
            isFeatured: false,
        },
        {
            key: '3-bulan',
            tag: 'UNTUK KEBIASAAN BAIK',
            name: baseName,
            durationLabel: '/ 3 bulan',
            months: 3,
            price: basePrice * 3,
            points: basePrice * 3,
            isFeatured: true, // Kartu utama berlatar night-ink
        },
        {
            key: '1-tahun',
            tag: 'UNTUK KEBIASAAN BERKELANJUTAN',
            name: baseName,
            durationLabel: '/ 1 tahun',
            months: 12,
            price: basePrice * 12,
            points: basePrice * 12,
            isFeatured: false,
        },
    ];
});

const benefitsList = [
    '✓  Jemput sampah terpilah',
    '✓  Rincian berat per kategori',
    '✓  Poin dari setiap setoran',
    '✓  Akses Upcycle Store',
];

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

        const cards = root.querySelectorAll('.sub-card');
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
        id="langganan"
        ref="sectionRef"
        class="scroll-mt-28 overflow-hidden bg-paper-white py-16 lg:py-20"
    >
        <div class="mx-auto max-w-[1200px] px-6">
            <!-- Header -->
            <div class="mb-12 text-center">
                <p
                    class="mb-3 text-[13px] font-bold tracking-[1.6px] text-charcoal uppercase"
                >
                    LANGGANAN REVALUE+
                </p>
                <h2
                    class="mb-4 text-3xl font-extrabold tracking-tight text-night-ink sm:text-5xl"
                >
                    Pilih langkah baik yang pas.
                </h2>
                <p
                    class="mx-auto max-w-2xl text-[16px] font-medium text-charcoal sm:text-[17px]"
                >
                    Langganan aktif setelah pembayaran. Bayar dengan Rupiah atau
                    poin hasil setoranmu.
                </p>
            </div>

            <!-- Pricing Cards (3 durasi: 1 Bulan, 3 Bulan, 1 Tahun) -->
            <div class="sub-cards-grid grid grid-cols-1 gap-6 md:grid-cols-3">
                <div
                    v-for="tier in tiers"
                    :key="tier.key"
                    class="sub-card group relative flex min-h-[500px] cursor-default flex-col justify-between rounded-[28px] p-7 transition-all duration-300 ease-out hover:-translate-y-2.5"
                    :class="
                        tier.isFeatured
                            ? 'border-2 border-b-[6px] border-night-ink bg-night-ink text-paper-white shadow-[0px_6px_0px_0px_#00021c] hover:shadow-[0_20px_40px_-10px_rgba(0,4,55,0.45),0px_8px_0px_0px_#00021c]'
                            : 'border-2 border-b-[6px] border-border-gray bg-paper-white text-night-ink shadow-[0px_4px_0px_0px_var(--color-border-gray)] hover:border-b-[6px] hover:border-eco-green hover:border-b-eco-green-dark hover:shadow-[0_16px_36px_-8px_rgba(88,204,2,0.22),0px_6px_0px_0px_var(--color-eco-green-dark)]'
                    "
                >
                    <!-- Top Info -->
                    <div>
                        <p
                            class="mb-4 text-[13px] font-bold tracking-[1.6px] uppercase transition-colors duration-200"
                            :class="
                                tier.isFeatured
                                    ? 'text-sprout-tint'
                                    : 'text-charcoal group-hover:text-eco-green-dark'
                            "
                        >
                            {{ tier.tag }}
                        </p>

                        <h3
                            class="mb-6 text-2xl font-bold tracking-tight sm:text-[26px]"
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
                                class="text-3xl leading-tight font-extrabold tracking-tight sm:text-[40px]"
                                :class="
                                    tier.isFeatured
                                        ? 'text-paper-white'
                                        : 'text-night-ink'
                                "
                            >
                                {{ formatRupiah(tier.price) }}
                            </div>
                            <div
                                class="mt-1 text-[15px] font-bold tracking-wide uppercase"
                                :class="
                                    tier.isFeatured
                                        ? 'text-paper-white/80'
                                        : 'text-night-ink'
                                "
                            >
                                {{ tier.durationLabel }}
                            </div>
                            <div
                                class="mt-1 text-[15px] font-bold tracking-wide uppercase"
                                :class="
                                    tier.isFeatured
                                        ? 'text-sprout-tint'
                                        : 'text-charcoal'
                                "
                            >
                                atau
                                {{ tier.points.toLocaleString('id-ID') }}
                                poin
                            </div>
                        </div>

                        <!-- Benefits -->
                        <div
                            class="mb-8 flex flex-col gap-3 text-[16px] font-medium sm:text-[17px]"
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
                        class="h-12 w-full text-[14px]"
                    >
                        PILIH PAKET
                    </AppButton>
                </div>
            </div>

            <p
                class="mt-8 text-center text-[13px] font-bold tracking-[1.6px] text-charcoal"
            >
                Contoh paket dan harga untuk rancangan. Tarif final mengikuti
                layanan ReValue.
            </p>
        </div>
    </section>
</template>
