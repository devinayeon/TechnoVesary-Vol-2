<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import DashboardLayout from '@/layouts/DashboardLayout.vue';
import LocationPicker from '@/components/Map/LocationPicker.vue';
import type { ProductItem } from '@/components/Product/ProductCard.vue';
import {
    ArrowLeft,
    Package,
    Coins,
    MapPin,
    CreditCard,
    CheckCircle2,
    ShieldCheck,
    AlertCircle,
    ExternalLink,
} from 'lucide-vue-next';

const props = defineProps<{
    product: ProductItem;
    quantity: number;
    customerPoints: number;
    defaultAddress: {
        address: string;
        latitude: number | null;
        longitude: number | null;
    };
    clientKey: string;
    snapJsUrl: string;
    basemapApiKey?: string;
}>();

// Form & Interaction State
const deliveryAddress = ref(props.defaultAddress.address || '');
const latitude = ref<number | null>(props.defaultAddress.latitude);
const longitude = ref<number | null>(props.defaultAddress.longitude);
const usePoints = ref(props.customerPoints > 0);
const isProcessing = ref(false);
const errorMessage = ref<string | null>(null);

// OSM Location Picker State
const hasLocation = computed(
    () => latitude.value !== null && longitude.value !== null,
);

const pickerCenter = computed<[number, number]>(() => {
    if (hasLocation.value) {
        return [Number(latitude.value), Number(longitude.value)];
    }
    return [-8.1739, 113.6878];
});

const pickerInitial = computed(() =>
    hasLocation.value
        ? {
              lat: Number(latitude.value),
              lng: Number(longitude.value),
              address: deliveryAddress.value,
          }
        : null,
);

const onLocationPicked = (location: {
    lat: number;
    lng: number;
    address?: string;
}) => {
    latitude.value = location.lat;
    longitude.value = location.lng;
    if (location.address) {
        deliveryAddress.value = location.address;
    }
};

// Midtrans Snap State
const snapToken = ref<string | null>(null);
const snapUrl = ref<string | null>(null);
const isSnapOpen = ref(false);

// Price & Points Calculations
const totalPrice = computed(() => props.product.price_rupiah * props.quantity);

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

// Dynamically inject Snap script
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
                router.visit('/customer/transactions');
            },
            onPending: () => {
                router.visit('/customer/transactions');
            },
            onError: () => {
                errorMessage.value =
                    'Pembayaran via Midtrans gagal diproses. Anda dapat mencoba kembali di riwayat transaksi.';
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

    if (!deliveryAddress.value.trim()) {
        errorMessage.value = 'Alamat pengiriman wajib diisi dengan lengkap.';
        return;
    }

    // Jika Snap sudah pernah di-generate dan belum dibayar, panggil ulang popup
    if (isSnapOpen.value && snapToken.value) {
        openSnapPopup(snapToken.value);
        return;
    }

    isProcessing.value = true;

    try {
        const response = await fetch('/customer/checkout', {
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
                product_id: props.product.id,
                quantity: props.quantity,
                delivery_address: deliveryAddress.value.trim(),
                latitude: latitude.value,
                longitude: longitude.value,
                use_points: usePoints.value,
            }),
        });

        const data = await response.json();

        if (!response.ok || !data.success) {
            errorMessage.value =
                data.message ||
                'Gagal memproses pesanan. Silakan periksa kembali saldo dan stok.';
            isProcessing.value = false;
            return;
        }

        // Skenario 1: Bayar penuh dengan Poin (100% Poin)
        if (data.is_paid_fully) {
            router.visit(data.redirect_url || '/customer/transactions');
            return;
        }

        // Skenario 2: Bayar via Midtrans (Sebagian Poin atau Tanpa Poin)
        if (data.snap_token) {
            snapToken.value = data.snap_token;
            snapUrl.value = data.snap_url;
            isSnapOpen.value = true;
            isProcessing.value = false;

            openSnapPopup(data.snap_token);
        } else {
            router.visit(data.redirect_url || '/customer/transactions');
        }
    } catch (e: any) {
        errorMessage.value =
            e.message || 'Terjadi kesalahan jaringan saat memproses checkout.';
        isProcessing.value = false;
    }
};
</script>

<template>
    <Head title="Checkout Pesanan - Upcycle Store ReValue" />

    <DashboardLayout>
        <!-- Back Link -->
        <div class="mb-6">
            <Link
                :href="`/customer/products/${product.id}`"
                class="group inline-flex cursor-pointer items-center gap-2 text-sm font-bold text-charcoal transition-colors hover:text-night-ink"
            >
                <div
                    class="rounded-lg border-2 border-border-gray bg-paper-white p-1.5 transition-colors group-hover:bg-cloud-surface"
                >
                    <ArrowLeft class="size-4" />
                </div>
                <span>Kembali ke Detail Produk</span>
            </Link>
        </div>

        <!-- Page Header -->
        <div class="mb-6">
            <h1
                class="text-[24px] font-black tracking-tight text-night-ink sm:text-[30px]"
            >
                Checkout Pesanan
            </h1>
            <p class="mt-1 text-sm font-medium text-charcoal">
                Selesaikan transaksi produk upcycle ramah lingkungan dan
                tentukan metode pembayaran Anda.
            </p>
        </div>

        <!-- Error Alert -->
        <div
            v-if="errorMessage"
            class="animate-in fade-in mb-6 flex items-start gap-3 rounded-2xl border-2 border-red-300 bg-red-50 p-4 text-sm font-bold text-red-800 shadow-2xs duration-200"
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
                <!-- 1. Product Summary Card -->
                <div
                    class="rounded-[24px] border-2 border-b-[5px] border-border-gray bg-paper-white p-6 shadow-xs"
                >
                    <h2
                        class="text-muted-text mb-4 flex items-center gap-2 text-xs font-bold tracking-wider uppercase"
                    >
                        <Package class="size-4 text-eco-green-dark" />
                        <span>Barang yang Dipesan</span>
                    </h2>

                    <div class="flex items-center gap-4">
                        <div
                            class="flex size-20 shrink-0 items-center justify-center overflow-hidden rounded-2xl border-2 border-border-gray/70 bg-cloud-surface p-1.5"
                        >
                            <img
                                v-if="product.photo_url || product.photo"
                                :src="
                                    product.photo_url ||
                                    (product.photo?.startsWith('http')
                                        ? product.photo
                                        : `/storage/${product.photo}`)
                                "
                                :alt="product.name"
                                class="h-full w-full rounded-xl object-cover"
                            />
                            <Package v-else class="size-8 text-charcoal/50" />
                        </div>

                        <div class="min-w-0 flex-1">
                            <h3
                                class="mb-1 truncate text-[16px] font-extrabold text-night-ink"
                            >
                                {{ product.name }}
                            </h3>
                            <div class="flex flex-wrap items-baseline gap-2">
                                <span class="text-sm font-bold text-night-ink">
                                    {{ formatRupiah(product.price_rupiah) }}
                                </span>
                                <span
                                    class="text-xs font-semibold text-charcoal"
                                >
                                    × {{ quantity }} item
                                </span>
                            </div>
                        </div>

                        <div class="shrink-0 text-right">
                            <span
                                class="text-muted-text mb-0.5 block text-xs font-bold"
                                >Subtotal</span
                            >
                            <span class="text-base font-black text-night-ink">
                                {{ formatRupiah(totalPrice) }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- 2. Delivery Address Card with Interactive OSM LocationPicker -->
                <div
                    class="rounded-[24px] border-2 border-b-[5px] border-border-gray bg-paper-white p-6 shadow-xs"
                >
                    <div class="mb-3 flex items-center justify-between">
                        <h2
                            class="text-muted-text flex items-center gap-2 text-xs font-bold tracking-wider uppercase"
                        >
                            <MapPin class="size-4 text-sky-blue-dark" />
                            <span>Titik & Alamat Pengiriman *</span>
                        </h2>
                        <Link
                            href="/profile?tab=address"
                            class="flex items-center gap-1 text-xs font-bold text-eco-green-dark hover:underline"
                        >
                            <span>Atur Alamat Profil</span>
                            <ExternalLink class="size-3" />
                        </Link>
                    </div>

                    <!-- Interactive Map Picker -->
                    <div class="mb-4">
                        <p class="mb-2 text-xs font-semibold text-charcoal">
                            Tentukan titik tujuan pengantaran pada peta OSM
                            (geser pin atau klik tombol GPS):
                        </p>
                        <div
                            class="overflow-hidden rounded-2xl border-2 border-border-gray shadow-xs"
                        >
                            <LocationPicker
                                :initial-location="pickerInitial"
                                :default-center="pickerCenter"
                                :basemap-api-key="basemapApiKey"
                                height="320px"
                                @location-picked="onLocationPicked"
                            />
                        </div>
                    </div>

                    <!-- Detail Alamat & Patokan Input -->
                    <div>
                        <label
                            class="text-muted-text mb-1.5 block text-xs font-bold tracking-wider uppercase"
                        >
                            Alamat Lengkap & Patokan Rumah
                        </label>
                        <textarea
                            v-model="deliveryAddress"
                            rows="2"
                            placeholder="Tuliskan nama jalan, nomor rumah, RT/RW, kecamatan, dan patokan..."
                            class="w-full resize-none rounded-xl border-2 border-border-gray bg-paper-white p-3.5 text-[14px] leading-relaxed text-night-ink transition focus:border-eco-green focus:ring-4 focus:ring-eco-green/15 focus:outline-none"
                        />
                    </div>
                    <p class="mt-2 text-[12px] font-medium text-charcoal">
                        Driver ReValue akan mengantarkan pesanan langsung ke
                        titik dan alamat di atas setelah pesanan lunas.
                    </p>
                </div>

                <!-- 3. Points Redemption Card (Poin Shopee Concept) -->
                <div
                    class="rounded-[24px] border-2 border-b-[5px] border-border-gray bg-paper-white p-6 shadow-xs"
                >
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <div class="flex items-center gap-2.5">
                            <div
                                class="flex size-10 items-center justify-center rounded-xl border-2 border-sunlight-gold bg-gold-tint"
                            >
                                <Coins class="text-sunlight-gold-dark size-5" />
                            </div>
                            <div>
                                <h3
                                    class="text-[16px] font-black text-night-ink"
                                >
                                    Poin Bank Sampah ReValue
                                </h3>
                                <p class="text-xs font-bold text-charcoal">
                                    Saldo:
                                    <span class="text-eco-green-dark"
                                        >{{
                                            customerPoints.toLocaleString(
                                                'id-ID',
                                            )
                                        }}
                                        Poin</span
                                    >
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
                            <span class="font-black text-eco-green-dark"
                                >{{ pointsUsed.toLocaleString('id-ID') }} Poin
                                (-{{ formatRupiah(pointsDiscount) }})</span
                            >
                        </div>
                        <div
                            class="flex items-center justify-between border-t border-eco-green/30 pt-1"
                        >
                            <span>Sisa poin setelah transaksi:</span>
                            <span class="font-bold text-charcoal"
                                >{{
                                    remainingPointsAfter.toLocaleString('id-ID')
                                }}
                                Poin</span
                            >
                        </div>

                        <p
                            v-if="isPaidWithPointsOnly"
                            class="pt-2 font-black text-eco-green-dark"
                        >
                            ✓ Saldo poin Anda mencukupi untuk melunasi 100%
                            pesanan ini. Anda tidak perlu membayar rupiah atau
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
                        Aktifkan opsi ini untuk memotong langsung total tagihan
                        Anda dengan perolehan poin dari setoran sampah terpilah
                        (1 Poin = Rp 1).
                    </p>
                </div>
            </div>

            <!-- Right Column: Cost Breakdown & Checkout CTA (5 Cols) -->
            <div class="flex flex-col gap-6 lg:col-span-5">
                <div
                    class="sticky top-6 flex flex-col gap-5 rounded-[26px] border-2 border-b-[5px] border-border-gray bg-paper-white p-6 shadow-xs sm:p-7"
                >
                    <h2
                        class="text-muted-text text-xs font-bold tracking-wider uppercase"
                    >
                        Rincian Pembayaran
                    </h2>

                    <!-- Cost Breakdown List -->
                    <div class="space-y-3 text-sm">
                        <div class="flex items-center justify-between">
                            <span class="font-medium text-charcoal"
                                >Total Harga Produk</span
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
                                <Coins class="size-3.5" />
                                Potongan Poin ReValue
                            </span>
                            <span class="font-black"
                                >-{{ formatRupiah(pointsDiscount) }}</span
                            >
                        </div>

                        <div
                            class="flex items-center justify-between font-medium text-charcoal"
                        >
                            <span>Biaya Pengantaran</span>
                            <span
                                class="rounded-full border border-eco-green/30 bg-sprout-tint px-2 py-0.5 text-xs font-bold text-eco-green-dark"
                            >
                                Gratis (Kurir ReValue)
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
                                    class="text-muted-text text-[11px] font-semibold"
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

                    <!-- Checkout CTA Button -->
                    <div class="pt-2">
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
                                    ? 'Memproses Pesanan...'
                                    : 'Bayar Penuh dengan Poin (Selesai)'
                            }}</span>
                        </button>

                        <!-- Mode 2: Pembayaran Midtrans Snap -->
                        <button
                            v-else
                            type="button"
                            @click="handleCheckout"
                            :disabled="isProcessing"
                            class="flex h-[52px] w-full cursor-pointer items-center justify-center gap-2 rounded-2xl border-2 border-b-[4px] border-night-ink bg-night-ink text-[15px] font-black text-white shadow-xs transition-all hover:bg-night-ink/90 active:translate-y-0.5 active:border-b-2 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            <CreditCard class="size-5 text-sprout-tint" />
                            <span v-if="isProcessing"
                                >Menghubungkan Midtrans...</span
                            >
                            <span v-else-if="isSnapOpen"
                                >Lanjutkan Pembayaran (Midtrans)</span
                            >
                            <span v-else>Bayar Sekarang (Midtrans)</span>
                        </button>
                    </div>

                    <!-- Trust Badge -->
                    <div
                        class="flex items-center justify-center gap-2 border-t border-border-gray/50 pt-3 text-xs font-semibold text-charcoal/80"
                    >
                        <ShieldCheck class="size-4 text-eco-green-dark" />
                        <span
                            >Transaksi Terverifikasi & Dilindungi Midtrans
                            Gateway</span
                        >
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
