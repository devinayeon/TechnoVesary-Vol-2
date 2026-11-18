<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import DashboardLayout from '@/layouts/DashboardLayout.vue';
import {
    ArrowLeft,
    Crown,
    Coins,
    CreditCard,
    CheckCircle2,
    ShieldCheck,
    AlertCircle,
    Calendar,
    ExternalLink,
} from 'lucide-vue-next';

export interface SelectedPackage {
    key: string;
    name: string;
    duration_months: number;
    duration_days: number;
    multiplier: number;
    price: number;
    badge: string;
    badge_tone: string;
    description: string;
    expires_at_preview: string;
    is_recommended: boolean;
}

const props = defineProps<{
    package: SelectedPackage;
    customerPoints: number;
    clientKey: string;
    snapJsUrl: string;
    isCurrentMember: boolean;
    currentExpiresAt: string | null;
}>();

// Form & Interaction State
const usePoints = ref(props.customerPoints > 0);
const isProcessing = ref(false);
const errorMessage = ref<string | null>(null);

// Midtrans Snap State
const snapToken = ref<string | null>(null);
const snapUrl = ref<string | null>(null);
const isSnapOpen = ref(false);

// Price & Points Calculations
const totalPrice = computed(() => props.package.price);

const pointsUsed = computed(() => {
    if (!usePoints.value || props.customerPoints <= 0) return 0;
    return Math.min(props.customerPoints, totalPrice.value);
});

const pointsDiscount = computed(() => pointsUsed.value); // 1 Poin = Rp 1
const payableAmount = computed(() =>
    Math.max(0, totalPrice.value - pointsDiscount.value),
);
const isPaidWithPointsOnly = computed(() => payableAmount.value === 0);

const remainingPointsAfter = computed(() => {
    if (!usePoints.value) return props.customerPoints;
    return Math.max(0, props.customerPoints - pointsUsed.value);
});

const formatRupiah = (amount: number): string => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(amount);
};

// Dynamically inject Midtrans Snap script
onMounted(() => {
    if (
        typeof window !== 'undefined' &&
        !document.getElementById('midtrans-snap-script') &&
        props.snapJsUrl &&
        props.clientKey
    ) {
        const script = document.createElement('script');
        script.id = 'midtrans-snap-script';
        script.src = props.snapJsUrl;
        script.setAttribute('data-client-key', props.clientKey);
        script.async = true;
        document.body.appendChild(script);
    }
});

const openSnapPopup = (token: string) => {
    if (typeof window !== 'undefined' && (window as any).snap) {
        (window as any).snap.pay(token, {
            onSuccess: () => {
                router.visit('/customer/subscription/history');
            },
            onPending: () => {
                router.visit('/customer/subscription/history');
            },
            onError: () => {
                errorMessage.value =
                    'Pembayaran via Midtrans gagal diproses. Anda dapat mencoba kembali di riwayat langganan.';
            },
            onClose: () => {
                isSnapOpen.value = true;
            },
        });
    } else if (snapUrl.value) {
        window.open(snapUrl.value, '_blank');
    }
};

const handleCheckout = async () => {
    errorMessage.value = null;

    // Jika Snap sudah pernah di-generate dan belum dibayar, panggil ulang popup
    if (isSnapOpen.value && snapToken.value) {
        openSnapPopup(snapToken.value);
        return;
    }

    isProcessing.value = true;

    try {
        const response = await fetch('/customer/subscription/checkout', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN':
                    (
                        document.querySelector(
                            'meta[name="csrf-token"]',
                        ) as HTMLMetaElement
                    )?.content || '',
                Accept: 'application/json',
            },
            body: JSON.stringify({
                package_key: props.package.key,
                use_points: usePoints.value,
            }),
        });

        const data = await response.json();

        if (!response.ok || !data.success) {
            errorMessage.value =
                data.message || 'Terjadi kesalahan saat memproses langganan.';
            isProcessing.value = false;
            return;
        }

        // Jika transaksi lunas 100% menggunakan poin
        if (data.is_full_points) {
            router.visit('/customer/subscription/history');
            return;
        }

        // Jika butuh pembayaran via Midtrans
        if (data.snap_token) {
            snapToken.value = data.snap_token;
            snapUrl.value = data.snap_url;
            isSnapOpen.value = true;
            isProcessing.value = false;
            openSnapPopup(data.snap_token);
        } else {
            router.visit('/customer/subscription/history');
        }
    } catch (err: any) {
        errorMessage.value =
            err.message || 'Gagal menghubungi server. Silakan coba lagi.';
        isProcessing.value = false;
    }
};
</script>

<template>
    <Head title="Checkout Langganan ReValue+" />

    <DashboardLayout>
        <!-- Back Link -->
        <div class="mb-6">
            <Link
                href="/customer/subscription"
                class="group inline-flex cursor-pointer items-center gap-2 text-sm font-bold text-charcoal transition-colors hover:text-night-ink"
            >
                <div
                    class="rounded-lg border-2 border-border-gray bg-paper-white p-1.5 transition-colors group-hover:bg-cloud-surface"
                >
                    <ArrowLeft class="size-4" />
                </div>
                <span>Kembali ke Pilihan Paket</span>
            </Link>
        </div>

        <!-- Page Header -->
        <div class="mb-6">
            <h1
                class="text-[24px] font-black tracking-tight text-night-ink sm:text-[30px]"
            >
                Checkout Langganan ReValue+
            </h1>
            <p class="mt-1 text-sm font-medium text-charcoal">
                Selesaikan transaksi paket langganan keanggotaan dan pilih
                metode pembayaran Anda.
            </p>
        </div>

        <!-- Error Alert -->
        <div
            v-if="errorMessage"
            class="mb-6 flex items-start gap-3 rounded-2xl border-2 border-red-300 bg-red-50 p-4 text-sm font-bold text-red-800 shadow-xs"
        >
            <AlertCircle class="mt-0.5 size-5 shrink-0 text-red-600" />
            <div class="flex-1">
                <p>{{ errorMessage }}</p>
            </div>
        </div>

        <!-- 2-Column Grid -->
        <div class="grid grid-cols-1 items-start gap-8 lg:grid-cols-12">
            <!-- Left Column: Form & Options (7 Cols) -->
            <div class="flex flex-col gap-6 lg:col-span-7">
                <!-- 1. Package Summary Card -->
                <div
                    class="rounded-[24px] border-2 border-b-[5px] border-border-gray bg-paper-white p-6 shadow-xs"
                >
                    <div
                        class="mb-4 flex items-center justify-between border-b border-border-gray/60 pb-3"
                    >
                        <h2
                            class="flex items-center gap-2 text-xs font-bold tracking-wider text-charcoal uppercase"
                        >
                            <Crown class="size-4 text-eco-green-dark" />
                            <span>Paket yang Dipilih</span>
                        </h2>
                        <Link
                            href="/customer/subscription"
                            class="text-xs font-bold text-eco-green-dark hover:underline"
                        >
                            Ganti Paket
                        </Link>
                    </div>

                    <div class="flex items-center gap-4">
                        <div
                            class="flex size-20 shrink-0 items-center justify-center overflow-hidden rounded-2xl border-2 border-border-gray/70 bg-cloud-surface p-2"
                        >
                            <img
                                src="/assets/Badge/ReValuePass.png"
                                alt="ReValue Pass"
                                class="h-full w-full object-contain"
                            />
                        </div>

                        <div class="min-w-0 flex-1">
                            <h3
                                class="mb-1 truncate text-[16px] font-extrabold text-night-ink"
                            >
                                {{ props.package.name }}
                            </h3>
                            <div class="flex flex-wrap items-baseline gap-2">
                                <span class="text-sm font-bold text-night-ink">
                                    {{ formatRupiah(props.package.price) }}
                                </span>
                                <span
                                    class="text-xs font-semibold text-charcoal"
                                >
                                    × Durasi
                                    {{ props.package.duration_days }} hari
                                </span>
                            </div>
                            <p class="mt-0.5 text-xs text-charcoal">
                                {{ props.package.description }}
                            </p>
                        </div>

                        <div class="shrink-0 text-right">
                            <span
                                class="mb-0.5 block text-xs font-bold text-charcoal"
                                >Subtotal</span
                            >
                            <span class="text-base font-black text-night-ink">
                                {{ formatRupiah(totalPrice) }}
                            </span>
                        </div>
                    </div>

                    <!-- Info Tanggal Berakhir Setelah Aktivasi -->
                    <div
                        class="mt-5 rounded-xl border border-border-gray/80 bg-cloud-surface p-3.5"
                    >
                        <div class="flex items-start gap-2.5">
                            <Calendar
                                class="mt-0.5 size-4 shrink-0 text-eco-green-dark"
                            />
                            <div class="text-xs font-medium text-charcoal">
                                <span class="font-bold text-night-ink">
                                    {{
                                        props.isCurrentMember
                                            ? 'Perpanjangan Masa Aktif:'
                                            : 'Masa Aktif Akun Setelah Aktivasi:'
                                    }}
                                </span>
                                <p class="mt-0.5 text-night-ink">
                                    Keanggotaan aktif hingga
                                    <strong class="font-black text-night-ink">{{
                                        props.package.expires_at_preview
                                    }}</strong
                                    >.
                                    <span
                                        v-if="
                                            props.isCurrentMember &&
                                            props.currentExpiresAt
                                        "
                                        class="text-charcoal"
                                    >
                                        (Masa aktif saat ini sampai
                                        {{ props.currentExpiresAt }})
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. ReValue Points Toggle Card -->
                <div
                    class="rounded-[24px] border-2 border-b-[5px] border-border-gray bg-paper-white p-6 shadow-xs"
                >
                    <div class="mb-4 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div
                                class="flex size-10 items-center justify-center rounded-xl bg-gold-tint text-night-ink"
                            >
                                <Coins class="size-6 text-sunlight-gold" />
                            </div>
                            <div>
                                <h3
                                    class="text-sm font-extrabold text-night-ink"
                                >
                                    Poin Bank Sampah ReValue
                                </h3>
                                <p class="text-xs font-bold text-charcoal">
                                    Saldo:
                                    <span class="text-eco-green-dark">
                                        {{
                                            customerPoints.toLocaleString(
                                                'id-ID',
                                            )
                                        }}
                                        Poin
                                    </span>
                                    (Senilai {{ formatRupiah(customerPoints) }})
                                </p>
                            </div>
                        </div>

                        <!-- Checkbox Toggle -->
                        <label
                            class="relative inline-flex cursor-pointer items-center select-none"
                            :class="{
                                'cursor-not-allowed opacity-50':
                                    customerPoints <= 0,
                            }"
                        >
                            <input
                                type="checkbox"
                                v-model="usePoints"
                                :disabled="customerPoints <= 0"
                                class="peer sr-only"
                            />
                            <div
                                class="peer h-7 w-12 rounded-full border-2 border-border-gray bg-cloud-surface peer-checked:bg-eco-green peer-focus:outline-none after:absolute after:top-[3px] after:left-[4px] after:h-5 after:w-5 after:rounded-full after:border after:border-border-gray after:bg-white after:transition-all after:content-[''] peer-checked:after:translate-x-full peer-checked:after:border-white"
                            />
                        </label>
                    </div>

                    <!-- Explanatory note -->
                    <div
                        v-if="usePoints && customerPoints > 0"
                        class="space-y-1.5 rounded-xl border border-eco-green/40 bg-sprout-tint/70 p-4 text-xs font-semibold text-night-ink"
                    >
                        <div class="flex items-center justify-between">
                            <span>Poin yang digunakan:</span>
                            <span class="font-black text-eco-green-dark">
                                {{ pointsUsed.toLocaleString('id-ID') }} Poin
                                (-{{ formatRupiah(pointsDiscount) }})
                            </span>
                        </div>
                        <div
                            class="flex items-center justify-between border-t border-eco-green/30 pt-1"
                        >
                            <span>Sisa poin setelah transaksi:</span>
                            <span class="font-bold text-charcoal">
                                {{
                                    remainingPointsAfter.toLocaleString('id-ID')
                                }}
                                Poin
                            </span>
                        </div>

                        <p
                            v-if="isPaidWithPointsOnly"
                            class="pt-2 font-black text-eco-green-dark"
                        >
                            ✓ Saldo poin Anda mencukupi untuk melunasi 100%
                            langganan ini. Anda tidak perlu membayar rupiah atau
                            membuka Midtrans!
                        </p>
                        <p v-else class="pt-2 font-medium text-charcoal">
                            Poin memotong {{ formatRupiah(pointsDiscount) }}.
                            Sisa tagihan
                            {{ formatRupiah(payableAmount) }} dibayarkan melalui
                            Midtrans.
                        </p>
                    </div>

                    <p v-else class="text-xs font-medium text-charcoal">
                        Aktifkan opsi ini untuk memotong langsung biaya
                        langganan Anda dengan perolehan poin dari setoran sampah
                        terpilah (1 Poin = Rp 1).
                    </p>
                </div>

                <!-- 3. Security Guarantee Card -->
                <div
                    class="flex items-center gap-3 rounded-2xl border-2 border-border-gray/70 bg-cloud-surface p-4 text-xs font-semibold text-charcoal"
                >
                    <ShieldCheck class="size-6 shrink-0 text-eco-green-dark" />
                    <div>
                        <strong class="font-bold text-night-ink"
                            >Transaksi Aman & Terverifikasi</strong
                        >
                        <p class="mt-0.5 font-medium">
                            Pembayaran dilindungi enkripsi standar perbankan
                            Midtrans Gateway. Keanggotaan langsung aktif
                            seketika setelah pembayaran terkonfirmasi.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Right Column: Cost Breakdown & Checkout CTA (5 Cols) -->
            <div class="flex flex-col gap-6 lg:col-span-5">
                <div
                    class="sticky top-6 flex flex-col gap-5 rounded-[26px] border-2 border-b-[5px] border-border-gray bg-paper-white p-6 shadow-xs sm:p-7"
                >
                    <h2
                        class="text-xs font-bold tracking-wider text-charcoal uppercase"
                    >
                        Rincian Pembayaran
                    </h2>

                    <!-- Cost Breakdown List -->
                    <div class="space-y-3 text-sm">
                        <div class="flex items-center justify-between">
                            <span class="font-medium text-charcoal"
                                >Harga Paket</span
                            >
                            <span class="font-bold text-night-ink">{{
                                formatRupiah(totalPrice)
                            }}</span>
                        </div>

                        <div
                            v-if="usePoints && pointsDiscount > 0"
                            class="flex items-center justify-between text-eco-green-dark"
                        >
                            <span class="flex items-center gap-1 font-semibold">
                                <Coins class="size-3.5 text-sunlight-gold" />
                                Potongan Poin ReValue
                            </span>
                            <span class="font-black"
                                >-{{ formatRupiah(pointsDiscount) }}</span
                            >
                        </div>

                        <div
                            class="flex items-center justify-between font-medium text-charcoal"
                        >
                            <span>Biaya Layanan</span>
                            <span
                                class="rounded-full border border-eco-green/30 bg-sprout-tint px-2 py-0.5 text-xs font-bold text-eco-green-dark"
                            >
                                Gratis
                            </span>
                        </div>

                        <div
                            class="flex items-baseline justify-between border-t-2 border-border-gray/70 pt-4"
                        >
                            <div>
                                <span
                                    class="block text-sm leading-none font-black text-night-ink"
                                    >Total Tagihan</span
                                >
                                <span
                                    class="text-[11px] font-semibold text-charcoal"
                                >
                                    {{
                                        isPaidWithPointsOnly
                                            ? 'Dibayar penuh via Poin'
                                            : 'Sisa dibayar via Midtrans'
                                    }}
                                </span>
                            </div>
                            <span class="text-[26px] font-black text-night-ink">
                                {{ formatRupiah(payableAmount) }}
                            </span>
                        </div>
                    </div>

                    <!-- Info Metode Pembayaran -->
                    <div
                        class="rounded-xl border border-border-gray/80 bg-cloud-surface p-3.5"
                    >
                        <span
                            class="block text-[11px] font-bold tracking-wider text-charcoal uppercase"
                        >
                            Metode Pembayaran
                        </span>
                        <div class="mt-1.5 flex items-center gap-2.5">
                            <div
                                class="flex size-7 items-center justify-center rounded-lg text-night-ink"
                                :class="
                                    isPaidWithPointsOnly
                                        ? 'bg-sprout-tint'
                                        : 'bg-sky-tint'
                                "
                            >
                                <Coins
                                    v-if="isPaidWithPointsOnly"
                                    class="size-4 text-sunlight-gold"
                                />
                                <CreditCard
                                    v-else
                                    class="size-4 text-sky-blue-dark"
                                />
                            </div>
                            <div>
                                <div
                                    class="text-xs font-extrabold text-night-ink"
                                >
                                    {{
                                        isPaidWithPointsOnly
                                            ? 'Poin Bank Sampah (Langsung Aktif)'
                                            : 'Midtrans Payment Gateway'
                                    }}
                                </div>
                                <div class="text-[11px] text-charcoal">
                                    {{
                                        isPaidWithPointsOnly
                                            ? 'Lunas tanpa perlu transfer'
                                            : 'QRIS, GoPay, VA Bank, Kartu'
                                    }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Checkout CTA Button -->
                    <div class="space-y-3 pt-2">
                        <!-- Mode 1: Bayar penuh dengan Poin (Rp 0) -->
                        <button
                            v-if="isPaidWithPointsOnly"
                            type="button"
                            @click="handleCheckout"
                            :disabled="isProcessing"
                            class="flex h-[52px] w-full cursor-pointer items-center justify-center gap-2 rounded-2xl border-2 border-b-[4px] border-eco-green-dark bg-eco-green text-[15px] font-black text-night-ink shadow-xs transition-all hover:brightness-105 active:translate-y-0.5 active:border-b-2 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            <CheckCircle2 class="size-5" />
                            <span>{{
                                isProcessing
                                    ? 'Memproses Langganan...'
                                    : 'Aktifkan Penuh dengan Poin'
                            }}</span>
                        </button>

                        <!-- Mode 2: Bayar via Midtrans Snap -->
                        <button
                            v-else
                            type="button"
                            @click="handleCheckout"
                            :disabled="isProcessing"
                            class="flex h-[52px] w-full cursor-pointer items-center justify-center gap-2 rounded-2xl border-2 border-b-[4px] border-eco-green-dark bg-eco-green text-[15px] font-black text-night-ink shadow-xs transition-all hover:brightness-105 active:translate-y-0.5 active:border-b-2 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            <CreditCard class="size-5" />
                            <span>{{
                                isProcessing
                                    ? 'Membuka Midtrans...'
                                    : `Bayar Sekarang (${formatRupiah(payableAmount)})`
                            }}</span>
                        </button>

                        <button
                            v-if="
                                isSnapOpen && snapToken && !isPaidWithPointsOnly
                            "
                            type="button"
                            @click="openSnapPopup(snapToken)"
                            class="flex h-[44px] w-full cursor-pointer items-center justify-center gap-2 rounded-xl border-2 border-b-[4px] border-sky-blue-dark bg-sky-blue text-xs font-black text-night-ink shadow-xs transition-all hover:brightness-105 active:translate-y-0.5 active:border-b-2"
                        >
                            <ExternalLink class="size-4" />
                            <span>Buka Ulang Jendela Pembayaran Midtrans</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
