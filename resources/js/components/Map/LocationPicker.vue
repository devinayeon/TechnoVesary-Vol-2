<script setup lang="ts">
/**
 * LocationPicker.vue — Komponen peta interaktif untuk penentuan titik lokasi.
 *
 * Fitur:
 * - Peta Leaflet (OpenStreetMap) dengan marker yang bisa digeser (draggable pin)
 * - Tombol "Gunakan Lokasi Saya" (HTML5 Geolocation API)
 * - Kolom pencarian alamat dengan debounce dan auto-complete (via /map/search)
 * - Auto reverse-geocoding saat marker digeser (via /map/reverse-geocode)
 * - Emit update:modelValue dengan format { lat, lng, address }
 */
import { onMounted, onUnmounted, ref, watch } from 'vue';
import type { Map as LeafletMap, Marker as LeafletMarker } from 'leaflet';
import { LocateFixed, Search } from 'lucide-vue-next';
import { addCartoBasemap } from '@/lib/map/basemap';
import { createPinIcon } from '@/lib/map/markers';

// --- Props & Emits ---
interface LatLngValue {
    lat: number;
    lng: number;
    address?: string;
}

const props = withDefaults(
    defineProps<{
        modelValue?: LatLngValue | null;
        initialValue?: LatLngValue | null;
        basemapApiKey?: string;
        center?: [number, number];
        zoom?: number;
        height?: string;
        placeholder?: string;
        disabled?: boolean;
    }>(),
    {
        modelValue: null,
        initialValue: null,
        center: () => [-6.2088, 106.8456],
        zoom: 13,
        height: '400px',
        placeholder: 'Cari alamat atau nama jalan...',
        disabled: false,
    },
);

const emit = defineEmits<{
    'update:modelValue': [value: LatLngValue];
}>();

// --- Reactive State ---
const mapContainerId = `map-${Math.random().toString(36).slice(2, 9)}`;
const searchQuery = ref('');
const searchResults = ref<
    Array<{ display_name: string; latitude: number; longitude: number }>
>([]);
const isSearching = ref(false);
const isLocating = ref(false);
const statusMessage = ref('');
const showDropdown = ref(false);

let L: any = null;
let map: LeafletMap | null = null;
let marker: LeafletMarker | null = null;
let searchDebounce: ReturnType<typeof setTimeout> | null = null;

// --- Lifecycle ---
onMounted(async () => {
    if (typeof window === 'undefined') return;

    try {
        const leafletModule = await import('leaflet');
        L = leafletModule.default || leafletModule;
        await import('leaflet/dist/leaflet.css');

        initMap();
    } catch (e) {
        console.error('Failed to init LocationPicker:', e);
    }
});

onUnmounted(() => {
    if (map) {
        map.remove();
        map = null;
    }
});

// --- Map Initialization ---
function initMap() {
    if (!L) return;

    const startValue = props.modelValue ?? props.initialValue;

    const initialCenter: [number, number] = startValue
        ? [startValue.lat, startValue.lng]
        : props.center;

    const instance = L.map(mapContainerId, {
        center: initialCenter,
        zoom: props.zoom,
        zoomControl: true,
    });
    map = instance;

    addCartoBasemap(L, instance, props.basemapApiKey);

    // Tampilkan marker awal tanpa memicu reverse geocoding berulang.
    if (startValue) {
        placeMarker(startValue.lat, startValue.lng, startValue.address, true);
    }

    // Click to place marker
    if (!props.disabled && map) {
        map.on('click', (e: any) => {
            placeMarker(e.latlng.lat, e.latlng.lng);
        });
    }
}

// --- Place / Move Marker ---
function placeMarker(
    lat: number,
    lng: number,
    addressLabel?: string,
    silent = false,
) {
    if (!map || !L) return;

    if (marker) {
        marker.setLatLng([lat, lng]);
    } else {
        const redPinIcon = L.divIcon({
            html: `<div style="display:flex;align-items:center;justify-content:center;filter:drop-shadow(0 4px 10px rgba(239,68,68,0.45));cursor:grab;">
        <svg xmlns="http://www.w3.org/2000/svg" width="42" height="42" viewBox="0 0 24 24" fill="#ef4444" stroke="#000437" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 10 4 10a8 8 0 0 1 16 0"/>
          <circle cx="12" cy="10" r="3" fill="#ffffff" stroke="#000437" stroke-width="1.5"/>
        </svg>
      </div>`,
            className: '',
            iconSize: [42, 42],
            iconAnchor: [21, 42],
        });

        marker = L.marker([lat, lng], {
            icon: redPinIcon,
            draggable: !props.disabled,
        }).addTo(map);

        if (!props.disabled && marker) {
            marker.on('dragend', () => {
                const pos = marker!.getLatLng();
                doReverseGeocode(pos.lat, pos.lng);
            });
        }
    }

    map.panTo([lat, lng]);

    if (silent) return;

    if (addressLabel) {
        emit('update:modelValue', { lat, lng, address: addressLabel });
    } else {
        doReverseGeocode(lat, lng);
    }
}

// --- Reverse Geocoding (Koordinat → Alamat) ---
async function doReverseGeocode(lat: number, lng: number) {
    statusMessage.value = 'Mengambil informasi alamat...';
    try {
        const res = await fetch(`/map/reverse-geocode?lat=${lat}&lng=${lng}`);
        const data = await res.json();
        const address =
            data.display_name ?? `${lat.toFixed(5)}, ${lng.toFixed(5)}`;
        statusMessage.value = '';
        emit('update:modelValue', { lat, lng, address });
    } catch {
        statusMessage.value = '';
        emit('update:modelValue', {
            lat,
            lng,
            address: `${lat.toFixed(5)}, ${lng.toFixed(5)}`,
        });
    }
}

// --- Address Search (Forward Geocoding) ---
function onSearchInput() {
    if (searchDebounce) clearTimeout(searchDebounce);
    if (searchQuery.value.length < 3) {
        searchResults.value = [];
        showDropdown.value = false;
        return;
    }
    searchDebounce = setTimeout(() => performSearch(), 500);
}

async function performSearch() {
    isSearching.value = true;
    showDropdown.value = false;
    try {
        const res = await fetch(
            `/map/search?q=${encodeURIComponent(searchQuery.value)}&limit=5`,
        );
        searchResults.value = await res.json();
        showDropdown.value = searchResults.value.length > 0;
    } catch {
        searchResults.value = [];
    } finally {
        isSearching.value = false;
    }
}

function selectSearchResult(result: {
    display_name: string;
    latitude: number;
    longitude: number;
}) {
    showDropdown.value = false;
    searchQuery.value = '';
    searchResults.value = [];
    map?.setView([result.latitude, result.longitude], 16);
    placeMarker(result.latitude, result.longitude, result.display_name);
}

// --- Geolocation (GPS Browser) ---
function useCurrentLocation() {
    if (typeof navigator === 'undefined' || !navigator.geolocation) {
        statusMessage.value = 'Perangkat Anda tidak mendukung GPS.';
        return;
    }
    isLocating.value = true;
    statusMessage.value = 'Mendeteksi lokasi Anda...';

    navigator.geolocation.getCurrentPosition(
        (pos) => {
            isLocating.value = false;
            map?.setView([pos.coords.latitude, pos.coords.longitude], 16);
            placeMarker(pos.coords.latitude, pos.coords.longitude);
        },
        () => {
            isLocating.value = false;
            statusMessage.value =
                'Gagal mendapatkan lokasi. Pastikan izin GPS diberikan.';
        },
        { timeout: 10000, enableHighAccuracy: true },
    );
}

// --- Watch external modelValue changes ---
watch(
    () => props.modelValue,
    (val) => {
        if (val && map) {
            placeMarker(val.lat, val.lng, val.address);
        }
    },
);
</script>

<template>
    <div class="relative w-full" :style="{ height }">
        <!-- Search Bar Overlay -->
        <div
            class="absolute top-2 right-14 left-2 z-[1000] flex flex-col gap-1"
        >
            <div class="relative">
                <input
                    v-model="searchQuery"
                    type="text"
                    :placeholder="placeholder"
                    :disabled="disabled || isLocating"
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-md focus:ring-2 focus:ring-green-500 focus:outline-none disabled:opacity-60"
                    @input="onSearchInput"
                    @keydown.escape="showDropdown = false"
                />
                <span
                    v-if="isSearching"
                    class="absolute top-2.5 right-3 text-xs text-gray-400"
                    >Mencari...</span
                >
            </div>

            <!-- Dropdown Autocomplete Results -->
            <ul
                v-if="showDropdown && searchResults.length"
                class="max-h-52 overflow-y-auto rounded-lg border border-gray-200 bg-white text-sm shadow-lg"
            >
                <li
                    v-for="result in searchResults"
                    :key="result.display_name"
                    class="cursor-pointer border-b px-3 py-2 text-gray-700 last:border-b-0 hover:bg-green-50"
                    @click="selectSearchResult(result)"
                >
                    {{ result.display_name }}
                </li>
            </ul>
        </div>

        <!-- GPS Button Overlay -->
        <div class="absolute top-2 right-2 z-[1000]">
            <button
                type="button"
                :disabled="disabled || isLocating"
                class="flex h-10 w-10 cursor-pointer items-center justify-center rounded-lg border border-gray-300 bg-white shadow-md transition-colors hover:bg-green-50 disabled:opacity-60"
                title="Gunakan Lokasi Saya Saat Ini"
                @click="useCurrentLocation"
            >
                <span v-if="isLocating" class="text-xs">...</span>
                <LocateFixed v-else class="h-5 w-5 text-green-600" />
            </button>
        </div>

        <!-- Map Container -->
        <div
            :id="mapContainerId"
            class="z-0 h-full w-full rounded-xl bg-gray-100"
        />

        <!-- Status Bar -->
        <div
            v-if="statusMessage"
            class="absolute right-2 bottom-2 left-2 z-[1000] rounded-lg bg-white/90 px-3 py-1.5 text-center text-xs text-gray-600 shadow backdrop-blur-sm"
        >
            {{ statusMessage }}
        </div>
    </div>
</template>
