<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import DashboardLayout from '@/layouts/DashboardLayout.vue';
import PageHeader from '@/components/Dashboard/PageHeader.vue';
import SearchInput from '@/components/Dashboard/SearchInput.vue';
import AppDropdown from '@/components/Dashboard/AppDropdown.vue';
import AppPagination from '@/components/Dashboard/AppPagination.vue';
import ProductCard, {
    type ProductItem,
} from '@/components/Product/ProductCard.vue';
import { ShoppingBag, RotateCcw, PackageOpen } from 'lucide-vue-next';

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
        sort?: string;
        availability?: string;
    };
}>();

const INDEX_URL = '/customer/products';

const search = ref(props.filters.search ?? '');
const selectedSort = ref(props.filters.sort ?? 'latest');
const selectedAvailability = ref(props.filters.availability ?? 'all');
const isSearching = ref(false);

const sortOptions = [
    { value: 'latest', label: 'Terbaru' },
    { value: 'price_low', label: 'Harga Terendah' },
    { value: 'price_high', label: 'Harga Tertinggi' },
    { value: 'name_asc', label: 'Nama A-Z' },
];

const availabilityOptions = [
    { value: 'all', label: 'Semua Ketersediaan' },
    { value: 'in_stock', label: 'Stok Tersedia' },
    { value: 'out_of_stock', label: 'Stok Habis' },
];

const hasFilter = computed(
    () =>
        search.value.trim() !== '' ||
        selectedSort.value !== 'latest' ||
        selectedAvailability.value !== 'all',
);

function applyFilters() {
    isSearching.value = true;

    router.get(
        INDEX_URL,
        {
            search: search.value.trim() || undefined,
            sort:
                selectedSort.value !== 'latest'
                    ? selectedSort.value
                    : undefined,
            availability:
                selectedAvailability.value !== 'all'
                    ? selectedAvailability.value
                    : undefined,
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
    selectedSort.value = 'latest';
    selectedAvailability.value = 'all';

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
</script>

<template>
    <Head title="Upcycle Store - Produk Daur Ulang ReValue" />

    <DashboardLayout>
        <PageHeader
            title="Upcycle Store"
            subtitle="Karya produk kreatif daur ulang ramah lingkungan. Belanja dengan Rupiah, Poin ReValue, atau kombinasi keduanya."
            badge="Toko Daur Ulang"
        />

        <!-- Toolbar: Search & Filter -->
        <div
            class="mb-6 flex flex-col items-stretch gap-3 lg:flex-row lg:items-center"
        >
            <SearchInput
                v-model="search"
                placeholder="Cari nama atau deskripsi produk..."
                :loading="isSearching"
            />

            <div
                class="flex flex-wrap items-center gap-2.5 sm:flex-nowrap lg:w-auto"
            >
                <AppDropdown
                    v-model="selectedSort"
                    :options="sortOptions"
                    placeholder="Urutan"
                    class="min-w-[150px] flex-1 sm:w-[170px] sm:flex-initial"
                    menu-width="w-full sm:w-[190px]"
                    align="right"
                    @change="applyFilters"
                />

                <AppDropdown
                    v-model="selectedAvailability"
                    :options="availabilityOptions"
                    placeholder="Ketersediaan"
                    class="min-w-[150px] flex-1 sm:w-[180px] sm:flex-initial"
                    menu-width="w-full sm:w-[200px]"
                    align="right"
                    @change="applyFilters"
                />

                <button
                    v-if="hasFilter"
                    type="button"
                    class="h-[44px] shrink-0 cursor-pointer rounded-xl border-2 border-border-gray bg-paper-white p-2.5 text-charcoal transition-colors hover:bg-cloud-surface hover:text-night-ink"
                    title="Reset Filter"
                    @click="resetFilters"
                >
                    <RotateCcw class="size-4" />
                </button>
            </div>
        </div>

        <!-- Result Summary -->
        <div
            class="mb-5 flex items-center justify-between gap-2 text-[13px] font-semibold text-charcoal"
        >
            <div class="flex items-center gap-2">
                <ShoppingBag class="size-4 text-eco-green-dark" />
                <span v-if="products.total > 0">
                    Menampilkan {{ products.from }}–{{ products.to }} dari
                    {{ products.total }} produk
                </span>
                <span v-else>Belum ada produk untuk ditampilkan</span>
            </div>
        </div>

        <!-- Product Grid (3 Kolom, 9 items per page) -->
        <div
            v-if="products.data.length > 0"
            class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3"
        >
            <ProductCard
                v-for="product in products.data"
                :key="product.id"
                :product="product"
            />
        </div>

        <!-- Empty State -->
        <div
            v-else
            class="flex flex-col items-center justify-center rounded-[24px] border-2 border-dashed border-border-gray bg-paper-white p-12 text-center"
        >
            <div
                class="mb-4 flex size-16 items-center justify-center rounded-2xl border-2 border-border-gray bg-cloud-surface"
            >
                <PackageOpen class="size-8 text-charcoal/60" />
            </div>
            <h3 class="mb-1 text-lg font-extrabold text-night-ink">
                Tidak ada produk ditemukan
            </h3>
            <p class="mb-6 max-w-md text-sm leading-relaxed text-charcoal">
                Coba sesuaikan kata kunci pencarian atau reset filter
                ketersediaan untuk melihat produk daur ulang lainnya.
            </p>
            <button
                v-if="hasFilter"
                @click="resetFilters"
                class="hover:bg-cloud-hover cursor-pointer rounded-xl border-2 border-b-[4px] border-border-gray bg-cloud-surface px-5 py-2.5 text-sm font-bold text-night-ink transition-all active:translate-y-0.5 active:border-b-2"
            >
                Reset Filter
            </button>
        </div>

        <!-- Pagination -->
        <div v-if="products.total > products.data.length" class="mt-8">
            <AppPagination :pagination="products" item-label="produk" />
        </div>
    </DashboardLayout>
</template>
