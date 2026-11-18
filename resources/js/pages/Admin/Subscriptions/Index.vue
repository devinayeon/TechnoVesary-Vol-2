<script setup lang="ts">
import { ref, watch, computed } from "vue";
import { Head, router, usePage } from "@inertiajs/vue3";
import DashboardLayout from "@/layouts/DashboardLayout.vue";
import PageHeader from "@/components/Dashboard/PageHeader.vue";
import StatusChip from "@/components/Dashboard/StatusChip.vue";
import AppDropdown from "@/components/Dashboard/AppDropdown.vue";
import AppTable from "@/components/Dashboard/AppTable.vue";
import AppPagination from "@/components/Dashboard/AppPagination.vue";
import UserAvatar from "@/components/Dashboard/UserAvatar.vue";
import EditSubscriptionPriceModal, {
    type SubscriptionData,
} from "@/components/Dashboard/EditSubscriptionPriceModal.vue";
import SubscriptionTransactionDetailModal, {
    type SubscriptionTransactionItem,
} from "@/components/Dashboard/SubscriptionTransactionDetailModal.vue";
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
} from "lucide-vue-next";

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
const search = ref(props.filters.search || "");
const selectedStatus = ref(props.filters.status || "all");

let searchTimeout: any = null;
const applyFilters = () => {
    router.get(
        "/admin/subscriptions",
        {
            search: search.value || undefined,
            status:
                selectedStatus.value !== "all"
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
    { value: "all", label: "Semua Status" },
    ...props.statuses.map((s) => ({
        value: s.value,
        label: s.label,
    })),
]);

const resetFilters = () => {
    search.value = "";
    selectedStatus.value = "all";
    router.get(
        "/admin/subscriptions",
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
    return Number(num || 0).toLocaleString("id-ID");
};

const formatDate = (dateString?: string | null): string => {
    if (!dateString) return "—";
    try {
        const d = new Date(dateString);
        return d.toLocaleDateString("id-ID", {
            day: "numeric",
            month: "short",
            year: "numeric",
        });
    } catch {
        return dateString;
    }
};

const getStatusTone = (status: string): "Green" | "Amber" | "Neutral" => {
    switch (status.toLowerCase()) {
        case "settled":
            return "Green";
        case "pending":
            return "Amber";
        default:
            return "Neutral";
    }
};

const getStatusLabel = (status: string): string => {
    switch (status.toLowerCase()) {
        case "settled":
            return "Berhasil";
        case "pending":
            return "Menunggu Bayar";
        case "failed":
            return "Gagal";
        case "canceled":
            return "Dibatalkan";
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
            class="mb-6 flex items-center justify-between gap-3 p-4 rounded-2xl bg-sprout-tint border-2 border-eco-green/40 text-night-ink font-bold text-[14px] shadow-2xs animate-in fade-in duration-200"
        >
            <div class="flex items-center gap-3 min-w-0">
                <CheckCircle2 class="size-5 text-eco-green-dark shrink-0" />
                <span class="truncate">{{ flashSuccess }}</span>
            </div>
            <button
                type="button"
                @click="showSuccessFlash = false"
                class="p-1 rounded-lg hover:bg-eco-green/20 text-charcoal hover:text-night-ink transition-colors cursor-pointer shrink-0"
                aria-label="Tutup notifikasi"
            >
                <X class="size-4" />
            </button>
        </div>

        <!-- Error Flash Alert -->
        <div
            v-if="showErrorFlash && flashError"
            class="mb-6 flex items-center justify-between gap-3 p-4 rounded-2xl bg-red-50 border-2 border-red-300 text-red-800 font-bold text-[14px] shadow-2xs animate-in fade-in duration-200"
        >
            <div class="flex items-center gap-3 min-w-0">
                <XCircle class="size-5 text-red-600 shrink-0" />
                <span class="truncate">{{ flashError }}</span>
            </div>
            <button
                type="button"
                @click="showErrorFlash = false"
                class="p-1 rounded-lg hover:bg-red-100 text-red-700 hover:text-red-900 transition-colors cursor-pointer shrink-0"
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
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8 items-stretch">
            <!-- Left Card: Subscription Plan (Figma 485:2428) -->
            <div
                class="lg:col-span-2 bg-paper-white border-2 border-b-[4px] border-border-gray rounded-[20px] p-6 sm:p-7 shadow-xs flex flex-col justify-between relative overflow-hidden"
            >
                <div>
                    <!-- Top Row: Status Chip & Mascot Illustration -->
                    <div class="flex items-start justify-between gap-4">
                        <div class="space-y-2">
                            <h2
                                class="text-[24px] sm:text-[28px] font-black text-night-ink leading-tight tracking-tight"
                            >
                                {{ subscription.name }}
                            </h2>
                        </div>
                        <div
                            class="size-20 sm:size-24 rounded-2xl bg-cloud-surface border-2 border-border-gray/80 flex items-center justify-center p-2 shrink-0 shadow-2xs"
                        >
                            <img
                                src="/assets/Badge/revalue-plus-badge.png"
                                alt="ReValue Plus"
                                class="w-full h-full object-contain"
                            />
                        </div>
                    </div>

                    <!-- Price Display -->
                    <div class="mt-4 mb-3">
                        <div class="flex items-baseline gap-2">
                            <span
                                class="text-[34px] sm:text-[40px] font-black text-night-ink leading-none tracking-tight"
                            >
                                Rp {{ formatNumber(subscription.price) }}
                            </span>
                            <span
                                class="text-[13px] sm:text-[14px] font-bold text-charcoal"
                            >
                                / 30 hari layanan
                            </span>
                        </div>
                    </div>

                    <!-- Description -->
                    <p
                        class="text-[14px] font-medium text-charcoal leading-relaxed max-w-[620px]"
                    >
                        {{
                            subscription.description ||
                            "Penjemputan sampah terpilah dari rumah, penimbangan transparan, dan poin dari hasil setoran."
                        }}
                    </p>
                </div>

                <!-- Action Button -->
                <div
                    class="pt-6 mt-4 border-t border-border-gray/70 flex items-center justify-start"
                >
                    <button
                        type="button"
                        @click="openPriceModal"
                        class="inline-flex items-center gap-2 px-5 h-[44px] rounded-xl border-2 border-b-[4px] border-border-gray bg-paper-white text-night-ink font-extrabold text-[14px] hover:bg-cloud-surface active:translate-y-0.5 active:border-b-2 transition-all cursor-pointer shadow-2xs"
                    >
                        <Edit3 class="size-4 text-charcoal" />
                        <span>Ubah Harga</span>
                    </button>
                </div>
            </div>

            <!-- Right Card: Subscription Rules (Figma 485:2441) -->
            <div
                class="lg:col-span-1 bg-paper-white border-2 border-b-[4px] border-border-gray rounded-[20px] p-6 sm:p-7 shadow-xs flex flex-col justify-between"
            >
                <div>
                    <h3
                        class="text-[18px] font-black text-night-ink leading-tight mb-4"
                    >
                        Harga jelas, layanan berlanjut.
                    </h3>

                    <!-- Detail Fields -->
                    <div class="space-y-3 mb-5">
                        <div
                            class="p-3 rounded-xl bg-cloud-surface/70 border border-border-gray flex items-center gap-3"
                        >
                            <div
                                class="size-9 rounded-lg bg-sprout-tint text-eco-green-dark border border-eco-green/30 flex items-center justify-center shrink-0"
                            >
                                <Calendar class="size-4.5" />
                            </div>
                            <div>
                                <span
                                    class="text-[11px] font-bold text-charcoal uppercase tracking-wider block"
                                >
                                    Periode Layanan
                                </span>
                                <span
                                    class="font-extrabold text-[13px] text-night-ink"
                                >
                                    30 Hari Kalender
                                </span>
                            </div>
                        </div>

                        <div
                            class="p-3 rounded-xl bg-cloud-surface/70 border border-border-gray flex items-center gap-3"
                        >
                            <div
                                class="size-9 rounded-lg bg-gold-tint text-sunlight-gold-dark border border-sunlight-gold/30 flex items-center justify-center shrink-0"
                            >
                                <Sparkles class="size-4.5 text-sunlight-gold" />
                            </div>
                            <div>
                                <span
                                    class="text-[11px] font-bold text-charcoal uppercase tracking-wider block"
                                >
                                    Akses Fitur Lengkap
                                </span>
                                <span
                                    class="font-extrabold text-[13px] text-night-ink"
                                >
                                    Penjemputan & Konversi Poin
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer Context Text -->
                <p
                    class="text-[12px] font-medium text-charcoal/80 leading-relaxed border-t border-border-gray/70 pt-4"
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
                class="text-[20px] sm:text-[22px] font-black text-night-ink leading-tight"
            >
                Riwayat Transaksi Langganan
            </h2>
            <p class="text-[13px] font-medium text-charcoal mt-0.5">
                Pantau tagihan dan status pembayaran langganan dari seluruh
                customer.
            </p>
        </div>

        <!-- Toolbar: Search & Status Filter (Matching User Management) -->
        <div
            class="flex flex-col lg:flex-row items-stretch lg:items-center gap-3 mb-6"
        >
            <!-- Search Input -->
            <div class="relative w-full">
                <Search
                    class="absolute left-3.5 top-1/2 -translate-y-1/2 size-4.5 text-charcoal/70 pointer-events-none"
                />
                <input
                    v-model="search"
                    type="text"
                    placeholder="Cari ID transaksi, nama customer, atau email..."
                    class="w-full h-[44px] pl-10 pr-10 bg-paper-white border-2 border-border-gray rounded-xl text-[14px] font-medium text-night-ink placeholder:text-charcoal/60 focus:outline-none focus:border-eco-green focus:ring-4 focus:ring-eco-green/15 transition-all shadow-2xs"
                />
                <button
                    v-if="search"
                    @click="search = ''"
                    type="button"
                    class="absolute right-3 top-1/2 -translate-y-1/2 p-1 rounded-md text-charcoal hover:bg-cloud-surface hover:text-night-ink transition-colors cursor-pointer"
                    aria-label="Bersihkan pencarian"
                >
                    <X class="size-3.5" />
                </button>
            </div>

            <!-- Filters Group -->
            <div
                class="flex flex-wrap sm:flex-nowrap items-center gap-2.5 w-full lg:w-auto"
            >
                <!-- Status Filter Dropdown -->
                <AppDropdown
                    v-model="selectedStatus"
                    :options="statusDropdownOptions"
                    placeholder="Semua Status"
                    class="flex-1 sm:flex-initial min-w-[150px] sm:w-[200px]"
                    menu-width="w-full sm:w-[210px]"
                    align="right"
                    @change="onStatusChange"
                />

                <!-- Reset Filter Button -->
                <button
                    v-if="search || selectedStatus !== 'all'"
                    type="button"
                    @click="resetFilters"
                    class="p-2.5 h-[44px] rounded-xl border-2 border-border-gray bg-paper-white text-charcoal hover:text-night-ink hover:bg-cloud-surface transition-colors cursor-pointer shrink-0"
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
                    class="p-4 flex flex-col gap-3 hover:bg-cloud-surface/30 transition-colors"
                >
                    <!-- Header: ID Transaksi + Status -->
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <span
                                class="font-extrabold text-[14px] text-night-ink"
                            >
                                #TRX-SUB-{{ String(trx.id).padStart(4, "0") }}
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
                                class="font-extrabold text-[14px] text-night-ink truncate"
                            >
                                {{ trx.customer?.user?.name || "Customer" }}
                            </h4>
                            <p
                                class="text-[12px] font-medium text-charcoal truncate"
                            >
                                {{ trx.customer?.user?.email || "—" }}
                            </p>
                        </div>
                    </div>

                    <!-- Financial Meta Box -->
                    <div
                        class="flex items-center justify-between text-[12px] bg-cloud-surface/50 rounded-xl px-3 py-2 border border-border-gray/50"
                    >
                        <span class="text-charcoal font-semibold">
                            {{
                                trx.payment_method === "points"
                                    ? "Poin Penuh"
                                    : "Midtrans"
                            }}
                        </span>
                        <div class="font-extrabold text-[13px] text-night-ink">
                            Rp {{ formatNumber(trx.payable_amount) }}
                        </div>
                    </div>

                    <!-- Action Button -->
                    <div
                        class="flex items-center justify-end pt-1 border-t border-border-gray/40"
                    >
                        <button
                            type="button"
                            @click="openDetailModal(trx)"
                            class="px-3 py-1.5 text-[12px] font-extrabold text-night-ink bg-cloud-surface hover:bg-sprout-tint hover:text-eco-green-dark rounded-xl transition-colors cursor-pointer"
                        >
                            Lihat Detail
                        </button>
                    </div>
                </div>
            </template>

            <!-- Desktop Table Header -->
            <template #header>
                <th class="py-3.5 px-6">ID Transaksi</th>
                <th class="py-3.5 px-6">Customer</th>
                <th class="py-3.5 px-6">Metode</th>
                <th class="py-3.5 px-6">Total Bayar</th>
                <th class="py-3.5 px-6">Status</th>
                <th class="py-3.5 px-6">Tanggal</th>
                <th class="py-3.5 px-6 text-right">Aksi</th>
            </template>

            <!-- Desktop Table Body -->
            <template #body>
                <tr
                    v-for="trx in transactions.data"
                    :key="trx.id"
                    class="hover:bg-cloud-surface/50 transition-colors"
                >
                    <!-- ID Transaksi -->
                    <td
                        class="py-3.5 px-6 font-extrabold text-[13px] text-night-ink"
                    >
                        #TRX-SUB-{{ String(trx.id).padStart(4, "0") }}
                    </td>

                    <!-- Customer -->
                    <td class="py-3.5 px-6">
                        <div class="flex items-center gap-3">
                            <UserAvatar
                                :photo="trx.customer?.user?.photo || null"
                                :role="trx.customer?.user?.role || 'customer'"
                                :name="trx.customer?.user?.name || 'Customer'"
                                size="md"
                            />
                            <div class="min-w-0">
                                <p
                                    class="font-bold text-[14px] text-night-ink leading-tight truncate"
                                >
                                    {{ trx.customer?.user?.name || "Customer" }}
                                </p>
                                <p
                                    class="text-[12px] font-medium text-charcoal/70 truncate mt-0.5"
                                >
                                    {{ trx.customer?.user?.email || "—" }}
                                </p>
                            </div>
                        </div>
                    </td>

                    <!-- Metode Pembayaran -->
                    <td class="py-3.5 px-6">
                        <span
                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[12px] font-bold border border-border-gray bg-cloud-surface text-night-ink"
                        >
                            <CreditCard class="size-3 text-charcoal" />
                            <span>{{
                                trx.payment_method === "points"
                                    ? "Poin Penuh"
                                    : "Midtrans"
                            }}</span>
                        </span>
                    </td>

                    <!-- Total Bayar -->
                    <td class="py-3.5 px-6">
                        <div class="font-extrabold text-[14px] text-night-ink">
                            Rp {{ formatNumber(trx.payable_amount) }}
                        </div>
                        <div
                            v-if="trx.points_used > 0"
                            class="text-[11px] font-medium text-eco-green-dark flex items-center gap-1"
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
                    <td class="py-3.5 px-6">
                        <StatusChip
                            :label="getStatusLabel(trx.status)"
                            :tone="getStatusTone(trx.status)"
                        />
                    </td>

                    <!-- Tanggal -->
                    <td
                        class="py-3.5 px-6 text-[13px] font-medium text-charcoal"
                    >
                        {{ formatDate(trx.created_at) }}
                    </td>

                    <!-- Aksi -->
                    <td class="py-3.5 px-6 text-right whitespace-nowrap">
                        <button
                            type="button"
                            @click="openDetailModal(trx)"
                            class="px-2.5 py-1 text-[13px] font-extrabold text-night-ink hover:text-eco-green-dark hover:bg-cloud-surface rounded-lg transition-colors cursor-pointer"
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
