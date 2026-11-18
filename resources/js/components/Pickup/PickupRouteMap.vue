<script setup lang="ts">
import { computed } from 'vue';
import { MapPinOff } from 'lucide-vue-next';
import RouteViewer from '@/components/Map/RouteViewer.vue';

const props = withDefaults(
    defineProps<{
        originLat?: number | string | null;
        originLng?: number | string | null;
        destinationLat: number | string;
        destinationLng: number | string;
        originLabel?: string;
        destinationLabel?: string;
        height?: string;
        basemapApiKey?: string;
    }>(),
    {
        originLat: null,
        originLng: null,
        originLabel: 'Posisi Driver',
        destinationLabel: 'Titik Jemput',
        height: '340px',
    },
);

const hasOrigin = computed(
    () =>
        Number.isFinite(Number(props.originLat)) &&
        Number.isFinite(Number(props.originLng)) &&
        props.originLat !== null &&
        props.originLng !== null,
);

const origin = computed(() => ({
    lat: Number(props.originLat),
    lng: Number(props.originLng),
    label: props.originLabel,
}));

const destination = computed(() => ({
    lat: Number(props.destinationLat),
    lng: Number(props.destinationLng),
    label: props.destinationLabel,
}));
</script>

<template>
    <div
        v-if="hasOrigin"
        class="overflow-hidden rounded-[20px] border-4 border-border-gray bg-cloud-surface shadow-[0px_6px_0px_0px_var(--color-border-gray)]"
    >
        <RouteViewer
            :origin="origin"
            :destination="destination"
            :height="height"
            :basemap-api-key="basemapApiKey"
        />
    </div>

    <div
        v-else
        class="flex flex-col items-center justify-center gap-2 rounded-[20px] border-2 border-dashed border-border-gray bg-paper-white p-8 text-center"
    >
        <div
            class="flex size-12 items-center justify-center rounded-2xl bg-cloud-surface"
        >
            <MapPinOff class="size-6 text-charcoal/60" />
        </div>
        <p class="text-[14px] font-bold text-night-ink">
            Posisi driver belum tersedia
        </p>
        <p class="max-w-[360px] text-[12px] leading-relaxed text-charcoal">
            Rute OSRM akan tampil otomatis setelah driver membagikan lokasi
            armadanya.
        </p>
    </div>
</template>
