<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import DashboardLayout from '@/layouts/DashboardLayout.vue';
import PageHeader from '@/components/Dashboard/PageHeader.vue';
import SearchInput from '@/components/Dashboard/SearchInput.vue';
import AppDropdown from '@/components/Dashboard/AppDropdown.vue';
import AppTable from '@/components/Dashboard/AppTable.vue';
import AppPagination from '@/components/Dashboard/AppPagination.vue';
import StatusChip from '@/components/Dashboard/StatusChip.vue';
import FlashMessages from '@/components/Dashboard/FlashMessages.vue';
import {
    Package,
    Truck,
    CheckCircle2,
    Clock,
    RotateCcw,
    MapPin,
    Eye,
    ChevronRight,
    ArrowUpRight,
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
    payable_amount: number;
    delivery_address: string;
    latitude: string | null;
    longitude: string | null;
    delivery_proof_photo: string | null;
    settled_at: string | null;
    delivered_at: string | null;
    created_at: string;
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

interface PaginatedDeliveries {
    data: DeliveryTransaction[];
    current_page: number;
    last_page: number;
    from: number | null;
    to: number | null;
    total: number;
    links: Array<{ url: string | null; label: string; active: boolean }>;
    prev_page_url: string | null;
    next_page_url: string | null;
}

const props = defineProps<{
    deliveries: PaginatedDeliveries;
    filters: {
        search?: string;
        status?: string;
    };
    statuses: Array<{ value: string; label: string }>;
    metrics: {
        active: number;
        delivered: number;
        completed: number;
    };
}>();

const INDEX_URL = '/driver/deliveries';

const search = ref(props.filters.search ?? '');
const selectedStatus = ref(props.filters.status ?? '');
const isSearching = ref(false);

const statusOptions = computed(() => [
    { value: '', label: 'Semua Status' },
    ...props.statuses,
]);

const hasFilter = computed(
    () => search.value.trim() !== '' || selectedStatus.value !== '',
);

function applyFilters() {
    isSearching.value = true;
    router.get(
        INDEX_URL,
        {
            search: search.value.trim() || undefined,
            status: selectedStatus.value || undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
            onFinish: () => {
                isSearching.value = false;
            },
        },
    );
}

let searchTimeout: ReturnType<typeof setTimeout> | null = null;
watch(search, () => {
    if (searchTimeout) clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => applyFilters(), 350);
});

function resetFilters() {
    search.value = '';
    selectedStatus.value = '';
    router.get(
        INDEX_URL,
        {},
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
}

function openDetail(delivery: DeliveryTransaction) {
    router.visit(`${INDEX_URL}/${delivery.id}`);
}

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
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};
</script>

<template>
    <Head title="Tugas Antar Pesanan Toko - Portal Driver" />

    <DashboardLayout>
        <!-- Flash Messages -->
        <FlashMessages class="mb-6" />

        <!-- Page Header -->
        <PageHeader
            title="Tugas Antar Pesanan Toko"
            subtitle="Daftar pesanan produk daur ulang yang ditugaskan kepada Anda untuk diantarkan ke pembeli."
        />

        <!-- Metric Cards -->
        <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
            <!-- Active / Delivering -->
            <div
                @click="
                    selectedStatus = 'delivering';
                    applyFilters();
                "
                class="group cursor-pointer rounded-2xl border-2 border-b-[5px] border-border-gray bg-white p-5 transition-all hover:border-sky-blue/70 active:translate-y-0.5 active:border-b-2"
            >
                <div class="mb-2 flex items-center justify-between">
                    <span
                        class="text-muted-text text-xs font-black tracking-wider uppercase"
                    >
                        Sedang Dikirim
                    </span>
                    <div
                        class="flex size-10 items-center justify-center rounded-xl border border-sky-blue/20 bg-sky-tint text-sky-blue"
                    >
                        <Truck class="size-5" />
                    </div>
                </div>
                <div class="flex items-baseline gap-2">
                    <span class="text-3xl font-black text-night-ink">{{
                        metrics.active
                    }}</span>
                    <span class="text-muted-text text-xs font-bold"
                        >Pesanan</span
                    >
                </div>
                <p class="mt-2 text-[11px] font-semibold text-charcoal/70">
                    Perlu diantarkan ke alamat pembeli
                </p>
            </div>

            <!-- Delivered -->
            <div
                @click="
                    selectedStatus = 'delivered';
                    applyFilters();
                "
                class="group cursor-pointer rounded-2xl border-2 border-b-[5px] border-border-gray bg-white p-5 transition-all hover:border-sunlight-gold/70 active:translate-y-0.5 active:border-b-2"
            >
                <div class="mb-2 flex items-center justify-between">
                    <span
                        class="text-muted-text text-xs font-black tracking-wider uppercase"
                    >
                        Terkirim
                    </span>
                    <div
                        class="flex size-10 items-center justify-center rounded-xl border border-sunlight-gold/20 bg-gold-tint text-sunlight-gold"
                    >
                        <Clock class="size-5" />
                    </div>
                </div>
                <div class="flex items-baseline gap-2">
                    <span class="text-3xl font-black text-night-ink">{{
                        metrics.delivered
                    }}</span>
                    <span class="text-muted-text text-xs font-bold"
                        >Pesanan</span
                    >
                </div>
                <p class="mt-2 text-[11px] font-semibold text-charcoal/70">
                    Menunggu konfirmasi penerimaan pembeli
                </p>
            </div>

            <!-- Completed -->
            <div
                @click="
                    selectedStatus = 'completed';
                    applyFilters();
                "
                class="group cursor-pointer rounded-2xl border-2 border-b-[5px] border-border-gray bg-white p-5 transition-all hover:border-eco-green/70 active:translate-y-0.5 active:border-b-2"
            >
                <div class="mb-2 flex items-center justify-between">
                    <span
                        class="text-muted-text text-xs font-black tracking-wider uppercase"
                    >
                        Selesai
                    </span>
                    <div
                        class="flex size-10 items-center justify-center rounded-xl border border-eco-green/20 bg-sprout-tint text-eco-green"
                    >
                        <CheckCircle2 class="size-5" />
                    </div>
                </div>
                <div class="flex items-baseline gap-2">
                    <span class="text-3xl font-black text-night-ink">{{
                        metrics.completed
                    }}</span>
                    <span class="text-muted-text text-xs font-bold"
                        >Pesanan</span
                    >
                </div>
                <p class="mt-2 text-[11px] font-semibold text-charcoal/70">
                    Diterima & diselesaikan pembeli
                </p>
            </div>
        </div>

        <!-- Filter Toolbar -->
        <div
            class="mb-6 rounded-2xl border-2 border-b-[4px] border-border-gray bg-white p-4"
        >
            <div
                class="flex flex-col items-stretch gap-3 sm:flex-row sm:items-center"
            >
                <div class="flex-1">
                    <SearchInput
                        v-model="search"
                        placeholder="Cari ID transaksi, alamat, pembeli, atau produk..."
                    />
                </div>
                <div class="w-full sm:w-56">
                    <AppDropdown
                        v-model="selectedStatus"
                        :options="statusOptions"
                        placeholder="Semua Status"
                        @update:model-value="applyFilters"
                    />
                </div>
                <button
                    v-if="hasFilter"
                    type="button"
                    @click="resetFilters"
                    class="flex shrink-0 cursor-pointer items-center justify-center gap-1.5 rounded-xl border-2 border-b-[3px] border-border-gray px-4 py-2.5 text-xs font-bold text-charcoal transition-all hover:bg-cloud-surface active:translate-y-0.5 active:border-b-2"
                >
                    <RotateCcw class="size-3.5" />
                    <span>Reset</span>
                </button>
            </div>
        </div>

        <!-- AppTable -->
        <AppTable
            :items="deliveries.data"
            :columns="[]"
            empty-title="Belum Ada Tugas Pengantaran"
            empty-description="Saat admin menugaskan pesanan toko kepada Anda, tugas pengantaran akan tampil di halaman ini."
        >
            <!-- Mobile View -->
            <template #mobile>
                <div class="divide-y divide-border-gray/60">
                    <div
                        v-for="d in deliveries.data"
                        :key="d.id"
                        class="p-4 transition-colors hover:bg-cloud-surface/40"
                    >
                        <div
                            class="mb-2.5 flex items-start justify-between gap-2"
                        >
                            <div>
                                <span class="text-xs font-black text-night-ink"
                                    >#TRX-{{ d.id }}</span
                                >
                                <p
                                    class="text-muted-text text-[11px] font-semibold"
                                >
                                    {{ formatDate(d.created_at) }}
                                </p>
                            </div>
                            <StatusChip
                                :tone="getStatusTone(d.status)"
                                :label="getStatusLabel(d.status)"
                            />
                        </div>

                        <!-- Customer & Address -->
                        <div
                            class="mb-3 space-y-1 rounded-xl border border-border-gray/70 bg-cloud-surface p-3"
                        >
                            <p class="text-xs font-bold text-night-ink">
                                {{ d.customer?.user?.name ?? 'Pembeli' }}
                            </p>
                            <p
                                class="flex items-start gap-1 text-[11px] text-charcoal/80"
                            >
                                <MapPin
                                    class="text-muted-text mt-0.5 size-3 shrink-0"
                                />
                                <span>{{ d.delivery_address }}</span>
                            </p>
                        </div>

                        <!-- Products summary -->
                        <div class="mb-3 flex items-center gap-2.5">
                            <div
                                class="flex size-9 shrink-0 items-center justify-center overflow-hidden rounded-lg border border-border-gray bg-white"
                            >
                                <img
                                    v-if="
                                        d.details?.[0]?.product?.photo_url ||
                                        d.details?.[0]?.product?.photo
                                    "
                                    :src="
                                        d.details?.[0]?.product?.photo_url ||
                                        `/storage/${d.details?.[0]?.product?.photo}`
                                    "
                                    :alt="d.details?.[0]?.product?.name"
                                    class="h-full w-full object-cover"
                                />
                                <Package
                                    v-else
                                    class="text-muted-text size-4"
                                />
                            </div>
                            <div class="min-w-0 flex-1">
                                <p
                                    class="truncate text-xs font-bold text-night-ink"
                                >
                                    {{
                                        d.details?.[0]?.product?.name ??
                                        'Produk Upcycle'
                                    }}
                                </p>
                                <p class="text-muted-text text-[11px]">
                                    {{ d.details?.[0]?.quantity }} item
                                    <span v-if="(d.details?.length ?? 0) > 1">
                                        (+{{ (d.details?.length ?? 1) - 1 }}
                                        lainnya)
                                    </span>
                                </p>
                            </div>
                        </div>

                        <!-- Action Button -->
                        <button
                            @click="openDetail(d)"
                            class="hover:bg-forest-green flex w-full cursor-pointer items-center justify-center gap-1.5 rounded-xl border-2 border-b-[4px] border-eco-green bg-eco-green px-4 py-2.5 text-xs font-black text-white transition-all active:translate-y-0.5 active:border-b-2"
                        >
                            <span>{{
                                d.status === 'delivering'
                                    ? 'Antar & Kirim Bukti'
                                    : 'Lihat Detail Pengantaran'
                            }}</span>
                            <ChevronRight class="size-4" />
                        </button>
                    </div>
                </div>
            </template>

            <!-- Desktop Header -->
            <template #header>
                <th
                    class="w-24 px-4 py-3.5 text-left text-[12px] font-extrabold tracking-wider text-charcoal uppercase"
                >
                    ID Pesanan
                </th>
                <th
                    class="w-48 px-4 py-3.5 text-left text-[12px] font-extrabold tracking-wider text-charcoal uppercase"
                >
                    Penerima
                </th>
                <th
                    class="px-4 py-3.5 text-left text-[12px] font-extrabold tracking-wider text-charcoal uppercase"
                >
                    Alamat Pengiriman
                </th>
                <th
                    class="w-56 px-4 py-3.5 text-left text-[12px] font-extrabold tracking-wider text-charcoal uppercase"
                >
                    Produk Dibawa
                </th>
                <th
                    class="w-36 px-4 py-3.5 text-left text-[12px] font-extrabold tracking-wider text-charcoal uppercase"
                >
                    Status
                </th>
                <th
                    class="w-28 px-4 py-3.5 text-center text-[12px] font-extrabold tracking-wider text-charcoal uppercase"
                >
                    Aksi
                </th>
            </template>

            <!-- Desktop Body -->
            <template #body>
                <tr
                    v-for="d in deliveries.data"
                    :key="d.id"
                    class="transition-colors hover:bg-cloud-surface/50"
                >
                    <!-- ID -->
                    <td class="px-4 py-4">
                        <span class="text-[13px] font-black text-night-ink"
                            >#TRX-{{ d.id }}</span
                        >
                        <p class="text-muted-text text-[11px] font-semibold">
                            {{ formatDate(d.created_at) }}
                        </p>
                    </td>

                    <!-- Customer -->
                    <td class="px-4 py-4">
                        <p class="text-xs font-bold text-night-ink">
                            {{ d.customer?.user?.name ?? 'Pembeli' }}
                        </p>
                        <p
                            class="text-muted-text max-w-44 truncate text-[11px]"
                        >
                            {{ d.customer?.user?.email ?? '-' }}
                        </p>
                    </td>

                    <!-- Address -->
                    <td class="px-4 py-4">
                        <p
                            class="line-clamp-2 max-w-md text-xs font-medium text-charcoal"
                        >
                            {{ d.delivery_address }}
                        </p>
                    </td>

                    <!-- Products -->
                    <td class="px-4 py-4">
                        <div class="flex items-center gap-2.5">
                            <div
                                class="flex size-10 shrink-0 items-center justify-center overflow-hidden rounded-lg border border-border-gray bg-cloud-surface"
                            >
                                <img
                                    v-if="
                                        d.details?.[0]?.product?.photo_url ||
                                        d.details?.[0]?.product?.photo
                                    "
                                    :src="
                                        d.details?.[0]?.product?.photo_url ||
                                        `/storage/${d.details?.[0]?.product?.photo}`
                                    "
                                    :alt="d.details?.[0]?.product?.name"
                                    class="h-full w-full object-cover"
                                />
                                <Package
                                    v-else
                                    class="text-muted-text size-5"
                                />
                            </div>
                            <div class="min-w-0">
                                <p
                                    class="max-w-40 truncate text-xs font-bold text-night-ink"
                                >
                                    {{
                                        d.details?.[0]?.product?.name ??
                                        'Produk Upcycle'
                                    }}
                                </p>
                                <p
                                    class="text-muted-text text-[11px] font-semibold"
                                >
                                    {{ d.details?.[0]?.quantity }} item
                                    <span v-if="(d.details?.length ?? 0) > 1">
                                        (+{{ (d.details?.length ?? 1) - 1 }}
                                        lainnya)
                                    </span>
                                </p>
                            </div>
                        </div>
                    </td>

                    <!-- Status -->
                    <td class="px-4 py-4">
                        <StatusChip
                            :tone="getStatusTone(d.status)"
                            :label="getStatusLabel(d.status)"
                        />
                    </td>

                    <!-- Aksi -->
                    <td class="px-4 py-4 text-center">
                        <button
                            @click="openDetail(d)"
                            class="inline-flex cursor-pointer items-center justify-center gap-1.5 rounded-xl border-2 border-b-[3px] border-border-gray bg-white px-3 py-2 text-xs font-bold text-night-ink transition-all hover:border-night-ink hover:bg-cloud-surface active:translate-y-0.5 active:border-b-2"
                            title="Buka Detail Pengantaran"
                        >
                            <Eye class="size-3.5" />
                            <span>Detail</span>
                        </button>
                    </td>
                </tr>
            </template>

            <!-- Pagination -->
            <template #pagination>
                <AppPagination
                    :pagination="deliveries"
                    item-label="pengantaran"
                />
            </template>
        </AppTable>
    </DashboardLayout>
</template>
