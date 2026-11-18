<script setup lang="ts">
import { computed } from 'vue';
import {
    CalendarClock,
    CheckCircle2,
    Clock,
    MapPin,
    Package,
    Scale,
    Truck,
    User,
    XCircle,
} from 'lucide-vue-next';
import UserAvatar from '@/components/Dashboard/UserAvatar.vue';
import PickupStatusChip from './PickupStatusChip.vue';
import PickupLocationMap from './PickupLocationMap.vue';
import PickupRouteMap from './PickupRouteMap.vue';
import PickupPointsBreakdown from './PickupPointsBreakdown.vue';
import type { PickupSummary } from '@/types/pickup';
import {
    formatPickupDate,
    pickupHasDriver,
    pickupPhotoUrl,
    pickupProofPhotoUrl,
    pickupStatusDescription,
    pickupStatusLabel,
} from '@/lib/pickup';

const props = withDefaults(
    defineProps<{
        pickup: PickupSummary;
        basemapApiKey?: string;
        driverLocation?: {
            latitude: number | null;
            longitude: number | null;
        } | null;
        showCustomer?: boolean;
        showDriver?: boolean;
        showMap?: boolean;
    }>(),
    {
        driverLocation: null,
        showCustomer: false,
        showDriver: true,
        showMap: true,
    },
);

const requestPhoto = computed(() => pickupPhotoUrl(props.pickup));
const proofPhoto = computed(() => pickupProofPhotoUrl(props.pickup));

const driverUser = computed(() => props.pickup.driver?.user ?? null);
const customerUser = computed(() => props.pickup.customer?.user ?? null);

const hasDriverAssigned = computed(() => pickupHasDriver(props.pickup.status));

/**
 * Peta tidak diperlukan lagi setelah penjemputan selesai.
 */
const shouldShowMap = computed(
    () => props.showMap && props.pickup.status !== 'completed',
);

const driverLat = computed(() => {
    if (
        props.driverLocation?.latitude !== null &&
        props.driverLocation?.latitude !== undefined
    ) {
        return props.driverLocation.latitude;
    }

    return props.pickup.driver?.current_latitude ?? null;
});

const driverLng = computed(() => {
    if (
        props.driverLocation?.longitude !== null &&
        props.driverLocation?.longitude !== undefined
    ) {
        return props.driverLocation.longitude;
    }

    return props.pickup.driver?.current_longitude ?? null;
});

const isCanceled = computed(() => props.pickup.status === 'canceled');

const steps = computed(() => {
    const status = props.pickup.status;
    const order = ['pending', 'on_the_way', 'picked_up', 'completed'];
    const currentIndex = order.indexOf(status);

    return [
        { key: 'pending', label: 'Pengajuan dikirim' },
        { key: 'on_the_way', label: 'Driver menuju lokasi' },
        { key: 'picked_up', label: 'Sampah ditimbang' },
        { key: 'completed', label: 'Poin diterbitkan' },
    ].map((step, index) => ({
        ...step,
        done: currentIndex >= index && currentIndex !== -1,
        active: currentIndex === index,
    }));
});

const categoryChips = computed(() =>
    (props.pickup.details ?? []).map((detail) => ({
        id: detail.id,
        name: detail.waste_category?.name ?? 'Sampah',
        points: Number(detail.points_per_kg ?? 0),
    })),
);

const hasWeights = computed(() =>
    (props.pickup.details ?? []).some((detail) => detail.weight !== null),
);
</script>

<template>
    <div class="flex flex-col gap-5">
        <!-- Status Header -->
        <div
            class="rounded-[20px] border-2 border-b-[4px] border-border-gray bg-paper-white p-5 sm:p-6"
        >
            <div
                class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between"
            >
                <div class="flex flex-col gap-1.5">
                    <div class="flex flex-wrap items-center gap-2">
                        <PickupStatusChip :status="pickup.status" />
                        <span class="text-[12px] font-bold text-charcoal/70">
                            #{{ pickup.id }}
                        </span>
                    </div>
                    <h2
                        class="text-[20px] leading-tight font-black tracking-tight text-night-ink sm:text-[24px]"
                    >
                        {{ pickupStatusLabel(pickup.status) }}
                    </h2>
                    <p
                        class="max-w-[560px] text-[13px] font-medium text-charcoal"
                    >
                        {{ pickupStatusDescription(pickup.status) }}
                    </p>
                </div>

                <div
                    class="flex shrink-0 flex-col gap-1 text-[12px] font-semibold text-charcoal"
                >
                    <span class="inline-flex items-center gap-1.5">
                        <CalendarClock class="size-3.5 text-charcoal/70" />
                        Diajukan {{ formatPickupDate(pickup.created_at, true) }}
                    </span>
                    <span
                        v-if="pickup.pickup_at"
                        class="inline-flex items-center gap-1.5"
                    >
                        <CheckCircle2 class="size-3.5 text-eco-green-dark" />
                        Selesai {{ formatPickupDate(pickup.pickup_at, true) }}
                    </span>
                </div>
            </div>

            <!-- Progress Steps -->
            <div
                v-if="!isCanceled"
                class="mt-5 flex flex-col gap-2 sm:flex-row"
            >
                <div
                    v-for="step in steps"
                    :key="step.key"
                    class="flex flex-1 items-center gap-2 rounded-xl border px-3 py-2"
                    :class="
                        step.done
                            ? 'border-eco-green/40 bg-sprout-tint'
                            : 'border-border-gray bg-cloud-surface/60'
                    "
                >
                    <span
                        class="flex size-6 shrink-0 items-center justify-center rounded-full border-2 text-[11px] font-extrabold"
                        :class="
                            step.done
                                ? 'border-eco-green-dark bg-eco-green text-night-ink'
                                : 'border-border-gray bg-paper-white text-charcoal/60'
                        "
                    >
                        <CheckCircle2
                            v-if="step.done"
                            class="size-3.5"
                            stroke-width="3"
                        />
                        <Clock v-else class="size-3.5" />
                    </span>
                    <span
                        class="text-[12px] font-bold"
                        :class="
                            step.done ? 'text-night-ink' : 'text-charcoal/70'
                        "
                    >
                        {{ step.label }}
                    </span>
                </div>
            </div>

            <div
                v-else
                class="mt-5 flex items-center gap-2 rounded-xl border-2 border-border-gray bg-cloud-surface px-3 py-2 text-[12px] font-bold text-charcoal"
            >
                <XCircle class="size-4 text-charcoal/70" />
                Pengajuan ini dibatalkan sebelum driver ditugaskan.
            </div>
        </div>

        <div class="grid grid-cols-1 gap-5 xl:grid-cols-3">
            <!-- Left: Map + Request Data -->
            <div class="flex flex-col gap-5 xl:col-span-2">
                <div
                    v-if="shouldShowMap"
                    class="rounded-[20px] border-2 border-b-[4px] border-border-gray bg-paper-white p-5 sm:p-6"
                >
                    <div
                        class="mb-3 flex flex-wrap items-center justify-between gap-2"
                    >
                        <h3
                            class="flex items-center gap-2 text-[15px] font-extrabold text-night-ink"
                        >
                            <MapPin class="size-4 text-eco-green-dark" />
                            Lokasi Penjemputan
                        </h3>
                        <span
                            class="text-[11px] font-bold tracking-wider text-charcoal uppercase"
                        >
                            {{
                                hasDriverAssigned
                                    ? 'Rute OSRM Driver'
                                    : 'Titik Jemput'
                            }}
                        </span>
                    </div>

                    <PickupRouteMap
                        v-if="hasDriverAssigned"
                        :origin-lat="driverLat"
                        :origin-lng="driverLng"
                        :destination-lat="pickup.latitude ?? 0"
                        :destination-lng="pickup.longitude ?? 0"
                        :origin-label="driverUser?.name ?? 'Driver'"
                        :basemap-api-key="basemapApiKey"
                        height="340px"
                    />
                    <PickupLocationMap
                        v-else
                        :lat="pickup.latitude"
                        :lng="pickup.longitude"
                        :basemap-api-key="basemapApiKey"
                        height="340px"
                    />

                    <p
                        class="mt-3 rounded-xl border border-border-gray/70 bg-cloud-surface px-4 py-3 text-[13px] leading-relaxed font-medium text-night-ink"
                    >
                        {{ pickup.address }}
                    </p>
                </div>

                <!-- Request Data -->
                <div
                    class="rounded-[20px] border-2 border-b-[4px] border-border-gray bg-paper-white p-5 sm:p-6"
                >
                    <h3
                        class="mb-4 flex items-center gap-2 text-[15px] font-extrabold text-night-ink"
                    >
                        <Package class="size-4 text-sky-blue-dark" />
                        Data Pengajuan
                    </h3>

                    <div class="flex flex-col gap-4">
                        <!-- Alamat tetap tampil saat peta disembunyikan (status selesai) -->
                        <div v-if="!shouldShowMap">
                            <p
                                class="mb-1 flex items-center gap-1.5 text-[11px] font-bold tracking-wider text-charcoal uppercase"
                            >
                                <MapPin class="size-3.5" />
                                Alamat Penjemputan
                            </p>
                            <p
                                class="rounded-xl border border-border-gray/70 bg-cloud-surface px-4 py-3 text-[13px] leading-relaxed font-medium text-night-ink"
                            >
                                {{ pickup.address }}
                            </p>
                        </div>

                        <div>
                            <p
                                class="mb-2 text-[11px] font-bold tracking-wider text-charcoal uppercase"
                            >
                                Jenis Sampah
                            </p>
                            <div class="flex flex-wrap gap-2">
                                <span
                                    v-for="chip in categoryChips"
                                    :key="chip.id"
                                    class="inline-flex items-center gap-1.5 rounded-full border border-eco-green/30 bg-sprout-tint px-2.5 py-1 text-[12px] font-bold text-night-ink"
                                >
                                    {{ chip.name }}
                                    <span
                                        class="text-[11px] font-semibold text-eco-green-dark"
                                    >
                                        {{
                                            chip.points.toLocaleString('id-ID')
                                        }}
                                        poin/kg
                                    </span>
                                </span>
                                <span
                                    v-if="categoryChips.length === 0"
                                    class="text-[13px] text-charcoal italic"
                                >
                                    Tidak ada jenis sampah tercatat.
                                </span>
                            </div>
                        </div>

                        <div v-if="pickup.notes">
                            <p
                                class="mb-1 text-[11px] font-bold tracking-wider text-charcoal uppercase"
                            >
                                Catatan Customer
                            </p>
                            <p
                                class="rounded-xl border border-border-gray/70 bg-cloud-surface px-4 py-3 text-[13px] leading-relaxed text-night-ink"
                            >
                                {{ pickup.notes }}
                            </p>
                        </div>

                        <div v-if="requestPhoto">
                            <p
                                class="mb-2 text-[11px] font-bold tracking-wider text-charcoal uppercase"
                            >
                                Foto Bukti Sampah
                            </p>
                            <a
                                :href="requestPhoto"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="block overflow-hidden rounded-2xl border-2 border-border-gray"
                            >
                                <img
                                    :src="requestPhoto"
                                    alt="Foto bukti sampah"
                                    class="max-h-[280px] w-full object-cover transition-transform duration-200 hover:scale-[1.02]"
                                />
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Driver / Customer / Result -->
            <div class="flex flex-col gap-5">
                <!-- Customer -->
                <div
                    v-if="showCustomer && customerUser"
                    class="rounded-[20px] border-2 border-b-[4px] border-border-gray bg-paper-white p-5"
                >
                    <h3
                        class="mb-3 flex items-center gap-2 text-[13px] font-extrabold tracking-wider text-charcoal uppercase"
                    >
                        <User class="size-4" />
                        Customer
                    </h3>
                    <div class="flex items-center gap-3">
                        <UserAvatar
                            :photo="customerUser.photo"
                            :role="customerUser.role"
                            :name="customerUser.name"
                            size="lg"
                        />
                        <div class="min-w-0">
                            <p
                                class="truncate text-[14px] font-extrabold text-night-ink"
                            >
                                {{ customerUser.name }}
                            </p>
                            <p
                                class="truncate text-[12px] font-medium text-charcoal"
                            >
                                {{
                                    customerUser.phone_number ??
                                    customerUser.email
                                }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Driver -->
                <div
                    v-if="showDriver"
                    class="rounded-[20px] border-2 border-b-[4px] border-border-gray bg-paper-white p-5"
                >
                    <h3
                        class="mb-3 flex items-center gap-2 text-[13px] font-extrabold tracking-wider text-charcoal uppercase"
                    >
                        <Truck class="size-4" />
                        Driver
                    </h3>

                    <div v-if="driverUser" class="flex flex-col gap-2">
                        <div class="flex items-center gap-3">
                            <UserAvatar
                                :photo="driverUser.photo"
                                :role="driverUser.role"
                                :name="driverUser.name"
                                size="lg"
                            />
                            <div class="min-w-0">
                                <p
                                    class="truncate text-[14px] font-extrabold text-night-ink"
                                >
                                    {{ driverUser.name }}
                                </p>
                                <p
                                    class="truncate text-[12px] font-medium text-charcoal"
                                >
                                    {{
                                        driverUser.phone_number ??
                                        driverUser.email
                                    }}
                                </p>
                            </div>
                        </div>
                        <p
                            v-if="pickup.driver?.location_updated_at"
                            class="text-[11px] font-semibold text-charcoal/80"
                        >
                            Lokasi diperbarui
                            {{
                                formatPickupDate(
                                    pickup.driver.location_updated_at,
                                    true,
                                )
                            }}
                        </p>
                    </div>

                    <p
                        v-else
                        class="rounded-xl border border-border-gray/70 bg-cloud-surface px-4 py-3 text-[13px] font-medium text-charcoal italic"
                    >
                        Belum ada driver yang ditugaskan.
                    </p>
                </div>

                <!-- Result -->
                <div
                    v-if="pickup.status === 'completed'"
                    class="flex flex-col gap-4 rounded-[20px] border-2 border-b-[4px] border-eco-green/40 bg-paper-white p-5"
                >
                    <h3
                        class="flex items-center gap-2 text-[13px] font-extrabold tracking-wider text-charcoal uppercase"
                    >
                        <Scale class="size-4" />
                        Hasil Penimbangan
                    </h3>

                    <PickupPointsBreakdown
                        v-if="hasWeights && pickup.details?.length"
                        :details="pickup.details"
                    />

                    <div v-if="proofPhoto">
                        <p
                            class="mb-2 text-[11px] font-bold tracking-wider text-charcoal uppercase"
                        >
                            Bukti Penjemputan Driver
                        </p>
                        <a
                            :href="proofPhoto"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="block overflow-hidden rounded-2xl border-2 border-border-gray"
                        >
                            <img
                                :src="proofPhoto"
                                alt="Bukti penjemputan"
                                class="max-h-[260px] w-full object-cover transition-transform duration-200 hover:scale-[1.02]"
                            />
                        </a>
                    </div>
                </div>

                <div
                    v-else-if="pickup.status === 'picked_up'"
                    class="rounded-[20px] border-2 border-b-[4px] border-sunlight-gold/50 bg-gold-tint p-5"
                >
                    <h3 class="text-[14px] font-extrabold text-night-ink">
                        Sampah sedang ditimbang
                    </h3>
                    <p
                        class="mt-1 text-[12px] leading-relaxed font-medium text-charcoal"
                    >
                        Poin akan otomatis masuk ke akun Anda setelah driver
                        menyelesaikan penimbangan dan mengunggah bukti.
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
