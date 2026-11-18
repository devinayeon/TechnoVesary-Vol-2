<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import DashboardLayout from '@/layouts/DashboardLayout.vue';
import PageHeader from '@/components/Dashboard/PageHeader.vue';
import SearchInput from '@/components/Dashboard/SearchInput.vue';
import AppDropdown from '@/components/Dashboard/AppDropdown.vue';
import FlashMessages from '@/components/Dashboard/FlashMessages.vue';
import PickupHistoryTable from '@/components/Pickup/PickupHistoryTable.vue';
import {
    getCurrentCoords,
    useDriverLocation,
} from '@/composables/useDriverLocation';
import type {
    ActivePickupInfo,
    PaginatedPickups,
    PickupStatusOption,
    PickupSummary,
} from '@/types/pickup';
import { ArrowRight, Navigation, RotateCcw, Truck } from 'lucide-vue-next';

const props = defineProps<{
    pickups: PaginatedPickups;
    filters: {
        search?: string;
        status?: string;
    };
    statuses: PickupStatusOption[];
    activePickup?: ActivePickupInfo | null;
}>();

const INDEX_URL = '/driver/pickups';

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

const { isSending: isLocationSending, sendOnce: sendLocationOnce } =
    useDriverLocation();

function claimTask(pickup: PickupSummary) {
    void (async () => {
        const coords = await getCurrentCoords();

        router.post(`${INDEX_URL}/${pickup.id}/claim`, coords ?? {}, {
            preserveScroll: true,
        });
    })();
}

/**
 * Kirim posisi armada terakhir agar rute OSRM customer akurat.
 */
function shareLocation() {
    void sendLocationOnce();
}
</script>

<template>
    <Head title="Tugas Penjemputan - Driver ReValue" />

    <DashboardLayout>
        <FlashMessages class="mb-6" />

        <PageHeader
            title="Tugas Penjemputan"
            subtitle="Antrean pengajuan customer dan tugas penjemputan yang sedang Anda kerjakan."
            badge="Driver"
        >
            <template #action>
                <button
                    type="button"
                    class="inline-flex h-[46px] cursor-pointer items-center gap-2 rounded-xl border-2 border-b-[4px] border-sky-blue-dark bg-sky-blue px-4 text-[13px] font-extrabold text-night-ink transition-all hover:brightness-105 active:translate-y-0.5 active:border-b-2 disabled:cursor-not-allowed disabled:opacity-60"
                    :disabled="isLocationSending"
                    @click="shareLocation"
                >
                    <Navigation class="size-4" />
                    {{ isLocationSending ? 'Mengirim...' : 'Bagikan Lokasi' }}
                </button>
            </template>
        </PageHeader>

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

        <!-- Active Task Notice -->
        <div
            v-if="activePickup"
            class="mb-6 flex flex-col justify-between gap-3 rounded-[20px] border-2 border-b-[4px] border-sky-blue/40 bg-sky-tint p-5 sm:flex-row sm:items-center"
        >
            <div class="flex items-start gap-3">
                <div
                    class="flex size-11 shrink-0 items-center justify-center rounded-2xl border-2 border-sky-blue/40 bg-paper-white"
                >
                    <Truck class="size-5 text-sky-blue-dark" />
                </div>
                <div class="min-w-0">
                    <p class="text-[14px] font-extrabold text-night-ink">
                        Anda sedang menjalankan tugas #{{ activePickup.id }}
                    </p>
                    <p class="mt-0.5 text-[12px] font-medium text-charcoal">
                        {{ activePickup.status_label ?? 'Sedang berjalan' }} ·
                        {{ activePickup.customer_name ?? 'Customer' }} ·
                        {{ activePickup.address }}
                    </p>
                    <p
                        class="mt-1 text-[11px] font-semibold text-sky-blue-dark"
                    >
                        Selesaikan tugas ini sebelum mengambil penjemputan
                        berikutnya.
                    </p>
                </div>
            </div>

            <Link
                :href="`${INDEX_URL}/${activePickup.id}`"
                class="inline-flex h-[42px] shrink-0 items-center justify-center gap-2 rounded-xl border-2 border-b-[4px] border-sky-blue-dark bg-sky-blue px-4 text-[12px] font-extrabold text-night-ink transition-all hover:brightness-105 active:translate-y-0.5 active:border-b-2"
            >
                Lanjutkan Tugas
                <ArrowRight class="size-4" />
            </Link>
        </div>

        <PickupHistoryTable
            :pickups="pickups"
            :has-filter="hasFilter"
            allow-claim
            :claim-disabled="!!activePickup"
            show-customer
            :show-driver="false"
            empty-title="Belum ada tugas penjemputan"
            empty-description="Antrean pengajuan customer akan muncul di sini dan siap Anda ambil."
            @view="openDetail"
            @claim="claimTask"
            @reset-filter="resetFilters"
        />
    </DashboardLayout>
</template>
