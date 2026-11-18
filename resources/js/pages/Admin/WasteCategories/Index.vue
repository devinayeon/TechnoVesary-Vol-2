<script setup lang="ts">
import { ref, watch, computed } from "vue";
import { Head, router, usePage } from "@inertiajs/vue3";
import DashboardLayout from "@/layouts/DashboardLayout.vue";
import StatusChip from "@/components/Dashboard/StatusChip.vue";
import AppDropdown from "@/components/Dashboard/AppDropdown.vue";
import AppTable from "@/components/Dashboard/AppTable.vue";
import AppPagination from "@/components/Dashboard/AppPagination.vue";
import PageHeader from "@/components/Dashboard/PageHeader.vue";
import WasteCategoryModal, {
    type WasteCategoryData,
} from "@/components/Dashboard/WasteCategoryModal.vue";
import WasteCategoryDeleteModal, {
    type WasteCategoryDeleteItem,
} from "@/components/Dashboard/WasteCategoryDeleteModal.vue";
import WasteCategoryRestoreModal, {
    type WasteCategoryRestoreItem,
} from "@/components/Dashboard/WasteCategoryRestoreModal.vue";
import {
    Search,
    Plus,
    RotateCcw,
    CheckCircle2,
    XCircle,
    Coins,
    X,
} from "lucide-vue-next";

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
const search = ref(props.filters.search || "");
const selectedStatus = ref(props.filters.status || "active");

let searchTimeout: any = null;
const applyFilters = () => {
    router.get(
        "/admin/waste-categories",
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
    { value: "active", label: "Aktif" },
    { value: "trashed", label: "Dinonaktifkan" },
    { value: "all", label: "Semua Status" },
];

const resetFilters = () => {
    search.value = "";
    selectedStatus.value = "active";
    router.get(
        "/admin/waste-categories",
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
    return Number(num).toLocaleString("id-ID");
};
</script>

<template>
    <Head title="Manajemen Kategori Sampah - Admin ReValue" />

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

        <!-- Header Section -->
        <PageHeader
            title="Kategori & Poin Sampah"
            subtitle="Kelola data jenis sampah dan atur nilai konversi perolehan poin per kilogram untuk nasabah."
            badge="Master Data"
        >
            <template #action>
                <button
                    type="button"
                    @click="openCreateModal"
                    class="inline-flex items-center justify-center gap-2 px-5 h-[46px] rounded-xl border-2 border-b-[4px] border-eco-green-dark bg-eco-green text-white font-extrabold text-[14px] hover:brightness-105 active:translate-y-0.5 active:border-b-2 transition-all cursor-pointer shadow-2xs shrink-0"
                >
                    <Plus class="size-5 stroke-[2.5]" />
                    <span>Tambah Kategori</span>
                </button>
            </template>
        </PageHeader>

        <!-- Toolbar: Search & Status Filter -->
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
                    placeholder="Cari nama jenis sampah atau deskripsi..."
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
                    class="p-4 flex flex-col gap-3 hover:bg-cloud-surface/30 transition-colors"
                    :class="{
                        'opacity-70 bg-cloud-surface/30': cat.deleted_at,
                    }"
                >
                    <!-- Header: Illustration + Name + Status -->
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="min-w-0">
                                <h4
                                    class="font-extrabold text-[15px] text-night-ink leading-tight truncate"
                                >
                                    {{ cat.name }}
                                </h4>
                                <p
                                    class="text-[12px] font-medium text-charcoal truncate mt-0.5"
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
                        class="flex items-center justify-between text-[12px] bg-cloud-surface/50 rounded-xl px-3 py-2 border border-border-gray/50"
                    >
                        <span class="text-charcoal font-semibold truncate mr-2">
                            {{
                                cat.description ||
                                "Tidak ada deskripsi tambahan"
                            }}
                        </span>
                        <div
                            class="inline-flex items-center gap-1 font-extrabold text-night-ink shrink-0"
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
                        class="flex items-center justify-end gap-2 pt-1 border-t border-border-gray/40"
                    >
                        <button
                            type="button"
                            @click="openEditModal(cat)"
                            class="px-3 py-1.5 text-[12px] font-extrabold text-night-ink bg-cloud-surface hover:bg-sprout-tint hover:text-eco-green-dark rounded-xl transition-colors cursor-pointer"
                        >
                            Ubah
                        </button>
                        <button
                            v-if="!cat.deleted_at"
                            type="button"
                            @click="openDeleteModal(cat)"
                            class="px-3 py-1.5 text-[12px] font-extrabold text-red-600 bg-red-50 hover:bg-red-100 rounded-xl transition-colors cursor-pointer"
                        >
                            Hapus
                        </button>
                        <button
                            v-else
                            type="button"
                            @click="openRestoreModal(cat)"
                            class="px-3 py-1.5 text-[12px] font-extrabold text-eco-green-dark bg-sprout-tint hover:brightness-105 rounded-xl transition-colors cursor-pointer"
                        >
                            Pulihkan
                        </button>
                    </div>
                </div>
            </template>

            <!-- Desktop Table Header -->
            <template #header>
                <th class="py-3.5 px-6">Jenis Sampah</th>
                <th class="py-3.5 px-6">Nilai Poin</th>
                <th class="py-3.5 px-6">Deskripsi & Kriteria</th>
                <th class="py-3.5 px-6">Status</th>
                <th class="py-3.5 px-6 text-right">Aksi</th>
            </template>

            <!-- Desktop Table Body -->
            <template #body>
                <tr
                    v-for="cat in categories.data"
                    :key="cat.id"
                    class="hover:bg-cloud-surface/50 transition-colors"
                    :class="{
                        'opacity-60 bg-cloud-surface/20': cat.deleted_at,
                    }"
                >
                    <!-- Nama & Ilustrasi -->
                    <td class="py-3.5 px-6">
                        <div class="flex items-center gap-3">
                            <div>
                                <p
                                    class="font-bold text-[14px] text-night-ink leading-tight"
                                >
                                    {{ cat.name }}
                                </p>
                                <p
                                    class="text-[12px] font-medium text-charcoal/70 mt-0.5"
                                >
                                    ID: #{{ cat.id }}
                                </p>
                            </div>
                        </div>
                    </td>

                    <!-- Nilai Poin -->
                    <td class="py-3.5 px-6">
                        <div
                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-gold-tint border border-sunlight-gold/30 text-night-ink font-extrabold text-[13px] shadow-2xs whitespace-nowrap"
                        >
                            <Coins
                                class="size-3.5 text-sunlight-gold shrink-0"
                            />
                            <span>{{ formatNumber(cat.points_per_kg) }}</span>
                            <span class="text-[11px] text-charcoal font-bold"
                                >Poin / kg</span
                            >
                        </div>
                    </td>

                    <!-- Deskripsi & Kriteria -->
                    <td class="py-3.5 px-6 max-w-[340px]">
                        <p
                            v-if="cat.description"
                            class="text-[13px] font-medium text-charcoal line-clamp-2 leading-relaxed"
                        >
                            {{ cat.description }}
                        </p>
                        <span
                            v-else
                            class="text-[12px] italic text-charcoal/50"
                        >
                            Tidak ada deskripsi tambahan
                        </span>
                    </td>

                    <!-- Status -->
                    <td class="py-3.5 px-6">
                        <StatusChip
                            :label="cat.deleted_at ? 'Nonaktif' : 'Aktif'"
                            :tone="cat.deleted_at ? 'Neutral' : 'Green'"
                        />
                    </td>

                    <!-- Aksi (Sesuai User Management) -->
                    <td class="py-3.5 px-6 text-right whitespace-nowrap">
                        <div
                            class="inline-flex items-center gap-1.5 justify-end"
                        >
                            <!-- Ubah (membuka popup edit) -->
                            <button
                                type="button"
                                @click="openEditModal(cat)"
                                class="px-2.5 py-1 text-[13px] font-extrabold text-night-ink hover:text-eco-green-dark hover:bg-cloud-surface rounded-lg transition-colors cursor-pointer"
                            >
                                Ubah
                            </button>

                            <!-- Hapus (jika aktif) -->
                            <button
                                v-if="!cat.deleted_at"
                                type="button"
                                @click="openDeleteModal(cat)"
                                class="px-2.5 py-1 text-[13px] font-extrabold text-red-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors cursor-pointer"
                            >
                                Hapus
                            </button>

                            <!-- Pulihkan (jika soft-deleted) -->
                            <button
                                v-else
                                type="button"
                                @click="openRestoreModal(cat)"
                                class="px-2.5 py-1 text-[13px] font-extrabold text-eco-green-dark hover:bg-sprout-tint/60 rounded-lg transition-colors cursor-pointer"
                            >
                                Pulihkan
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
