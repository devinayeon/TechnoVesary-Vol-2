<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import DashboardLayout from '@/layouts/DashboardLayout.vue';
import PageHeader from '@/components/Dashboard/PageHeader.vue';
import StatusChip from '@/components/Dashboard/StatusChip.vue';
import SearchInput from '@/components/Dashboard/SearchInput.vue';
import AppDropdown from '@/components/Dashboard/AppDropdown.vue';
import AppTable from '@/components/Dashboard/AppTable.vue';
import AppPagination from '@/components/Dashboard/AppPagination.vue';
import CustomerTransactionDetailModal, {
    type CustomerTransactionData,
} from '@/components/Customer/CustomerTransactionDetailModal.vue';
import {
    ShoppingCart,
    RotateCcw,
    Eye,
    CheckCircle2,
    XCircle,
    X,
    Package,
} from 'lucide-vue-next';

interface PaginationLinks {
    url: string | null;
    label: string;
    active: boolean;
}

interface PaginatedTransactions {
    data: CustomerTransactionData[];
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
    transactions: PaginatedTransactions;
    filters: {
        search?: string;
        status?: string;
    };
    statuses: Array<{ value: string; label: string }>;
    clientKey: string;
    snapJsUrl: string;
    basemapApiKey?: string;
}>();

const page = usePage();

// Flash alerts
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
const search = ref(props.filters.search ?? '');
const selectedStatus = ref(props.filters.status ?? '');
const isSearching = ref(false);

const statusOptions = computed(() => [
    { value: '', label: 'Semua Status' },
    ...props.statuses,
]);

const hasActiveFilter = computed(
    () => !!(search.value.trim() || selectedStatus.value),
);

const applyFilters = () => {
    isSearching.value = true;
    router.get(
        '/customer/transactions',
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
};

let searchTimeout: ReturnType<typeof setTimeout> | null = null;
watch(search, () => {
    if (searchTimeout) clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => applyFilters(), 350);
});

const resetFilters = () => {
    search.value = '';
    selectedStatus.value = '';
    router.get(
        '/customer/transactions',
        {},
        { preserveState: true, preserveScroll: true },
    );
};

// Modal & Payment Actions State
const isDetailModalOpen = ref(false);
const activeTransaction = ref<CustomerTransactionData | null>(null);
const isCanceling = ref(false);
const isRepaying = ref(false);
const isCheckingStatus = ref(false);
const isConfirming = ref(false);

const openDetailModal = (t: CustomerTransactionData) => {
    activeTransaction.value = t;
    isDetailModalOpen.value = true;
};

const closeDetailModal = () => {
    isDetailModalOpen.value = false;
    activeTransaction.value = null;
};

const handleCheckStatus = (transaction: CustomerTransactionData) => {
    isCheckingStatus.value = true;
    router.post(
        `/customer/transactions/${transaction.id}/check-status`,
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                closeDetailModal();
            },
            onFinish: () => {
                isCheckingStatus.value = false;
            },
        },
    );
};

const handleConfirmReceived = (transaction: CustomerTransactionData) => {
    if (
        !confirm(
            `Apakah Anda yakin pesanan #TRX-${transaction.id} sudah diterima dengan baik? Transaksi akan diselesaikan.`,
        )
    ) {
        return;
    }

    isConfirming.value = true;
    router.post(
        `/customer/transactions/${transaction.id}/confirm-received`,
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                closeDetailModal();
            },
            onFinish: () => {
                isConfirming.value = false;
            },
        },
    );
};

// Dynamic Snap Script
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

const handleCancelOrder = (transaction: CustomerTransactionData) => {
    if (
        !confirm(
            `Apakah Anda yakin ingin membatalkan pesanan #TRX-${transaction.id}? Stok barang dan poin yang digunakan akan dikembalikan.`,
        )
    ) {
        return;
    }

    isCanceling.value = true;
    router.post(
        `/customer/transactions/${transaction.id}/cancel`,
        {},
        {
            preserveScroll: true,
            onFinish: () => {
                isCanceling.value = false;
                closeDetailModal();
            },
        },
    );
};

const handleRepayOrder = async (transaction: CustomerTransactionData) => {
    isRepaying.value = true;

    try {
        const response = await fetch(
            `/customer/transactions/${transaction.id}/repay`,
            {
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
            },
        );

        const data = await response.json();
        isRepaying.value = false;

        if (data.success && data.snap_token) {
            closeDetailModal();
            if (typeof window !== 'undefined' && (window as any).snap) {
                (window as any).snap.pay(data.snap_token, {
                    onSuccess: () => router.visit('/customer/transactions'),
                    onPending: () => router.visit('/customer/transactions'),
                    onClose: () => router.visit('/customer/transactions'),
                });
            } else if (data.snap_url) {
                window.open(data.snap_url, '_blank');
            }
        } else {
            alert(data.message || 'Gagal membuka halaman pembayaran Midtrans.');
        }
    } catch {
        isRepaying.value = false;
        alert('Terjadi kendala jaringan saat menghubungkan ke Midtrans.');
    }
};

// Formatting helpers
const getStatusTone = (
    status: string,
): 'Green' | 'Amber' | 'Blue' | 'Neutral' => {
    const map: Record<string, 'Green' | 'Amber' | 'Blue' | 'Neutral'> = {
        pending: 'Amber',
        processing: 'Blue',
        delivering: 'Blue',
        delivered: 'Amber',
        completed: 'Green',
        canceled: 'Neutral',
    };
    return map[status] ?? 'Neutral';
};

const getStatusLabel = (status: string): string => {
    const map: Record<string, string> = {
        pending: 'Menunggu Bayar',
        processing: 'Diproses',
        delivering: 'Dikirim',
        delivered: 'Terkirim',
        completed: 'Selesai',
        canceled: 'Dibatalkan',
    };
    return map[status] ?? status;
};

const getPaymentLabel = (method: string): string => {
    return method === 'points' ? 'Poin Full' : 'Midtrans';
};

const getPaymentTone = (
    method: string,
): 'Green' | 'Amber' | 'Blue' | 'Neutral' => {
    return method === 'points' ? 'Green' : 'Blue';
};

const formatDate = (dateString?: string | null): string => {
    if (!dateString) return '—';
    try {
        return new Date(dateString).toLocaleDateString('id-ID', {
            day: 'numeric',
            month: 'short',
            year: 'numeric',
        });
    } catch {
        return dateString;
    }
};

const formatRupiah = (n: number) => 'Rp ' + n.toLocaleString('id-ID');
</script>

<template>
    <Head title="Riwayat Pesanan Saya - Upcycle Store ReValue" />

    <DashboardLayout>
        <!-- Success Flash -->
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
            >
                <X class="size-4" />
            </button>
        </div>

        <!-- Error Flash -->
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
            >
                <X class="size-4" />
            </button>
        </div>

        <!-- Header -->
        <div class="mb-6">
            <PageHeader
                title="Pesanan Saya"
                subtitle="Pantau status pengiriman, rincian produk yang dibeli, dan riwayat transaksi Upcycle Store Anda."
                badge="Pesanan Toko"
                class="mb-0"
            />
        </div>

        <!-- Toolbar: Search & Filter -->
        <div
            class="mb-6 flex flex-col items-stretch gap-3 lg:flex-row lg:items-center"
        >
            <SearchInput
                v-model="search"
                placeholder="Cari ID transaksi atau nama produk..."
                :loading="isSearching"
            />

            <div
                class="flex w-full flex-wrap items-center gap-2.5 sm:flex-nowrap lg:w-auto"
            >
                <AppDropdown
                    v-model="selectedStatus"
                    :options="statusOptions"
                    placeholder="Semua Status"
                    class="min-w-[160px] flex-1 sm:w-[190px] sm:flex-initial"
                    menu-width="w-full sm:w-[210px]"
                    align="right"
                    @change="applyFilters"
                />

                <button
                    v-if="hasActiveFilter"
                    type="button"
                    @click="resetFilters"
                    class="h-[44px] shrink-0 cursor-pointer rounded-xl border-2 border-border-gray bg-paper-white p-2.5 text-charcoal transition-colors hover:bg-cloud-surface hover:text-night-ink"
                    title="Reset Filter"
                >
                    <RotateCcw class="size-4" />
                </button>
            </div>
        </div>

        <!-- Table & Pagination via AppTable -->
        <AppTable
            :is-empty="transactions.data.length === 0"
            :has-filter="hasActiveFilter"
            empty-title="Belum ada pesanan produk"
            empty-description="Jelajahi karya kreasi daur ulang berkualitas di Upcycle Store dan tukarkan poin atau rupiah Anda."
            empty-filter-title="Tidak ada pesanan yang cocok"
            empty-filter-description="Coba ubah kata kunci pencarian atau sesuaikan filter status transaksi Anda."
            empty-action-label="Jelajahi Upcycle Store"
            min-width="min-w-[850px]"
            @create="router.visit('/customer/products')"
            @reset-filter="resetFilters"
        >
            <!-- Mobile Cards View (< md) -->
            <template #mobile>
                <div
                    v-for="t in transactions.data"
                    :key="`m-${t.id}`"
                    class="flex flex-col gap-3 p-4 transition-colors hover:bg-cloud-surface/30"
                >
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex flex-wrap items-center gap-2">
                            <StatusChip
                                :tone="getStatusTone(t.status)"
                                :label="getStatusLabel(t.status)"
                            />
                            <StatusChip
                                :tone="getPaymentTone(t.payment_method)"
                                :label="getPaymentLabel(t.payment_method)"
                            />
                        </div>
                        <span class="text-xs font-bold text-charcoal"
                            >#TRX-{{ t.id }}</span
                        >
                    </div>

                    <div class="flex items-start gap-3">
                        <div class="min-w-0 flex-1">
                            <h4
                                class="mb-1 truncate text-sm font-bold text-night-ink"
                            >
                                {{
                                    t.details?.[0]?.product?.name ??
                                    'Produk Upcycle'
                                }}
                                <span
                                    v-if="(t.details?.length ?? 0) > 1"
                                    class="text-xs font-semibold text-charcoal"
                                >
                                    +{{ (t.details?.length ?? 1) - 1 }} lainnya
                                </span>
                            </h4>
                            <div
                                class="flex items-center gap-2 text-xs font-semibold text-charcoal"
                            >
                                <span>Total:</span>
                                <span class="font-extrabold text-night-ink">{{
                                    formatRupiah(t.payable_amount)
                                }}</span>
                                <span
                                    v-if="t.points_used > 0"
                                    class="text-eco-green-dark"
                                >
                                    ({{ t.points_used.toLocaleString('id-ID') }}
                                    Poin)
                                </span>
                            </div>
                        </div>

                        <button
                            @click="openDetailModal(t)"
                            class="shrink-0 rounded-lg border-2 border-b-[3px] border-border-gray p-2 text-charcoal transition-all hover:bg-cloud-surface hover:text-night-ink active:translate-y-0.5 active:border-b-2"
                            title="Lihat Rincian"
                        >
                            <Eye class="size-4" />
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
                    class="px-4 py-3.5 text-left text-[12px] font-extrabold tracking-wider text-charcoal uppercase"
                >
                    Rincian Produk
                </th>
                <th
                    class="w-36 px-4 py-3.5 text-right text-[12px] font-extrabold tracking-wider text-charcoal uppercase"
                >
                    Total Bayar
                </th>
                <th
                    class="w-32 px-4 py-3.5 text-left text-[12px] font-extrabold tracking-wider text-charcoal uppercase"
                >
                    Metode
                </th>
                <th
                    class="w-36 px-4 py-3.5 text-left text-[12px] font-extrabold tracking-wider text-charcoal uppercase"
                >
                    Status
                </th>
                <th
                    class="w-32 px-4 py-3.5 text-left text-[12px] font-extrabold tracking-wider text-charcoal uppercase"
                >
                    Tanggal
                </th>
                <th
                    class="w-20 px-4 py-3.5 text-center text-[12px] font-extrabold tracking-wider text-charcoal uppercase"
                >
                    Aksi
                </th>
            </template>

            <!-- Desktop Body -->
            <template #body>
                <tr
                    v-for="t in transactions.data"
                    :key="t.id"
                    class="transition-colors hover:bg-cloud-surface/50"
                >
                    <!-- ID -->
                    <td class="px-4 py-4">
                        <span class="text-[13px] font-bold text-charcoal/70"
                            >#TRX-{{ t.id }}</span
                        >
                    </td>

                    <!-- Produk -->
                    <td class="px-4 py-4">
                        <div class="flex items-center gap-3">
                            <div
                                class="flex size-10 shrink-0 items-center justify-center overflow-hidden rounded-lg border border-border-gray bg-cloud-surface"
                            >
                                <img
                                    v-if="
                                        t.details?.[0]?.product?.photo_url ||
                                        t.details?.[0]?.product?.photo
                                    "
                                    :src="
                                        t.details?.[0]?.product?.photo_url ||
                                        `/storage/${t.details?.[0]?.product?.photo}`
                                    "
                                    :alt="t.details?.[0]?.product?.name"
                                    class="h-full w-full object-cover"
                                />
                                <Package
                                    v-else
                                    class="size-5 text-charcoal/50"
                                />
                            </div>
                            <div class="min-w-0">
                                <p
                                    class="truncate text-[14px] font-bold text-night-ink"
                                >
                                    {{
                                        t.details?.[0]?.product?.name ??
                                        'Produk Upcycle'
                                    }}
                                </p>
                                <p class="text-[12px] text-charcoal/70">
                                    {{ t.details?.[0]?.quantity }} item
                                    <span v-if="(t.details?.length ?? 0) > 1">
                                        (dan
                                        {{ (t.details?.length ?? 1) - 1 }}
                                        produk lainnya)
                                    </span>
                                </p>
                            </div>
                        </div>
                    </td>

                    <!-- Total Bayar -->
                    <td class="px-4 py-4 text-right">
                        <span
                            class="block text-[14px] leading-none font-black text-night-ink"
                        >
                            {{ formatRupiah(t.payable_amount) }}
                        </span>
                        <span
                            v-if="t.points_used > 0"
                            class="text-[11px] font-bold text-eco-green-dark"
                        >
                            +{{ t.points_used.toLocaleString('id-ID') }} Poin
                        </span>
                    </td>

                    <!-- Metode -->
                    <td class="px-4 py-4">
                        <StatusChip
                            :tone="getPaymentTone(t.payment_method)"
                            :label="getPaymentLabel(t.payment_method)"
                        />
                    </td>

                    <!-- Status -->
                    <td class="px-4 py-4">
                        <StatusChip
                            :tone="getStatusTone(t.status)"
                            :label="getStatusLabel(t.status)"
                        />
                    </td>

                    <!-- Tanggal -->
                    <td class="px-4 py-4">
                        <span class="text-[13px] font-semibold text-charcoal">
                            {{ formatDate(t.created_at) }}
                        </span>
                    </td>

                    <!-- Aksi -->
                    <td class="px-4 py-4 text-center">
                        <div class="flex items-center justify-center gap-1.5">
                            <button
                                v-if="t.status === 'delivered'"
                                @click="handleConfirmReceived(t)"
                                class="hover:bg-forest-green inline-flex cursor-pointer items-center gap-1 rounded-lg border-2 border-b-[3px] border-eco-green bg-eco-green px-2.5 py-1.5 text-xs font-black text-white shadow-sm transition-all active:translate-y-0.5 active:border-b-2"
                                title="Konfirmasi Terima Pesanan"
                            >
                                <CheckCircle2 class="size-3.5" />
                                <span>Terima</span>
                            </button>
                            <button
                                @click="openDetailModal(t)"
                                class="cursor-pointer rounded-lg border-2 border-b-[3px] border-border-gray p-2 text-charcoal transition-all hover:bg-cloud-surface hover:text-night-ink active:translate-y-0.5 active:border-b-2"
                                title="Lihat Detail Pesanan"
                            >
                                <Eye class="size-4" />
                            </button>
                        </div>
                    </td>
                </tr>
            </template>

            <!-- Pagination -->
            <template #pagination>
                <AppPagination
                    :pagination="transactions"
                    item-label="pesanan"
                />
            </template>
        </AppTable>
    </DashboardLayout>

    <!-- Modal Detail Transaksi -->
    <CustomerTransactionDetailModal
        :show="isDetailModalOpen"
        :transaction="activeTransaction"
        :is-canceling="isCanceling"
        :is-repaying="isRepaying"
        :is-checking-status="isCheckingStatus"
        :is-confirming="isConfirming"
        :basemap-api-key="basemapApiKey"
        @close="closeDetailModal"
        @cancel="handleCancelOrder"
        @repay="handleRepayOrder"
        @check-status="handleCheckStatus"
        @confirm-received="handleConfirmReceived"
    />
</template>
