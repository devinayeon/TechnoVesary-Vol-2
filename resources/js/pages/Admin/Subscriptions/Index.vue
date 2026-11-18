<script setup lang="ts">
import { ref, watch, computed } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import DashboardLayout from '@/layouts/DashboardLayout.vue';
import PageHeader from '@/components/Dashboard/PageHeader.vue';
import StatusChip from '@/components/Dashboard/StatusChip.vue';
import AppDropdown from '@/components/Dashboard/AppDropdown.vue';
import AppTable from '@/components/Dashboard/AppTable.vue';
import AppPagination from '@/components/Dashboard/AppPagination.vue';
import UserAvatar from '@/components/Dashboard/UserAvatar.vue';
import EditSubscriptionPriceModal, {
    type SubscriptionData,
} from '@/components/Dashboard/EditSubscriptionPriceModal.vue';
import SubscriptionTransactionDetailModal, {
    type SubscriptionTransactionItem,
} from '@/components/Dashboard/SubscriptionTransactionDetailModal.vue';
import {
    Search,
    RotateCcw,
    CheckCircle2,
    XCircle,
    X,
    CreditCard,
    Calendar,
    Sparkles,
    Edit3,
    Receipt,
    Coins,
} from 'lucide-vue-next';

interface PaginationLinks {
    url: string | null;
    label: string;
    active: boolean;
}

interface PaginatedTransactions {
    data: SubscriptionTransactionItem[];
    current_page: number;
    last_page: number;
    from: number | null;
    to: number | null;
    total: number;
    links: PaginationLinks[];
    prev_page_url: string | null;
    next_page_url: string | null;
}

const props = defineProps<{
    subscription: SubscriptionData;
    transactions: PaginatedTransactions;
    filters: {
        search?: string;
        status?: string;
    };
    statuses: Array<{ value: string; label: string }>;
}>();

const page = usePage();

// Flash alerts visibility state
const showSuccessFlash = ref(true);
const showErrorFlash = ref(true);

const flashSuccess = computed(() => page.props.flash?.success);
const flashError = computed(
    () =>
        page.props.flash?.error ||
        (page.props.errors as any)?.error ||
        (Object.keys(page.props.errors || {}).length > 0
            ? Object.values(page.props.errors)[0]
            : null),
);

watch(
    () => page.props.flash,
    () => {
        showSuccessFlash.value = true;
        showErrorFlash.value = true;
    },
    { deep: true },
);

// Search & Filter state
const search = ref(props.filters.search || '');
const selectedStatus = ref(props.filters.status || 'all');

let searchTimeout: any = null;
const applyFilters = () => {
    router.get(
        '/admin/subscriptions',
        {
            search: search.value || undefined,
            status:
                selectedStatus.value !== 'all'
                    ? selectedStatus.value
                    : undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
};

watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        applyFilters();
    }, 350);
});

const onStatusChange = () => {
    applyFilters();
};

const statusDropdownOptions = computed(() => [
    { value: 'all', label: 'Semua Status' },
    ...props.statuses.map((s) => ({
        value: s.value,
        label: s.label,
    })),
]);

const resetFilters = () => {
    search.value = '';
    selectedStatus.value = 'all';
    router.get(
        '/admin/subscriptions',
        {},
        {
            preserveState: true,
            preserveScroll: true,
        },
    );
};

// Modal State: Edit Price
const isPriceModalOpen = ref(false);
const openPriceModal = () => {
    isPriceModalOpen.value = true;
};
const closePriceModal = () => {
    isPriceModalOpen.value = false;
};

// Modal State: Transaction Detail
const isDetailModalOpen = ref(false);
const selectedTransaction = ref<SubscriptionTransactionItem | null>(null);

const openDetailModal = (trx: SubscriptionTransactionItem) => {
    selectedTransaction.value = trx;
    isDetailModalOpen.value = true;
};
const closeDetailModal = () => {
    isDetailModalOpen.value = false;
    selectedTransaction.value = null;
};

// Number & Date Formatters
const formatNumber = (num: number): string => {
    return Number(num || 0).toLocaleString('id-ID');
};

const formatDate = (dateString?: string | null): string => {
    if (!dateString) return '—';
    try {
        const d = new Date(dateString);
        return d.toLocaleDateString('id-ID', {
            day: 'numeric',
            month: 'short',
            year: 'numeric',
        });
    } catch {
        return dateString;
    }
};

const getStatusTone = (status: string): 'Green' | 'Amber' | 'Neutral' => {
    switch (status.toLowerCase()) {
        case 'settled':
            return 'Green';
        case 'pending':
            return 'Amber';
        default:
            return 'Neutral';
    }
};

const getStatusLabel = (status: string): string => {
    switch (status.toLowerCase()) {
        case 'settled':
            return 'Berhasil';
        case 'pending':
            return 'Menunggu Bayar';
        case 'failed':
            return 'Gagal';
        case 'canceled':
            return 'Dibatalkan';
        default:
            return status;
    }
};
</script>

<template>
    <Head title="Manajemen Subscription - Admin ReValue" />

    <DashboardLayout>
        <!-- Success Flash Alert -->
        <div
            v-if="showSuccessFlash && flashSuccess"
            class="animate-in fade-in mb-6 flex items-center justify-between gap-3 rounded-2xl border-2 border-eco-green/40 bg-sprout-tint p-4 text-[14px] font-bold text-night-ink shadow-2xs duration-200"
        >
            <div class="flex min-w-0 items-center gap-3">
                <CheckCircle2 class="size-5 shrink-0 text-eco-green-dark" />
                <span class="truncate">{{ flashSuccess }}</span>
            </div>
            <button
                type="button"
                @click="showSuccessFlash = false"
                class="shrink-0 cursor-pointer rounded-lg p-1 text-charcoal transition-colors hover:bg-eco-green/20 hover:text-night-ink"
                aria-label="Tutup notifikasi"
            >
                <X class="size-4" />
            </button>
        </div>

        <!-- Error Flash Alert -->
        <div
            v-if="showErrorFlash && flashError"
            class="animate-in fade-in mb-6 flex items-center justify-between gap-3 rounded-2xl border-2 border-red-300 bg-red-50 p-4 text-[14px] font-bold text-red-800 shadow-2xs duration-200"
        >
            <div class="flex min-w-0 items-center gap-3">
                <XCircle class="size-5 shrink-0 text-red-600" />
                <span class="truncate">{{ flashError }}</span>
            </div>
            <button
                type="button"
                @click="showErrorFlash = false"
                class="shrink-0 cursor-pointer rounded-lg p-1 text-red-700 transition-colors hover:bg-red-100 hover:text-red-900"
                aria-label="Tutup notifikasi"
            >
                <X class="size-4" />
            </button>
        </div>

        <!-- Page Header -->
        <PageHeader
            title="Subscription"
            subtitle="Periksa harga layanan penjemputan dan pantau riwayat transaksi langganan dari customer."
            badge="Layanan"
        />

        <!-- Section 1: Subscription Plan & Rules Overview (Figma node 485:2332) -->
        <div class="mb-8 grid grid-cols-1 items-stretch gap-6 lg:grid-cols-3">
            <!-- Left Card: Subscription Plan (Figma 485:2428) -->
            <div
                class="relative flex flex-col justify-between overflow-hidden rounded-[20px] border-2 border-b-[4px] border-border-gray bg-paper-white p-6 shadow-xs sm:p-7 lg:col-span-2"
            >
                <div>
                    <!-- Top Row: Status Chip & Mascot Illustration -->
                    <div class="flex items-start justify-between gap-4">
                        <div class="space-y-2">
                            <h2
                                class="text-[24px] leading-tight font-black tracking-tight text-night-ink sm:text-[28px]"
                            >
                                {{ subscription.name }}
                            </h2>
                        </div>
                        <div
                            class="flex size-20 shrink-0 items-center justify-center rounded-2xl border-2 border-border-gray/80 bg-cloud-surface p-2 shadow-2xs sm:size-24"
                        >
                            <img
                                src="/assets/Badge/revalue-plus-badge.png"
                                alt="ReValue Plus"
                                class="h-full w-full object-contain"
                            />
                        </div>
                    </div>

                    <!-- Price Display -->
                    <div class="mt-4 mb-3">
                        <div class="flex items-baseline gap-2">
                            <span
                                class="text-[34px] leading-none font-black tracking-tight text-night-ink sm:text-[40px]"
                            >
                                Rp {{ formatNumber(subscription.price) }}
                            </span>
                            <span
                                class="text-[13px] font-bold text-charcoal sm:text-[14px]"
                            >
                                / 30 hari layanan
                            </span>
                        </div>
                    </div>

                    <!-- Description -->
                    <p
                        class="max-w-[620px] text-[14px] leading-relaxed font-medium text-charcoal"
                    >
                        {{
                            subscription.description ||
                            'Penjemputan sampah terpilah dari rumah, penimbangan transparan, dan poin dari hasil setoran.'
                        }}
                    </p>
                </div>

                <!-- Action Button -->
                <div
                    class="mt-4 flex items-center justify-start border-t border-border-gray/70 pt-6"
                >
                    <button
                        type="button"
                        @click="openPriceModal"
                        class="inline-flex h-[44px] cursor-pointer items-center gap-2 rounded-xl border-2 border-b-[4px] border-border-gray bg-paper-white px-5 text-[14px] font-extrabold text-night-ink shadow-2xs transition-all hover:bg-cloud-surface active:translate-y-0.5 active:border-b-2"
                    >
                        <Edit3 class="size-4 text-charcoal" />
                        <span>Ubah Harga</span>
                    </button>
                </div>
            </div>

            <!-- Right Card: Subscription Rules (Figma 485:2441) -->
            <div
                class="flex flex-col justify-between rounded-[20px] border-2 border-b-[4px] border-border-gray bg-paper-white p-6 shadow-xs sm:p-7 lg:col-span-1"
            >
                <div>
                    <h3
                        class="mb-4 text-[18px] leading-tight font-black text-night-ink"
                    >
                        Harga jelas, layanan berlanjut.
                    </h3>

                    <!-- Detail Fields -->
                    <div class="mb-5 space-y-3">
                        <div
                            class="flex items-center gap-3 rounded-xl border border-border-gray bg-cloud-surface/70 p-3"
                        >
                            <div
                                class="flex size-9 shrink-0 items-center justify-center rounded-lg border border-eco-green/30 bg-sprout-tint text-eco-green-dark"
                            >
                                <Calendar class="size-4.5" />
                            </div>
                            <div>
                                <span
                                    class="block text-[11px] font-bold tracking-wider text-charcoal uppercase"
                                >
                                    Periode Layanan
                                </span>
                                <span
                                    class="text-[13px] font-extrabold text-night-ink"
                                >
                                    30 Hari Kalender
                                </span>
                            </div>
                        </div>

                        <div
                            class="flex items-center gap-3 rounded-xl border border-border-gray bg-cloud-surface/70 p-3"
                        >
                            <div
                                class="text-sunlight-gold-dark flex size-9 shrink-0 items-center justify-center rounded-lg border border-sunlight-gold/30 bg-gold-tint"
                            >
                                <Sparkles class="size-4.5 text-sunlight-gold" />
                            </div>
                            <div>
                                <span
                                    class="block text-[11px] font-bold tracking-wider text-charcoal uppercase"
                                >
                                    Akses Fitur Lengkap
                                </span>
                                <span
                                    class="text-[13px] font-extrabold text-night-ink"
                                >
                                    Penjemputan & Konversi Poin
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer Context Text -->
                <p
                    class="border-t border-border-gray/70 pt-4 text-[12px] leading-relaxed font-medium text-charcoal/80"
                >
                    Perubahan harga berlaku pada transaksi baru. Tagihan dan
                    masa aktif yang sudah tercatat pada akun customer tetap
                    memakai tarif sebelumnya.
                </p>
            </div>
        </div>

        <!-- Section 2: Riwayat Transaksi Heading -->
        <div class="mb-4">
            <h2
                class="text-[20px] leading-tight font-black text-night-ink sm:text-[22px]"
            >
                Riwayat Transaksi Langganan
            </h2>
            <p class="mt-0.5 text-[13px] font-medium text-charcoal">
                Pantau tagihan dan status pembayaran langganan dari seluruh
                customer.
            </p>
        </div>

        <!-- Toolbar: Search & Status Filter (Matching User Management) -->
        <div
            class="mb-6 flex flex-col items-stretch gap-3 lg:flex-row lg:items-center"
        >
            <!-- Search Input -->
            <div class="relative w-full">
                <Search
                    class="pointer-events-none absolute top-1/2 left-3.5 size-4.5 -translate-y-1/2 text-charcoal/70"
                />
                <input
                    v-model="search"
                    type="text"
                    placeholder="Cari ID transaksi, nama customer, atau email..."
                    class="h-[44px] w-full rounded-xl border-2 border-border-gray bg-paper-white pr-10 pl-10 text-[14px] font-medium text-night-ink shadow-2xs transition-all placeholder:text-charcoal/60 focus:border-eco-green focus:ring-4 focus:ring-eco-green/15 focus:outline-none"
                />
                <button
                    v-if="search"
                    @click="search = ''"
                    type="button"
                    class="absolute top-1/2 right-3 -translate-y-1/2 cursor-pointer rounded-md p-1 text-charcoal transition-colors hover:bg-cloud-surface hover:text-night-ink"
                    aria-label="Bersihkan pencarian"
                >
                    <X class="size-3.5" />
                </button>
            </div>

            <!-- Filters Group -->
            <div
                class="flex w-full flex-wrap items-center gap-2.5 sm:flex-nowrap lg:w-auto"
            >
                <!-- Status Filter Dropdown -->
                <AppDropdown
                    v-model="selectedStatus"
                    :options="statusDropdownOptions"
                    placeholder="Semua Status"
                    class="min-w-[150px] flex-1 sm:w-[200px] sm:flex-initial"
                    menu-width="w-full sm:w-[210px]"
                    align="right"
                    @change="onStatusChange"
                />

                <!-- Reset Filter Button -->
                <button
                    v-if="search || selectedStatus !== 'all'"
                    type="button"
                    @click="resetFilters"
                    class="h-[44px] shrink-0 cursor-pointer rounded-xl border-2 border-border-gray bg-paper-white p-2.5 text-charcoal transition-colors hover:bg-cloud-surface hover:text-night-ink"
                    title="Reset Filter"
                >
                    <RotateCcw class="size-4" />
                </button>
            </div>
        </div>

        <!-- Table & Pagination (Using Reusable AppTable & AppPagination) -->
        <AppTable
            :is-empty="transactions.data.length === 0"
            :has-filter="!!(search || selectedStatus !== 'all')"
            empty-title="Belum ada transaksi langganan"
            empty-description="Transaksi langganan dari customer akan otomatis tercatat di sini."
            empty-filter-title="Tidak ada hasil yang cocok"
            empty-filter-description="Coba gunakan kata kunci pencarian lain atau ubah filter status transaksi."
            empty-action-label=""
            min-width="min-w-[800px]"
            @reset-filter="resetFilters"
        >
            <!-- Mobile Cards View (< md) -->
            <template #mobile>
                <div
                    v-for="trx in transactions.data"
                    :key="`m-${trx.id}`"
                    class="flex flex-col gap-3 p-4 transition-colors hover:bg-cloud-surface/30"
                >
                    <!-- Header: ID Transaksi + Status -->
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <span
                                class="text-[14px] font-extrabold text-night-ink"
                            >
                                #TRX-SUB-{{ String(trx.id).padStart(4, '0') }}
                            </span>
                            <p class="text-[11px] font-bold text-charcoal/70">
                                {{ formatDate(trx.created_at) }}
                            </p>
                        </div>
                        <StatusChip
                            :label="getStatusLabel(trx.status)"
                            :tone="getStatusTone(trx.status)"
                        />
                    </div>

                    <!-- Customer Info -->
                    <div class="flex items-center gap-3">
                        <UserAvatar
                            :photo="trx.customer?.user?.photo || null"
                            :role="trx.customer?.user?.role || 'customer'"
                            :name="trx.customer?.user?.name || 'Customer'"
                            size="md"
                        />
                        <div class="min-w-0">
                            <h4
                                class="truncate text-[14px] font-extrabold text-night-ink"
                            >
                                {{ trx.customer?.user?.name || 'Customer' }}
                            </h4>
                            <p
                                class="truncate text-[12px] font-medium text-charcoal"
                            >
                                {{ trx.customer?.user?.email || '—' }}
                            </p>
                        </div>
                    </div>

                    <!-- Financial Meta Box -->
                    <div
                        class="flex items-center justify-between rounded-xl border border-border-gray/50 bg-cloud-surface/50 px-3 py-2 text-[12px]"
                    >
                        <span class="font-semibold text-charcoal">
                            {{
                                trx.payment_method === 'points'
                                    ? 'Poin Penuh'
                                    : 'Midtrans'
                            }}
                        </span>
                        <div class="text-[13px] font-extrabold text-night-ink">
                            Rp {{ formatNumber(trx.payable_amount) }}
                        </div>
                    </div>

                    <!-- Action Button -->
                    <div
                        class="flex items-center justify-end border-t border-border-gray/40 pt-1"
                    >
                        <button
                            type="button"
                            @click="openDetailModal(trx)"
                            class="cursor-pointer rounded-xl bg-cloud-surface px-3 py-1.5 text-[12px] font-extrabold text-night-ink transition-colors hover:bg-sprout-tint hover:text-eco-green-dark"
                        >
                            Lihat Detail
                        </button>
                    </div>
                </div>
            </template>

            <!-- Desktop Table Header -->
            <template #header>
                <th class="px-6 py-3.5">ID Transaksi</th>
                <th class="px-6 py-3.5">Customer</th>
                <th class="px-6 py-3.5">Metode</th>
                <th class="px-6 py-3.5">Total Bayar</th>
                <th class="px-6 py-3.5">Status</th>
                <th class="px-6 py-3.5">Tanggal</th>
                <th class="px-6 py-3.5 text-right">Aksi</th>
            </template>

            <!-- Desktop Table Body -->
            <template #body>
                <tr
                    v-for="trx in transactions.data"
                    :key="trx.id"
                    class="transition-colors hover:bg-cloud-surface/50"
                >
                    <!-- ID Transaksi -->
                    <td
                        class="px-6 py-3.5 text-[13px] font-extrabold text-night-ink"
                    >
                        #TRX-SUB-{{ String(trx.id).padStart(4, '0') }}
                    </td>

                    <!-- Customer -->
                    <td class="px-6 py-3.5">
                        <div class="flex items-center gap-3">
                            <UserAvatar
                                :photo="trx.customer?.user?.photo || null"
                                :role="trx.customer?.user?.role || 'customer'"
                                :name="trx.customer?.user?.name || 'Customer'"
                                size="md"
                            />
                            <div class="min-w-0">
                                <p
                                    class="truncate text-[14px] leading-tight font-bold text-night-ink"
                                >
                                    {{ trx.customer?.user?.name || 'Customer' }}
                                </p>
                                <p
                                    class="mt-0.5 truncate text-[12px] font-medium text-charcoal/70"
                                >
                                    {{ trx.customer?.user?.email || '—' }}
                                </p>
                            </div>
                        </div>
                    </td>

                    <!-- Metode Pembayaran -->
                    <td class="px-6 py-3.5">
                        <span
                            class="inline-flex items-center gap-1.5 rounded-lg border border-border-gray bg-cloud-surface px-2.5 py-1 text-[12px] font-bold text-night-ink"
                        >
                            <CreditCard class="size-3 text-charcoal" />
                            <span>{{
                                trx.payment_method === 'points'
                                    ? 'Poin Penuh'
                                    : 'Midtrans'
                            }}</span>
                        </span>
                    </td>

                    <!-- Total Bayar -->
                    <td class="px-6 py-3.5">
                        <div class="text-[14px] font-extrabold text-night-ink">
                            Rp {{ formatNumber(trx.payable_amount) }}
                        </div>
                        <div
                            v-if="trx.points_used > 0"
                            class="flex items-center gap-1 text-[11px] font-medium text-eco-green-dark"
                        >
                            <Coins class="size-3" />
                            <span
                                >-{{
                                    formatNumber(trx.points_discount_amount)
                                }}
                                (Poin)</span
                            >
                        </div>
                    </td>

                    <!-- Status -->
                    <td class="px-6 py-3.5">
                        <StatusChip
                            :label="getStatusLabel(trx.status)"
                            :tone="getStatusTone(trx.status)"
                        />
                    </td>

                    <!-- Tanggal -->
                    <td
                        class="px-6 py-3.5 text-[13px] font-medium text-charcoal"
                    >
                        {{ formatDate(trx.created_at) }}
                    </td>

                    <!-- Aksi -->
                    <td class="px-6 py-3.5 text-right whitespace-nowrap">
                        <button
                            type="button"
                            @click="openDetailModal(trx)"
                            class="cursor-pointer rounded-lg px-2.5 py-1 text-[13px] font-extrabold text-night-ink transition-colors hover:bg-cloud-surface hover:text-eco-green-dark"
                        >
                            Detail
                        </button>
                    </td>
                </tr>
            </template>

            <!-- Pagination Bar -->
            <template #pagination>
                <AppPagination
                    :pagination="transactions"
                    item-label="transaksi"
                />
            </template>
        </AppTable>

        <!-- Modals -->
        <EditSubscriptionPriceModal
            :show="isPriceModalOpen"
            :subscription="subscription"
            @close="closePriceModal"
        />

        <SubscriptionTransactionDetailModal
            :show="isDetailModalOpen"
            :transaction="selectedTransaction"
            @close="closeDetailModal"
        />
    </DashboardLayout>
</template>
