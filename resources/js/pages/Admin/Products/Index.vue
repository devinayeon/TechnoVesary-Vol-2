<script setup lang="ts">
import { ref, watch, computed } from "vue";
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import DashboardLayout from "@/layouts/DashboardLayout.vue";
import PageHeader from "@/components/Dashboard/PageHeader.vue";
import StatusChip from "@/components/Dashboard/StatusChip.vue";
import AppDropdown from "@/components/Dashboard/AppDropdown.vue";
import AppTable from "@/components/Dashboard/AppTable.vue";
import AppPagination from "@/components/Dashboard/AppPagination.vue";
import ProductDeleteModal, {
    type ProductDeleteItem,
} from "@/components/Dashboard/ProductDeleteModal.vue";
import ProductRestoreModal, {
    type ProductRestoreItem,
} from "@/components/Dashboard/ProductRestoreModal.vue";
import {
    Search,
    Plus,
    RotateCcw,
    CheckCircle2,
    XCircle,
    X,
    ShoppingBag,
    Edit3,
    Trash2,
} from "lucide-vue-next";

export interface ProductItem {
    id: number;
    admin_id: number;
    name: string;
    description: string | null;
    price_rupiah: number;
    price_points: number;
    stock: number;
    photo: string | null;
    photo_url?: string | null;
    created_at: string;
    updated_at: string;
    deleted_at: string | null;
    admin?: {
        id: number;
        user?: {
            id: number;
            name: string;
            email: string;
        } | null;
    } | null;
}

interface PaginationLinks {
    url: string | null;
    label: string;
    active: boolean;
}

interface PaginatedProducts {
    data: ProductItem[];
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
    products: PaginatedProducts;
    filters: {
        search?: string;
        status?: string;
    };
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

// Search & Filter state
const search = ref(props.filters.search || "");
const selectedStatus = ref(props.filters.status || "active");

let searchTimeout: ReturnType<typeof setTimeout> | null = null;

const applyFilters = () => {
    router.get(
        "/admin/products",
        {
            search: search.value || undefined,
            status: selectedStatus.value || undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        }
    );
};

watch(search, () => {
    if (searchTimeout) clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        applyFilters();
    }, 350);
});

const onFilterChange = () => {
    applyFilters();
};

const statusOptions = [
    { value: "active", label: "Aktif" },
    { value: "trashed", label: "Dinonaktifkan" },
    { value: "all", label: "Semua Status" },
];

const resetFilters = () => {
    search.value = "";
    selectedStatus.value = "active";
    router.get(
        "/admin/products",
        {},
        {
            preserveState: true,
            preserveScroll: true,
        }
    );
};

// Modal State: Soft Delete
const isDeleteModalOpen = ref(false);
const targetProductToDelete = ref<ProductDeleteItem | null>(null);

const openDeleteModal = (product: ProductItem) => {
    targetProductToDelete.value = {
        id: product.id,
        name: product.name,
        photo_url: product.photo_url,
        price_rupiah: product.price_rupiah,
    };
    isDeleteModalOpen.value = true;
};

const closeDeleteModal = () => {
    isDeleteModalOpen.value = false;
    targetProductToDelete.value = null;
};

// Modal State: Restore
const isRestoreModalOpen = ref(false);
const targetProductToRestore = ref<ProductRestoreItem | null>(null);

const openRestoreModal = (product: ProductItem) => {
    targetProductToRestore.value = {
        id: product.id,
        name: product.name,
        photo_url: product.photo_url,
        price_rupiah: product.price_rupiah,
    };
    isRestoreModalOpen.value = true;
};

const closeRestoreModal = () => {
    isRestoreModalOpen.value = false;
    targetProductToRestore.value = null;
};

// Helpers
const formatRupiah = (val: number) => "Rp " + val.toLocaleString("id-ID");

const formatDate = (dateStr: string) => {
    return new Date(dateStr).toLocaleDateString("id-ID", {
        day: "numeric",
        month: "short",
        year: "numeric",
    });
};
</script>

<template>
    <Head title="Manajemen Produk - Upcycle Store" />

    <DashboardLayout>
        <!-- Flash Message Banners -->
        <div
            v-if="flashSuccess && showSuccessFlash"
            class="mb-6 p-4 rounded-2xl bg-sprout-tint border-2 border-eco-green/40 flex items-center justify-between gap-3 text-eco-green-dark font-extrabold text-[14px] shadow-2xs"
        >
            <div class="flex items-center gap-3 min-w-0">
                <CheckCircle2 class="size-5 text-eco-green-dark shrink-0" />
                <span class="truncate">{{ flashSuccess }}</span>
            </div>
            <button
                type="button"
                @click="showSuccessFlash = false"
                class="p-1 rounded-lg hover:bg-eco-green/10 text-eco-green-dark transition-colors cursor-pointer shrink-0"
                aria-label="Tutup notifikasi"
            >
                <X class="size-4" />
            </button>
        </div>

        <div
            v-if="flashError && showErrorFlash"
            class="mb-6 p-4 rounded-2xl bg-red-50 border-2 border-red-200 flex items-center justify-between gap-3 text-red-700 font-extrabold text-[14px] shadow-2xs"
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

        <!-- Header + Action Button -->
        <div
            class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 mb-6"
        >
            <PageHeader
                title="Manajemen Produk"
                subtitle="Kelola produk daur ulang dan ramah lingkungan untuk dijual kepada nasabah ReValue."
                badge="Upcycle Store"
                class="mb-0"
            />

            <div class="flex flex-wrap items-center gap-2.5 shrink-0 self-start sm:self-auto">
                <Link
                    href="/admin/products/create"
                    class="inline-flex items-center justify-center gap-2 px-5 h-[46px] rounded-xl border-2 border-b-[4px] border-eco-green-dark bg-eco-green text-white font-extrabold text-[14px] hover:brightness-105 active:translate-y-0.5 active:border-b-2 transition-all cursor-pointer shadow-xs"
                >
                    <Plus class="size-4.5" />
                    <span>Tambah Produk</span>
                </Link>
            </div>
        </div>

        <!-- Toolbar: Search & Filter Dropdowns -->
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
                    placeholder="Cari nama atau deskripsi produk..."
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
                <!-- Status Filter -->
                <AppDropdown
                    v-model="selectedStatus"
                    :options="statusOptions"
                    placeholder="Semua Status"
                    class="flex-1 sm:flex-initial min-w-[140px] sm:w-[170px]"
                    menu-width="w-full sm:w-[180px]"
                    align="right"
                    @change="onFilterChange"
                />

                <!-- Reset Filter Button -->
                <button
                    v-if="search || selectedStatus !== 'active'"
                    type="button"
                    @click="resetFilters"
                    class="p-2.5 h-[44px] rounded-xl border-2 border-border-gray bg-paper-white text-charcoal hover:text-night-ink hover:bg-cloud-surface transition-colors cursor-pointer shrink-0"
                    title="Reset Filter"
                >
                    <RotateCcw class="size-4" />
                </button>
            </div>
        </div>

        <!-- Table & Pagination via AppTable -->
        <AppTable
            :is-empty="products.data.length === 0"
            :has-filter="!!(search || selectedStatus !== 'active')"
            empty-title="Belum ada produk di Upcycle Store"
            empty-description="Mulai tambahkan produk daur ulang ramah lingkungan pertama untuk dijual kepada nasabah ReValue."
            empty-filter-title="Tidak ada produk yang cocok"
            empty-filter-description="Coba ubah kata kunci pencarian atau sesuaikan filter status untuk melihat produk lainnya."
            empty-action-label="Tambah Produk Baru"
            min-width="min-w-[850px]"
            @create="router.visit('/admin/products/create')"
            @reset-filter="resetFilters"
        >
            <!-- Mobile Cards View (< md) -->
            <template #mobile>
                <div
                    v-for="product in products.data"
                    :key="`m-${product.id}`"
                    class="p-4 flex flex-col gap-3 hover:bg-cloud-surface/30 transition-colors"
                >
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-2">
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-[12px] font-bold border"
                                :class="
                                    product.stock > 0
                                        ? 'bg-sprout-tint border-eco-green/30 text-eco-green-dark'
                                        : 'bg-red-50 border-red-200 text-red-700'
                                "
                            >
                                {{ product.stock > 0 ? `Stok: ${product.stock} pcs` : 'Stok Habis' }}
                            </span>
                            <StatusChip
                                v-if="product.deleted_at"
                                label="Nonaktif"
                                tone="Neutral"
                            />
                            <StatusChip
                                v-else
                                label="Aktif"
                                tone="Green"
                            />
                        </div>
                        <span class="text-[12px] font-bold text-charcoal/70">
                            {{ formatDate(product.created_at) }}
                        </span>
                    </div>

                    <div class="flex items-start gap-3.5">
                        <div
                            class="size-16 rounded-xl bg-cloud-surface border border-border-gray overflow-hidden shrink-0 flex items-center justify-center shadow-2xs"
                        >
                            <img
                                v-if="product.photo_url"
                                :src="product.photo_url"
                                :alt="product.name"
                                class="w-full h-full object-cover"
                            />
                            <ShoppingBag v-else class="size-6 text-charcoal/50" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <h4
                                class="font-extrabold text-[15px] text-night-ink leading-tight line-clamp-2"
                            >
                                {{ product.name }}
                            </h4>
                            <p
                                class="text-[13px] font-extrabold text-eco-green-dark mt-1"
                            >
                                {{ formatRupiah(product.price_rupiah) }}
                            </p>
                            <p
                                v-if="product.description"
                                class="text-[12px] font-medium text-charcoal/80 line-clamp-2 mt-1"
                            >
                                {{ product.description }}
                            </p>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div
                        class="flex items-center justify-end gap-2 pt-2 border-t border-border-gray/40"
                    >
                        <template v-if="!product.deleted_at">
                            <Link
                                :href="`/admin/products/${product.id}/edit`"
                                class="px-3 py-1.5 text-[12px] font-extrabold text-night-ink bg-cloud-surface hover:bg-sprout-tint hover:text-eco-green-dark rounded-xl transition-colors cursor-pointer"
                            >
                                Edit
                            </Link>
                            <button
                                type="button"
                                @click="openDeleteModal(product)"
                                class="px-3 py-1.5 text-[12px] font-extrabold text-red-600 bg-red-50 hover:bg-red-100 rounded-xl transition-colors cursor-pointer"
                            >
                                Nonaktifkan
                            </button>
                        </template>

                        <button
                            v-else
                            type="button"
                            @click="openRestoreModal(product)"
                            class="px-3 py-1.5 text-[12px] font-extrabold text-eco-green-dark bg-sprout-tint hover:brightness-95 rounded-xl transition-colors cursor-pointer"
                        >
                            Pulihkan
                        </button>
                    </div>
                </div>
            </template>

            <!-- Desktop Table Header -->
            <template #header>
                <th class="py-3.5 px-6 w-20">Foto</th>
                <th class="py-3.5 px-6">Nama & Deskripsi</th>
                <th class="py-3.5 px-6">Harga (Rp)</th>
                <th class="py-3.5 px-6">Stok</th>
                <th class="py-3.5 px-6">Tanggal</th>
                <th class="py-3.5 px-6">Status</th>
                <th class="py-3.5 px-6 text-right">Aksi</th>
            </template>

            <!-- Desktop Table Body -->
            <template #body>
                <tr
                    v-for="product in products.data"
                    :key="product.id"
                    class="hover:bg-cloud-surface/50 transition-colors"
                >
                    <!-- Foto -->
                    <td class="py-3.5 px-6">
                        <div
                            class="size-14 rounded-xl bg-cloud-surface border border-border-gray overflow-hidden flex items-center justify-center shrink-0 shadow-2xs"
                        >
                            <img
                                v-if="product.photo_url"
                                :src="product.photo_url"
                                :alt="product.name"
                                class="w-full h-full object-cover"
                            />
                            <ShoppingBag v-else class="size-5 text-charcoal/50" />
                        </div>
                    </td>

                    <!-- Nama & Deskripsi -->
                    <td class="py-3.5 px-6 max-w-[340px]">
                        <h4
                            class="font-extrabold text-[14px] text-night-ink leading-tight line-clamp-1"
                        >
                            {{ product.name }}
                        </h4>
                        <p
                            v-if="product.description"
                            class="text-[12px] font-medium text-charcoal/70 line-clamp-1 mt-1"
                        >
                            {{ product.description }}
                        </p>
                    </td>

                    <!-- Harga -->
                    <td class="py-3.5 px-6 whitespace-nowrap">
                        <span class="font-extrabold text-[14px] text-night-ink">
                            {{ formatRupiah(product.price_rupiah) }}
                        </span>
                    </td>

                    <!-- Stok -->
                    <td class="py-3.5 px-6 whitespace-nowrap">
                        <span
                            class="inline-flex items-center px-2.5 py-1 rounded-lg text-[12px] font-bold border"
                            :class="
                                product.stock > 0
                                    ? 'bg-sprout-tint border-eco-green/30 text-eco-green-dark'
                                    : 'bg-red-50 border-red-200 text-red-700'
                            "
                        >
                            {{ product.stock > 0 ? `${product.stock} pcs` : 'Habis' }}
                        </span>
                    </td>

                    <!-- Tanggal -->
                    <td class="py-3.5 px-6 whitespace-nowrap text-[13px] font-bold text-charcoal/70">
                        {{ formatDate(product.created_at) }}
                    </td>

                    <!-- Status -->
                    <td class="py-3.5 px-6 whitespace-nowrap">
                        <StatusChip
                            :label="product.deleted_at ? 'Nonaktif' : 'Aktif'"
                            :tone="product.deleted_at ? 'Neutral' : 'Green'"
                        />
                    </td>

                    <!-- Aksi -->
                    <td class="py-3.5 px-6 text-right whitespace-nowrap">
                        <div class="inline-flex items-center justify-end gap-1">
                            <template v-if="!product.deleted_at">
                                <!-- Edit Button -->
                                <Link
                                    :href="`/admin/products/${product.id}/edit`"
                                    class="p-2 rounded-lg text-charcoal hover:text-eco-green-dark hover:bg-sprout-tint/60 transition-colors cursor-pointer"
                                    title="Edit Produk"
                                >
                                    <Edit3 class="size-4" />
                                </Link>

                                <!-- Delete Button -->
                                <button
                                    type="button"
                                    @click="openDeleteModal(product)"
                                    class="p-2 rounded-lg text-charcoal hover:text-red-600 hover:bg-red-50 transition-colors cursor-pointer"
                                    title="Nonaktifkan Produk"
                                >
                                    <Trash2 class="size-4" />
                                </button>
                            </template>

                            <template v-else>
                                <!-- Restore Button -->
                                <button
                                    type="button"
                                    @click="openRestoreModal(product)"
                                    class="p-2 rounded-lg text-eco-green-dark hover:bg-sprout-tint transition-colors cursor-pointer"
                                    title="Pulihkan Produk"
                                >
                                    <RotateCcw class="size-4" />
                                </button>
                            </template>
                        </div>
                    </td>
                </tr>
            </template>

            <!-- Pagination Bar -->
            <template #pagination>
                <AppPagination
                    :pagination="products"
                    item-label="produk"
                />
            </template>
        </AppTable>

        <!-- Modals -->
        <ProductDeleteModal
            :show="isDeleteModalOpen"
            :product="targetProductToDelete"
            @close="closeDeleteModal"
        />
        <ProductRestoreModal
            :show="isRestoreModalOpen"
            :product="targetProductToRestore"
            @close="closeRestoreModal"
        />
    </DashboardLayout>
</template>
