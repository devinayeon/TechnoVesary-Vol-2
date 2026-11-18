<script setup lang="ts">
import { computed } from 'vue';
import {
    X,
    ShoppingCart,
    MapPin,
    Package,
    Truck,
    CreditCard,
    Coins,
    AlertCircle,
    CheckCircle2,
    RotateCcw,
} from 'lucide-vue-next';
import StatusChip from '@/components/Dashboard/StatusChip.vue';
import UserAvatar from '@/components/Dashboard/UserAvatar.vue';
import PickupRouteMap from '@/components/Pickup/PickupRouteMap.vue';
import PickupLocationMap from '@/components/Pickup/PickupLocationMap.vue';

export interface TransactionDetailItem {
    id: number;
    transaction_id: number;
    product_id: number;
    quantity: number;
    unit_price: number;
    product?: {
        id: number;
        name: string;
        photo: string | null;
        photo_url?: string | null;
    } | null;
}

export interface CustomerTransactionData {
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
    snap_url: string | null;
    settled_at: string | null;
    delivered_at: string | null;
    canceled_at: string | null;
    created_at: string;
    updated_at: string;
    driver?: {
        id: number;
        current_latitude?: number | string | null;
        current_longitude?: number | string | null;
        location_updated_at?: string | null;
        user?: {
            id: number;
            name: string;
            email: string;
            photo: string | null;
            role: string;
        } | null;
    } | null;
    details?: TransactionDetailItem[];
}

const props = defineProps<{
    show: boolean;
    transaction: CustomerTransactionData | null;
    isCanceling?: boolean;
    isRepaying?: boolean;
    isCheckingStatus?: boolean;
    isConfirming?: boolean;
    basemapApiKey?: string;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'cancel', transaction: CustomerTransactionData): void;
    (e: 'repay', transaction: CustomerTransactionData): void;
    (e: 'checkStatus', transaction: CustomerTransactionData): void;
    (e: 'confirmReceived', transaction: CustomerTransactionData): void;
}>();

const statusTone = computed((): 'Green' | 'Amber' | 'Blue' | 'Neutral' => {
    if (!props.transaction) return 'Neutral';
    const map: Record<string, 'Green' | 'Amber' | 'Blue' | 'Neutral'> = {
        pending: 'Amber',
        processing: 'Blue',
        delivering: 'Blue',
        delivered: 'Amber',
        completed: 'Green',
        canceled: 'Neutral',
    };
    return map[props.transaction.status] ?? 'Neutral';
});

const statusLabel = computed(() => {
    if (!props.transaction) return '';
    const map: Record<string, string> = {
        pending: 'Menunggu Pembayaran',
        processing: 'Sedang Diproses Gudang',
        delivering: 'Sedang Dikirim Driver',
        delivered: 'Pesanan Terkirim',
        completed: 'Pesanan Selesai',
        canceled: 'Pesanan Dibatalkan',
    };
    return map[props.transaction.status] ?? props.transaction.status;
});

const paymentMethodLabel = computed(() => {
    if (!props.transaction) return '';
    return props.transaction.payment_method === 'points'
        ? 'Poin Bank Sampah (Full)'
        : 'Midtrans Payment Gateway';
});

const paymentTone = computed((): 'Green' | 'Amber' | 'Blue' | 'Neutral' => {
    if (!props.transaction) return 'Neutral';
    return props.transaction.payment_method === 'points' ? 'Green' : 'Blue';
});

const formatDate = (dateStr: string | null | undefined) => {
    if (!dateStr) return '-';
    return new Date(dateStr).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const formatRupiah = (n: number) => 'Rp ' + n.toLocaleString('id-ID');

const hasDestinationCoords = computed(() => {
    if (!props.transaction) return false;
    const lat = Number(props.transaction.latitude);
    const lng = Number(props.transaction.longitude);
    return (
        Number.isFinite(lat) &&
        Number.isFinite(lng) &&
        props.transaction.latitude !== null &&
        props.transaction.longitude !== null
    );
});

const driverLat = computed(
    () => props.transaction?.driver?.current_latitude ?? null,
);
const driverLng = computed(
    () => props.transaction?.driver?.current_longitude ?? null,
);
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="show && transaction"
                class="fixed inset-0 z-50 flex items-center justify-center p-4"
                @click.self="emit('close')"
            >
                <!-- Backdrop -->
                <div
                    class="absolute inset-0 bg-night-ink/40 backdrop-blur-sm"
                    @click="emit('close')"
                />

                <!-- Modal Card -->
                <div
                    class="relative z-10 max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-[26px] border-2 border-b-[6px] border-border-gray bg-paper-white shadow-2xl"
                >
                    <!-- Header -->
                    <div
                        class="flex items-start justify-between gap-4 border-b border-border-gray/60 p-6"
                    >
                        <div class="min-w-0 flex-1">
                            <div class="mb-1.5 flex items-center gap-2">
                                <ShoppingCart
                                    class="size-5 shrink-0 text-eco-green-dark"
                                />
                                <h2
                                    class="truncate text-lg font-black text-night-ink"
                                >
                                    Pesanan #TRX-{{ transaction.id }}
                                </h2>
                            </div>
                            <div class="flex flex-wrap items-center gap-2">
                                <StatusChip
                                    :tone="statusTone"
                                    :label="statusLabel"
                                />
                                <span
                                    class="text-xs font-semibold text-charcoal"
                                >
                                    {{ formatDate(transaction.created_at) }}
                                </span>
                            </div>
                        </div>
                        <button
                            @click="emit('close')"
                            class="shrink-0 cursor-pointer rounded-xl border-2 border-b-[3px] border-border-gray p-2 text-charcoal transition-all hover:bg-cloud-surface hover:text-night-ink active:translate-y-0.5 active:border-b-2"
                        >
                            <X class="size-4" />
                        </button>
                    </div>

                    <!-- Body -->
                    <div class="space-y-6 p-6">
                        <!-- Pending Alert -->
                        <div
                            v-if="transaction.status === 'pending'"
                            class="flex items-start gap-3 rounded-xl border-2 border-amber-300 bg-amber-50 p-4"
                        >
                            <AlertCircle
                                class="mt-0.5 size-5 shrink-0 text-amber-600"
                            />
                            <div
                                class="text-xs leading-relaxed font-semibold text-amber-900"
                            >
                                Pesanan ini belum dibayar. Silakan selesaikan
                                pembayaran agar barang segera disiapkan oleh
                                gudang dan diantar oleh driver.
                            </div>
                        </div>

                        <!-- Delivered Alert (Kurir sudah mengantar) -->
                        <div
                            v-if="transaction.status === 'delivered'"
                            class="flex items-start gap-3 rounded-xl border-2 border-sunlight-gold/40 bg-gold-tint/40 p-4"
                        >
                            <CheckCircle2
                                class="mt-0.5 size-5 shrink-0 text-sunlight-gold"
                            />
                            <div class="text-xs leading-relaxed text-night-ink">
                                <p
                                    class="mb-0.5 text-sm font-black text-night-ink"
                                >
                                    Kurir Telah Mengantarkan Pesanan Anda!
                                </p>
                                Silakan periksa foto bukti serah terima dan
                                fisik barang yang diterima di bawah. Jika
                                pesanan sudah sesuai, klik tombol
                                <b>Konfirmasi Terima Pesanan</b> di bawah untuk
                                menyelesaikan transaksi.
                            </div>
                        </div>

                        <!-- 1. Detail Produk Dipesan -->
                        <div>
                            <h3
                                class="text-muted-text mb-3 flex items-center gap-1.5 text-xs font-bold tracking-wider uppercase"
                            >
                                <Package class="size-3.5" /> Produk Dipesan
                            </h3>
                            <div
                                class="overflow-hidden rounded-2xl border border-border-gray/70"
                            >
                                <table class="w-full text-sm">
                                    <thead
                                        class="border-b border-border-gray/70 bg-cloud-surface"
                                    >
                                        <tr>
                                            <th
                                                class="text-muted-text px-4 py-2.5 text-left text-xs font-bold"
                                            >
                                                Produk
                                            </th>
                                            <th
                                                class="text-muted-text px-4 py-2.5 text-right text-xs font-bold"
                                            >
                                                Qty
                                            </th>
                                            <th
                                                class="text-muted-text px-4 py-2.5 text-right text-xs font-bold"
                                            >
                                                Harga
                                            </th>
                                            <th
                                                class="text-muted-text px-4 py-2.5 text-right text-xs font-bold"
                                            >
                                                Subtotal
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody
                                        class="divide-y divide-border-gray/50"
                                    >
                                        <tr
                                            v-for="detail in transaction.details"
                                            :key="detail.id"
                                        >
                                            <td class="px-4 py-3">
                                                <div
                                                    class="flex items-center gap-2.5"
                                                >
                                                    <img
                                                        v-if="
                                                            detail.product
                                                                ?.photo_url ||
                                                            detail.product
                                                                ?.photo
                                                        "
                                                        :src="
                                                            detail.product
                                                                .photo_url ||
                                                            `/storage/${detail.product.photo}`
                                                        "
                                                        :alt="
                                                            detail.product.name
                                                        "
                                                        class="size-9 shrink-0 rounded-lg border border-border-gray object-cover"
                                                    />
                                                    <div
                                                        v-else
                                                        class="flex size-9 shrink-0 items-center justify-center rounded-lg border border-border-gray bg-cloud-surface"
                                                    >
                                                        <Package
                                                            class="text-muted-text size-4"
                                                        />
                                                    </div>
                                                    <span
                                                        class="text-sm font-bold text-night-ink"
                                                        >{{
                                                            detail.product
                                                                ?.name ?? '-'
                                                        }}</span
                                                    >
                                                </div>
                                            </td>
                                            <td
                                                class="px-4 py-3 text-right font-semibold text-charcoal"
                                            >
                                                {{ detail.quantity }}
                                            </td>
                                            <td
                                                class="px-4 py-3 text-right font-semibold text-charcoal"
                                            >
                                                {{
                                                    formatRupiah(
                                                        detail.unit_price,
                                                    )
                                                }}
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
                        </div>

                        <!-- 2. Rincian Pembayaran -->
                        <div>
                            <h3
                                class="text-muted-text mb-3 flex items-center gap-1.5 text-xs font-bold tracking-wider uppercase"
                            >
                                <CreditCard class="size-3.5" /> Rincian Biaya
                            </h3>
                            <div
                                class="space-y-2.5 rounded-2xl border border-border-gray/70 bg-cloud-surface p-4"
                            >
                                <div
                                    class="flex items-center justify-between text-sm"
                                >
                                    <span class="font-medium text-charcoal"
                                        >Metode Pembayaran</span
                                    >
                                    <StatusChip
                                        :tone="paymentTone"
                                        :label="paymentMethodLabel"
                                    />
                                </div>
                                <div
                                    class="flex items-center justify-between text-sm"
                                >
                                    <span class="font-medium text-charcoal"
                                        >Total Harga Produk</span
                                    >
                                    <span class="font-bold text-night-ink">{{
                                        formatRupiah(transaction.total_amount)
                                    }}</span>
                                </div>
                                <div
                                    v-if="transaction.points_used > 0"
                                    class="flex items-center justify-between text-sm"
                                >
                                    <span
                                        class="flex items-center gap-1 font-semibold text-eco-green-dark"
                                    >
                                        <Coins class="size-3.5" />
                                        Potongan Poin ({{
                                            transaction.points_used.toLocaleString(
                                                'id-ID',
                                            )
                                        }}
                                        Poin)
                                    </span>
                                    <span class="font-black text-eco-green-dark"
                                        >-{{
                                            formatRupiah(
                                                transaction.points_discount_amount,
                                            )
                                        }}</span
                                    >
                                </div>
                                <div
                                    class="flex items-center justify-between border-t border-border-gray/60 pt-2"
                                >
                                    <span
                                        class="text-sm font-black text-night-ink"
                                        >Total Tagihan (Rupiah)</span
                                    >
                                    <span
                                        class="text-base font-black text-night-ink"
                                        >{{
                                            formatRupiah(
                                                transaction.payable_amount,
                                            )
                                        }}</span
                                    >
                                </div>
                            </div>
                        </div>

                        <!-- 3. Peta OSM & Alamat Pengiriman & Driver -->
                        <div v-if="hasDestinationCoords">
                            <div class="mb-2 flex items-center justify-between">
                                <h3
                                    class="text-muted-text flex items-center gap-1.5 text-xs font-bold tracking-wider uppercase"
                                >
                                    <MapPin
                                        class="size-3.5 text-sky-blue-dark"
                                    />
                                    <span>{{
                                        transaction.status === 'delivering'
                                            ? 'Pelacakan Rute Kurir (OSRM)'
                                            : 'Peta Lokasi Tujuan'
                                    }}</span>
                                </h3>
                                <span
                                    v-if="transaction.status === 'delivering'"
                                    class="flex items-center gap-1.5 text-[11px] font-bold text-sky-blue"
                                >
                                    <span class="relative flex size-2">
                                        <span
                                            class="absolute inline-flex size-full animate-ping rounded-full bg-sky-blue opacity-75"
                                        />
                                        <span
                                            class="relative inline-flex size-2 rounded-full bg-sky-blue"
                                        />
                                    </span>
                                    Kurir Dalam Perjalanan
                                </span>
                            </div>

                            <div
                                v-if="transaction.status === 'delivering'"
                                class="mb-3"
                            >
                                <PickupRouteMap
                                    :origin-lat="driverLat"
                                    :origin-lng="driverLng"
                                    :destination-lat="transaction.latitude ?? 0"
                                    :destination-lng="
                                        transaction.longitude ?? 0
                                    "
                                    origin-label="Armada Kurir"
                                    destination-label="Alamat Anda"
                                    :basemap-api-key="basemapApiKey"
                                    height="280px"
                                />
                            </div>

                            <div v-else class="mb-3">
                                <PickupLocationMap
                                    :lat="transaction.latitude"
                                    :lng="transaction.longitude"
                                    label="Alamat Pengiriman"
                                    :basemap-api-key="basemapApiKey"
                                    height="260px"
                                />
                            </div>
                        </div>

                        <!-- Info Teks Alamat Pengiriman & Driver -->
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <h3
                                    class="text-muted-text mb-2 flex items-center gap-1.5 text-xs font-bold tracking-wider uppercase"
                                >
                                    <MapPin
                                        class="size-3.5 text-sky-blue-dark"
                                    />
                                    Alamat Lengkap
                                </h3>
                                <p
                                    class="rounded-xl border border-border-gray/70 bg-cloud-surface p-3 text-xs leading-relaxed font-medium text-night-ink"
                                >
                                    {{ transaction.delivery_address }}
                                </p>
                            </div>

                            <div>
                                <h3
                                    class="text-muted-text mb-2 flex items-center gap-1.5 text-xs font-bold tracking-wider uppercase"
                                >
                                    <Truck
                                        class="size-3.5 text-sunlight-gold"
                                    />
                                    Kurir Pengantar
                                </h3>
                                <div
                                    v-if="transaction.driver?.user"
                                    class="flex items-center gap-2.5 rounded-xl border border-border-gray/70 bg-cloud-surface p-3"
                                >
                                    <UserAvatar
                                        :user="transaction.driver.user"
                                        size="sm"
                                    />
                                    <div>
                                        <p
                                            class="text-xs font-bold text-night-ink"
                                        >
                                            {{ transaction.driver.user.name }}
                                        </p>
                                        <p class="text-muted-text text-[10px]">
                                            Driver ReValue
                                        </p>
                                    </div>
                                </div>
                                <p
                                    v-else
                                    class="rounded-xl border border-border-gray/70 bg-cloud-surface p-3 text-xs text-charcoal/70 italic"
                                >
                                    Belum ditugaskan driver
                                </p>
                            </div>
                        </div>

                        <!-- 4. Bukti Serah Terima Pesanan -->
                        <div v-if="transaction.delivery_proof_photo">
                            <h3
                                class="text-muted-text mb-2 flex items-center gap-1.5 text-xs font-bold tracking-wider uppercase"
                            >
                                <CheckCircle2
                                    class="size-3.5 text-eco-green-dark"
                                />
                                Bukti Pengantaran Driver
                            </h3>
                            <img
                                :src="transaction.delivery_proof_photo"
                                alt="Bukti Serah Terima"
                                class="max-h-48 w-full rounded-xl border-2 border-border-gray object-cover"
                            />
                        </div>
                    </div>

                    <!-- Footer Action Buttons -->
                    <div
                        class="flex flex-wrap items-center justify-between gap-3 border-t border-border-gray/60 p-6"
                    >
                        <!-- Left Action: Batalkan Pesanan (Hanya jika pending) -->
                        <div>
                            <button
                                v-if="transaction.status === 'pending'"
                                type="button"
                                @click="emit('cancel', transaction)"
                                :disabled="isCanceling"
                                class="cursor-pointer rounded-xl border-2 border-b-[3px] border-red-300 px-4 py-2.5 text-xs font-bold text-red-600 transition-all hover:bg-red-50 active:translate-y-0.5 active:border-b-2 disabled:opacity-50"
                            >
                                {{
                                    isCanceling
                                        ? 'Membatalkan...'
                                        : 'Batalkan Pesanan'
                                }}
                            </button>
                        </div>

                        <!-- Right Actions: Cek Status / Lanjutkan Pembayaran / Konfirmasi Terima / Tutup -->
                        <div class="flex items-center gap-2.5">
                            <button
                                v-if="transaction.status === 'delivered'"
                                type="button"
                                @click="emit('confirmReceived', transaction)"
                                :disabled="isConfirming"
                                class="hover:bg-forest-green flex cursor-pointer items-center gap-2 rounded-xl border-2 border-b-[4px] border-eco-green bg-eco-green px-5 py-2.5 text-xs font-black text-white shadow-sm transition-all active:translate-y-0.5 active:border-b-2 disabled:opacity-50"
                            >
                                <CheckCircle2 class="size-4" />
                                <span>{{
                                    isConfirming
                                        ? 'Menyelesaikan...'
                                        : 'Konfirmasi Terima Pesanan'
                                }}</span>
                            </button>

                            <button
                                v-if="
                                    transaction.status === 'pending' &&
                                    transaction.payment_method === 'midtrans'
                                "
                                type="button"
                                @click="emit('checkStatus', transaction)"
                                :disabled="isCheckingStatus"
                                class="flex cursor-pointer items-center gap-1.5 rounded-xl border-2 border-b-[3px] border-sunlight-gold/70 bg-sunlight-gold/15 px-4 py-2.5 text-xs font-bold text-night-ink transition-all hover:bg-sunlight-gold/25 active:translate-y-0.5 active:border-b-2 disabled:opacity-50"
                                title="Cek apakah pembayaran Anda sudah terverifikasi di Midtrans"
                            >
                                <RotateCcw
                                    class="size-3.5 text-night-ink"
                                    :class="{
                                        'animate-spin': isCheckingStatus,
                                    }"
                                />
                                <span>{{
                                    isCheckingStatus
                                        ? 'Memeriksa...'
                                        : 'Cek Status Pembayaran'
                                }}</span>
                            </button>

                            <button
                                v-if="
                                    transaction.status === 'pending' &&
                                    transaction.payable_amount > 0
                                "
                                type="button"
                                @click="emit('repay', transaction)"
                                :disabled="isRepaying"
                                class="flex cursor-pointer items-center gap-2 rounded-xl border-2 border-b-[4px] border-night-ink bg-night-ink px-5 py-2.5 text-xs font-black text-white transition-all hover:bg-night-ink/90 active:translate-y-0.5 active:border-b-2 disabled:opacity-50"
                            >
                                <CreditCard class="size-4 text-sprout-tint" />
                                <span>{{
                                    isRepaying
                                        ? 'Memuat Midtrans...'
                                        : 'Lanjutkan Pembayaran'
                                }}</span>
                            </button>

                            <button
                                type="button"
                                @click="emit('close')"
                                class="hover:bg-cloud-hover cursor-pointer rounded-xl border-2 border-b-[3px] border-border-gray bg-cloud-surface px-5 py-2.5 text-xs font-bold text-night-ink transition-all active:translate-y-0.5 active:border-b-2"
                            >
                                Tutup
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
