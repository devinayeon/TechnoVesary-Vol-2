<script setup lang="ts">
import { ref, watch, computed } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import DashboardLayout from '@/layouts/DashboardLayout.vue';
import PageHeader from '@/components/Dashboard/PageHeader.vue';
import StatusChip from '@/components/Dashboard/StatusChip.vue';
import AppDropdown from '@/components/Dashboard/AppDropdown.vue';
import AppTable from '@/components/Dashboard/AppTable.vue';
import AppPagination from '@/components/Dashboard/AppPagination.vue';
import ProductDeleteModal, {
    type ProductDeleteItem,
} from '@/components/Dashboard/ProductDeleteModal.vue';
import ProductRestoreModal, {
    type ProductRestoreItem,
} from '@/components/Dashboard/ProductRestoreModal.vue';
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
} from 'lucide-vue-next';

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
const selectedStatus = ref(props.filters.status || 'active');

let searchTimeout: ReturnType<typeof setTimeout> | null = null;

const applyFilters = () => {
    router.get(
        '/admin/products',
        {
            search: search.value || undefined,
            status: selectedStatus.value || undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
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
    { value: 'active', label: 'Aktif' },
    { value: 'trashed', label: 'Dinonaktifkan' },
    { value: 'all', label: 'Semua Status' },
];

const resetFilters = () => {
    search.value = '';
    selectedStatus.value = 'active';
    router.get(
        '/admin/products',
        {},
        {
            preserveState: true,
            preserveScroll: true,
        },
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
const formatRupiah = (val: number) => 'Rp ' + val.toLocaleString('id-ID');

const formatDate = (dateStr: string) => {
    return new Date(dateStr).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
};
</script>

<template>
    <Head title="Manajemen Produk - Upcycle Store" />

    <DashboardLayout>
        <!-- Flash Message Banners -->
        <div
            v-if="flashSuccess && showSuccessFlash"
            class="mb-6 flex items-center justify-between gap-3 rounded-2xl border-2 border-eco-green/40 bg-sprout-tint p-4 text-[14px] font-extrabold text-eco-green-dark shadow-2xs"
        >
            <div class="flex min-w-0 items-center gap-3">
                <CheckCircle2 class="size-5 shrink-0 text-eco-green-dark" />
                <span class="truncate">{{ flashSuccess }}</span>
            </div>
            <button
                type="button"
                @click="showSuccessFlash = false"
                class="shrink-0 cursor-pointer rounded-lg p-1 text-eco-green-dark transition-colors hover:bg-eco-green/10"
                aria-label="Tutup notifikasi"
            >
                <X class="size-4" />
            </button>
        </div>

        <div
            v-if="flashError && showErrorFlash"
            class="mb-6 flex items-center justify-between gap-3 rounded-2xl border-2 border-red-200 bg-red-50 p-4 text-[14px] font-extrabold text-red-700 shadow-2xs"
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

        <!-- Header + Action Button -->
        <div
            class="mb-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-start"
        >
            <PageHeader
                title="Manajemen Produk"
                subtitle="Kelola produk daur ulang dan ramah lingkungan untuk dijual kepada customer ReValue."
                badge="Upcycle Store"
                class="mb-0"
            />

            <div
                class="flex shrink-0 flex-wrap items-center gap-2.5 self-start sm:self-auto"
            >
                <Link
                    href="/admin/products/create"
                    class="inline-flex h-[46px] cursor-pointer items-center justify-center gap-2 rounded-xl border-2 border-b-[4px] border-eco-green-dark bg-eco-green px-5 text-[14px] font-extrabold text-white shadow-xs transition-all hover:brightness-105 active:translate-y-0.5 active:border-b-2"
                >
                    <Plus class="size-4.5" />
                    <span>Tambah Produk</span>
                </Link>
            </div>
        </div>

        <!-- Toolbar: Search & Filter Dropdowns -->
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
                    placeholder="Cari nama atau deskripsi produk..."
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
                <!-- Status Filter -->
                <AppDropdown
                    v-model="selectedStatus"
                    :options="statusOptions"
                    placeholder="Semua Status"
                    class="min-w-[140px] flex-1 sm:w-[170px] sm:flex-initial"
                    menu-width="w-full sm:w-[180px]"
                    align="right"
                    @change="onFilterChange"
                />

                <!-- Reset Filter Button -->
                <button
                    v-if="search || selectedStatus !== 'active'"
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
            :is-empty="products.data.length === 0"
            :has-filter="!!(search || selectedStatus !== 'active')"
            empty-title="Belum ada produk di Upcycle Store"
            empty-description="Mulai tambahkan produk daur ulang ramah lingkungan pertama untuk dijual kepada customer ReValue."
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
                    class="flex flex-col gap-3 p-4 transition-colors hover:bg-cloud-surface/30"
                >
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-2">
                            <span
                                class="inline-flex items-center rounded-lg border px-2.5 py-0.5 text-[12px] font-bold"
                                :class="
                                    product.stock > 0
                                        ? 'border-eco-green/30 bg-sprout-tint text-eco-green-dark'
                                        : 'border-red-200 bg-red-50 text-red-700'
                                "
                            >
                                {{
                                    product.stock > 0
                                        ? `Stok: ${product.stock} pcs`
                                        : 'Stok Habis'
                                }}
                            </span>
                            <StatusChip
                                v-if="product.deleted_at"
                                label="Nonaktif"
                                tone="Neutral"
                            />
                            <StatusChip v-else label="Aktif" tone="Green" />
                        </div>
                        <span class="text-[12px] font-bold text-charcoal/70">
                            {{ formatDate(product.created_at) }}
                        </span>
                    </div>

                    <div class="flex items-start gap-3.5">
                        <div
                            class="flex size-16 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-border-gray bg-cloud-surface shadow-2xs"
                        >
                            <img
                                v-if="product.photo_url"
                                :src="product.photo_url"
                                :alt="product.name"
                                class="h-full w-full object-cover"
                            />
                            <ShoppingBag
                                v-else
                                class="size-6 text-charcoal/50"
                            />
                        </div>
                        <div class="min-w-0 flex-1">
                            <h4
                                class="line-clamp-2 text-[15px] leading-tight font-extrabold text-night-ink"
                            >
                                {{ product.name }}
                            </h4>
                            <p
                                class="mt-1 text-[13px] font-extrabold text-eco-green-dark"
                            >
                                {{ formatRupiah(product.price_rupiah) }}
                            </p>
                            <p
                                v-if="product.description"
                                class="mt-1 line-clamp-2 text-[12px] font-medium text-charcoal/80"
                            >
                                {{ product.description }}
                            </p>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div
                        class="flex items-center justify-end gap-2 border-t border-border-gray/40 pt-2"
                    >
                        <template v-if="!product.deleted_at">
                            <Link
                                :href="`/admin/products/${product.id}/edit`"
                                class="cursor-pointer rounded-xl bg-cloud-surface px-3 py-1.5 text-[12px] font-extrabold text-night-ink transition-colors hover:bg-sprout-tint hover:text-eco-green-dark"
                            >
                                Edit
                            </Link>
                            <button
                                type="button"
                                @click="openDeleteModal(product)"
                                class="cursor-pointer rounded-xl bg-red-50 px-3 py-1.5 text-[12px] font-extrabold text-red-600 transition-colors hover:bg-red-100"
                            >
                                Nonaktifkan
                            </button>
                        </template>

                        <button
                            v-else
                            type="button"
                            @click="openRestoreModal(product)"
                            class="cursor-pointer rounded-xl bg-sprout-tint px-3 py-1.5 text-[12px] font-extrabold text-eco-green-dark transition-colors hover:brightness-95"
                        >
                            Pulihkan
                        </button>
                    </div>
                </div>
            </template>

            <!-- Desktop Table Header -->
            <template #header>
                <th class="w-20 px-6 py-3.5">Foto</th>
                <th class="px-6 py-3.5">Nama & Deskripsi</th>
                <th class="px-6 py-3.5">Harga (Rp)</th>
                <th class="px-6 py-3.5">Stok</th>
                <th class="px-6 py-3.5">Tanggal</th>
                <th class="px-6 py-3.5">Status</th>
                <th class="px-6 py-3.5 text-right">Aksi</th>
            </template>

            <!-- Desktop Table Body -->
            <template #body>
                <tr
                    v-for="product in products.data"
                    :key="product.id"
                    class="transition-colors hover:bg-cloud-surface/50"
                >
                    <!-- Foto -->
                    <td class="px-6 py-3.5">
                        <div
                            class="flex size-14 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-border-gray bg-cloud-surface shadow-2xs"
                        >
                            <img
                                v-if="product.photo_url"
                                :src="product.photo_url"
                                :alt="product.name"
                                class="h-full w-full object-cover"
                            />
                            <ShoppingBag
                                v-else
                                class="size-5 text-charcoal/50"
                            />
                        </div>
                    </td>

                    <!-- Nama & Deskripsi -->
                    <td class="max-w-[340px] px-6 py-3.5">
                        <h4
                            class="line-clamp-1 text-[14px] leading-tight font-extrabold text-night-ink"
                        >
                            {{ product.name }}
                        </h4>
                        <p
                            v-if="product.description"
                            class="mt-1 line-clamp-1 text-[12px] font-medium text-charcoal/70"
                        >
                            {{ product.description }}
                        </p>
                    </td>

                    <!-- Harga -->
                    <td class="px-6 py-3.5 whitespace-nowrap">
                        <span class="text-[14px] font-extrabold text-night-ink">
                            {{ formatRupiah(product.price_rupiah) }}
                        </span>
                    </td>

                    <!-- Stok -->
                    <td class="px-6 py-3.5 whitespace-nowrap">
                        <span
                            class="inline-flex items-center rounded-lg border px-2.5 py-1 text-[12px] font-bold"
                            :class="
                                product.stock > 0
                                    ? 'border-eco-green/30 bg-sprout-tint text-eco-green-dark'
                                    : 'border-red-200 bg-red-50 text-red-700'
                            "
                        >
                            {{
                                product.stock > 0
                                    ? `${product.stock} pcs`
                                    : 'Habis'
                            }}
                        </span>
                    </td>

                    <!-- Tanggal -->
                    <td
                        class="px-6 py-3.5 text-[13px] font-bold whitespace-nowrap text-charcoal/70"
                    >
                        {{ formatDate(product.created_at) }}
                    </td>

                    <!-- Status -->
                    <td class="px-6 py-3.5 whitespace-nowrap">
                        <StatusChip
                            :label="product.deleted_at ? 'Nonaktif' : 'Aktif'"
                            :tone="product.deleted_at ? 'Neutral' : 'Green'"
                        />
                    </td>

                    <!-- Aksi -->
                    <td class="px-6 py-3.5 text-right whitespace-nowrap">
                        <div class="inline-flex items-center justify-end gap-1">
                            <template v-if="!product.deleted_at">
                                <!-- Edit Button -->
                                <Link
                                    :href="`/admin/products/${product.id}/edit`"
                                    class="cursor-pointer rounded-lg p-2 text-charcoal transition-colors hover:bg-sprout-tint/60 hover:text-eco-green-dark"
                                    title="Edit Produk"
                                >
                                    <Edit3 class="size-4" />
                                </Link>

                                <!-- Delete Button -->
                                <button
                                    type="button"
                                    @click="openDeleteModal(product)"
                                    class="cursor-pointer rounded-lg p-2 text-charcoal transition-colors hover:bg-red-50 hover:text-red-600"
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
                                    class="cursor-pointer rounded-lg p-2 text-eco-green-dark transition-colors hover:bg-sprout-tint"
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
                <AppPagination :pagination="products" item-label="produk" />
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
