<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import DashboardLayout from '@/layouts/DashboardLayout.vue';
import PageHeader from '@/components/Dashboard/PageHeader.vue';
import FlashMessages from '@/components/Dashboard/FlashMessages.vue';
import PickupDetailCard from '@/components/Pickup/PickupDetailCard.vue';
import PickupPhotoUpload from '@/components/Pickup/PickupPhotoUpload.vue';
import {
    getCurrentCoords,
    useDriverLocation,
} from '@/composables/useDriverLocation';
import type { ActivePickupInfo, PickupSummary } from '@/types/pickup';
import { formatPickupDate, formatPoints, formatWeight } from '@/lib/pickup';
import {
    ArrowLeft,
    ArrowRight,
    CheckCircle2,
    Navigation,
    Scale,
    Truck,
} from 'lucide-vue-next';

const props = defineProps<{
    pickup: PickupSummary;
    activePickup?: ActivePickupInfo | null;
    driverLocation: {
        latitude: number | null;
        longitude: number | null;
    };
    basemapApiKey?: string;
}>();

const isProcessing = ref(false);

const form = useForm({
    details: (props.pickup.details ?? []).map((detail) => ({
        id: detail.id,
        weight: detail.weight !== null ? String(detail.weight) : '',
    })),
    pickup_proof_photo: null as File | null,
});

const hasOtherActivePickup = computed(() => props.activePickup !== null);

const canClaim = computed(
    () =>
        props.pickup.status === 'pending' &&
        !props.pickup.driver_id &&
        !hasOtherActivePickup.value,
);
const canMarkArrived = computed(() => props.pickup.status === 'on_the_way');
const canComplete = computed(() => props.pickup.status === 'picked_up');
const isCompleted = computed(() => props.pickup.status === 'completed');

/**
 * Berbagi lokasi otomatis hanya selama driver menuju lokasi atau menimbang.
 */
const shouldTrackLocation = computed(() =>
    ['on_the_way', 'picked_up'].includes(props.pickup.status),
);

const {
    isTracking: isLocationTracking,
    isSending: isLocationSending,
    lastSentAt: lastLocationSentAt,
    error: locationError,
    start: startLocation,
    stop: stopLocation,
    sendOnce: sendLocationOnce,
} = useDriverLocation({
    minDistanceMeters: 30,
    heartbeatMs: 60_000,
});

watch(
    shouldTrackLocation,
    (active) => {
        if (active) {
            startLocation();
        } else {
            stopLocation();
        }
    },
    { immediate: true },
);

const previewRows = computed(() =>
    (props.pickup.details ?? []).map((detail, index) => {
        const input = form.details[index];
        const weight = Number(input?.weight ?? 0);
        const subtotal = Number.isNaN(weight)
            ? 0
            : Math.round(weight * Number(detail.points_per_kg));

        return {
            id: detail.id,
            name: detail.waste_category?.name ?? 'Sampah',
            pointsPerKg: Number(detail.points_per_kg),
            weight,
            subtotal,
        };
    }),
);

const previewTotal = computed(() =>
    previewRows.value.reduce((total, row) => total + row.subtotal, 0),
);

function shareLocation() {
    void sendLocationOnce();
}

async function claimTask() {
    isProcessing.value = true;

    const coords = await getCurrentCoords();

    router.post(`/driver/pickups/${props.pickup.id}/claim`, coords ?? {}, {
        preserveScroll: true,
        onFinish: () => {
            isProcessing.value = false;
        },
    });
}

async function markArrived() {
    isProcessing.value = true;

    const coords = await getCurrentCoords();

    router.post(`/driver/pickups/${props.pickup.id}/picked-up`, coords ?? {}, {
        preserveScroll: true,
        onFinish: () => {
            isProcessing.value = false;
        },
    });
}

function completePickup() {
    form.post(`/driver/pickups/${props.pickup.id}/complete`, {
        forceFormData: true,
        preserveScroll: true,
    });
}
</script>

<template>
    <Head :title="`Tugas #${pickup.id} - Driver ReValue`" />

    <DashboardLayout>
        <Link
            href="/driver/pickups"
            class="mb-4 inline-flex w-fit items-center gap-1.5 text-[13px] font-extrabold tracking-wide text-charcoal uppercase transition-colors hover:text-eco-green-dark"
        >
            <ArrowLeft class="size-4" />
            Kembali ke daftar tugas
        </Link>

        <PageHeader
            :title="`Tugas Penjemputan #${pickup.id}`"
            subtitle="Navigasi ke lokasi, timbang sampah, dan unggah bukti penjemputan."
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

        <FlashMessages class="mb-6" />

        <div class="grid grid-cols-1 gap-5 xl:grid-cols-3">
            <!-- Detail -->
            <div class="xl:col-span-2">
                <PickupDetailCard
                    :pickup="pickup"
                    :basemap-api-key="basemapApiKey"
                    :driver-location="driverLocation"
                    show-customer
                />
            </div>

            <!-- Actions -->
            <aside class="flex flex-col gap-5">
                <!-- Automatic Location Sharing -->
                <div
                    v-if="shouldTrackLocation"
                    class="rounded-[20px] border-2 border-b-[4px] border-sky-blue/40 bg-sky-tint p-5"
                >
                    <h2
                        class="flex items-center gap-2 text-[13px] font-extrabold tracking-wider text-charcoal uppercase"
                    >
                        <Navigation class="size-4 text-sky-blue-dark" />
                        Berbagi Lokasi
                    </h2>

                    <p
                        class="mt-1 text-[12px] leading-relaxed font-medium text-charcoal"
                    >
                        Lokasi armada terkirim otomatis setiap Anda bergerak ±30
                        meter, dengan pembaruan berkala tiap 60 detik.
                    </p>

                    <div class="mt-3 flex flex-col gap-1.5">
                        <span
                            v-if="isLocationTracking"
                            class="inline-flex items-center gap-1.5 text-[12px] font-extrabold text-eco-green-dark"
                        >
                            <span class="relative flex size-2">
                                <span
                                    class="absolute inline-flex size-full animate-ping rounded-full bg-eco-green opacity-75"
                                />
                                <span
                                    class="relative inline-flex size-2 rounded-full bg-eco-green"
                                />
                            </span>
                            Berbagi lokasi otomatis aktif
                        </span>

                        <span
                            v-if="lastLocationSentAt"
                            class="text-[11px] font-semibold text-charcoal"
                        >
                            Terakhir terkirim
                            {{
                                formatPickupDate(
                                    lastLocationSentAt.toISOString(),
                                    true,
                                )
                            }}
                        </span>

                        <span
                            v-if="locationError"
                            class="text-[11px] font-semibold text-red-500"
                        >
                            {{ locationError }}
                        </span>
                    </div>

                    <button
                        type="button"
                        class="mt-3 flex h-[40px] w-full cursor-pointer items-center justify-center gap-2 rounded-xl border-2 border-b-[3px] border-border-gray bg-paper-white text-[12px] font-extrabold text-night-ink transition-all hover:bg-cloud-surface active:translate-y-0.5 active:border-b-2 disabled:cursor-not-allowed disabled:opacity-60"
                        :disabled="isLocationSending"
                        @click="shareLocation"
                    >
                        <Navigation class="size-3.5" />
                        {{
                            isLocationSending
                                ? 'Mengirim...'
                                : 'Kirim Lokasi Sekarang'
                        }}
                    </button>
                </div>

                <!-- Another Active Task Notice -->
                <div
                    v-if="hasOtherActivePickup && pickup.status === 'pending'"
                    class="rounded-[20px] border-2 border-b-[4px] border-sunlight-gold/50 bg-gold-tint p-5"
                >
                    <h2
                        class="flex items-center gap-2 text-[13px] font-extrabold tracking-wider text-charcoal uppercase"
                    >
                        <Truck class="size-4 text-sunlight-gold" />
                        Selesaikan Tugas Aktif Dulu
                    </h2>
                    <p
                        class="mt-1 text-[12px] leading-relaxed font-medium text-charcoal"
                    >
                        Anda masih menjalankan penjemputan
                        <span class="font-extrabold text-night-ink">
                            #{{ activePickup?.id }}
                        </span>
                        ({{ activePickup?.status_label ?? 'sedang berjalan' }}).
                        Satu driver hanya dapat menangani satu penjemputan dalam
                        satu waktu.
                    </p>
                    <Link
                        v-if="activePickup"
                        :href="`/driver/pickups/${activePickup.id}`"
                        class="mt-3 inline-flex h-[42px] w-full items-center justify-center gap-2 rounded-xl border-2 border-b-[4px] border-sunlight-gold bg-paper-white text-[12px] font-extrabold text-night-ink transition-all hover:brightness-105 active:translate-y-0.5 active:border-b-2"
                    >
                        Lanjutkan Tugas #{{ activePickup.id }}
                        <ArrowRight class="size-4" />
                    </Link>
                </div>

                <!-- Claim -->
                <div
                    v-if="canClaim"
                    class="rounded-[20px] border-2 border-b-[4px] border-eco-green/40 bg-paper-white p-5"
                >
                    <h2
                        class="flex items-center gap-2 text-[15px] font-extrabold text-night-ink"
                    >
                        <Truck class="size-4 text-eco-green-dark" />
                        Ambil Tugas Ini
                    </h2>
                    <p
                        class="mt-1 text-[12px] leading-relaxed font-medium text-charcoal"
                    >
                        Setelah diambil, customer dapat melihat bahwa driver
                        sedang menuju lokasi.
                    </p>
                    <button
                        type="button"
                        class="mt-4 flex h-[46px] w-full cursor-pointer items-center justify-center gap-2 rounded-xl border-2 border-b-[4px] border-eco-green-dark bg-eco-green text-[13px] font-extrabold text-night-ink transition-all hover:brightness-105 active:translate-y-0.5 active:border-b-2 disabled:cursor-not-allowed disabled:opacity-60"
                        :disabled="isProcessing"
                        @click="claimTask"
                    >
                        <Navigation class="size-4" />
                        {{ isProcessing ? 'Memproses...' : 'Ambil Tugas' }}
                    </button>
                </div>

                <!-- Arrived -->
                <div
                    v-else-if="canMarkArrived"
                    class="rounded-[20px] border-2 border-b-[4px] border-sky-blue/40 bg-paper-white p-5"
                >
                    <h2
                        class="flex items-center gap-2 text-[15px] font-extrabold text-night-ink"
                    >
                        <Navigation class="size-4 text-sky-blue-dark" />
                        Sudah Sampai?
                    </h2>
                    <p
                        class="mt-1 text-[12px] leading-relaxed font-medium text-charcoal"
                    >
                        Tandai tiba di lokasi agar customer tahu sampah akan
                        segera ditimbang.
                    </p>
                    <button
                        type="button"
                        class="mt-4 flex h-[46px] w-full cursor-pointer items-center justify-center gap-2 rounded-xl border-2 border-b-[4px] border-sky-blue-dark bg-sky-blue text-[13px] font-extrabold text-night-ink transition-all hover:brightness-105 active:translate-y-0.5 active:border-b-2 disabled:cursor-not-allowed disabled:opacity-60"
                        :disabled="isProcessing"
                        @click="markArrived"
                    >
                        <CheckCircle2 class="size-4" />
                        {{ isProcessing ? 'Memproses...' : 'Saya Sudah Tiba' }}
                    </button>
                </div>

                <!-- Completed -->
                <div
                    v-else-if="isCompleted"
                    class="rounded-[20px] border-2 border-b-[4px] border-eco-green/40 bg-sprout-tint p-5"
                >
                    <h2
                        class="flex items-center gap-2 text-[15px] font-extrabold text-night-ink"
                    >
                        <CheckCircle2 class="size-4 text-eco-green-dark" />
                        Penjemputan Selesai
                    </h2>
                    <p
                        class="mt-1 text-[12px] leading-relaxed font-medium text-charcoal"
                    >
                        Poin customer telah diterbitkan berdasarkan berat hasil
                        penimbangan.
                    </p>
                </div>

                <!-- Weighing Form -->
                <form
                    v-if="canComplete"
                    class="flex flex-col gap-4 rounded-[20px] border-2 border-b-[4px] border-border-gray bg-paper-white p-5"
                    @submit.prevent="completePickup"
                >
                    <div>
                        <h2
                            class="flex items-center gap-2 text-[15px] font-extrabold text-night-ink"
                        >
                            <Scale class="size-4 text-sunlight-gold" />
                            Penimbangan & Bukti
                        </h2>
                        <p
                            class="mt-1 text-[12px] leading-relaxed font-medium text-charcoal"
                        >
                            Isi berat aktual tiap jenis sampah, lalu unggah foto
                            bukti penjemputan.
                        </p>
                    </div>

                    <div class="flex flex-col gap-3">
                        <div
                            v-for="(row, index) in previewRows"
                            :key="row.id"
                            class="rounded-xl border border-border-gray/70 bg-cloud-surface p-3"
                        >
                            <div
                                class="flex items-center justify-between gap-2"
                            >
                                <span
                                    class="text-[13px] font-extrabold text-night-ink"
                                >
                                    {{ row.name }}
                                </span>
                                <span
                                    class="text-[11px] font-bold text-eco-green-dark"
                                >
                                    {{
                                        row.pointsPerKg.toLocaleString('id-ID')
                                    }}
                                    poin/kg
                                </span>
                            </div>

                            <div class="mt-2 flex items-center gap-2">
                                <input
                                    v-model="form.details[index].weight"
                                    type="number"
                                    min="0.1"
                                    step="0.01"
                                    placeholder="0.00"
                                    class="h-[42px] w-full rounded-xl border-2 border-border-gray bg-paper-white px-3 text-[14px] font-bold text-night-ink transition focus:border-eco-green focus:ring-4 focus:ring-eco-green/15 focus:outline-none"
                                />
                                <span
                                    class="shrink-0 text-[12px] font-bold text-charcoal"
                                >
                                    kg
                                </span>
                            </div>

                            <div
                                class="mt-1.5 flex items-center justify-between text-[11px] font-semibold text-charcoal"
                            >
                                <span>
                                    Subtotal:
                                    {{ formatPoints(row.subtotal) }}
                                </span>
                                <span>
                                    {{ formatWeight(row.weight) }}
                                </span>
                            </div>

                            <span
                                v-if="form.errors[`details.${index}.weight`]"
                                class="mt-1 block text-[11px] font-semibold text-red-500"
                            >
                                {{ form.errors[`details.${index}.weight`] }}
                            </span>
                        </div>
                    </div>

                    <div
                        class="flex items-center justify-between rounded-xl border border-eco-green/30 bg-sprout-tint px-4 py-3"
                    >
                        <span class="text-[12px] font-bold text-night-ink">
                            Total Poin Customer
                        </span>
                        <span
                            class="text-[15px] font-black text-eco-green-dark"
                        >
                            {{ formatPoints(previewTotal) }}
                        </span>
                    </div>

                    <PickupPhotoUpload
                        v-model="form.pickup_proof_photo"
                        label="Foto Bukti Penjemputan"
                        hint="Foto timbangan atau sampah yang sudah dijemput."
                        :error="form.errors.pickup_proof_photo"
                    />

                    <button
                        type="submit"
                        class="flex h-[46px] w-full cursor-pointer items-center justify-center gap-2 rounded-xl border-2 border-b-[4px] border-eco-green-dark bg-eco-green text-[13px] font-extrabold text-night-ink transition-all hover:brightness-105 active:translate-y-0.5 active:border-b-2 disabled:cursor-not-allowed disabled:opacity-60"
                        :disabled="form.processing"
                    >
                        <CheckCircle2 class="size-4" />
                        {{
                            form.processing
                                ? 'Menyimpan...'
                                : 'Selesaikan Penjemputan'
                        }}
                    </button>
                </form>
            </aside>
        </div>
    </DashboardLayout>
</template>
