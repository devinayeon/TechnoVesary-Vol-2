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
import ProductTransactionDetailModal, {
    type ProductTransactionDetailData,
} from "@/components/Dashboard/ProductTransactionDetailModal.vue";
import {
    Search,
    RotateCcw,
    CheckCircle2,
    XCircle,
    X,
    Eye,
    ShoppingCart,
} from "lucide-vue-next";

// --- Types ---
interface UserInfo {
    id: number;
    name: string;
    email: string;
    photo: string | null;
    role: string;
}

interface TransactionDetailItem {
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

interface TransactionItem {
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
    customer?: { id: number; user?: UserInfo | null } | null;
    driver?: { id: number; user?: UserInfo | null } | null;
    details?: TransactionDetailItem[];
}

interface PaginationLinks {
    url: string | null;
    label: string;
    active: boolean;
}

interface PaginatedTransactions {
    data: TransactionItem[];
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
        payment_method?: string;
    };
    statuses: Array<{ value: string; label: string }>;
    paymentMethods: Array<{ value: string; label: string }>;
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
            : null)
);

watch(
    () => page.props.flash,
    () => {
        showSuccessFlash.value = true;
        showErrorFlash.value = true;
    },
    { deep: true }
);

// Search & Filter
const search = ref(props.filters.search || "");
const selectedStatus = ref(props.filters.status || "");
const selectedPaymentMethod = ref(props.filters.payment_method || "");

let searchTimeout: any = null;

const applyFilters = () => {
    router.get(
        "/admin/product-transactions",
        {
            search: search.value || undefined,
            status: selectedStatus.value || undefined,
            payment_method: selectedPaymentMethod.value || undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        }
    );
};

watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        applyFilters();
    }, 350);
});

const onFilterChange = () => {
    applyFilters();
};

const statusOptions = computed(() => [
    { value: "", label: "Semua Status" },
    ...props.statuses,
]);

const paymentMethodOptions = computed(() => [
    { value: "", label: "Semua Metode" },
    ...props.paymentMethods,
]);

const hasActiveFilter = computed(
    () => !!(search.value || selectedStatus.value || selectedPaymentMethod.value)
);

const resetFilters = () => {
    search.value = "";
    selectedStatus.value = "";
    selectedPaymentMethod.value = "";
    router.get(
        "/admin/product-transactions",
        {},
        { preserveState: true, preserveScroll: true }
    );
};

// Status mapping
const getStatusTone = (status: string): "Green" | "Amber" | "Blue" | "Neutral" => {
    const map: Record<string, "Green" | "Amber" | "Blue" | "Neutral"> = {
        pending: "Amber",
        processing: "Blue",
        delivering: "Blue",
        completed: "Green",
        canceled: "Neutral",
    };
    return map[status] ?? "Neutral";
};

const getStatusLabel = (status: string): string => {
    const map: Record<string, string> = {
        pending: "Menunggu Bayar",
        processing: "Diproses",
        delivering: "Dikirim",
        completed: "Selesai",
        canceled: "Dibatalkan",
    };
    return map[status] ?? status;
};

const getPaymentLabel = (method: string): string => {
    const map: Record<string, string> = {
        midtrans: "Midtrans",
        points: "Poin",
    };
    return map[method] ?? method;
};

const getPaymentTone = (method: string): "Green" | "Amber" | "Blue" | "Neutral" => {
    return method === "midtrans" ? "Blue" : "Green";
};

// Date Formatter
const formatDate = (dateString?: string | null): string => {
    if (!dateString) return "—";
    try {
        return new Date(dateString).toLocaleDateString("id-ID", {
            day: "numeric",
            month: "short",
            year: "numeric",
        });
    } catch {
        return dateString;
    }
};

const formatRupiah = (n: number) =>
    "Rp " + n.toLocaleString("id-ID");

// Modal State
const isDetailModalOpen = ref(false);
const targetTransaction = ref<ProductTransactionDetailData | null>(null);

const openDetailModal = (transaction: TransactionItem) => {
    targetTransaction.value = transaction as ProductTransactionDetailData;
    isDetailModalOpen.value = true;
};

const closeDetailModal = () => {
    isDetailModalOpen.value = false;
    targetTransaction.value = null;
};
</script>

<template>
    <Head title="Transaksi Upcycle Store - Admin ReValue" />

    <DashboardLayout>
        <!-- Success Flash -->
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

        <!-- Error Flash -->
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
        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 mb-6">
            <PageHeader
                title="Transaksi Upcycle Store"
                subtitle="Riwayat pembelian produk daur ulang oleh customer dari Upcycle Store."
                badge="Pembelian Produk"
                class="mb-0"
            />
        </div>

        <!-- Toolbar -->
        <div class="flex flex-col lg:flex-row items-stretch lg:items-center gap-3 mb-6">
            <!-- Search -->
            <div class="relative w-full">
                <Search
                    class="absolute left-3.5 top-1/2 -translate-y-1/2 size-4.5 text-charcoal/70 pointer-events-none"
                />
                <input
                    v-model="search"
                    type="text"
                    placeholder="Cari nama customer atau alamat pengiriman..."
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

            <!-- Filters -->
            <div class="flex flex-wrap sm:flex-nowrap items-center gap-2.5 w-full lg:w-auto">
                <!-- Status Filter -->
                <AppDropdown
                    v-model="selectedStatus"
                    :options="statusOptions"
                    placeholder="Semua Status"
                    class="flex-1 sm:flex-initial min-w-[150px] sm:w-[180px]"
                    menu-width="w-full sm:w-[200px]"
                    align="right"
                    @change="onFilterChange"
                />

                <!-- Payment Method Filter -->
                <AppDropdown
                    v-model="selectedPaymentMethod"
                    :options="paymentMethodOptions"
                    placeholder="Semua Metode"
                    class="flex-1 sm:flex-initial min-w-[140px] sm:w-[170px]"
                    menu-width="w-full sm:w-[190px]"
                    align="right"
                    @change="onFilterChange"
                />

                <!-- Reset -->
                <button
                    v-if="hasActiveFilter"
                    type="button"
                    @click="resetFilters"
                    class="p-2.5 h-[44px] rounded-xl border-2 border-border-gray bg-paper-white text-charcoal hover:text-night-ink hover:bg-cloud-surface transition-colors cursor-pointer shrink-0"
                    title="Reset Filter"
                >
                    <RotateCcw class="size-4" />
                </button>
            </div>
        </div>

        <!-- Table -->
        <AppTable
            :is-empty="transactions.data.length === 0"
            :has-filter="hasActiveFilter"
            empty-title="Belum ada transaksi produk"
            empty-description="Data transaksi pembelian produk dari Upcycle Store akan muncul di sini."
            empty-filter-title="Tidak ada transaksi yang cocok"
            empty-filter-description="Coba ubah kata kunci pencarian atau sesuaikan filter status dan metode pembayaran."
            min-width="min-w-[960px]"
            @reset-filter="resetFilters"
        >
            <!-- Mobile Cards -->
            <template #mobile>
                <div
                    v-for="transaction in transactions.data"
                    :key="`m-${transaction.id}`"
                    class="p-4 flex flex-col gap-3 hover:bg-cloud-surface/30 transition-colors"
                >
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2 flex-wrap">
                            <StatusChip
                                :tone="getStatusTone(transaction.status)"
                                :label="getStatusLabel(transaction.status)"
                            />
                            <StatusChip
                                :tone="getPaymentTone(transaction.payment_method)"
                                :label="getPaymentLabel(transaction.payment_method)"
                            />
                        </div>
                        <span class="text-[12px] font-bold text-charcoal/70">
                            #{{ transaction.id }}
                        </span>
                    </div>

                    <div class="flex items-start gap-3">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-1.5 mb-1">
                                <span class="text-xs text-muted-text font-semibold">Customer:</span>
                                <span class="text-sm font-bold text-night-ink truncate">
                                    {{ transaction.customer?.user?.name ?? "-" }}
                                </span>
                            </div>
                            <div class="flex items-center gap-1.5 mb-1">
                                <span class="text-xs text-muted-text font-semibold">Total:</span>
                                <span class="text-sm font-black text-eco-green">
                                    {{ formatRupiah(transaction.payable_amount) }}
                                </span>
                            </div>
                            <p class="text-[12px] text-charcoal/70">
                                {{ transaction.details?.length ?? 0 }} item produk
                            </p>
                        </div>
                        <button
                            @click="openDetailModal(transaction)"
                            class="p-2 rounded-lg border-2 border-b-[3px] border-border-gray text-charcoal hover:text-night-ink hover:bg-cloud-surface active:translate-y-0.5 active:border-b-2 transition-all shrink-0"
                            title="Lihat Detail"
                        >
                            <Eye class="size-4" />
                        </button>
                    </div>
                </div>
            </template>

            <!-- Desktop Header -->
            <template #header>
                <th class="px-4 py-3.5 text-left text-[12px] font-extrabold text-charcoal uppercase tracking-wider w-16">
                    ID
                </th>
                <th class="px-4 py-3.5 text-left text-[12px] font-extrabold text-charcoal uppercase tracking-wider">
                    Customer
                </th>
                <th class="px-4 py-3.5 text-center text-[12px] font-extrabold text-charcoal uppercase tracking-wider w-24">
                    Produk
                </th>
                <th class="px-4 py-3.5 text-right text-[12px] font-extrabold text-charcoal uppercase tracking-wider w-36">
                    Total Bayar
                </th>
                <th class="px-4 py-3.5 text-left text-[12px] font-extrabold text-charcoal uppercase tracking-wider w-32">
                    Metode
                </th>
                <th class="px-4 py-3.5 text-left text-[12px] font-extrabold text-charcoal uppercase tracking-wider w-36">
                    Status
                </th>
                <th class="px-4 py-3.5 text-left text-[12px] font-extrabold text-charcoal uppercase tracking-wider w-32">
                    Tanggal
                </th>
                <th class="px-4 py-3.5 text-center text-[12px] font-extrabold text-charcoal uppercase tracking-wider w-20">
                    Aksi
                </th>
            </template>

            <!-- Desktop Body -->
            <template #body>
                <tr
                    v-for="transaction in transactions.data"
                    :key="transaction.id"
                    class="hover:bg-cloud-surface/50 transition-colors"
                >
                    <!-- ID -->
                    <td class="px-4 py-4">
                        <span class="text-[13px] font-bold text-charcoal/60">#{{ transaction.id }}</span>
                    </td>

                    <!-- Customer -->
                    <td class="px-4 py-4">
                        <div v-if="transaction.customer?.user" class="flex items-center gap-2.5">
                            <UserAvatar :user="transaction.customer.user" size="sm" />
                            <div class="min-w-0">
                                <p class="text-[14px] font-bold text-night-ink truncate">
                                    {{ transaction.customer.user.name }}
                                </p>
                                <p class="text-[12px] text-charcoal/70 truncate">
                                    {{ transaction.customer.user.email }}
                                </p>
                            </div>
                        </div>
                        <span v-else class="text-[13px] text-charcoal/50 italic">-</span>
                    </td>

                    <!-- Produk -->
                    <td class="px-4 py-4 text-center">
                        <span class="inline-flex items-center gap-1 text-[13px] font-bold text-charcoal">
                            <ShoppingCart class="size-3.5 text-charcoal/60" />
                            {{ transaction.details?.length ?? 0 }} item
                        </span>
                    </td>

                    <!-- Total -->
                    <td class="px-4 py-4 text-right">
                        <span class="text-[14px] font-black text-eco-green">
                            {{ formatRupiah(transaction.payable_amount) }}
                        </span>
                    </td>

                    <!-- Metode -->
                    <td class="px-4 py-4">
                        <StatusChip
                            :tone="getPaymentTone(transaction.payment_method)"
                            :label="getPaymentLabel(transaction.payment_method)"
                        />
                    </td>

                    <!-- Status -->
                    <td class="px-4 py-4">
                        <StatusChip
                            :tone="getStatusTone(transaction.status)"
                            :label="getStatusLabel(transaction.status)"
                        />
                    </td>

                    <!-- Tanggal -->
                    <td class="px-4 py-4">
                        <span class="text-[13px] font-semibold text-charcoal">
                            {{ formatDate(transaction.created_at) }}
                        </span>
                    </td>

                    <!-- Aksi -->
                    <td class="px-4 py-4">
                        <div class="flex items-center justify-center gap-1">
                            <button
                                @click="openDetailModal(transaction)"
                                class="p-2 rounded-lg border-2 border-b-[3px] border-border-gray text-charcoal hover:text-night-ink hover:bg-cloud-surface active:translate-y-0.5 active:border-b-2 transition-all"
                                title="Lihat Detail"
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
                    item-label="transaksi"
                />
            </template>
        </AppTable>
    </DashboardLayout>

    <!-- Detail Modal -->
    <ProductTransactionDetailModal
        :show="isDetailModalOpen"
        :transaction="targetTransaction"
        @close="closeDetailModal"
    />
</template>
