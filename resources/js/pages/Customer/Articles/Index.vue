<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import DashboardLayout from '@/layouts/DashboardLayout.vue';
import PageHeader from '@/components/Dashboard/PageHeader.vue';
import SearchInput from '@/components/Dashboard/SearchInput.vue';
import AppDropdown from '@/components/Dashboard/AppDropdown.vue';
import AppPagination from '@/components/Dashboard/AppPagination.vue';
import { ArticleCard, ArticleEmptyState } from '@/components/Article';
import type { ArticleCategory, PaginatedArticles } from '@/types/article';
import { BookOpen, RotateCcw } from 'lucide-vue-next';

const props = defineProps<{
    articles: PaginatedArticles;
    categories: ArticleCategory[];
    filters: {
        search?: string;
        category_id?: string | number;
        sort?: string;
    };
}>();

const INDEX_URL = '/customer/articles';

const search = ref(props.filters.search ?? '');
const selectedCategory = ref(
    props.filters.category_id ? String(props.filters.category_id) : 'all',
);
const selectedSort = ref(props.filters.sort ?? 'latest');
const isSearching = ref(false);

const sortOptions = [
    { value: 'latest', label: 'Terbaru' },
    { value: 'oldest', label: 'Terlama' },
    { value: 'title', label: 'Judul A-Z' },
];

const categoryOptions = computed(() => [
    { value: 'all', label: 'Semua Kategori' },
    ...props.categories.map((category) => ({
        value: String(category.id),
        label: category.name,
    })),
]);

const hasFilter = computed(
    () =>
        search.value.trim() !== '' ||
        selectedCategory.value !== 'all' ||
        selectedSort.value !== 'latest',
);

function applyFilters() {
    isSearching.value = true;

    router.get(
        INDEX_URL,
        {
            search: search.value.trim() || undefined,
            category_id:
                selectedCategory.value !== 'all'
                    ? selectedCategory.value
                    : undefined,
            sort:
                selectedSort.value !== 'latest'
                    ? selectedSort.value
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
    selectedCategory.value = 'all';
    selectedSort.value = 'latest';

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
    <Head title="Artikel Edukasi - ReValue" />

    <DashboardLayout>
        <PageHeader
            title="Artikel Edukasi"
            subtitle="Panduan pemilahan, tips daur ulang, dan wawasan ekonomi sirkular untuk gaya hidup yang lebih baik."
            badge="Edukasi"
        />

        <!-- Toolbar: Search & Filter -->
        <div
            class="mb-6 flex flex-col items-stretch gap-3 lg:flex-row lg:items-center"
        >
            <SearchInput
                v-model="search"
                placeholder="Cari judul atau isi artikel..."
                :loading="isSearching"
            />

            <div
                class="flex flex-wrap items-center gap-2.5 sm:flex-nowrap lg:w-auto"
            >
                <AppDropdown
                    v-model="selectedCategory"
                    :options="categoryOptions"
                    placeholder="Semua Kategori"
                    class="min-w-[170px] flex-1 sm:w-[200px] sm:flex-initial"
                    menu-width="w-full sm:w-[220px]"
                    align="right"
                    @change="applyFilters"
                />

                <AppDropdown
                    v-model="selectedSort"
                    :options="sortOptions"
                    placeholder="Terbaru"
                    class="min-w-[140px] flex-1 sm:w-[160px] sm:flex-initial"
                    menu-width="w-full sm:w-[180px]"
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
            class="mb-4 flex items-center gap-2 text-[13px] font-semibold text-charcoal"
        >
            <BookOpen class="size-4 text-eco-green-dark" />
            <span v-if="articles.total > 0">
                Menampilkan {{ articles.from }}–{{ articles.to }} dari
                {{ articles.total }} artikel
            </span>
            <span v-else>Belum ada artikel untuk ditampilkan</span>
        </div>

        <!-- Article Grid -->
        <div
            v-if="articles.data.length > 0"
            class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-3"
        >
            <ArticleCard
                v-for="article in articles.data"
                :key="article.id"
                :article="article"
                :href="`${INDEX_URL}/${article.slug}`"
            />
        </div>

        <!-- Empty State -->
        <ArticleEmptyState
            v-else
            :has-filter="hasFilter"
            title="Belum ada artikel edukasi"
            description="Artikel edukasi akan tampil di sini setelah dipublikasikan oleh admin."
            @reset="resetFilters"
        />

        <!-- Pagination -->
        <div
            v-if="articles.data.length > 0"
            class="mt-6 overflow-hidden rounded-[20px] border-2 border-b-[4px] border-border-gray bg-paper-white shadow-xs"
        >
            <AppPagination :pagination="articles" item-label="artikel" />
        </div>
    </DashboardLayout>
</template>
