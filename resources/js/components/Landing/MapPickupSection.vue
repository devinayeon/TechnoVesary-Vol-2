<script setup lang="ts">
import { onMounted, onUnmounted, ref } from 'vue';
import type { Map as LeafletMap } from 'leaflet';
import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { Car, Clock, LocateFixed, Plus, Minus } from 'lucide-vue-next';
import { addCartoBasemap } from '@/lib/map/basemap';
import { createCurrentLocationIcon, createPinIcon } from '@/lib/map/markers';

const props = defineProps<{
    basemapApiKey?: string;
}>();

const sectionRef = ref<HTMLElement | null>(null);
const destinationCardRef = ref<HTMLElement | null>(null);
let gsapCtx: gsap.Context | null = null;

// --- Jember Coordinates (Data Dummy Statis) ---
const JEMBER_CENTER: [number, number] = [-8.178, 113.682];

// Titik Pool Penjemputan (Dummy statis, hijau)
const dummyPickupPoints = [
    { lat: -8.165, lng: 113.674, label: 'Pool Sumbersari Utara' },
    { lat: -8.192, lng: 113.665, label: 'Pool Patrang Selatan' },
    { lat: -8.162, lng: 113.702, label: 'Pool Kaliwates' },
    { lat: -8.188, lng: 113.712, label: 'Pool Pakusari' },
    { lat: -8.152, lng: 113.691, label: 'Pool Tegalboto' },
    { lat: -8.178, lng: 113.648, label: 'Pool Mangli' },
];

// Titik Tujuan (1 data dummy statis, TIDAK bisa dipindah atau dipilih ulang)
const targetPoint = {
    lat: -8.1739,
    lng: 113.6878,
    label: 'JL RA. Kartini, Sumbersari Jember',
};

// Titik Lokasi Saat Ini (Statis: Panah Merah, Stroke Hitam)
const currentLocation = {
    lat: -8.1882,
    lng: 113.6625,
    label: 'Lokasi Anda (Kaliwates)',
};

const mapContainerId = 'hero-map-jember';
let map: LeafletMap | null = null;

// Route info
const routeInfo = ref<{ distanceKm: number; durationMinutes: number } | null>(
    null,
);
const routeLoading = ref(true);

function zoomIn() {
    map?.zoomIn();
}

function zoomOut() {
    map?.zoomOut();
}

function panToCurrentLocation() {
    if (map) {
        map.flyTo([currentLocation.lat, currentLocation.lng], 14, {
            duration: 0.8,
        });
    }
}

onMounted(async () => {
    if (typeof window === 'undefined') return;

    try {
        const leafletModule = await import('leaflet');
        const L = leafletModule.default || leafletModule;
        await import('leaflet/dist/leaflet.css');

        // Marker bersama (lib/map/markers) agar konsisten dengan peta lain
        const greenPoolIcon = createPinIcon(L, {
            size: 34,
            fill: '#58cc02',
            dropShadow: '0 4px 6px rgba(0,4,55,0.3)',
        });
        const redTargetIcon = createPinIcon(L, { size: 42 });
        const redArrowCurrentIcon = createCurrentLocationIcon(L);
        // Inisialisasi Peta Leaflet: hanya bisa bergeser (drag/pan) dan zoom in/out
        map = L.map(mapContainerId, {
            center: [-8.181, 113.675],
            zoom: 13,
            zoomControl: false,
            scrollWheelZoom: false, // Tidak mengganggu scrolling halaman
            dragging: true, // Interaksi bergeser diperbolehkan
            touchZoom: true,
            doubleClickZoom: true,
            attributionControl: false,
        });

        // Basemap Carto CDN dengan API Key dari .env (BASEMAP_API_KEY)
        addCartoBasemap(L, map, props.basemapApiKey);

        // Tambahkan Green Pool Markers (Hanya informasi popup, tidak memindah rute)
        for (const point of dummyPickupPoints) {
            L.marker([point.lat, point.lng], { icon: greenPoolIcon })
                .addTo(map)
                .bindPopup(
                    `<strong>${point.label}</strong><br/><small style="color:#46a302;">Pool Penjemputan Terpilah</small>`,
                );
        }

        // Tambahkan Red Target Destination Marker (Statis, 1 titik tidak dapat diubah)
        L.marker([targetPoint.lat, targetPoint.lng], { icon: redTargetIcon })
            .addTo(map)
            .bindPopup(
                `<strong>🎯 Titik Jemput</strong><br/>${targetPoint.label}`,
            )
            .openPopup();

        // Tambahkan Red Arrow Current Location Marker (Statis: Panah Merah, Stroke Hitam)
        L.marker([currentLocation.lat, currentLocation.lng], {
            icon: redArrowCurrentIcon,
        })
            .addTo(map)
            .bindPopup(`<strong>📍 ${currentLocation.label}</strong>`);

        // Fetch rute jalan raya OSRM asli
        try {
            const params = new URLSearchParams({
                start_lat: String(currentLocation.lat),
                start_lng: String(currentLocation.lng),
                end_lat: String(targetPoint.lat),
                end_lng: String(targetPoint.lng),
            });

            const res = await fetch(`/map/route?${params}`);
            const data = await res.json();

            if (data.coordinates && data.coordinates.length > 0) {
                // Casing polyline gelap untuk efek lebar jalan raya
                L.polyline(data.coordinates, {
                    color: '#000437',
                    weight: 9,
                    opacity: 0.35,
                    lineCap: 'round',
                    lineJoin: 'round',
                }).addTo(map);

                // Core route polyline biru langit
                L.polyline(data.coordinates, {
                    color: '#1cb0f6',
                    weight: 6,
                    opacity: 0.95,
                    lineCap: 'round',
                    lineJoin: 'round',
                    dashArray: data.fallback ? '10 8' : undefined,
                }).addTo(map);

                routeInfo.value = {
                    distanceKm: data.distance_km,
                    durationMinutes: data.duration_minutes,
                };
            }
        } catch {
            // Fallback line jika OSRM lambat
            L.polyline(
                [
                    [currentLocation.lat, currentLocation.lng],
                    [targetPoint.lat, targetPoint.lng],
                ],
                {
                    color: '#1cb0f6',
                    weight: 6,
                    opacity: 0.8,
                    dashArray: '8 6',
                },
            ).addTo(map);

            routeInfo.value = { distanceKm: 3, durationMinutes: 5 };
        } finally {
            routeLoading.value = false;
        }
        // GSAP ScrollTrigger Entrance Animation with fromTo
        if (sectionRef.value) {
            gsap.registerPlugin(ScrollTrigger);
            gsapCtx = gsap.context(() => {
                gsap.fromTo(
                    '.map-card-wrapper',
                    { scale: 0.94, y: 50, opacity: 0 },
                    {
                        scale: 1,
                        y: 0,
                        opacity: 1,
                        duration: 0.8,
                        ease: 'power3.out',
                        scrollTrigger: {
                            trigger: '.map-card-wrapper',
                            start: 'top 85%',
                        },
                    },
                );

                if (destinationCardRef.value) {
                    gsap.fromTo(
                        destinationCardRef.value,
                        { x: -40, opacity: 0, scale: 0.9 },
                        {
                            x: 0,
                            opacity: 1,
                            scale: 1,
                            duration: 0.7,
                            delay: 0.2,
                            ease: 'back.out(1.4)',
                            scrollTrigger: {
                                trigger: '.map-card-wrapper',
                                start: 'top 85%',
                            },
                        },
                    );
                }
            }, sectionRef.value);
        }
    } catch (err) {
        console.error('Failed to init Leaflet Map:', err);
        routeLoading.value = false;
    }
});

onUnmounted(() => {
    gsapCtx?.revert();
    map?.remove();
    map = null;
});
</script>

<template>
    <section
        ref="sectionRef"
        class="overflow-hidden bg-paper-white py-12 lg:py-16"
    >
        <div class="mx-auto max-w-[1200px] px-4 sm:px-6">
            <!-- Section Title & Subtitle -->
            <div
                class="mb-6 flex flex-col justify-between gap-2 sm:flex-row sm:items-baseline"
            >
                <h2
                    class="text-2xl font-bold tracking-tight text-night-ink sm:text-[26px]"
                >
                    Dekat rumahmu, siap menjemput.
                </h2>
                <span
                    class="text-[13px] font-bold tracking-[1.6px] text-charcoal uppercase"
                >
                    JEMBER · PETA ILUSTRASI
                </span>
            </div>

            <!-- Tactile Card Container -->
            <div
                class="map-card-wrapper relative h-[480px] overflow-hidden rounded-[24px] border-4 border-border-gray bg-cloud-surface shadow-[0px_6px_0px_0px_var(--color-border-gray)] sm:h-[530px] sm:rounded-[28px]"
            >
                <!-- Map Canvas (Mendukung drag/bergeser dan zoom) -->
                <div
                    :id="mapContainerId"
                    class="z-0 h-full w-full cursor-grab active:cursor-grabbing"
                />

                <!-- Overlay: Destination Card (100% Responsif di Mobile & Desktop) -->
                <div
                    ref="destinationCardRef"
                    class="group absolute right-4 bottom-4 left-4 z-[1000] cursor-default rounded-2xl border-2 border-b-[5px] border-border-gray bg-paper-white p-4 shadow-[0_8px_20px_rgba(0,0,0,0.06)] transition-all duration-300 ease-out hover:-translate-y-1 hover:border-night-ink/30 hover:border-b-night-ink/50 hover:shadow-[0_16px_32px_-6px_rgba(0,4,55,0.18)] sm:right-auto sm:bottom-6 sm:left-6 sm:w-[411px] sm:p-5"
                >
                    <div class="mb-1 flex items-center justify-between">
                        <p
                            class="text-[13px] font-bold text-night-ink sm:text-[15px]"
                        >
                            Tujuan :
                        </p>
                        <span
                            v-if="routeLoading"
                            class="animate-pulse text-[11px] text-charcoal"
                        >
                            Menghitung rute...
                        </span>
                    </div>

                    <h3
                        class="mb-3 line-clamp-2 text-[16px] leading-snug font-extrabold text-night-ink sm:mb-4 sm:text-[20px] sm:leading-tight"
                    >
                        {{ targetPoint.label }}
                    </h3>

                    <div class="flex items-center gap-5 sm:gap-6">
                        <!-- Distance (Lucide Car) -->
                        <div class="flex items-center gap-2">
                            <Car
                                class="size-4.5 stroke-[2.2] text-night-ink transition-transform duration-200 group-hover:scale-110 sm:size-5"
                            />
                            <span
                                class="text-[14px] font-extrabold text-night-ink sm:text-[15px]"
                            >
                                {{
                                    routeInfo
                                        ? `${routeInfo.distanceKm} Km`
                                        : '3 Km'
                                }}
                            </span>
                        </div>
                        <!-- Duration (Lucide Clock) -->
                        <div class="flex items-center gap-2">
                            <Clock
                                class="size-4 stroke-[2.2] text-night-ink transition-transform duration-200 group-hover:scale-110 sm:size-4.5"
                            />
                            <span
                                class="text-[14px] font-extrabold text-night-ink sm:text-[15px]"
                            >
                                {{
                                    routeInfo
                                        ? `${routeInfo.durationMinutes} Menit`
                                        : '5 Menit'
                                }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Overlay: Tactile Control Buttons (Mobile di Atas Kanan, Desktop di Bawah Kanan) -->
                <div
                    class="absolute top-4 right-4 z-[1000] flex flex-col gap-2.5 sm:top-auto sm:right-6 sm:bottom-6 sm:gap-3"
                >
                    <!-- Current Location GPS Button (Sky Blue Tactile + Lucide LocateFixed) -->
                    <button
                        type="button"
                        class="flex size-[42px] cursor-pointer items-center justify-center rounded-xl border-2 border-b-[4px] border-sky-blue-dark bg-sky-blue shadow-xs transition-all duration-200 hover:scale-105 hover:brightness-105 active:translate-y-0.5 active:border-b-2 sm:size-[47px]"
                        title="Pusatkan ke lokasi"
                        @click="panToCurrentLocation"
                    >
                        <LocateFixed
                            class="size-5 stroke-[2.2] text-night-ink sm:size-6"
                        />
                    </button>

                    <!-- Zoom In Button (Eco Green Tactile + Lucide Plus) -->
                    <button
                        type="button"
                        class="flex size-[42px] cursor-pointer items-center justify-center rounded-xl border-2 border-b-[4px] border-eco-green-dark bg-eco-green shadow-xs transition-all duration-200 hover:scale-105 hover:brightness-105 active:translate-y-0.5 active:border-b-2 sm:size-[47px]"
                        title="Perbesar Peta"
                        @click="zoomIn"
                    >
                        <Plus
                            class="size-5 stroke-[2.5] text-night-ink sm:size-6"
                        />
                    </button>

                    <!-- Zoom Out Button (Eco Green Tactile + Lucide Minus) -->
                    <button
                        type="button"
                        class="flex size-[42px] cursor-pointer items-center justify-center rounded-xl border-2 border-b-[4px] border-eco-green-dark bg-eco-green shadow-xs transition-all duration-200 hover:scale-105 hover:brightness-105 active:translate-y-0.5 active:border-b-2 sm:size-[47px]"
                        title="Perkecil Peta"
                        @click="zoomOut"
                    >
                        <Minus
                            class="size-5 stroke-[2.5] text-night-ink sm:size-6"
                        />
                    </button>
                </div>
            </div>

            <!-- Caption -->
            <p
                class="mt-4 text-[12px] font-bold tracking-[1.6px] text-charcoal sm:text-[13px]"
            >
                Peta dan estimasi perjalanan ditampilkan sebagai ilustrasi
                layanan penjemputan.
            </p>
        </div>
    </section>
</template>
