<script setup lang="ts">
import { onMounted, onUnmounted, ref, watch } from 'vue';
import type { Map as LeafletMap, Marker as LeafletMarker } from 'leaflet';
import { LocateFixed, MapPin, Minus, Plus } from 'lucide-vue-next';
import { addCartoBasemap } from '@/lib/map/basemap';
import { createPinIcon } from '@/lib/map/markers';

const props = withDefaults(
    defineProps<{
        lat: number;
        lng: number;
        address: string;
        height?: string;
        basemapApiKey?: string;
        label?: string;
    }>(),
    { height: '460px', label: 'Lokasi Anda' },
);

const mapContainerId = `profile-hero-map-${Math.random().toString(36).slice(2, 9)}`;
let L: typeof import('leaflet') | null = null;
let map: LeafletMap | null = null;
let locationMarker: LeafletMarker | null = null;

function zoomIn() {
    map?.zoomIn();
}

function zoomOut() {
    map?.zoomOut();
}

function panToAddress() {
    if (map) map.flyTo([props.lat, props.lng], 15, { duration: 0.8 });
}

function popupContent(address: string) {
    return `<strong>📍 ${props.label}</strong><br/>${address}`;
}

onMounted(async () => {
    if (typeof window === 'undefined') return;

    try {
        const leafletModule = await import('leaflet');
        L = leafletModule.default || leafletModule;
        await import('leaflet/dist/leaflet.css');

        map = L.map(mapContainerId, {
            center: [props.lat, props.lng],
            zoom: 15,
            zoomControl: false,
            scrollWheelZoom: false,
            dragging: true,
            touchZoom: true,
            doubleClickZoom: true,
            attributionControl: false,
        });

        addCartoBasemap(L, map, props.basemapApiKey);

        locationMarker = L.marker([props.lat, props.lng], {
            icon: createPinIcon(L, { size: 42 }),
        })
            .addTo(map)
            .bindPopup(popupContent(props.address));
    } catch (err) {
        console.error('Gagal menginisialisasi Leaflet Map Profil:', err);
    }
});

watch(
    () => [props.lat, props.lng, props.address] as const,
    ([newLat, newLng, newAddr]) => {
        if (!locationMarker) return;
        if (typeof newLat !== 'number' || typeof newLng !== 'number') return;

        locationMarker.setLatLng([newLat, newLng]);
        locationMarker.setPopupContent(popupContent(newAddr));
        map?.panTo([newLat, newLng]);
    },
);

onUnmounted(() => {
    map?.remove();
    map = null;
    locationMarker = null;
});
</script>

<template>
    <div
        class="relative w-full overflow-hidden rounded-[24px] border-4 border-border-gray bg-cloud-surface shadow-[0px_6px_0px_0px_var(--color-border-gray)] sm:rounded-[28px]"
        :style="{ height }"
    >
        <!-- Map Canvas -->
        <div
            :id="mapContainerId"
            class="z-0 h-full w-full cursor-grab active:cursor-grabbing"
        />

        <!-- Overlay: Saved Address Card -->
        <div
            class="group absolute right-4 bottom-4 left-4 z-[1000] cursor-default rounded-2xl border-2 border-b-[5px] border-border-gray bg-paper-white p-4 shadow-[0_8px_20px_rgba(0,0,0,0.06)] transition-all duration-300 ease-out hover:-translate-y-1 hover:border-night-ink/30 hover:border-b-night-ink/50 hover:shadow-[0_16px_32px_-6px_rgba(0,4,55,0.18)] sm:right-auto sm:bottom-6 sm:left-6 sm:w-[390px] sm:p-5"
        >
            <div class="mb-1 flex items-center justify-between gap-2">
                <span
                    class="flex items-center gap-1.5 text-[12px] font-bold tracking-wider text-charcoal uppercase sm:text-[13px]"
                >
                    <MapPin class="size-3.5 stroke-[2.5] text-eco-green" />
                    {{ label }} :
                </span>
                <span
                    class="rounded-full border border-eco-green/30 bg-sprout-tint px-2.5 py-0.5 text-[11px] font-bold text-eco-green-dark"
                >
                    Titik Tersimpan
                </span>
            </div>

            <h3
                class="line-clamp-2 text-[15px] leading-snug font-extrabold text-night-ink sm:text-[18px] sm:leading-tight"
            >
                {{ address }}
            </h3>

            <p
                class="mt-3 border-t border-border-gray/50 pt-2 text-[12px] leading-relaxed text-charcoal"
            >
                Titik merah menandai lokasi alamat utama kamu.
            </p>
        </div>

        <!-- Overlay: Tactile Control Buttons -->
        <div
            class="absolute top-4 right-4 z-[1000] flex flex-col gap-2.5 sm:top-auto sm:right-6 sm:bottom-6 sm:gap-3"
        >
            <button
                type="button"
                class="flex size-[42px] cursor-pointer items-center justify-center rounded-xl border-2 border-b-[4px] border-sky-blue-dark bg-sky-blue shadow-xs transition-all duration-200 hover:scale-105 hover:brightness-105 active:translate-y-0.5 active:border-b-2 sm:size-[47px]"
                title="Pusatkan ke alamat saya"
                @click="panToAddress"
            >
                <LocateFixed
                    class="size-5 stroke-[2.2] text-night-ink sm:size-6"
                />
            </button>
            <button
                type="button"
                class="flex size-[42px] cursor-pointer items-center justify-center rounded-xl border-2 border-b-[4px] border-eco-green-dark bg-eco-green shadow-xs transition-all duration-200 hover:scale-105 hover:brightness-105 active:translate-y-0.5 active:border-b-2 sm:size-[47px]"
                title="Perbesar Peta"
                @click="zoomIn"
            >
                <Plus class="size-5 stroke-[2.5] text-night-ink sm:size-6" />
            </button>
            <button
                type="button"
                class="flex size-[42px] cursor-pointer items-center justify-center rounded-xl border-2 border-b-[4px] border-eco-green-dark bg-eco-green shadow-xs transition-all duration-200 hover:scale-105 hover:brightness-105 active:translate-y-0.5 active:border-b-2 sm:size-[47px]"
                title="Perkecil Peta"
                @click="zoomOut"
            >
                <Minus class="size-5 stroke-[2.5] text-night-ink sm:size-6" />
            </button>
        </div>
    </div>
</template>
