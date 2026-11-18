<script setup lang="ts">
import { onMounted, onUnmounted, ref, watch } from 'vue';
import type { Map as LeafletMap, Marker as LeafletMarker } from 'leaflet';
import { LocateFixed, Minus, Plus } from 'lucide-vue-next';
import { addCartoBasemap } from '@/lib/map/basemap';
import { createPinIcon } from '@/lib/map/markers';

const props = withDefaults(
    defineProps<{
        lat: number | string | null;
        lng: number | string | null;
        label?: string;
        height?: string;
        basemapApiKey?: string;
        zoom?: number;
    }>(),
    {
        label: 'Titik Penjemputan',
        height: '320px',
        zoom: 16,
    },
);

const mapContainerId = `pickup-location-map-${Math.random().toString(36).slice(2, 9)}`;
let L: typeof import('leaflet') | null = null;
let map: LeafletMap | null = null;
let marker: LeafletMarker | null = null;

function coordinates(): [number, number] | null {
    const lat = Number(props.lat);
    const lng = Number(props.lng);

    if (Number.isNaN(lat) || Number.isNaN(lng)) return null;

    return [lat, lng];
}

function zoomIn() {
    map?.zoomIn();
}

function zoomOut() {
    map?.zoomOut();
}

function recenter() {
    const coords = coordinates();
    if (map && coords) {
        map.flyTo(coords, props.zoom, { duration: 0.6 });
    }
}

onMounted(async () => {
    if (typeof window === 'undefined') return;

    const coords = coordinates();
    if (!coords) return;

    const leafletModule = await import('leaflet');
    L = leafletModule.default || leafletModule;
    await import('leaflet/dist/leaflet.css');

    map = L.map(mapContainerId, {
        center: coords,
        zoom: props.zoom,
        zoomControl: false,
        scrollWheelZoom: false,
        dragging: true,
        touchZoom: true,
        doubleClickZoom: true,
        attributionControl: false,
    });

    addCartoBasemap(L, map, props.basemapApiKey);

    marker = L.marker(coords, { icon: createPinIcon(L, { size: 42 }) })
        .addTo(map)
        .bindPopup(`<strong>📍 ${props.label}</strong>`);
});

watch(
    () => [props.lat, props.lng] as const,
    ([lat, lng]) => {
        const numericLat = Number(lat);
        const numericLng = Number(lng);

        if (Number.isNaN(numericLat) || Number.isNaN(numericLng)) return;

        if (marker) {
            marker.setLatLng([numericLat, numericLng]);
        }

        map?.panTo([numericLat, numericLng]);
    },
);

onUnmounted(() => {
    map?.remove();
    map = null;
    marker = null;
});
</script>

<template>
    <div
        class="relative w-full overflow-hidden rounded-[20px] border-4 border-border-gray bg-cloud-surface shadow-[0px_6px_0px_0px_var(--color-border-gray)]"
        :style="{ height }"
    >
        <div
            :id="mapContainerId"
            class="z-0 h-full w-full cursor-grab active:cursor-grabbing"
        />

        <div class="absolute top-3 right-3 z-[1000] flex flex-col gap-2">
            <button
                type="button"
                class="flex size-10 cursor-pointer items-center justify-center rounded-xl border-2 border-b-[4px] border-sky-blue-dark bg-sky-blue shadow-xs transition-all hover:brightness-105 active:translate-y-0.5 active:border-b-2"
                title="Pusatkan ke titik jemput"
                @click="recenter"
            >
                <LocateFixed class="size-5 text-night-ink" />
            </button>
            <button
                type="button"
                class="flex size-10 cursor-pointer items-center justify-center rounded-xl border-2 border-b-[4px] border-eco-green-dark bg-eco-green shadow-xs transition-all hover:brightness-105 active:translate-y-0.5 active:border-b-2"
                title="Perbesar Peta"
                @click="zoomIn"
            >
                <Plus class="size-5 text-night-ink" />
            </button>
            <button
                type="button"
                class="flex size-10 cursor-pointer items-center justify-center rounded-xl border-2 border-b-[4px] border-eco-green-dark bg-eco-green shadow-xs transition-all hover:brightness-105 active:translate-y-0.5 active:border-b-2"
                title="Perkecil Peta"
                @click="zoomOut"
            >
                <Minus class="size-5 text-night-ink" />
            </button>
        </div>
    </div>
</template>
