<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import DashboardLayout from '@/layouts/DashboardLayout.vue';
import PageHeader from '@/components/Dashboard/PageHeader.vue';
import SearchInput from '@/components/Dashboard/SearchInput.vue';
import AppDropdown from '@/components/Dashboard/AppDropdown.vue';
import FlashMessages from '@/components/Dashboard/FlashMessages.vue';
import ConfirmModal from '@/components/Dashboard/ConfirmModal.vue';
import PickupBanner from '@/components/Pickup/PickupBanner.vue';
import PickupHistoryTable from '@/components/Pickup/PickupHistoryTable.vue';
import { RotateCcw } from 'lucide-vue-next';
import type {
    MembershipInfo,
    PaginatedPickups,
    PickupStatusOption,
    PickupSummary,
} from '@/types/pickup';

const props = defineProps<{
    pickups: PaginatedPickups;
    filters: {
        search?: string;
        status?: string;
    };
    statuses: PickupStatusOption[];
    membership: MembershipInfo;
}>();

const INDEX_URL = '/customer/pickups';

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
    router.visit(`${INDEX_URL}/${pickup.id}`);
}

const cancelTarget = ref<PickupSummary | null>(null);
const isCancelling = ref(false);

function confirmCancel() {
    if (!cancelTarget.value) return;

    isCancelling.value = true;

    router.post(
        `${INDEX_URL}/${cancelTarget.value.id}/cancel`,
        {},
        {
            preserveScroll: true,
            onFinish: () => {
                isCancelling.value = false;
                cancelTarget.value = null;
            },
        },
    );
}
</script>

<template>
    <Head title="Riwayat Penjemputan - ReValue" />

    <DashboardLayout>
        <FlashMessages class="mb-6" />

        <PageHeader
            title="Penjemputan Sampah"
            subtitle="Ajukan penjemputan sampah terpilah dan pantau status penjemputan Anda di satu tempat."
            badge="ReValue+"
        />

        <div class="mb-6">
            <PickupBanner
                :is-member="membership.is_member"
                :expires-at="membership.expires_at"
            />
        </div>

        <!-- Toolbar -->
        <div
            class="mb-6 flex flex-col items-stretch gap-3 lg:flex-row lg:items-center"
        >
            <SearchInput
                v-model="search"
                placeholder="Cari alamat atau nomor pengajuan..."
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
            allow-cancel
            show-driver
            empty-title="Belum ada penjemputan"
            empty-description="Pengajuan penjemputan sampah Anda akan tampil di sini setelah dikirim."
            @view="openDetail"
            @cancel="cancelTarget = $event"
            @reset-filter="resetFilters"
        />

        <ConfirmModal
            :show="cancelTarget !== null"
            title="Batalkan pengajuan penjemputan?"
            description="Pengajuan yang dibatalkan tidak dapat dikembalikan. Anda dapat membuat pengajuan baru kapan saja."
            confirm-label="Ya, Batalkan"
            tone="danger"
            :processing="isCancelling"
            @confirm="confirmCancel"
            @close="cancelTarget = null"
        />
    </DashboardLayout>
</template>
