<script setup lang="ts">
import { ref, watch, computed } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import DashboardLayout from '@/layouts/DashboardLayout.vue';
import PageHeader from '@/components/Dashboard/PageHeader.vue';
import StatusChip from '@/components/Dashboard/StatusChip.vue';
import AppDropdown from '@/components/Dashboard/AppDropdown.vue';
import AppTable from '@/components/Dashboard/AppTable.vue';
import AppPagination from '@/components/Dashboard/AppPagination.vue';
import UserAvatar from '@/components/Dashboard/UserAvatar.vue';
import ArticleDeleteModal, {
    type ArticleDeleteItem,
} from '@/components/Dashboard/ArticleDeleteModal.vue';
import ArticleRestoreModal, {
    type ArticleRestoreItem,
} from '@/components/Dashboard/ArticleRestoreModal.vue';
import ArticlePreviewModal, {
    type ArticlePreviewData,
} from '@/components/Dashboard/ArticlePreviewModal.vue';
import ArticleCategoryModal, {
    type ArticleCategoryItem,
} from '@/components/Dashboard/ArticleCategoryModal.vue';
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
} from 'lucide-vue-next';

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
const selectedCategory = ref(
    props.filters.category_id ? String(props.filters.category_id) : 'all',
);

let searchTimeout: any = null;
const applyFilters = () => {
    router.get(
        '/admin/articles',
        {
            search: search.value || undefined,
            status: selectedStatus.value || undefined,
            category_id:
                selectedCategory.value !== 'all'
                    ? selectedCategory.value
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

const onFilterChange = () => {
    applyFilters();
};

const statusOptions = [
    { value: 'active', label: 'Aktif' },
    { value: 'trashed', label: 'Dinonaktifkan' },
    { value: 'all', label: 'Semua Status' },
];

const categoryOptions = computed(() => [
    { value: 'all', label: 'Semua Kategori' },
    ...props.categories.map((c) => ({
        value: String(c.id),
        label: c.name,
    })),
]);

const resetFilters = () => {
    search.value = '';
    selectedStatus.value = 'active';
    selectedCategory.value = 'all';
    router.get(
        '/admin/articles',
        {},
        {
            preserveState: true,
            preserveScroll: true,
        },
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
    if (!dateString) return '—';
    try {
        const d = new Date(dateString);
        return d.toLocaleDateString('id-ID', {
            day: 'numeric',
            month: 'short',
            year: 'numeric',
        });
    } catch {
        return dateString;
    }
};

const stripExcerpt = (html: string, length = 90): string => {
    if (!html) return '';
    const text = html
        .replace(/<[^>]+>/g, ' ')
        .replace(/\s+/g, ' ')
        .trim();
    return text.length > length ? text.substring(0, length) + '...' : text;
};
</script>

<template>
    <Head title="Manajemen Artikel Edukasi - Admin ReValue" />

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

        <!-- Header + Action Button -->
        <div
            class="mb-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-start"
        >
            <PageHeader
                title="Manajemen Artikel"
                subtitle="Kelola artikel edukasi lingkungan, panduan pemilahan sampah, dan tips daur ulang untuk customer."
                badge="Edukasi"
                class="mb-0"
            />

            <div
                class="flex shrink-0 flex-wrap items-center gap-2.5 self-start sm:self-auto"
            >
                <button
                    type="button"
                    @click="isCategoryModalOpen = true"
                    class="inline-flex h-[46px] cursor-pointer items-center justify-center gap-2 rounded-xl border-2 border-b-[4px] border-border-gray bg-paper-white px-4 text-[14px] font-extrabold text-night-ink shadow-2xs transition-all hover:bg-cloud-surface active:translate-y-0.5 active:border-b-2"
                >
                    <FolderKanban class="size-4.5 text-charcoal" />
                    <span>Kelola Kategori</span>
                </button>

                <Link
                    href="/admin/articles/create"
                    class="inline-flex h-[46px] cursor-pointer items-center justify-center gap-2 rounded-xl border-2 border-b-[4px] border-eco-green-dark bg-eco-green px-5 text-[14px] font-extrabold text-white shadow-xs transition-all hover:brightness-105 active:translate-y-0.5 active:border-b-2"
                >
                    <Plus class="size-4.5" />
                    <span>Tambah Artikel</span>
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
                    placeholder="Cari judul atau isi konten artikel..."
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
                <!-- Category Filter -->
                <AppDropdown
                    v-model="selectedCategory"
                    :options="categoryOptions"
                    placeholder="Semua Kategori"
                    class="min-w-[170px] flex-1 sm:w-[200px] sm:flex-initial"
                    menu-width="w-full sm:w-[220px]"
                    align="right"
                    @change="onFilterChange"
                />

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
                    v-if="
                        search ||
                        selectedStatus !== 'active' ||
                        selectedCategory !== 'all'
                    "
                    type="button"
                    @click="resetFilters"
                    class="h-[44px] shrink-0 cursor-pointer rounded-xl border-2 border-border-gray bg-paper-white p-2.5 text-charcoal transition-colors hover:bg-cloud-surface hover:text-night-ink"
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
            empty-description="Mulai publikasikan artikel pertama Anda untuk mengedukasi customer tentang gaya hidup zero waste."
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
                    class="flex flex-col gap-3 p-4 transition-colors hover:bg-cloud-surface/30"
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
                            class="flex size-16 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-border-gray bg-cloud-surface shadow-2xs"
                        >
                            <img
                                v-if="article.photo_url"
                                :src="article.photo_url"
                                :alt="article.title"
                                class="h-full w-full object-cover"
                            />
                            <BookOpen v-else class="size-6 text-charcoal/50" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <h4
                                class="line-clamp-2 text-[15px] leading-tight font-extrabold text-night-ink"
                            >
                                {{ article.title }}
                            </h4>
                            <p
                                class="mt-1 line-clamp-2 text-[12px] font-medium text-charcoal/80"
                            >
                                {{ stripExcerpt(article.content) }}
                            </p>
                        </div>
                    </div>

                    <!-- Author Info -->
                    <div
                        class="flex items-center justify-between rounded-xl border border-border-gray/50 bg-cloud-surface/60 px-3 py-2 text-[12px]"
                    >
                        <span class="font-semibold text-charcoal"
                            >Penulis:</span
                        >
                        <span
                            class="max-w-[200px] truncate font-bold text-night-ink"
                        >
                            {{ article.admin?.user?.name || 'Admin ReValue' }}
                        </span>
                    </div>

                    <!-- Actions -->
                    <div
                        class="flex items-center justify-end gap-2 border-t border-border-gray/40 pt-2"
                    >
                        <button
                            type="button"
                            @click="openPreviewModal(article)"
                            class="cursor-pointer rounded-xl bg-cloud-surface px-3 py-1.5 text-[12px] font-extrabold text-night-ink transition-colors hover:bg-sprout-tint hover:text-eco-green-dark"
                        >
                            Preview
                        </button>

                        <template v-if="!article.deleted_at">
                            <Link
                                :href="`/admin/articles/${article.id}/edit`"
                                class="cursor-pointer rounded-xl bg-cloud-surface px-3 py-1.5 text-[12px] font-extrabold text-night-ink transition-colors hover:bg-sprout-tint hover:text-eco-green-dark"
                            >
                                Edit
                            </Link>
                            <button
                                type="button"
                                @click="openDeleteModal(article)"
                                class="cursor-pointer rounded-xl bg-red-50 px-3 py-1.5 text-[12px] font-extrabold text-red-600 transition-colors hover:bg-red-100"
                            >
                                Nonaktifkan
                            </button>
                        </template>

                        <button
                            v-else
                            type="button"
                            @click="openRestoreModal(article)"
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
                <th class="px-6 py-3.5">Judul & Slug</th>
                <th class="px-6 py-3.5">Kategori</th>
                <th class="px-6 py-3.5">Penulis</th>
                <th class="px-6 py-3.5">Tanggal</th>
                <th class="px-6 py-3.5">Status</th>
                <th class="px-6 py-3.5 text-right">Aksi</th>
            </template>

            <!-- Desktop Table Body -->
            <template #body>
                <tr
                    v-for="article in articles.data"
                    :key="article.id"
                    class="transition-colors hover:bg-cloud-surface/50"
                >
                    <!-- Thumbnail -->
                    <td class="px-6 py-3.5">
                        <div
                            class="flex size-14 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-border-gray bg-cloud-surface shadow-2xs"
                        >
                            <img
                                v-if="article.photo_url"
                                :src="article.photo_url"
                                :alt="article.title"
                                class="h-full w-full object-cover"
                            />
                            <BookOpen v-else class="size-5 text-charcoal/50" />
                        </div>
                    </td>

                    <!-- Judul & Slug -->
                    <td class="max-w-[340px] px-6 py-3.5">
                        <h4
                            class="line-clamp-2 text-[14px] leading-tight font-extrabold text-night-ink"
                        >
                            {{ article.title }}
                        </h4>
                        <p
                            class="mt-1 truncate text-[12px] font-medium text-charcoal/70"
                        >
                            /{{ article.slug }}
                        </p>
                    </td>

                    <!-- Kategori -->
                    <td class="px-6 py-3.5 whitespace-nowrap">
                        <StatusChip
                            :label="article.category?.name || 'Umum'"
                            tone="Green"
                        />
                    </td>

                    <!-- Penulis -->
                    <td class="px-6 py-3.5 whitespace-nowrap">
                        <div class="flex items-center gap-2.5">
                            <UserAvatar
                                :photo="article.admin?.user?.photo || null"
                                :role="article.admin?.user?.role || 'admin'"
                                :name="article.admin?.user?.name || 'Admin'"
                                size="sm"
                            />
                            <span
                                class="max-w-[140px] truncate text-[13px] font-bold text-night-ink"
                            >
                                {{ article.admin?.user?.name || 'Admin' }}
                            </span>
                        </div>
                    </td>

                    <!-- Tanggal -->
                    <td
                        class="px-6 py-3.5 text-[13px] font-medium whitespace-nowrap text-charcoal"
                    >
                        {{ formatDate(article.created_at) }}
                    </td>

                    <!-- Status -->
                    <td class="px-6 py-3.5 whitespace-nowrap">
                        <StatusChip
                            v-if="!article.deleted_at"
                            label="Aktif"
                            tone="Green"
                        />
                        <StatusChip v-else label="Nonaktif" tone="Neutral" />
                    </td>

                    <!-- Aksi -->
                    <td class="px-6 py-3.5 text-right whitespace-nowrap">
                        <div class="inline-flex items-center justify-end gap-1">
                            <!-- Preview Button -->
                            <button
                                type="button"
                                @click="openPreviewModal(article)"
                                class="cursor-pointer rounded-lg p-2 text-charcoal transition-colors hover:bg-cloud-surface hover:text-night-ink"
                                title="Lihat Pratinjau Artikel"
                            >
                                <Eye class="size-4" />
                            </button>

                            <template v-if="!article.deleted_at">
                                <!-- Edit Button -->
                                <Link
                                    :href="`/admin/articles/${article.id}/edit`"
                                    class="cursor-pointer rounded-lg p-2 text-charcoal transition-colors hover:bg-sprout-tint/60 hover:text-eco-green-dark"
                                    title="Edit Artikel"
                                >
                                    <Edit3 class="size-4" />
                                </Link>

                                <!-- Delete Button -->
                                <button
                                    type="button"
                                    @click="openDeleteModal(article)"
                                    class="cursor-pointer rounded-lg p-2 text-charcoal transition-colors hover:bg-red-50 hover:text-red-600"
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
                                    class="cursor-pointer rounded-lg p-2 text-eco-green-dark transition-colors hover:bg-sprout-tint"
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
                <AppPagination :pagination="articles" item-label="artikel" />
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
