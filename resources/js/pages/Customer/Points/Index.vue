<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import DashboardLayout from '@/layouts/DashboardLayout.vue';
import PageHeader from '@/components/Dashboard/PageHeader.vue';
import SearchInput from '@/components/Dashboard/SearchInput.vue';
import AppDropdown from '@/components/Dashboard/AppDropdown.vue';
import FlashMessages from '@/components/Dashboard/FlashMessages.vue';
import PointSummaryCards from '@/components/Point/PointSummaryCards.vue';
import PointHistoryTable from '@/components/Point/PointHistoryTable.vue';
import type {
    PaginatedPointHistories,
    PointSummary,
    PointTypeOption,
} from '@/types/point';
import { RotateCcw } from 'lucide-vue-next';

const props = defineProps<{
    histories: PaginatedPointHistories;
    filters: {
        search?: string;
        type?: string;
    };
    types: PointTypeOption[];
    summary: PointSummary;
}>();

const INDEX_URL = '/customer/points';

const search = ref(props.filters.search ?? '');
const selectedType = ref(props.filters.type ?? '');
const isSearching = ref(false);

const typeOptions = computed(() => [
    { value: '', label: 'Semua Tipe' },
    ...props.types.map((type) => ({
        value: type.value,
        label: type.short_label ?? type.label,
    })),
]);

const hasFilter = computed(
    () => search.value.trim() !== '' || selectedType.value !== '',
);

function applyFilters() {
    isSearching.value = true;

    router.get(
        INDEX_URL,
        {
            search: search.value.trim() || undefined,
            type: selectedType.value || undefined,
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
    selectedType.value = '';

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
    <Head title="Riwayat Poin - ReValue" />

    <DashboardLayout>
        <FlashMessages class="mb-6" />

        <PageHeader
            title="Riwayat Poin"
            subtitle="Pantau seluruh mutasi poin masuk dan poin keluar dari aktivitas penjemputan sampah serta penukaran di Upcycle Store."
            badge="Poin ReValue"
        />

        <PointSummaryCards :summary="summary" />

        <!-- Toolbar -->
        <div
            class="mb-6 flex flex-col items-stretch gap-3 lg:flex-row lg:items-center"
        >
            <SearchInput
                v-model="search"
                placeholder="Cari sumber mutasi poin..."
                :loading="isSearching"
            />

            <div
                class="flex flex-wrap items-center gap-2.5 sm:flex-nowrap lg:w-auto"
            >
                <AppDropdown
                    v-model="selectedType"
                    :options="typeOptions"
                    placeholder="Semua Tipe"
                    class="min-w-[160px] flex-1 sm:w-[190px] sm:flex-initial"
                    menu-width="w-full sm:w-[210px]"
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

        <PointHistoryTable
            :histories="histories"
            :has-filter="hasFilter"
            @reset-filter="resetFilters"
        />
    </DashboardLayout>
</template>
