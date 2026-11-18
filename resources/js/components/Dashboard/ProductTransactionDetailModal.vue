<script setup lang="ts">
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import {
    X,
    ShoppingCart,
    MapPin,
    Package,
    Truck,
    User,
    CreditCard,
} from 'lucide-vue-next';
import StatusChip from '@/components/Dashboard/StatusChip.vue';
import UserAvatar from '@/components/Dashboard/UserAvatar.vue';
import PickupRouteMap from '@/components/Pickup/PickupRouteMap.vue';
import PickupLocationMap from '@/components/Pickup/PickupLocationMap.vue';

export interface DriverOption {
    id: number;
    name: string;
    email: string;
    is_available: boolean;
}

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

export interface ProductTransactionDetailData {
    id: number;
    status: string;
    payment_method: string;
    total_amount: number;
    points_used: number;
    points_discount_amount: number;
    payable_amount: number;
    delivery_address: string | null;
    latitude: string | null;
    longitude: string | null;
    delivery_proof_photo: string | null;
    snap_url: string | null;
    settled_at: string | null;
    delivered_at: string | null;
    canceled_at: string | null;
    created_at: string;
    updated_at: string;
    customer?: {
        id: number;
        user?: {
            id: number;
            name: string;
            email: string;
            photo: string | null;
            role: string;
        } | null;
    } | null;
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
    transaction: ProductTransactionDetailData | null;
    drivers?: DriverOption[];
    basemapApiKey?: string;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
}>();

const selectedDriverId = ref<number | ''>('');
const isAssigning = ref(false);

const assignDriver = () => {
    if (!props.transaction || !selectedDriverId.value) return;
    isAssigning.value = true;
    router.post(
        `/admin/product-transactions/${props.transaction.id}/assign-driver`,
        { driver_id: selectedDriverId.value },
        {
            preserveScroll: true,
            onSuccess: () => {
                selectedDriverId.value = '';
                emit('close');
            },
            onFinish: () => {
                isAssigning.value = false;
            },
        },
    );
};

const statusTone = computed(() => {
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
    const map: Record<string, string> = {
        midtrans: 'Midtrans',
        points: 'Poin Bank Sampah',
    };
    return (
        map[props.transaction.payment_method] ??
        props.transaction.payment_method
    );
});

const paymentTone = computed((): 'Green' | 'Amber' | 'Blue' | 'Neutral' => {
    if (!props.transaction) return 'Neutral';
    return props.transaction.payment_method === 'midtrans' ? 'Blue' : 'Green';
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
                    class="relative z-10 max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-[24px] border-2 border-b-[6px] border-border-gray bg-cloud-surface shadow-2xl"
                >
                    <!-- Header -->
                    <div
                        class="flex items-start justify-between gap-4 border-b border-border-gray/50 p-6"
                    >
                        <div class="min-w-0 flex-1">
                            <div class="mb-1 flex items-center gap-2">
                                <ShoppingCart
                                    class="size-5 shrink-0 text-eco-green"
                                />
                                <h2
                                    class="truncate text-lg font-black text-night-ink"
                                >
                                    Transaksi #{{ transaction.id }}
                                </h2>
                            </div>
                            <div class="flex flex-wrap items-center gap-2">
                                <StatusChip
                                    :tone="statusTone"
                                    :label="statusLabel"
                                />
                                <span class="text-muted-text text-sm">{{
                                    formatDate(transaction.created_at)
                                }}</span>
                            </div>
                        </div>
                        <button
                            @click="emit('close')"
                            class="text-muted-text hover:bg-cloud-hover shrink-0 rounded-xl border-2 border-b-[3px] border-border-gray p-2 transition-all hover:text-night-ink active:translate-y-0.5 active:border-b-2"
                        >
                            <X class="size-4" />
                        </button>
                    </div>

                    <!-- Body -->
                    <div class="space-y-6 p-6">
                        <!-- Customer -->
                        <div>
                            <h3
                                class="text-muted-text mb-3 flex items-center gap-1.5 text-xs font-bold tracking-wider uppercase"
                            >
                                <User class="size-3.5" /> Customer
                            </h3>
                            <div
                                v-if="transaction.customer?.user"
                                class="bg-cloud-hover flex items-center gap-3 rounded-xl border border-border-gray/50 p-3"
                            >
                                <UserAvatar
                                    :user="transaction.customer.user"
                                    size="sm"
                                />
                                <div>
                                    <p class="text-sm font-bold text-night-ink">
                                        {{ transaction.customer.user.name }}
                                    </p>
                                    <p class="text-muted-text text-xs">
                                        {{ transaction.customer.user.email }}
                                    </p>
                                </div>
                            </div>
                            <p v-else class="text-muted-text text-sm italic">
                                Data pembeli tidak tersedia
                            </p>
                        </div>

                        <!-- Metode Pembayaran & Ringkasan -->
                        <div>
                            <h3
                                class="text-muted-text mb-3 flex items-center gap-1.5 text-xs font-bold tracking-wider uppercase"
                            >
                                <CreditCard class="size-3.5" /> Pembayaran
                            </h3>
                            <div
                                class="bg-cloud-hover space-y-2 rounded-xl border border-border-gray/50 p-4"
                            >
                                <div class="flex items-center justify-between">
                                    <span class="text-muted-text text-sm"
                                        >Metode</span
                                    >
                                    <StatusChip
                                        :tone="paymentTone"
                                        :label="paymentMethodLabel"
                                    />
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-muted-text text-sm"
                                        >Total Produk</span
                                    >
                                    <span
                                        class="text-sm font-semibold text-night-ink"
                                        >{{
                                            formatRupiah(
                                                transaction.total_amount,
                                            )
                                        }}</span
                                    >
                                </div>
                                <div
                                    v-if="transaction.points_used > 0"
                                    class="flex items-center justify-between"
                                >
                                    <span class="text-muted-text text-sm"
                                        >Potongan Poin ({{
                                            transaction.points_used.toLocaleString(
                                                'id-ID',
                                            )
                                        }}
                                        poin)</span
                                    >
                                    <span
                                        class="text-sm font-semibold text-eco-green"
                                        >-{{
                                            formatRupiah(
                                                transaction.points_discount_amount,
                                            )
                                        }}</span
                                    >
                                </div>
                                <div
                                    class="flex items-center justify-between border-t border-border-gray/50 pt-2"
                                >
                                    <span
                                        class="text-sm font-black text-night-ink"
                                        >Total Bayar</span
                                    >
                                    <span
                                        class="text-base font-black text-eco-green"
                                        >{{
                                            formatRupiah(
                                                transaction.payable_amount,
                                            )
                                        }}</span
                                    >
                                </div>
                            </div>
                        </div>

                        <!-- Detail Produk -->
                        <div
                            v-if="
                                transaction.details &&
                                transaction.details.length > 0
                            "
                        >
                            <h3
                                class="text-muted-text mb-3 flex items-center gap-1.5 text-xs font-bold tracking-wider uppercase"
                            >
                                <Package class="size-3.5" /> Produk Dipesan
                            </h3>
                            <div
                                class="overflow-hidden rounded-xl border border-border-gray/60"
                            >
                                <table class="w-full text-sm">
                                    <thead
                                        class="bg-cloud-hover border-b border-border-gray/60"
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
                                        class="divide-y divide-border-gray/40"
                                    >
                                        <tr
                                            v-for="detail in transaction.details"
                                            :key="detail.id"
                                        >
                                            <td class="px-4 py-3">
                                                <div
                                                    class="flex items-center gap-2"
                                                >
                                                    <img
                                                        v-if="
                                                            detail.product
                                                                ?.photo_url
                                                        "
                                                        :src="
                                                            detail.product
                                                                .photo_url
                                                        "
                                                        :alt="
                                                            detail.product.name
                                                        "
                                                        class="size-8 shrink-0 rounded-lg border border-border-gray object-cover"
                                                    />
                                                    <div
                                                        v-else
                                                        class="bg-cloud-hover flex size-8 shrink-0 items-center justify-center rounded-lg border border-border-gray"
                                                    >
                                                        <Package
                                                            class="text-muted-text size-4"
                                                        />
                                                    </div>
                                                    <span
                                                        class="font-medium text-night-ink"
                                                        >{{
                                                            detail.product
                                                                ?.name ?? '-'
                                                        }}</span
                                                    >
                                                </div>
                                            </td>
                                            <td
                                                class="text-muted-text px-4 py-3 text-right"
                                            >
                                                {{ detail.quantity }}
                                            </td>
                                            <td
                                                class="text-muted-text px-4 py-3 text-right"
                                            >
                                                {{
                                                    formatRupiah(
                                                        detail.unit_price,
                                                    )
                                                }}
                                            </td>
                                            <td
                                                class="px-4 py-3 text-right font-bold text-night-ink"
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

                        <!-- Alamat Pengiriman & Peta OSM -->
                        <div v-if="transaction.delivery_address">
                            <div class="mb-2 flex items-center justify-between">
                                <h3
                                    class="text-muted-text flex items-center gap-1.5 text-xs font-bold tracking-wider uppercase"
                                >
                                    <MapPin class="size-3.5" />
                                    <span>{{
                                        transaction.status === 'delivering'
                                            ? 'Peta Rute OSRM Driver'
                                            : 'Alamat Pengiriman'
                                    }}</span>
                                </h3>
                                <span
                                    v-if="transaction.status === 'delivering'"
                                    class="flex items-center gap-1 text-[11px] font-bold text-sky-blue"
                                >
                                    <span class="relative flex size-2">
                                        <span
                                            class="absolute inline-flex size-full animate-ping rounded-full bg-sky-blue opacity-75"
                                        />
                                        <span
                                            class="relative inline-flex size-2 rounded-full bg-sky-blue"
                                        />
                                    </span>
                                    Driver Dalam Perjalanan
                                </span>
                            </div>

                            <!-- Peta Rute OSRM saat status delivering -->
                            <div
                                v-if="
                                    transaction.status === 'delivering' &&
                                    hasDestinationCoords
                                "
                                class="mb-3"
                            >
                                <PickupRouteMap
                                    :origin-lat="driverLat"
                                    :origin-lng="driverLng"
                                    :destination-lat="transaction.latitude ?? 0"
                                    :destination-lng="
                                        transaction.longitude ?? 0
                                    "
                                    origin-label="Armada Driver"
                                    destination-label="Alamat Pembeli"
                                    :basemap-api-key="basemapApiKey"
                                    height="300px"
                                />
                            </div>

                            <!-- Peta Single Location pin saat status selain delivering -->
                            <div v-else-if="hasDestinationCoords" class="mb-3">
                                <PickupLocationMap
                                    :lat="transaction.latitude"
                                    :lng="transaction.longitude"
                                    label="Alamat Pengiriman"
                                    :basemap-api-key="basemapApiKey"
                                    height="260px"
                                />
                            </div>

                            <p
                                class="bg-cloud-hover rounded-xl border border-border-gray/50 px-4 py-3 text-sm leading-relaxed text-night-ink"
                            >
                                {{ transaction.delivery_address }}
                            </p>
                        </div>

                        <!-- Driver Pengantar -->
                        <div v-if="transaction.driver?.user">
                            <h3
                                class="text-muted-text mb-3 flex items-center gap-1.5 text-xs font-bold tracking-wider uppercase"
                            >
                                <Truck class="size-3.5" /> Driver Pengantar
                            </h3>
                            <div
                                class="bg-cloud-hover flex items-center gap-3 rounded-xl border border-border-gray/50 p-3"
                            >
                                <UserAvatar
                                    :user="transaction.driver.user"
                                    size="sm"
                                />
                                <div>
                                    <p class="text-sm font-bold text-night-ink">
                                        {{ transaction.driver.user.name }}
                                    </p>
                                    <p class="text-muted-text text-xs">
                                        {{ transaction.driver.user.email }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Form Tugaskan Driver (Saat pesanan sedang diproses / belum ada driver) -->
                        <div
                            v-if="
                                transaction.status === 'processing' &&
                                drivers &&
                                drivers.length > 0
                            "
                            class="space-y-3 rounded-xl border-2 border-eco-green/40 bg-eco-green/5 p-4"
                        >
                            <div
                                class="flex items-center gap-2 text-sm font-bold text-eco-green"
                            >
                                <Truck class="size-4" />
                                <span>{{
                                    transaction.driver
                                        ? 'Ganti Driver Pengantar'
                                        : 'Tugaskan Driver Pengantar'
                                }}</span>
                            </div>
                            <p class="text-muted-text text-xs">
                                Pesanan ini sudah lunas dan siap dikirim. Pilih
                                kurir aktif untuk mengantarkan pesanan ke alamat
                                pembeli.
                            </p>
                            <div class="flex flex-col gap-2 sm:flex-row">
                                <select
                                    v-model="selectedDriverId"
                                    class="flex-1 rounded-xl border-2 border-border-gray bg-white px-3 py-2 text-sm font-semibold text-night-ink focus:border-eco-green focus:outline-none"
                                >
                                    <option value="" disabled>
                                        Pilih Driver Tersedia...
                                    </option>
                                    <option
                                        v-for="drv in drivers"
                                        :key="drv.id"
                                        :value="drv.id"
                                    >
                                        {{ drv.name }}
                                        {{
                                            drv.is_available
                                                ? '(Tersedia)'
                                                : '(Sedang Antar)'
                                        }}
                                    </option>
                                </select>
                                <button
                                    type="button"
                                    :disabled="!selectedDriverId || isAssigning"
                                    @click="assignDriver"
                                    class="hover:bg-forest-green inline-flex items-center justify-center gap-1.5 rounded-xl border-2 border-b-[4px] border-eco-green bg-eco-green px-5 py-2 text-sm font-black text-white transition-all active:translate-y-0.5 active:border-b-2 disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    <Truck class="size-4" />
                                    <span>{{
                                        isAssigning
                                            ? 'Menugaskan...'
                                            : 'Tugaskan Driver'
                                    }}</span>
                                </button>
                            </div>
                        </div>

                        <!-- Foto Bukti Pengiriman -->
                        <div v-if="transaction.delivery_proof_photo">
                            <h3
                                class="text-muted-text mb-3 text-xs font-bold tracking-wider uppercase"
                            >
                                Bukti Pengiriman
                            </h3>
                            <img
                                :src="transaction.delivery_proof_photo"
                                alt="Bukti Pengiriman"
                                class="max-h-56 w-full rounded-xl border-2 border-border-gray object-cover"
                            />
                        </div>

                        <!-- Timestamps -->
                        <div class="grid grid-cols-2 gap-3">
                            <div v-if="transaction.settled_at">
                                <p
                                    class="text-muted-text mb-1 text-xs font-bold tracking-wider uppercase"
                                >
                                    Waktu Bayar
                                </p>
                                <p class="text-sm text-night-ink">
                                    {{ formatDate(transaction.settled_at) }}
                                </p>
                            </div>
                            <div v-if="transaction.delivered_at">
                                <p
                                    class="text-muted-text mb-1 text-xs font-bold tracking-wider uppercase"
                                >
                                    Waktu Terima
                                </p>
                                <p class="text-sm text-night-ink">
                                    {{ formatDate(transaction.delivered_at) }}
                                </p>
                            </div>
                            <div v-if="transaction.canceled_at">
                                <p
                                    class="text-muted-text mb-1 text-xs font-bold tracking-wider uppercase"
                                >
                                    Waktu Batal
                                </p>
                                <p class="text-sm text-night-ink">
                                    {{ formatDate(transaction.canceled_at) }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div
                        class="flex justify-end border-t border-border-gray/50 p-6"
                    >
                        <button
                            @click="emit('close')"
                            class="hover:bg-cloud-hover rounded-xl border-2 border-b-[4px] border-border-gray bg-cloud-surface px-6 py-2.5 text-sm font-bold text-night-ink transition-all active:translate-y-0.5 active:border-b-2"
                        >
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
