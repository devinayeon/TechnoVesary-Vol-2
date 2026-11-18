<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import DashboardLayout from '@/layouts/DashboardLayout.vue';
import PageHeader from '@/components/Dashboard/PageHeader.vue';
import FlashMessages from '@/components/Dashboard/FlashMessages.vue';
import ConfirmModal from '@/components/Dashboard/ConfirmModal.vue';
import PickupDetailCard from '@/components/Pickup/PickupDetailCard.vue';
import type { PickupSummary } from '@/types/pickup';
import { pickupIsCancelable } from '@/lib/pickup';
import { ArrowLeft, RefreshCw, Truck, XCircle } from 'lucide-vue-next';

const props = defineProps<{
    pickup: PickupSummary;
    basemapApiKey?: string;
}>();

const canCancel = computed(() => pickupIsCancelable(props.pickup.status));
const isCancelling = ref(false);
const showCancelModal = ref(false);

/**
 * Selama driver menuju lokasi / menimbang, posisi driver berubah.
 * Halaman detail menyegarkan data secara berkala agar rute OSRM mengikuti.
 */
const isTrackingActive = computed(() =>
    ['on_the_way', 'picked_up'].includes(props.pickup.status),
);

const isRefreshing = ref(false);
let pollTimer: ReturnType<typeof setInterval> | null = null;

function refreshPickup() {
    if (isRefreshing.value) return;

    isRefreshing.value = true;

    router.reload({
        only: ['pickup'],
        onFinish: () => {
            isRefreshing.value = false;
        },
    });
}

function startPolling() {
    stopPolling();

    if (!isTrackingActive.value) return;

    pollTimer = setInterval(() => {
        if (typeof document !== 'undefined' && document.hidden) return;
        refreshPickup();
    }, 20_000);
}

function stopPolling() {
    if (pollTimer) {
        clearInterval(pollTimer);
        pollTimer = null;
    }
}

watch(isTrackingActive, startPolling);
onMounted(startPolling);
onUnmounted(stopPolling);

function confirmCancel() {
    isCancelling.value = true;

    router.post(
        `/customer/pickups/${props.pickup.id}/cancel`,
        {},
        {
            preserveScroll: true,
            onFinish: () => {
                isCancelling.value = false;
                showCancelModal.value = false;
            },
        },
    );
}
</script>

<template>
    <Head :title="`Penjemputan #${pickup.id} - ReValue`" />

    <DashboardLayout>
        <Link
            href="/customer/pickups"
            class="mb-4 inline-flex w-fit items-center gap-1.5 text-[13px] font-extrabold tracking-wide text-charcoal uppercase transition-colors hover:text-eco-green-dark"
        >
            <ArrowLeft class="size-4" />
            Kembali ke riwayat
        </Link>

        <PageHeader
            :title="`Detail Penjemputan #${pickup.id}`"
            subtitle="Pantau status penjemputan, rute driver, dan poin yang Anda dapatkan."
            badge="Penjemputan"
        >
            <template #action>
                <div class="flex flex-wrap items-center gap-2">
                    <button
                        v-if="canCancel"
                        type="button"
                        class="inline-flex h-[46px] cursor-pointer items-center gap-2 rounded-xl border-2 border-b-[4px] border-border-gray bg-paper-white px-4 text-[13px] font-extrabold text-red-500 transition-all hover:bg-red-50 active:translate-y-0.5 active:border-b-2"
                        @click="showCancelModal = true"
                    >
                        <XCircle class="size-4" />
                        Batalkan
                    </button>
                    <Link
                        v-if="canCancel"
                        href="/customer/pickups/create"
                        class="inline-flex h-[46px] items-center gap-2 rounded-xl border-2 border-b-[4px] border-eco-green-dark bg-eco-green px-5 text-[13px] font-extrabold text-night-ink transition-all hover:brightness-105 active:translate-y-0.5 active:border-b-2"
                    >
                        <Truck class="size-4" />
                        Ajukan Lagi
                    </Link>
                </div>
            </template>
        </PageHeader>

        <FlashMessages class="mb-6" />

        <div
            v-if="isTrackingActive"
            class="mb-4 flex flex-wrap items-center justify-between gap-2 rounded-2xl border-2 border-sky-blue/40 bg-sky-tint px-4 py-3"
        >
            <span
                class="flex items-center gap-2 text-[12px] font-bold text-night-ink"
            >
                <span class="relative flex size-2">
                    <span
                        class="absolute inline-flex size-full animate-ping rounded-full bg-sky-blue opacity-75"
                    />
                    <span
                        class="relative inline-flex size-2 rounded-full bg-sky-blue"
                    />
                </span>
                Posisi driver diperbarui otomatis setiap 20 detik.
            </span>

            <button
                type="button"
                class="inline-flex cursor-pointer items-center gap-1.5 rounded-lg px-2 py-1 text-[12px] font-extrabold text-sky-blue-dark transition-colors hover:bg-paper-white/70 disabled:cursor-not-allowed disabled:opacity-60"
                :disabled="isRefreshing"
                @click="refreshPickup"
            >
                <RefreshCw
                    class="size-3.5"
                    :class="{ 'animate-spin': isRefreshing }"
                />
                Perbarui sekarang
            </button>
        </div>

        <PickupDetailCard :pickup="pickup" :basemap-api-key="basemapApiKey" />

        <ConfirmModal
            :show="showCancelModal"
            title="Batalkan pengajuan penjemputan?"
            description="Pengajuan yang dibatalkan tidak dapat dikembalikan. Anda dapat membuat pengajuan baru kapan saja."
            confirm-label="Ya, Batalkan"
            tone="danger"
            :processing="isCancelling"
            @confirm="confirmCancel"
            @close="showCancelModal = false"
        />
    </DashboardLayout>
</template>
