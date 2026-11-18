<script setup lang="ts">
/**
 * RouteViewer.vue — Komponen visualisasi rute navigasi driver ke titik penjemputan.
 *
 * Fitur:
 * - Menggambar polyline rute jalan raya nyata (via OSRM) di atas peta Leaflet
 * - Menampilkan marker Asal (Driver) dan Tujuan (Titik Penjemputan)
 * - Widget ringkasan rute: Jarak (km) dan Estimasi Waktu Tiba (ETA menit)
 * - Fallback ke garis lurus jika OSRM tidak tersedia
 * - Auto-fit bounds agar seluruh rute terlihat
 */
import { onMounted, onUnmounted, ref, watch } from 'vue';
import type {
    Map as LeafletMap,
    Polyline as LeafletPolyline,
    Marker as LeafletMarker,
} from 'leaflet';
import { Car, Clock } from 'lucide-vue-next';
import { addCartoBasemap } from '@/lib/map/basemap';
import { createPinIcon } from '@/lib/map/markers';

// --- Props ---
interface LatLng {
    lat: number;
    lng: number;
    label?: string;
}

const props = withDefaults(
    defineProps<{
        origin: LatLng;
        destination: LatLng;
        height?: string;
        autoFetch?: boolean;
        basemapApiKey?: string;
    }>(),
    {
        height: '400px',
        autoFetch: true,
    },
);

const emit = defineEmits<{
    'route-loaded': [data: { distanceKm: number; durationMinutes: number }];
}>();

// --- Reactive State ---
const mapContainerId = `route-map-${Math.random().toString(36).slice(2, 9)}`;
const isLoading = ref(false);
const routeInfo = ref<{
    distanceKm: number;
    durationMinutes: number;
    isFallback: boolean;
} | null>(null);
const errorMessage = ref('');

let L: any = null;
let map: LeafletMap | null = null;
let routePolyline: LeafletPolyline | null = null;
let originMarker: LeafletMarker | null = null;
let destinationMarker: LeafletMarker | null = null;
let driverIcon: any = null;
let pickupIcon: any = null;

// --- Lifecycle ---
onMounted(async () => {
    if (typeof window === 'undefined') return;

    try {
        const leafletModule = await import('leaflet');
        L = leafletModule.default || leafletModule;
        await import('leaflet/dist/leaflet.css');

        const iconUrl = (await import('leaflet/dist/images/marker-icon.png'))
            .default;
        const iconRetinaUrl = (
            await import('leaflet/dist/images/marker-icon-2x.png')
        ).default;
        const shadowUrl = (
            await import('leaflet/dist/images/marker-shadow.png')
        ).default;

        delete (L.Icon.Default.prototype as any)._getIconUrl;
        L.Icon.Default.mergeOptions({ iconUrl, iconRetinaUrl, shadowUrl });

        driverIcon = L.divIcon({
            html: `
        <div style="width:36px;height:36px;border-radius:50%;background:#1cb0f6;border:2.5px solid #000437;box-shadow:0 3px 8px rgba(0,4,55,0.3);display:flex;align-items:center;justify-content:center;">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#000437" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.5 2.8C2.1 11 2 11.5 2 12v4c0 .6.4 1 1 1h2"/>
            <circle cx="7" cy="17" r="2"/>
            <path d="M9 17h6"/>
            <circle cx="17" cy="17" r="2"/>
          </svg>
        </div>
      `,
            className: '',
            iconSize: [36, 36],
            iconAnchor: [18, 18],
        });

        pickupIcon = createPinIcon(L, { size: 36, fill: '#58cc02' });

        initMap();

        if (props.autoFetch) {
            await fetchAndDrawRoute();
        }
    } catch (e) {
        console.error('Failed to init RouteViewer:', e);
    }
});

onUnmounted(() => {
    map?.remove();
    map = null;
});

// --- Map Init ---
function initMap() {
    if (!L) return;

    const instance = L.map(mapContainerId, {
        center: [props.origin.lat, props.origin.lng],
        zoom: 13,
        zoomControl: true,
    });
    map = instance;

    addCartoBasemap(L, instance, props.basemapApiKey);
}

// --- Fetch Route from Backend Proxy ---
async function fetchAndDrawRoute() {
    if (!map || !L) return;
    isLoading.value = true;
    errorMessage.value = '';

    try {
        const params = new URLSearchParams({
            start_lat: String(props.origin.lat),
            start_lng: String(props.origin.lng),
            end_lat: String(props.destination.lat),
            end_lng: String(props.destination.lng),
        });

        const res = await fetch(`/map/route?${params}`);
        const data = await res.json();

        // Clear old layers
        clearRoute();

        // Draw route polyline
        if (data.coordinates && data.coordinates.length > 0) {
            const lineColor = data.fallback ? '#f97316' : '#2563eb';
            routePolyline = L.polyline(data.coordinates, {
                color: lineColor,
                weight: 5,
                opacity: 0.85,
            }).addTo(map!);
        }

        // Place markers
        originMarker = L.marker([props.origin.lat, props.origin.lng], {
            icon: driverIcon,
        })
            .addTo(map!)
            .bindPopup(props.origin.label ?? 'Driver');

        destinationMarker = L.marker(
            [props.destination.lat, props.destination.lng],
            { icon: pickupIcon },
        )
            .addTo(map!)
            .bindPopup(props.destination.label ?? 'Titik Penjemputan');

        // Auto-fit map bounds to show entire route
        const bounds = L.latLngBounds([
            [props.origin.lat, props.origin.lng],
            [props.destination.lat, props.destination.lng],
        ]);
        if (data.coordinates?.length > 0 && routePolyline) {
            bounds.extend(routePolyline.getBounds());
        }
        map!.fitBounds(bounds, { padding: [40, 40] });

        routeInfo.value = {
            distanceKm: data.distance_km ?? 0,
            durationMinutes: data.duration_minutes ?? 0,
            isFallback: !!data.fallback,
        };

        emit('route-loaded', {
            distanceKm: data.distance_km,
            durationMinutes: data.duration_minutes,
        });
    } catch (err) {
        errorMessage.value = 'Gagal memuat rute. Periksa koneksi Anda.';
    } finally {
        isLoading.value = false;
    }
}

function clearRoute() {
    if (routePolyline) {
        routePolyline.remove();
        routePolyline = null;
    }
    if (originMarker) {
        originMarker.remove();
        originMarker = null;
    }
    if (destinationMarker) {
        destinationMarker.remove();
        destinationMarker = null;
    }
}

// Watchers
watch(
    () => [props.origin, props.destination],
    async () => {
        if (props.autoFetch) {
            await fetchAndDrawRoute();
        }
    },
    { deep: true },
);
</script>

<template>
    <div
        class="relative w-full overflow-hidden rounded-2xl border border-gray-200 shadow-lg"
        :style="{ height }"
    >
        <!-- Route Info Banner Overlay -->
        <div
            v-if="routeInfo"
            class="absolute top-3 left-3 z-[1000] flex items-center gap-4 rounded-xl border border-gray-100 bg-white/95 px-4 py-3 text-sm shadow-md backdrop-blur-sm"
        >
            <div class="flex items-center gap-1.5 font-semibold text-blue-700">
                <Car class="h-4 w-4 text-blue-600" />
                <span>{{ routeInfo.distanceKm }} km</span>
            </div>
            <div class="h-4 w-px bg-gray-200"></div>
            <div
                class="flex items-center gap-1.5 font-semibold text-emerald-700"
            >
                <Clock class="h-4 w-4 text-emerald-600" />
                <span>~{{ routeInfo.durationMinutes }} menit</span>
            </div>
            <span
                v-if="routeInfo.isFallback"
                class="rounded-full bg-amber-100 px-2 py-0.5 text-xs text-amber-700"
                title="Jalur estimasi garis lurus (layanan OSRM sedang lambat)"
            >
                Estimasi Garis Lurus
            </span>
        </div>

        <!-- Loading Overlay -->
        <div
            v-if="isLoading"
            class="absolute inset-0 z-[1001] flex items-center justify-center bg-white/60 backdrop-blur-xs"
        >
            <div
                class="flex items-center gap-2 rounded-xl bg-white px-4 py-2 text-sm text-gray-700 shadow-md"
            >
                <svg
                    class="h-4 w-4 animate-spin text-blue-600"
                    fill="none"
                    viewBox="0 0 24 24"
                >
                    <circle
                        class="opacity-25"
                        cx="12"
                        cy="12"
                        r="10"
                        stroke="currentColor"
                        stroke-width="4"
                    ></circle>
                    <path
                        class="opacity-75"
                        fill="currentColor"
                        d="M4 12a8 8 0 018-8v8H4z"
                    ></path>
                </svg>
                <span>Menghitung rute navigasi...</span>
            </div>
        </div>

        <!-- Error Banner -->
        <div
            v-if="errorMessage"
            class="absolute right-3 bottom-3 left-3 z-[1000] rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-center text-xs text-red-700"
        >
            {{ errorMessage }}
        </div>

        <!-- Map Canvas -->
        <div :id="mapContainerId" class="z-0 h-full w-full bg-gray-100" />
    </div>
</template>
