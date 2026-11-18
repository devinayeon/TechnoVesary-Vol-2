<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import DashboardLayout from '@/layouts/DashboardLayout.vue';
import PageHeader from '@/components/Dashboard/PageHeader.vue';
import StatusChip from '@/components/Dashboard/StatusChip.vue';
import SearchInput from '@/components/Dashboard/SearchInput.vue';
import AppDropdown from '@/components/Dashboard/AppDropdown.vue';
import AppTable from '@/components/Dashboard/AppTable.vue';
import AppPagination from '@/components/Dashboard/AppPagination.vue';
import CustomerSubscriptionDetailModal, {
    type CustomerSubscriptionTransactionData,
} from '@/components/Customer/CustomerSubscriptionDetailModal.vue';
import {
    Crown,
    RotateCcw,
    Eye,
    CheckCircle2,
    XCircle,
    X,
    CreditCard,
    Plus,
    Sparkles,
} from 'lucide-vue-next';

interface PaginationLinks {
    url: string | null;
    label: string;
    active: boolean;
}

interface PaginatedSubscriptionTransactions {
    data: CustomerSubscriptionTransactionData[];
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
    transactions: PaginatedSubscriptionTransactions;
    filters: {
        search?: string;
        status?: string;
    };
    statuses: Array<{ value: string; label: string }>;
    clientKey: string;
    snapJsUrl: string;
    customerPoints: number;
    isCurrentMember: boolean;
    currentExpiresAt: string | null;
}>();

const page = usePage();

// Flash alerts
const showSuccessFlash = ref(true);
const showErrorFlash = ref(true);
const showInfoFlash = ref(true);

const flashSuccess = computed(() => page.props.flash?.success);
const flashError = computed(
    () =>
        page.props.flash?.error ||
        (page.props.errors as any)?.error ||
        (Object.keys(page.props.errors || {}).length > 0
            ? Object.values(page.props.errors)[0]
            : null),
);
const flashInfo = computed(() => (page.props.flash as any)?.info);

watch(
    () => page.props.flash,
    () => {
        showSuccessFlash.value = true;
        showErrorFlash.value = true;
        showInfoFlash.value = true;
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
        '/customer/subscription/history',
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
        '/customer/subscription/history',
        {},
        { preserveState: true, preserveScroll: true },
    );
};

// Modal & Payment Actions State
const isDetailModalOpen = ref(false);
const activeTransaction = ref<CustomerSubscriptionTransactionData | null>(null);
const isCanceling = ref(false);
const isRepaying = ref(false);
const isCheckingStatus = ref(false);

const openDetailModal = (t: CustomerSubscriptionTransactionData) => {
    activeTransaction.value = t;
    isDetailModalOpen.value = true;
};

const closeDetailModal = () => {
    isDetailModalOpen.value = false;
    activeTransaction.value = null;
};

// Midtrans Snap Injection
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

const handleCheckStatus = (t: CustomerSubscriptionTransactionData) => {
    isCheckingStatus.value = true;
    router.post(
        `/customer/subscription/history/${t.id}/check-status`,
        {},
        {
            preserveScroll: true,
            onFinish: () => {
                isCheckingStatus.value = false;
                closeDetailModal();
            },
        },
    );
};

const handleCancelOrder = (t: CustomerSubscriptionTransactionData) => {
    if (
        !confirm(
            `Apakah Anda yakin ingin membatalkan transaksi langganan #SUB-${t.id}? Poin yang digunakan akan dikembalikan ke saldo Anda.`,
        )
    ) {
        return;
    }

    isCanceling.value = true;
    router.post(
        `/customer/subscription/history/${t.id}/cancel`,
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

const handleRepayOrder = async (t: CustomerSubscriptionTransactionData) => {
    isRepaying.value = true;

    try {
        const response = await fetch(
            `/customer/subscription/history/${t.id}/repay`,
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
                    onSuccess: () =>
                        router.visit('/customer/subscription/history'),
                    onPending: () =>
                        router.visit('/customer/subscription/history'),
                    onClose: () =>
                        router.visit('/customer/subscription/history'),
                });
            } else if (data.snap_url) {
                window.open(data.snap_url, '_blank');
            }
        } else {
            alert(data.message || 'Gagal membuka jendela pembayaran Midtrans.');
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
        settled: 'Green',
        canceled: 'Neutral',
        failed: 'Neutral',
        expired: 'Neutral',
    };
    return map[status] ?? 'Neutral';
};

const getStatusLabel = (status: string): string => {
    const map: Record<string, string> = {
        pending: 'Menunggu Bayar',
        settled: 'Lunas & Aktif',
        canceled: 'Dibatalkan',
        failed: 'Gagal',
        expired: 'Kedaluwarsa',
    };
    return map[status] ?? status;
};

const getPaymentLabel = (method: string): string => {
    return method === 'points' ? 'Poin Full' : 'Midtrans';
};

const getPaymentTone = (method: string): 'Green' | 'Blue' | 'Neutral' => {
    return method === 'points' ? 'Green' : 'Blue';
};

const getPackageTitle = (t: CustomerSubscriptionTransactionData): string => {
    if (t.package_key === '3_months') return 'ReValue+ 3 Bulan';
    if (t.package_key === '1_year') return 'ReValue+ 1 Tahun';
    if (t.package_key === '1_month') return 'ReValue+ 1 Bulan';
    return t.subscription?.name ?? 'ReValue+';
};

const getPackageDays = (t: CustomerSubscriptionTransactionData): number => {
    if (t.package_key === '3_months') return 90;
    if (t.package_key === '1_year') return 365;
    return 30;
};

const formatDate = (dateStr: string | null | undefined): string => {
    if (!dateStr) return '-';
    return new Date(dateStr).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const formatRupiah = (amount: number): string => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(amount);
};
</script>

<template>
    <Head title="Riwayat Langganan ReValue+" />

    <DashboardLayout>
        <!-- Header Halaman & CTA Tambah Langganan -->
        <div
            class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <PageHeader
                title="Riwayat Langganan ReValue+"
                subtitle="Pantau status aktivasi dan riwayat tagihan langganan keanggotaan Anda."
                badge="Manajemen Akun"
            />
            <div class="flex items-center gap-3">
                <Link
                    href="/customer/subscription"
                    class="inline-flex cursor-pointer items-center gap-2 rounded-2xl bg-eco-green px-5 py-3 text-xs font-extrabold tracking-wide text-night-ink uppercase shadow-[0px_4px_0px_0px_var(--color-eco-green-dark)] transition-all hover:brightness-105 active:translate-y-1 active:shadow-none"
                >
                    <Plus class="size-4" />
                    <span>Beli Paket Baru</span>
                </Link>
            </div>
        </div>

        <!-- Banner Ringkas Status Keanggotaan Saat Ini -->
        <div
            v-if="isCurrentMember"
            class="mb-6 flex items-center justify-between rounded-[22px] border-2 border-b-[5px] border-eco-green bg-sprout-tint/40 p-4 shadow-xs"
        >
            <div class="flex items-center gap-3">
                <div
                    class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-eco-green text-night-ink shadow-[0px_3px_0px_0px_var(--color-eco-green-dark)]"
                >
                    <Crown class="size-5" />
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span
                            class="text-xs font-black tracking-wider text-night-ink uppercase"
                        >
                            ReValue+ Sedang Aktif
                        </span>
                        <Sparkles class="size-3.5 text-sunlight-gold" />
                    </div>
                    <p class="text-xs font-medium text-charcoal">
                        Masa aktif berlaku sampai:
                        <strong class="font-extrabold text-night-ink">{{
                            currentExpiresAt
                        }}</strong>
                    </p>
                </div>
            </div>
            <Link
                href="/customer/subscription"
                class="hidden cursor-pointer rounded-xl border-2 border-b-2 border-border-gray bg-paper-white px-3 py-1.5 text-xs font-extrabold text-night-ink shadow-2xs transition hover:bg-cloud-surface active:translate-y-0.5 sm:inline-block"
            >
                Perpanjang Sekarang
            </Link>
        </div>

        <!-- Flash Alert Messages -->
        <div v-if="flashSuccess && showSuccessFlash" class="mb-6">
            <div
                class="flex items-center justify-between rounded-2xl border-2 border-b-4 border-eco-green/40 bg-sprout-tint/70 p-4 text-night-ink shadow-xs"
            >
                <div class="flex items-center gap-3">
                    <CheckCircle2 class="size-5 shrink-0 text-eco-green-dark" />
                    <span class="text-sm font-bold">{{ flashSuccess }}</span>
                </div>
                <button
                    type="button"
                    @click="showSuccessFlash = false"
                    class="cursor-pointer rounded-lg p-1 text-night-ink hover:bg-sprout-tint"
                >
                    <X class="size-4" />
                </button>
            </div>
        </div>

        <div v-if="flashError && showErrorFlash" class="mb-6">
            <div
                class="flex items-center justify-between rounded-2xl border-2 border-b-4 border-red-300 bg-red-50 p-4 text-red-900 shadow-xs"
            >
                <div class="flex items-center gap-3">
                    <XCircle class="size-5 shrink-0 text-red-600" />
                    <span class="text-sm font-bold">{{ flashError }}</span>
                </div>
                <button
                    type="button"
                    @click="showErrorFlash = false"
                    class="cursor-pointer rounded-lg p-1 text-red-600 hover:bg-red-100"
                >
                    <X class="size-4" />
                </button>
            </div>
        </div>

        <div v-if="flashInfo && showInfoFlash" class="mb-6">
            <div
                class="flex items-center justify-between rounded-2xl border-2 border-b-4 border-sky-blue/40 bg-sky-tint p-4 text-night-ink shadow-xs"
            >
                <div class="flex items-center gap-3">
                    <Crown class="size-5 shrink-0 text-sky-blue-dark" />
                    <span class="text-sm font-bold">{{ flashInfo }}</span>
                </div>
                <button
                    type="button"
                    @click="showInfoFlash = false"
                    class="cursor-pointer rounded-lg p-1 text-night-ink hover:bg-sky-blue/20"
                >
                    <X class="size-4" />
                </button>
            </div>
        </div>

        <!-- Filter & Search Toolbar -->
        <div
            class="mb-6 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between"
        >
            <SearchInput
                v-model="search"
                placeholder="Cari ID langganan atau nomor tagihan..."
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
            empty-title="Belum ada riwayat langganan"
            empty-description="Tingkatkan ke akun ReValue+ untuk menikmati layanan jemput sampah prioritas dan bebas biaya jemput."
            empty-filter-title="Tidak ada transaksi yang cocok"
            empty-filter-description="Coba ubah kata kunci pencarian atau sesuaikan filter status transaksi Anda."
            empty-action-label="Lihat Paket ReValue+"
            min-width="min-w-[850px]"
            @create="router.visit('/customer/subscription')"
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
                        <span class="text-xs font-bold text-charcoal">
                            #SUB-{{ t.id }}
                        </span>
                    </div>

                    <div class="flex items-start gap-3">
                        <div
                            class="relative flex size-12 shrink-0 items-center justify-center rounded-xl border border-border-gray/70 bg-cloud-surface p-1"
                        >
                            <img
                                src="/assets/Badge/ReValuePass.png"
                                alt="ReValue Pass"
                                class="h-9 w-9 object-contain"
                            />
                        </div>

                        <div class="min-w-0 flex-1">
                            <h4
                                class="truncate text-sm font-bold text-night-ink"
                            >
                                {{ getPackageTitle(t) }}
                            </h4>
                            <div
                                class="flex items-center gap-2 text-xs font-semibold text-charcoal"
                            >
                                <span>Tagihan:</span>
                                <span class="font-extrabold text-night-ink">
                                    {{ formatRupiah(t.payable_amount) }}
                                </span>
                                <span
                                    v-if="t.points_used > 0"
                                    class="text-eco-green-dark"
                                >
                                    ({{ t.points_used.toLocaleString('id-ID') }}
                                    Poin)
                                </span>
                            </div>
                            <div class="mt-1 text-[11px] text-charcoal/70">
                                {{ formatDate(t.created_at) }}
                            </div>
                        </div>

                        <div class="flex items-center gap-1">
                            <button
                                v-if="t.status === 'pending'"
                                @click="openDetailModal(t)"
                                class="shrink-0 cursor-pointer rounded-lg border-2 border-b-[3px] border-eco-green-dark bg-eco-green p-2 text-night-ink shadow-xs transition hover:brightness-105 active:translate-y-0.5 active:border-b-2"
                                title="Bayar Sekarang"
                            >
                                <CreditCard class="size-4" />
                            </button>
                            <button
                                @click="openDetailModal(t)"
                                class="shrink-0 cursor-pointer rounded-lg border-2 border-b-[3px] border-border-gray p-2 text-charcoal transition-all hover:bg-cloud-surface hover:text-night-ink active:translate-y-0.5 active:border-b-2"
                                title="Lihat Rincian"
                            >
                                <Eye class="size-4" />
                            </button>
                        </div>
                    </div>
                </div>
            </template>

            <!-- Desktop Header -->
            <template #header>
                <th
                    class="w-24 px-4 py-3.5 text-left text-[12px] font-extrabold tracking-wider text-charcoal uppercase"
                >
                    ID Transaksi
                </th>
                <th
                    class="px-4 py-3.5 text-left text-[12px] font-extrabold tracking-wider text-charcoal uppercase"
                >
                    Paket Langganan
                </th>
                <th
                    class="w-36 px-4 py-3.5 text-right text-[12px] font-extrabold tracking-wider text-charcoal uppercase"
                >
                    Total Tagihan
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
                    class="w-36 px-4 py-3.5 text-left text-[12px] font-extrabold tracking-wider text-charcoal uppercase"
                >
                    Tanggal
                </th>
                <th
                    class="w-24 px-4 py-3.5 text-center text-[12px] font-extrabold tracking-wider text-charcoal uppercase"
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
                        <span class="text-[13px] font-bold text-charcoal/70">
                            #SUB-{{ t.id }}
                        </span>
                    </td>

                    <!-- Paket Langganan -->
                    <td class="px-4 py-4">
                        <div class="flex items-center gap-3">
                            <div
                                class="flex size-10 shrink-0 items-center justify-center overflow-hidden rounded-lg border border-border-gray bg-cloud-surface p-1"
                            >
                                <img
                                    src="/assets/Badge/ReValuePass.png"
                                    alt="ReValue Pass"
                                    class="h-full w-full object-contain"
                                />
                            </div>
                            <div class="min-w-0">
                                <p
                                    class="truncate text-[14px] font-bold text-night-ink"
                                >
                                    {{ getPackageTitle(t) }}
                                </p>
                                <p class="text-[12px] text-charcoal/70">
                                    Durasi {{ getPackageDays(t) }} hari
                                    keanggotaan
                                </p>
                            </div>
                        </div>
                    </td>

                    <!-- Total Tagihan -->
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
                                v-if="t.status === 'pending'"
                                @click="openDetailModal(t)"
                                class="inline-flex cursor-pointer items-center gap-1 rounded-lg border-2 border-b-[3px] border-eco-green-dark bg-eco-green px-2.5 py-1.5 text-xs font-black text-night-ink shadow-xs transition-all hover:brightness-105 active:translate-y-0.5 active:border-b-2"
                                title="Bayar Sekarang"
                            >
                                <CreditCard class="size-3.5" />
                                <span>Bayar</span>
                            </button>
                            <button
                                @click="openDetailModal(t)"
                                class="cursor-pointer rounded-lg border-2 border-b-[3px] border-border-gray p-2 text-charcoal transition-all hover:bg-cloud-surface hover:text-night-ink active:translate-y-0.5 active:border-b-2"
                                title="Lihat Detail Transaksi"
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
                    item-label="transaksi langganan"
                />
            </template>
        </AppTable>
    </DashboardLayout>

    <!-- Modal Detail Transaksi Langganan -->
    <CustomerSubscriptionDetailModal
        :show="isDetailModalOpen"
        :transaction="activeTransaction"
        :is-checking-status="isCheckingStatus"
        :is-canceling="isCanceling"
        :is-repaying="isRepaying"
        @close="closeDetailModal"
        @check-status="handleCheckStatus"
        @cancel="handleCancelOrder"
        @repay="handleRepayOrder"
    />
</template>
