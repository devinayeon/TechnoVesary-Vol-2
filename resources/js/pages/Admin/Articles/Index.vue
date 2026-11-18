<script setup lang="ts">
import { ref, watch, computed } from "vue";
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import DashboardLayout from "@/layouts/DashboardLayout.vue";
import PageHeader from "@/components/Dashboard/PageHeader.vue";
import StatusChip from "@/components/Dashboard/StatusChip.vue";
import AppDropdown from "@/components/Dashboard/AppDropdown.vue";
import AppTable from "@/components/Dashboard/AppTable.vue";
import AppPagination from "@/components/Dashboard/AppPagination.vue";
import UserAvatar from "@/components/Dashboard/UserAvatar.vue";
import ArticleDeleteModal, {
    type ArticleDeleteItem,
} from "@/components/Dashboard/ArticleDeleteModal.vue";
import ArticleRestoreModal, {
    type ArticleRestoreItem,
} from "@/components/Dashboard/ArticleRestoreModal.vue";
import ArticlePreviewModal, {
    type ArticlePreviewData,
} from "@/components/Dashboard/ArticlePreviewModal.vue";
import ArticleCategoryModal, {
    type ArticleCategoryItem,
} from "@/components/Dashboard/ArticleCategoryModal.vue";
import {
    Search,
    Plus,
    RotateCcw,
    CheckCircle2,
    XCircle,
    X,
    BookOpen,
    Edit3,
    Trash2,
    Eye,
    FolderKanban,
} from "lucide-vue-next";

export interface ArticleItem {
    id: number;
    admin_id: number;
    category_id: number;
    title: string;
    slug: string;
    photo: string | null;
    photo_url?: string | null;
    content: string;
    created_at: string;
    updated_at: string;
    deleted_at: string | null;
    category?: {
        id: number;
        name: string;
    } | null;
    admin?: {
        id: number;
        user?: {
            id: number;
            name: string;
            email: string;
            photo: string | null;
            role: string;
        } | null;
    } | null;
}

interface PaginationLinks {
    url: string | null;
    label: string;
    active: boolean;
}

interface PaginatedArticles {
    data: ArticleItem[];
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
    articles: PaginatedArticles;
    categories: Array<{ id: number; name: string }>;
    filters: {
        search?: string;
        status?: string;
        category_id?: string | number;
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
const selectedCategory = ref(
    props.filters.category_id ? String(props.filters.category_id) : "all"
);

let searchTimeout: any = null;
const applyFilters = () => {
    router.get(
        "/admin/articles",
        {
            search: search.value || undefined,
            status: selectedStatus.value || undefined,
            category_id:
                selectedCategory.value !== "all"
                    ? selectedCategory.value
                    : undefined,
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

const statusOptions = [
    { value: "active", label: "Aktif" },
    { value: "trashed", label: "Dinonaktifkan" },
    { value: "all", label: "Semua Status" },
];

const categoryOptions = computed(() => [
    { value: "all", label: "Semua Kategori" },
    ...props.categories.map((c) => ({
        value: String(c.id),
        label: c.name,
    })),
]);

const resetFilters = () => {
    search.value = "";
    selectedStatus.value = "active";
    selectedCategory.value = "all";
    router.get(
        "/admin/articles",
        {},
        {
            preserveState: true,
            preserveScroll: true,
        }
    );
};

// Modal State: Soft Delete
const isDeleteModalOpen = ref(false);
const targetArticleToDelete = ref<ArticleDeleteItem | null>(null);

const openDeleteModal = (article: ArticleItem) => {
    targetArticleToDelete.value = {
        id: article.id,
        title: article.title,
        photo_url: article.photo_url,
        category: article.category,
    };
    isDeleteModalOpen.value = true;
};

const closeDeleteModal = () => {
    isDeleteModalOpen.value = false;
    targetArticleToDelete.value = null;
};

// Modal State: Restore
const isRestoreModalOpen = ref(false);
const targetArticleToRestore = ref<ArticleRestoreItem | null>(null);

const openRestoreModal = (article: ArticleItem) => {
    targetArticleToRestore.value = {
        id: article.id,
        title: article.title,
        photo_url: article.photo_url,
        category: article.category,
    };
    isRestoreModalOpen.value = true;
};

const closeRestoreModal = () => {
    isRestoreModalOpen.value = false;
    targetArticleToRestore.value = null;
};

// Modal State: Preview
const isPreviewModalOpen = ref(false);
const targetArticleToPreview = ref<ArticlePreviewData | null>(null);

const openPreviewModal = (article: ArticleItem) => {
    targetArticleToPreview.value = {
        id: article.id,
        title: article.title,
        slug: article.slug,
        photo_url: article.photo_url,
        content: article.content,
        created_at: article.created_at,
        deleted_at: article.deleted_at,
        category: article.category,
        admin: article.admin,
    };
    isPreviewModalOpen.value = true;
};

const closePreviewModal = () => {
    isPreviewModalOpen.value = false;
    targetArticleToPreview.value = null;
};

// Modal State: Category Management
const isCategoryModalOpen = ref(false);

// Date Formatter
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

const stripExcerpt = (html: string, length = 90): string => {
    if (!html) return "";
    const text = html.replace(/<[^>]+>/g, " ").replace(/\s+/g, " ").trim();
    return text.length > length ? text.substring(0, length) + "..." : text;
};
</script>

<template>
    <Head title="Manajemen Artikel Edukasi - Admin ReValue" />

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

        <!-- Header + Action Button -->
        <div
            class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 mb-6"
        >
            <PageHeader
                title="Manajemen Artikel"
                subtitle="Kelola artikel edukasi lingkungan, panduan pemilahan sampah, dan tips daur ulang untuk nasabah."
                badge="Edukasi"
                class="mb-0"
            />

            <div class="flex flex-wrap items-center gap-2.5 shrink-0 self-start sm:self-auto">
                <button
                    type="button"
                    @click="isCategoryModalOpen = true"
                    class="inline-flex items-center justify-center gap-2 px-4 h-[46px] rounded-xl border-2 border-b-[4px] border-border-gray bg-paper-white text-night-ink font-extrabold text-[14px] hover:bg-cloud-surface active:translate-y-0.5 active:border-b-2 transition-all cursor-pointer shadow-2xs"
                >
                    <FolderKanban class="size-4.5 text-charcoal" />
                    <span>Kelola Kategori</span>
                </button>

                <Link
                    href="/admin/articles/create"
                    class="inline-flex items-center justify-center gap-2 px-5 h-[46px] rounded-xl border-2 border-b-[4px] border-eco-green-dark bg-eco-green text-white font-extrabold text-[14px] hover:brightness-105 active:translate-y-0.5 active:border-b-2 transition-all cursor-pointer shadow-xs"
                >
                    <Plus class="size-4.5" />
                    <span>Tambah Artikel</span>
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
                    placeholder="Cari judul atau isi konten artikel..."
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
                <!-- Category Filter -->
                <AppDropdown
                    v-model="selectedCategory"
                    :options="categoryOptions"
                    placeholder="Semua Kategori"
                    class="flex-1 sm:flex-initial min-w-[170px] sm:w-[200px]"
                    menu-width="w-full sm:w-[220px]"
                    align="right"
                    @change="onFilterChange"
                />

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
                    v-if="
                        search ||
                        selectedStatus !== 'active' ||
                        selectedCategory !== 'all'
                    "
                    type="button"
                    @click="resetFilters"
                    class="p-2.5 h-[44px] rounded-xl border-2 border-border-gray bg-paper-white text-charcoal hover:text-night-ink hover:bg-cloud-surface transition-colors cursor-pointer shrink-0"
                    title="Reset Filter"
                >
                    <RotateCcw class="size-4" />
                </button>
            </div>
        </div>

        <!-- Table & Pagination -->
        <AppTable
            :is-empty="articles.data.length === 0"
            :has-filter="
                !!(
                    search ||
                    selectedStatus !== 'active' ||
                    selectedCategory !== 'all'
                )
            "
            empty-title="Belum ada artikel edukasi"
            empty-description="Mulai publikasikan artikel pertama Anda untuk mengedukasi nasabah tentang gaya hidup zero waste."
            empty-filter-title="Tidak ada artikel yang cocok"
            empty-filter-description="Coba ubah kata kunci pencarian atau sesuaikan filter status dan kategori."
            empty-action-label="Tambah Artikel Baru"
            min-width="min-w-[900px]"
            @action="router.visit('/admin/articles/create')"
            @reset-filter="resetFilters"
        >
            <!-- Mobile Cards View (< md) -->
            <template #mobile>
                <div
                    v-for="article in articles.data"
                    :key="`m-${article.id}`"
                    class="p-4 flex flex-col gap-3 hover:bg-cloud-surface/30 transition-colors"
                >
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-2">
                            <StatusChip
                                :label="article.category?.name || 'Umum'"
                                tone="Green"
                            />
                            <StatusChip
                                v-if="article.deleted_at"
                                label="Nonaktif"
                                tone="Neutral"
                            />
                        </div>
                        <span class="text-[12px] font-bold text-charcoal/70">
                            {{ formatDate(article.created_at) }}
                        </span>
                    </div>

                    <div class="flex items-start gap-3.5">
                        <div
                            class="size-16 rounded-xl bg-cloud-surface border border-border-gray overflow-hidden shrink-0 flex items-center justify-center shadow-2xs"
                        >
                            <img
                                v-if="article.photo_url"
                                :src="article.photo_url"
                                :alt="article.title"
                                class="w-full h-full object-cover"
                            />
                            <BookOpen v-else class="size-6 text-charcoal/50" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <h4
                                class="font-extrabold text-[15px] text-night-ink leading-tight line-clamp-2"
                            >
                                {{ article.title }}
                            </h4>
                            <p
                                class="text-[12px] font-medium text-charcoal/80 line-clamp-2 mt-1"
                            >
                                {{ stripExcerpt(article.content) }}
                            </p>
                        </div>
                    </div>

                    <!-- Author Info -->
                    <div
                        class="flex items-center justify-between text-[12px] bg-cloud-surface/60 rounded-xl px-3 py-2 border border-border-gray/50"
                    >
                        <span class="text-charcoal font-semibold">Penulis:</span>
                        <span class="font-bold text-night-ink truncate max-w-[200px]">
                            {{ article.admin?.user?.name || "Admin ReValue" }}
                        </span>
                    </div>

                    <!-- Actions -->
                    <div
                        class="flex items-center justify-end gap-2 pt-2 border-t border-border-gray/40"
                    >
                        <button
                            type="button"
                            @click="openPreviewModal(article)"
                            class="px-3 py-1.5 text-[12px] font-extrabold text-night-ink bg-cloud-surface hover:bg-sprout-tint hover:text-eco-green-dark rounded-xl transition-colors cursor-pointer"
                        >
                            Preview
                        </button>

                        <template v-if="!article.deleted_at">
                            <Link
                                :href="`/admin/articles/${article.id}/edit`"
                                class="px-3 py-1.5 text-[12px] font-extrabold text-night-ink bg-cloud-surface hover:bg-sprout-tint hover:text-eco-green-dark rounded-xl transition-colors cursor-pointer"
                            >
                                Edit
                            </Link>
                            <button
                                type="button"
                                @click="openDeleteModal(article)"
                                class="px-3 py-1.5 text-[12px] font-extrabold text-red-600 bg-red-50 hover:bg-red-100 rounded-xl transition-colors cursor-pointer"
                            >
                                Nonaktifkan
                            </button>
                        </template>

                        <button
                            v-else
                            type="button"
                            @click="openRestoreModal(article)"
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
                <th class="py-3.5 px-6">Judul & Slug</th>
                <th class="py-3.5 px-6">Kategori</th>
                <th class="py-3.5 px-6">Penulis</th>
                <th class="py-3.5 px-6">Tanggal</th>
                <th class="py-3.5 px-6">Status</th>
                <th class="py-3.5 px-6 text-right">Aksi</th>
            </template>

            <!-- Desktop Table Body -->
            <template #body>
                <tr
                    v-for="article in articles.data"
                    :key="article.id"
                    class="hover:bg-cloud-surface/50 transition-colors"
                >
                    <!-- Thumbnail -->
                    <td class="py-3.5 px-6">
                        <div
                            class="size-14 rounded-xl bg-cloud-surface border border-border-gray overflow-hidden flex items-center justify-center shrink-0 shadow-2xs"
                        >
                            <img
                                v-if="article.photo_url"
                                :src="article.photo_url"
                                :alt="article.title"
                                class="w-full h-full object-cover"
                            />
                            <BookOpen v-else class="size-5 text-charcoal/50" />
                        </div>
                    </td>

                    <!-- Judul & Slug -->
                    <td class="py-3.5 px-6 max-w-[340px]">
                        <h4
                            class="font-extrabold text-[14px] text-night-ink leading-tight line-clamp-2"
                        >
                            {{ article.title }}
                        </h4>
                        <p class="text-[12px] font-medium text-charcoal/70 truncate mt-1">
                            /{{ article.slug }}
                        </p>
                    </td>

                    <!-- Kategori -->
                    <td class="py-3.5 px-6 whitespace-nowrap">
                        <StatusChip
                            :label="article.category?.name || 'Umum'"
                            tone="Green"
                        />
                    </td>

                    <!-- Penulis -->
                    <td class="py-3.5 px-6 whitespace-nowrap">
                        <div class="flex items-center gap-2.5">
                            <UserAvatar
                                :photo="article.admin?.user?.photo || null"
                                :role="article.admin?.user?.role || 'admin'"
                                :name="article.admin?.user?.name || 'Admin'"
                                size="sm"
                            />
                            <span class="font-bold text-[13px] text-night-ink truncate max-w-[140px]">
                                {{ article.admin?.user?.name || "Admin" }}
                            </span>
                        </div>
                    </td>

                    <!-- Tanggal -->
                    <td class="py-3.5 px-6 text-[13px] font-medium text-charcoal whitespace-nowrap">
                        {{ formatDate(article.created_at) }}
                    </td>

                    <!-- Status -->
                    <td class="py-3.5 px-6 whitespace-nowrap">
                        <StatusChip
                            v-if="!article.deleted_at"
                            label="Aktif"
                            tone="Green"
                        />
                        <StatusChip
                            v-else
                            label="Nonaktif"
                            tone="Neutral"
                        />
                    </td>

                    <!-- Aksi -->
                    <td class="py-3.5 px-6 text-right whitespace-nowrap">
                        <div class="inline-flex items-center justify-end gap-1">
                            <!-- Preview Button -->
                            <button
                                type="button"
                                @click="openPreviewModal(article)"
                                class="p-2 rounded-lg text-charcoal hover:text-night-ink hover:bg-cloud-surface transition-colors cursor-pointer"
                                title="Lihat Pratinjau Artikel"
                            >
                                <Eye class="size-4" />
                            </button>

                            <template v-if="!article.deleted_at">
                                <!-- Edit Button -->
                                <Link
                                    :href="`/admin/articles/${article.id}/edit`"
                                    class="p-2 rounded-lg text-charcoal hover:text-eco-green-dark hover:bg-sprout-tint/60 transition-colors cursor-pointer"
                                    title="Edit Artikel"
                                >
                                    <Edit3 class="size-4" />
                                </Link>

                                <!-- Delete Button -->
                                <button
                                    type="button"
                                    @click="openDeleteModal(article)"
                                    class="p-2 rounded-lg text-charcoal hover:text-red-600 hover:bg-red-50 transition-colors cursor-pointer"
                                    title="Nonaktifkan Artikel"
                                >
                                    <Trash2 class="size-4" />
                                </button>
                            </template>

                            <template v-else>
                                <!-- Restore Button -->
                                <button
                                    type="button"
                                    @click="openRestoreModal(article)"
                                    class="p-2 rounded-lg text-eco-green-dark hover:bg-sprout-tint transition-colors cursor-pointer"
                                    title="Pulihkan Artikel"
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
                    :pagination="articles"
                    item-label="artikel"
                />
            </template>
        </AppTable>

        <!-- Modals -->
        <ArticleDeleteModal
            :show="isDeleteModalOpen"
            :article="targetArticleToDelete"
            @close="closeDeleteModal"
        />

        <ArticleRestoreModal
            :show="isRestoreModalOpen"
            :article="targetArticleToRestore"
            @close="closeRestoreModal"
        />

        <ArticlePreviewModal
            :show="isPreviewModalOpen"
            :article="targetArticleToPreview"
            @close="closePreviewModal"
        />

        <ArticleCategoryModal
            :show="isCategoryModalOpen"
            :categories="categories"
            @close="isCategoryModalOpen = false"
        />
    </DashboardLayout>
</template>
