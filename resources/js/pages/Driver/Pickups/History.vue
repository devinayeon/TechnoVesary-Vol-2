<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import DashboardLayout from '@/layouts/DashboardLayout.vue';
import PageHeader from '@/components/Dashboard/PageHeader.vue';
import SearchInput from '@/components/Dashboard/SearchInput.vue';
import AppDropdown from '@/components/Dashboard/AppDropdown.vue';
import FlashMessages from '@/components/Dashboard/FlashMessages.vue';
import PickupHistoryTable from '@/components/Pickup/PickupHistoryTable.vue';
import type {
    PaginatedPickups,
    PickupStatusOption,
    PickupSummary,
} from '@/types/pickup';
import { RotateCcw } from 'lucide-vue-next';

const props = defineProps<{
    pickups: PaginatedPickups;
    filters: {
        search?: string;
        status?: string;
    };
    statuses: PickupStatusOption[];
}>();

const INDEX_URL = '/driver/pickups/history';

const search = ref(props.filters.search ?? '');
const selectedStatus = ref(props.filters.status ?? '');
const isSearching = ref(false);

const statusOptions = computed(() => [
    { value: '', label: 'Semua Status' },
    ...props.statuses.map((status) => ({
        value: status.value,
        label: status.short_label ?? status.label,
    })),
]);

const hasFilter = computed(
    () => search.value.trim() !== '' || selectedStatus.value !== '',
);

function applyFilters() {
    isSearching.value = true;

    router.get(
        INDEX_URL,
        {
            search: search.value.trim() || undefined,
            status: selectedStatus.value || undefined,
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
    selectedStatus.value = '';

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

function openDetail(pickup: PickupSummary) {
    router.visit(`/driver/pickups/${pickup.id}`);
}
</script>

<template>
    <Head title="Riwayat Penjemputan - Driver ReValue" />

    <DashboardLayout>
        <FlashMessages class="mb-6" />

        <PageHeader
            title="Riwayat Penjemputan"
            subtitle="Daftar penjemputan yang telah Anda selesaikan atau batalkan."
            badge="Driver"
        />

        <!-- Toolbar -->
        <div
            class="mb-6 flex flex-col items-stretch gap-3 lg:flex-row lg:items-center"
        >
            <SearchInput
                v-model="search"
                placeholder="Cari alamat atau nama customer..."
                :loading="isSearching"
            />

            <div
                class="flex flex-wrap items-center gap-2.5 sm:flex-nowrap lg:w-auto"
            >
                <AppDropdown
                    v-model="selectedStatus"
                    :options="statusOptions"
                    placeholder="Semua Status"
                    class="min-w-[160px] flex-1 sm:w-[200px] sm:flex-initial"
                    menu-width="w-full sm:w-[220px]"
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

        <PickupHistoryTable
            :pickups="pickups"
            :has-filter="hasFilter"
            show-customer
            :show-driver="false"
            empty-title="Belum ada riwayat penjemputan"
            empty-description="Penjemputan yang telah Anda selesaikan atau batalkan akan tampil di sini."
            @view="openDetail"
            @reset-filter="resetFilters"
        />
    </DashboardLayout>
</template>
