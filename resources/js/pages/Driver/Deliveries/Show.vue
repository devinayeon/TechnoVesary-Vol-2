<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import DashboardLayout from '@/layouts/DashboardLayout.vue';
import PageHeader from '@/components/Dashboard/PageHeader.vue';
import StatusChip from '@/components/Dashboard/StatusChip.vue';
import FlashMessages from '@/components/Dashboard/FlashMessages.vue';
import UserAvatar from '@/components/Dashboard/UserAvatar.vue';
import PickupRouteMap from '@/components/Pickup/PickupRouteMap.vue';
import PickupLocationMap from '@/components/Pickup/PickupLocationMap.vue';
import { useDriverLocation } from '@/composables/useDriverLocation';
import {
    ArrowLeft,
    Package,
    MapPin,
    Navigation,
    CheckCircle2,
    Clock,
    Camera,
    UploadCloud,
    ImagePlus,
    X,
    ExternalLink,
} from 'lucide-vue-next';

interface ProductItem {
    id: number;
    name: string;
    photo: string | null;
    photo_url?: string | null;
    price_rupiah: number;
}

interface DetailItem {
    id: number;
    product_id: number;
    quantity: number;
    unit_price: number;
    product?: ProductItem | null;
}

interface DeliveryTransaction {
    id: number;
    status: string;
    payment_method: string;
    total_amount: number;
    points_used: number;
    points_discount_amount: number;
    payable_amount: number;
    delivery_address: string;
    latitude: string | null;
    longitude: string | null;
    delivery_proof_photo: string | null;
    settled_at: string | null;
    delivered_at: string | null;
    canceled_at: string | null;
    created_at: string;
    updated_at?: string;
    customer?: {
        id: number;
        user?: {
            id: number;
            name: string;
            email: string;
            photo: string | null;
        } | null;
    } | null;
    details?: DetailItem[];
}

const props = withDefaults(
    defineProps<{
        delivery: DeliveryTransaction;
        driverLocation?: {
            latitude: number | null;
            longitude: number | null;
        } | null;
        basemapApiKey?: string;
    }>(),
    {
        driverLocation: null,
    },
);

// Automatic Driver Location Sharing during 'delivering' status
const shouldTrackLocation = computed(
    () => props.delivery.status === 'delivering',
);

const {
    isTracking: isLocationTracking,
    lastSentAt: lastLocationSentAt,
    error: locationError,
    start: startLocation,
    stop: stopLocation,
} = useDriverLocation({
    minDistanceMeters: 30,
    heartbeatMs: 60_000,
});

watch(
    shouldTrackLocation,
    (active) => {
        if (active) {
            startLocation();
        } else {
            stopLocation();
        }
    },
    { immediate: true },
);

const driverLat = computed(() => props.driverLocation?.latitude ?? null);
const driverLng = computed(() => props.driverLocation?.longitude ?? null);

const hasCustomerCoords = computed(
    () =>
        Number.isFinite(Number(props.delivery.latitude)) &&
        Number.isFinite(Number(props.delivery.longitude)) &&
        props.delivery.latitude !== null &&
        props.delivery.longitude !== null,
);

const form = useForm({
    delivery_proof_photo: null as File | null,
});

const previewUrl = ref<string | null>(null);
const fileInputRef = ref<HTMLInputElement | null>(null);

const onFileSelected = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        const file = target.files[0];
        form.delivery_proof_photo = file;
        if (previewUrl.value) URL.revokeObjectURL(previewUrl.value);
        previewUrl.value = URL.createObjectURL(file);
    }
};

const onDropFile = (event: DragEvent) => {
    if (event.dataTransfer?.files && event.dataTransfer.files[0]) {
        const file = event.dataTransfer.files[0];
        form.delivery_proof_photo = file;
        if (previewUrl.value) URL.revokeObjectURL(previewUrl.value);
        previewUrl.value = URL.createObjectURL(file);
    }
};

const removeSelectedFile = () => {
    form.delivery_proof_photo = null;
    if (previewUrl.value) {
        URL.revokeObjectURL(previewUrl.value);
        previewUrl.value = null;
    }
    if (fileInputRef.value) {
        fileInputRef.value.value = '';
    }
};

const submitCompleteDelivery = () => {
    if (!form.delivery_proof_photo) {
        alert('Silakan pilih atau unggah foto bukti serah terima pesanan.');
        return;
    }

    form.post(`/driver/deliveries/${props.delivery.id}/complete`, {
        preserveScroll: true,
        onError: () => {
            // Error ditangani via FlashMessages & form.errors
        },
    });
};

const getStatusTone = (
    status: string,
): 'Green' | 'Amber' | 'Blue' | 'Neutral' => {
    switch (status) {
        case 'delivering':
            return 'Blue';
        case 'delivered':
            return 'Amber';
        case 'completed':
            return 'Green';
        default:
            return 'Neutral';
    }
};

const getStatusLabel = (status: string): string => {
    switch (status) {
        case 'delivering':
            return 'Sedang Dikirim';
        case 'delivered':
            return 'Pesanan Terkirim';
        case 'completed':
            return 'Pesanan Selesai';
        default:
            return status;
    }
};

const formatRupiah = (val: number) => {
    return 'Rp ' + Number(val).toLocaleString('id-ID');
};

const formatDate = (dateStr?: string | null) => {
    if (!dateStr) return '-';
    return new Date(dateStr).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const mapsNavigationUrl = computed(() => {
    if (props.delivery.latitude && props.delivery.longitude) {
        return `https://www.google.com/maps/search/?api=1&query=${props.delivery.latitude},${props.delivery.longitude}`;
    }
    return `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(props.delivery.delivery_address)}`;
});
</script>

<template>
    <Head :title="`Pengantaran #TRX-${delivery.id} - Portal Driver`" />

    <DashboardLayout>
        <!-- Flash Messages -->
        <FlashMessages class="mb-6" />

        <!-- Back Link & Header -->
        <div class="mb-6">
            <Link
                href="/driver/deliveries"
                class="text-muted-text mb-3 inline-flex items-center gap-2 text-xs font-black transition-colors hover:text-night-ink"
            >
                <ArrowLeft class="size-4" />
                <span>Kembali ke Daftar Pengantaran</span>
            </Link>

            <div
                class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center"
            >
                <div>
                    <h1
                        class="flex items-center gap-2.5 text-2xl font-black tracking-tight text-night-ink"
                    >
                        <span>Pengantaran Pesanan #TRX-{{ delivery.id }}</span>
                        <StatusChip
                            :tone="getStatusTone(delivery.status)"
                            :label="getStatusLabel(delivery.status)"
                        />
                    </h1>
                    <p class="text-muted-text mt-1 text-xs font-semibold">
                        Dibuat pada {{ formatDate(delivery.created_at) }}
                    </p>
                </div>

                <a
                    :href="mapsNavigationUrl"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex shrink-0 cursor-pointer items-center justify-center gap-2 rounded-xl border-2 border-b-[4px] border-sky-blue bg-sky-blue px-5 py-2.5 text-xs font-black text-white shadow-sm transition-all hover:bg-sky-blue/90 active:translate-y-0.5 active:border-b-2"
                >
                    <Navigation class="size-4" />
                    <span>Buka Rute di Google Maps</span>
                    <ExternalLink class="size-3.5 opacity-80" />
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
            <!-- Left Column: Map, Recipient & Order Details (7 cols) -->
            <div class="space-y-6 lg:col-span-7">
                <!-- Interactive OSM Route & Location Map -->
                <div
                    class="rounded-2xl border-2 border-b-[5px] border-border-gray bg-white p-5"
                >
                    <div class="mb-3 flex items-center justify-between">
                        <h2
                            class="text-muted-text flex items-center gap-1.5 text-xs font-black tracking-wider uppercase"
                        >
                            <Navigation class="size-4 text-sky-blue" />
                            <span>{{
                                delivery.status === 'delivering'
                                    ? 'Peta Rute OSRM Driver'
                                    : 'Peta Lokasi Pengantaran'
                            }}</span>
                        </h2>
                        <span
                            v-if="delivery.status === 'delivering'"
                            class="flex items-center gap-1 text-[11px] font-bold text-sky-blue"
                        >
                            <span>Rute Real-Time</span>
                        </span>
                    </div>

                    <!-- OSM Route Map (When delivering and coords available) -->
                    <div
                        v-if="
                            delivery.status === 'delivering' &&
                            hasCustomerCoords
                        "
                        class="mb-3"
                    >
                        <PickupRouteMap
                            :origin-lat="driverLat"
                            :origin-lng="driverLng"
                            :destination-lat="delivery.latitude ?? 0"
                            :destination-lng="delivery.longitude ?? 0"
                            origin-label="Armada Driver"
                            destination-label="Alamat Pembeli"
                            :basemap-api-key="basemapApiKey"
                            height="360px"
                        />
                    </div>

                    <!-- OSM Single Location Map (When delivered/completed or fallback) -->
                    <div v-else-if="hasCustomerCoords" class="mb-3">
                        <PickupLocationMap
                            :lat="delivery.latitude"
                            :lng="delivery.longitude"
                            label="Titik Pengantaran"
                            :basemap-api-key="basemapApiKey"
                            height="320px"
                        />
                    </div>

                    <!-- Address Box -->
                    <div
                        class="flex items-start gap-2 rounded-xl border border-border-gray/70 bg-cloud-surface p-3.5 text-xs leading-relaxed font-bold text-night-ink"
                    >
                        <MapPin class="mt-0.5 size-4 shrink-0 text-eco-green" />
                        <div>
                            <span
                                class="text-muted-text mb-0.5 block text-[10px] font-extrabold uppercase"
                                >Alamat Pengiriman:</span
                            >
                            <span>{{ delivery.delivery_address }}</span>
                        </div>
                    </div>
                </div>

                <!-- Penerima & Kontak Card -->
                <div
                    class="rounded-2xl border-2 border-b-[5px] border-border-gray bg-white p-5"
                >
                    <h2
                        class="text-muted-text mb-4 flex items-center gap-1.5 text-xs font-black tracking-wider uppercase"
                    >
                        <MapPin class="size-4 text-eco-green" /> Informasi
                        Penerima
                    </h2>

                    <div class="space-y-4">
                        <!-- Customer -->
                        <div
                            class="flex items-center gap-3 rounded-xl border border-border-gray/70 bg-cloud-surface p-3"
                        >
                            <UserAvatar
                                :user="delivery.customer?.user"
                                size="md"
                            />
                            <div>
                                <p class="text-sm font-black text-night-ink">
                                    {{
                                        delivery.customer?.user?.name ??
                                        'Pelanggan ReValue'
                                    }}
                                </p>
                                <p
                                    class="text-muted-text text-xs font-semibold"
                                >
                                    {{ delivery.customer?.user?.email ?? '-' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Produk yang Dibawa Card -->
                <div
                    class="rounded-2xl border-2 border-b-[5px] border-border-gray bg-white p-5"
                >
                    <h2
                        class="text-muted-text mb-4 flex items-center gap-1.5 text-xs font-black tracking-wider uppercase"
                    >
                        <Package class="size-4 text-sunlight-gold" /> Produk
                        yang Harus Diantarkan
                    </h2>

                    <div
                        class="overflow-hidden rounded-xl border border-border-gray/70"
                    >
                        <table class="w-full text-xs">
                            <thead
                                class="border-b border-border-gray/70 bg-cloud-surface"
                            >
                                <tr>
                                    <th
                                        class="px-4 py-2.5 text-left text-[11px] font-black text-charcoal uppercase"
                                    >
                                        Produk
                                    </th>
                                    <th
                                        class="w-20 px-4 py-2.5 text-center text-[11px] font-black text-charcoal uppercase"
                                    >
                                        Qty
                                    </th>
                                    <th
                                        class="w-32 px-4 py-2.5 text-right text-[11px] font-black text-charcoal uppercase"
                                    >
                                        Subtotal
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border-gray/40">
                                <tr
                                    v-for="detail in delivery.details"
                                    :key="detail.id"
                                    class="hover:bg-cloud-surface/30"
                                >
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="flex size-10 shrink-0 items-center justify-center overflow-hidden rounded-lg border border-border-gray bg-cloud-surface"
                                            >
                                                <img
                                                    v-if="
                                                        detail.product
                                                            ?.photo_url ||
                                                        detail.product?.photo
                                                    "
                                                    :src="
                                                        detail.product
                                                            ?.photo_url ||
                                                        `/storage/${detail.product?.photo}`
                                                    "
                                                    :alt="detail.product?.name"
                                                    class="h-full w-full object-cover"
                                                />
                                                <Package
                                                    v-else
                                                    class="text-muted-text size-4"
                                                />
                                            </div>
                                            <span
                                                class="font-bold text-night-ink"
                                                >{{
                                                    detail.product?.name ??
                                                    'Produk Upcycle'
                                                }}</span
                                            >
                                        </div>
                                    </td>
                                    <td
                                        class="px-4 py-3 text-center font-black text-night-ink"
                                    >
                                        {{ detail.quantity }}
                                    </td>
                                    <td
                                        class="px-4 py-3 text-right font-black text-night-ink"
                                    >
                                        {{
                                            formatRupiah(
                                                detail.quantity *
                                                    detail.unit_price,
                                            )
                                        }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Payment Summary -->
                    <div
                        class="mt-4 flex items-center justify-between rounded-xl border border-border-gray/70 bg-cloud-surface p-3.5"
                    >
                        <div>
                            <span
                                class="text-muted-text block text-[11px] font-bold"
                                >Metode Pembayaran</span
                            >
                            <span
                                class="text-xs font-black text-night-ink uppercase"
                            >
                                {{
                                    delivery.payment_method === 'midtrans'
                                        ? 'Midtrans (Lunas)'
                                        : 'Poin Bank Sampah'
                                }}
                            </span>
                        </div>
                        <div class="text-right">
                            <span
                                class="text-muted-text block text-[11px] font-bold"
                                >Total Belanja</span
                            >
                            <span class="text-sm font-black text-eco-green">
                                {{ formatRupiah(delivery.total_amount) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Delivery Proof & Fulfillment (5 cols) -->
            <div class="space-y-6 lg:col-span-5">
                <!-- Automatic Location Sharing Status -->
                <div
                    v-if="shouldTrackLocation"
                    class="rounded-2xl border-2 border-b-[4px] border-sky-blue/40 bg-sky-tint p-4"
                >
                    <h3
                        class="mb-1 flex items-center gap-2 text-xs font-black tracking-wider text-charcoal uppercase"
                    >
                        <Navigation class="size-3.5 text-sky-blue-dark" />
                        <span>Berbagi Lokasi Armada</span>
                    </h3>
                    <p
                        class="text-[11px] leading-relaxed font-medium text-charcoal"
                    >
                        Posisi Anda terkirim otomatis setiap bergerak &plusmn;30
                        meter atau tiap 60 detik agar pembeli dapat memantau
                        kedatangan pesanan di peta.
                    </p>
                    <div class="mt-2.5 flex items-center gap-2">
                        <span
                            v-if="isLocationTracking"
                            class="inline-flex items-center gap-1.5 text-xs font-black text-eco-green-dark"
                        >
                            <span class="relative flex size-2">
                                <span
                                    class="absolute inline-flex size-full animate-ping rounded-full bg-eco-green opacity-75"
                                />
                                <span
                                    class="relative inline-flex size-2 rounded-full bg-eco-green"
                                />
                            </span>
                            Pelacakan GPS Aktif
                        </span>
                        <span
                            v-if="locationError"
                            class="text-[11px] font-bold text-red-600"
                        >
                            {{ locationError }}
                        </span>
                    </div>
                </div>

                <!-- State 1: Sedang Dikirim (Driver Form to Upload Proof) -->
                <div
                    v-if="delivery.status === 'delivering'"
                    class="rounded-2xl border-2 border-b-[5px] border-border-gray bg-white p-5"
                >
                    <div class="mb-2 flex items-center gap-2">
                        <div
                            class="flex size-8 items-center justify-center rounded-lg bg-sprout-tint text-eco-green"
                        >
                            <Camera class="size-4" />
                        </div>
                        <h2 class="text-sm font-black text-night-ink">
                            Unggah Bukti Serah Terima
                        </h2>
                    </div>
                    <p class="text-muted-text mb-4 text-xs font-semibold">
                        Ambil foto barang saat diserahkan ke penerima atau di
                        depan pintu rumah pembeli sebagai bukti pengantaran.
                    </p>

                    <form
                        @submit.prevent="submitCompleteDelivery"
                        class="space-y-4"
                    >
                        <!-- Dropzone / File Picker -->
                        <div>
                            <input
                                ref="fileInputRef"
                                type="file"
                                accept="image/jpeg,image/png,image/jpg,image/webp"
                                class="hidden"
                                @change="onFileSelected"
                            />

                            <!-- Preview if selected -->
                            <div
                                v-if="previewUrl"
                                class="group relative overflow-hidden rounded-xl border-2 border-border-gray bg-cloud-surface"
                            >
                                <img
                                    :src="previewUrl"
                                    alt="Preview Bukti"
                                    class="max-h-56 w-full object-cover"
                                />
                                <button
                                    type="button"
                                    @click="removeSelectedFile"
                                    class="absolute top-2 right-2 flex size-8 cursor-pointer items-center justify-center rounded-full bg-night-ink/80 text-white transition-all hover:bg-night-ink"
                                    title="Hapus Foto"
                                >
                                    <X class="size-4" />
                                </button>
                            </div>

                            <!-- Dropzone if not selected -->
                            <div
                                v-else
                                @click="fileInputRef?.click()"
                                @dragover.prevent
                                @drop.prevent="onDropFile"
                                class="group cursor-pointer rounded-xl border-2 border-dashed border-border-gray bg-cloud-surface/50 p-6 text-center transition-all hover:border-eco-green hover:bg-sprout-tint/20"
                            >
                                <div
                                    class="text-muted-text mx-auto mb-2 flex size-12 items-center justify-center rounded-xl border border-border-gray/70 bg-white transition-colors group-hover:text-eco-green"
                                >
                                    <UploadCloud class="size-6" />
                                </div>
                                <p class="text-xs font-bold text-night-ink">
                                    Klik atau seret foto bukti ke sini
                                </p>
                                <p class="text-muted-text mt-0.5 text-[11px]">
                                    Format JPG, PNG, atau WEBP. Maksimal 4 MB.
                                </p>
                            </div>

                            <!-- Error Message -->
                            <p
                                v-if="form.errors.delivery_proof_photo"
                                class="mt-2 text-xs font-bold text-red-600"
                            >
                                {{ form.errors.delivery_proof_photo }}
                            </p>
                        </div>

                        <!-- Submit CTA Button -->
                        <button
                            type="submit"
                            :disabled="
                                form.processing || !form.delivery_proof_photo
                            "
                            class="hover:bg-forest-green flex w-full cursor-pointer items-center justify-center gap-2 rounded-xl border-2 border-b-[4px] border-eco-green bg-eco-green px-5 py-3 text-xs font-black text-white shadow-sm transition-all active:translate-y-0.5 active:border-b-2 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            <CheckCircle2 class="size-4" />
                            <span>{{
                                form.processing
                                    ? 'Mengunggah Bukti...'
                                    : 'Kirim Bukti & Tandai Terkirim'
                            }}</span>
                        </button>
                    </form>
                </div>

                <!-- State 2: Terkirim / Selesai (Bukti Sudah Ada) -->
                <div
                    v-else
                    class="space-y-4 rounded-2xl border-2 border-b-[5px] border-border-gray bg-white p-5"
                >
                    <div class="flex items-center gap-2">
                        <div
                            class="flex size-8 items-center justify-center rounded-lg bg-sprout-tint text-eco-green"
                        >
                            <CheckCircle2 class="size-4" />
                        </div>
                        <div>
                            <h2 class="text-sm font-black text-night-ink">
                                {{
                                    delivery.status === 'completed'
                                        ? 'Pengantaran Selesai'
                                        : 'Pengantaran Terkirim'
                                }}
                            </h2>
                            <p
                                class="text-muted-text text-[11px] font-semibold"
                            >
                                Diantar pada
                                {{
                                    formatDate(
                                        delivery.delivered_at ||
                                            delivery.updated_at,
                                    )
                                }}
                            </p>
                        </div>
                    </div>

                    <!-- Photo Display -->
                    <div v-if="delivery.delivery_proof_photo">
                        <span
                            class="text-muted-text mb-1.5 block text-[11px] font-black uppercase"
                        >
                            Foto Bukti Serah Terima:
                        </span>
                        <img
                            :src="delivery.delivery_proof_photo"
                            alt="Bukti Serah Terima Driver"
                            class="max-h-64 w-full rounded-xl border-2 border-border-gray object-cover"
                        />
                    </div>

                    <!-- Status Notification Banner -->
                    <div
                        v-if="delivery.status === 'delivered'"
                        class="rounded-xl border border-sunlight-gold/30 bg-gold-tint/40 p-3.5 text-xs leading-relaxed text-night-ink"
                    >
                        <p
                            class="mb-1 flex items-center gap-1.5 font-bold text-sunlight-gold"
                        >
                            <Clock class="size-3.5" /> Menunggu Konfirmasi
                            Pelanggan
                        </p>
                        Pesanan telah Anda antarkan ke tujuan. Pembeli akan
                        memeriksa kondisi produk dan menekan tombol konfirmasi
                        selesai.
                    </div>

                    <div
                        v-else-if="delivery.status === 'completed'"
                        class="rounded-xl border border-eco-green/30 bg-sprout-tint p-3.5 text-xs leading-relaxed text-night-ink"
                    >
                        <p
                            class="mb-1 flex items-center gap-1.5 font-bold text-eco-green"
                        >
                            <CheckCircle2 class="size-3.5" /> Pesanan Telah
                            Diterima Pelanggan
                        </p>
                        Pelanggan telah mengonfirmasi penerimaan barang dan
                        transaksi resmi selesai sepenuhnya. Kerja bagus!
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
