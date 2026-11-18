<script setup lang="ts">
import { ref, watch, computed } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import DashboardLayout from '@/layouts/DashboardLayout.vue';
import StatusChip from '@/components/Dashboard/StatusChip.vue';
import AppDropdown from '@/components/Dashboard/AppDropdown.vue';
import AppTable from '@/components/Dashboard/AppTable.vue';
import AppPagination from '@/components/Dashboard/AppPagination.vue';
import PageHeader from '@/components/Dashboard/PageHeader.vue';
import WasteCategoryModal, {
    type WasteCategoryData,
} from '@/components/Dashboard/WasteCategoryModal.vue';
import WasteCategoryDeleteModal, {
    type WasteCategoryDeleteItem,
} from '@/components/Dashboard/WasteCategoryDeleteModal.vue';
import WasteCategoryRestoreModal, {
    type WasteCategoryRestoreItem,
} from '@/components/Dashboard/WasteCategoryRestoreModal.vue';
import {
    Search,
    Plus,
    RotateCcw,
    CheckCircle2,
    XCircle,
    Coins,
    X,
    Edit3,
    Trash2,
} from 'lucide-vue-next';

export interface WasteCategoryItem {
    id: number;
    name: string;
    description: string | null;
    points_per_kg: number;
    created_at: string;
    updated_at: string;
    deleted_at: string | null;
}

interface PaginationLinks {
    url: string | null;
    label: string;
    active: boolean;
}

interface PaginatedCategories {
    data: WasteCategoryItem[];
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
    categories: PaginatedCategories;
    filters: {
        search?: string;
        status?: string;
        sort_by?: string;
        sort_direction?: string;
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

let searchTimeout: any = null;
const applyFilters = () => {
    router.get(
        '/admin/waste-categories',
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
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        applyFilters();
    }, 350);
});

const onStatusChange = () => {
    applyFilters();
};

const statusDropdownOptions = [
    { value: 'active', label: 'Aktif' },
    { value: 'trashed', label: 'Dinonaktifkan' },
    { value: 'all', label: 'Semua Status' },
];

const resetFilters = () => {
    search.value = '';
    selectedStatus.value = 'active';
    router.get(
        '/admin/waste-categories',
        {},
        {
            preserveState: true,
            preserveScroll: true,
        },
    );
};

// Modals State
// 1. Create / Edit Modal
const isCategoryModalOpen = ref(false);
const selectedCategoryForEdit = ref<WasteCategoryData | null>(null);

const openCreateModal = () => {
    selectedCategoryForEdit.value = null;
    isCategoryModalOpen.value = true;
};

const openEditModal = (cat: WasteCategoryItem) => {
    selectedCategoryForEdit.value = {
        id: cat.id,
        name: cat.name,
        points_per_kg: cat.points_per_kg,
        description: cat.description,
        deleted_at: cat.deleted_at,
    };
    isCategoryModalOpen.value = true;
};

const closeCategoryModal = () => {
    isCategoryModalOpen.value = false;
    selectedCategoryForEdit.value = null;
};

// 2. Delete (Deactivate) Modal
const isDeleteModalOpen = ref(false);
const selectedCategoryForDelete = ref<WasteCategoryDeleteItem | null>(null);

const openDeleteModal = (cat: WasteCategoryItem) => {
    selectedCategoryForDelete.value = {
        id: cat.id,
        name: cat.name,
        points_per_kg: cat.points_per_kg,
    };
    isDeleteModalOpen.value = true;
};

const closeDeleteModal = () => {
    isDeleteModalOpen.value = false;
    selectedCategoryForDelete.value = null;
};

// 3. Restore Modal
const isRestoreModalOpen = ref(false);
const selectedCategoryForRestore = ref<WasteCategoryRestoreItem | null>(null);

const openRestoreModal = (cat: WasteCategoryItem) => {
    selectedCategoryForRestore.value = {
        id: cat.id,
        name: cat.name,
        points_per_kg: cat.points_per_kg,
    };
    isRestoreModalOpen.value = true;
};

const closeRestoreModal = () => {
    isRestoreModalOpen.value = false;
    selectedCategoryForRestore.value = null;
};

// Format currency / number
const formatNumber = (num: number): string => {
    return Number(num).toLocaleString('id-ID');
};
</script>

<template>
    <Head title="Manajemen Kategori Sampah - Admin ReValue" />

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

        <!-- Header Section -->
        <PageHeader
            title="Kategori & Poin Sampah"
            subtitle="Kelola data jenis sampah dan atur nilai konversi perolehan poin per kilogram untuk customer."
            badge="Master Data"
        >
            <template #action>
                <button
                    type="button"
                    @click="openCreateModal"
                    class="inline-flex h-[46px] shrink-0 cursor-pointer items-center justify-center gap-2 rounded-xl border-2 border-b-[4px] border-eco-green-dark bg-eco-green px-5 text-[14px] font-extrabold text-white shadow-2xs transition-all hover:brightness-105 active:translate-y-0.5 active:border-b-2"
                >
                    <Plus class="size-5 stroke-[2.5]" />
                    <span>Tambah Kategori</span>
                </button>
            </template>
        </PageHeader>

        <!-- Toolbar: Search & Status Filter -->
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
                    placeholder="Cari nama jenis sampah atau deskripsi..."
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

        <!-- Table & Pagination Layout (Mirrors User Management) -->
        <AppTable
            :is-empty="categories.data.length === 0"
            :has-filter="!!(search || selectedStatus !== 'active')"
            empty-title="Belum ada kategori sampah"
            empty-description="Tambahkan jenis sampah pertama untuk mulai mengatur perolehan poin per kilogram."
            empty-filter-title="Tidak ada hasil yang cocok"
            empty-filter-description="Coba kata kunci lain atau hapus filter untuk melihat seluruh data jenis sampah."
            empty-action-label="Tambah Kategori"
            min-width="min-w-[760px]"
            @reset-filter="resetFilters"
            @create="openCreateModal"
        >
            <!-- Mobile Cards View (< md) -->
            <template #mobile>
                <div
                    v-for="cat in categories.data"
                    :key="`m-${cat.id}`"
                    class="flex flex-col gap-3 p-4 transition-colors hover:bg-cloud-surface/30"
                    :class="{
                        'bg-cloud-surface/30 opacity-70': cat.deleted_at,
                    }"
                >
                    <!-- Header: Illustration + Name + Status -->
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex min-w-0 items-center gap-3">
                            <div class="min-w-0">
                                <h4
                                    class="truncate text-[15px] leading-tight font-extrabold text-night-ink"
                                >
                                    {{ cat.name }}
                                </h4>
                                <p
                                    class="mt-0.5 truncate text-[12px] font-medium text-charcoal"
                                >
                                    ID: #{{ cat.id }}
                                </p>
                            </div>
                        </div>
                        <StatusChip
                            :label="cat.deleted_at ? 'Nonaktif' : 'Aktif'"
                            :tone="cat.deleted_at ? 'Neutral' : 'Green'"
                        />
                    </div>

                    <!-- Meta Box: Description & Points per Kg -->
                    <div
                        class="flex items-center justify-between rounded-xl border border-border-gray/50 bg-cloud-surface/50 px-3 py-2 text-[12px]"
                    >
                        <span class="mr-2 truncate font-semibold text-charcoal">
                            {{
                                cat.description ||
                                'Tidak ada deskripsi tambahan'
                            }}
                        </span>
                        <div
                            class="inline-flex shrink-0 items-center gap-1 font-extrabold text-night-ink"
                        >
                            <span
                                >{{
                                    formatNumber(cat.points_per_kg)
                                }}
                                Pts/kg</span
                            >
                        </div>
                    </div>

                    <!-- Actions -->
                    <div
                        class="flex items-center justify-end gap-2 border-t border-border-gray/40 pt-1"
                    >
                        <button
                            type="button"
                            @click="openEditModal(cat)"
                            class="cursor-pointer rounded-xl bg-cloud-surface px-3 py-1.5 text-[12px] font-extrabold text-night-ink transition-colors hover:bg-sprout-tint hover:text-eco-green-dark"
                        >
                            Ubah
                        </button>
                        <button
                            v-if="!cat.deleted_at"
                            type="button"
                            @click="openDeleteModal(cat)"
                            class="cursor-pointer rounded-xl bg-red-50 px-3 py-1.5 text-[12px] font-extrabold text-red-600 transition-colors hover:bg-red-100"
                        >
                            Hapus
                        </button>
                        <button
                            v-else
                            type="button"
                            @click="openRestoreModal(cat)"
                            class="cursor-pointer rounded-xl bg-sprout-tint px-3 py-1.5 text-[12px] font-extrabold text-eco-green-dark transition-colors hover:brightness-105"
                        >
                            Pulihkan
                        </button>
                    </div>
                </div>
            </template>

            <!-- Desktop Table Header -->
            <template #header>
                <th class="px-6 py-3.5">Jenis Sampah</th>
                <th class="px-6 py-3.5">Nilai Poin</th>
                <th class="px-6 py-3.5">Deskripsi & Kriteria</th>
                <th class="px-6 py-3.5">Status</th>
                <th class="px-6 py-3.5 text-right">Aksi</th>
            </template>

            <!-- Desktop Table Body -->
            <template #body>
                <tr
                    v-for="cat in categories.data"
                    :key="cat.id"
                    class="transition-colors hover:bg-cloud-surface/50"
                    :class="{
                        'bg-cloud-surface/20 opacity-60': cat.deleted_at,
                    }"
                >
                    <!-- Nama & Ilustrasi -->
                    <td class="px-6 py-3.5">
                        <div class="flex items-center gap-3">
                            <div>
                                <p
                                    class="text-[14px] leading-tight font-bold text-night-ink"
                                >
                                    {{ cat.name }}
                                </p>
                                <p
                                    class="mt-0.5 text-[12px] font-medium text-charcoal/70"
                                >
                                    ID: #{{ cat.id }}
                                </p>
                            </div>
                        </div>
                    </td>

                    <!-- Nilai Poin -->
                    <td class="px-6 py-3.5">
                        <div
                            class="inline-flex items-center gap-1.5 rounded-lg border border-sunlight-gold/30 bg-gold-tint px-2.5 py-1 text-[13px] font-extrabold whitespace-nowrap text-night-ink shadow-2xs"
                        >
                            <Coins
                                class="size-3.5 shrink-0 text-sunlight-gold"
                            />
                            <span>{{ formatNumber(cat.points_per_kg) }}</span>
                            <span class="text-[11px] font-bold text-charcoal"
                                >Poin / kg</span
                            >
                        </div>
                    </td>

                    <!-- Deskripsi & Kriteria -->
                    <td class="max-w-[340px] px-6 py-3.5">
                        <p
                            v-if="cat.description"
                            class="line-clamp-2 text-[13px] leading-relaxed font-medium text-charcoal"
                        >
                            {{ cat.description }}
                        </p>
                        <span
                            v-else
                            class="text-[12px] text-charcoal/50 italic"
                        >
                            Tidak ada deskripsi tambahan
                        </span>
                    </td>

                    <!-- Status -->
                    <td class="px-6 py-3.5">
                        <StatusChip
                            :label="cat.deleted_at ? 'Nonaktif' : 'Aktif'"
                            :tone="cat.deleted_at ? 'Neutral' : 'Green'"
                        />
                    </td>

                    <!-- Aksi (Konsisten dengan Manajemen Artikel) -->
                    <td class="px-6 py-3.5 text-right whitespace-nowrap">
                        <div class="inline-flex items-center justify-end gap-1">
                            <!-- Ubah (membuka popup edit) -->
                            <button
                                type="button"
                                @click="openEditModal(cat)"
                                class="cursor-pointer rounded-lg p-2 text-charcoal transition-colors hover:bg-sprout-tint/60 hover:text-eco-green-dark"
                                title="Ubah Kategori Sampah"
                            >
                                <Edit3 class="size-4" />
                            </button>

                            <!-- Hapus (jika aktif) -->
                            <button
                                v-if="!cat.deleted_at"
                                type="button"
                                @click="openDeleteModal(cat)"
                                class="cursor-pointer rounded-lg p-2 text-charcoal transition-colors hover:bg-red-50 hover:text-red-600"
                                title="Nonaktifkan Kategori Sampah"
                            >
                                <Trash2 class="size-4" />
                            </button>

                            <!-- Pulihkan (jika soft-deleted) -->
                            <button
                                v-else
                                type="button"
                                @click="openRestoreModal(cat)"
                                class="cursor-pointer rounded-lg p-2 text-eco-green-dark transition-colors hover:bg-sprout-tint"
                                title="Pulihkan Kategori Sampah"
                            >
                                <RotateCcw class="size-4" />
                            </button>
                        </div>
                    </td>
                </tr>
            </template>

            <!-- Pagination Bar -->
            <template #pagination>
                <AppPagination
                    :pagination="categories"
                    item-label="kategori sampah"
                />
            </template>
        </AppTable>

        <!-- Modals -->
        <WasteCategoryModal
            :show="isCategoryModalOpen"
            :category="selectedCategoryForEdit"
            @close="closeCategoryModal"
        />

        <WasteCategoryDeleteModal
            :show="isDeleteModalOpen"
            :category="selectedCategoryForDelete"
            @close="closeDeleteModal"
        />

        <WasteCategoryRestoreModal
            :show="isRestoreModalOpen"
            :category="selectedCategoryForRestore"
            @close="closeRestoreModal"
        />
    </DashboardLayout>
</template>
