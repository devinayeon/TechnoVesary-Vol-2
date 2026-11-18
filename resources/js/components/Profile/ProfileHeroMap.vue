<script setup lang="ts">
import { onMounted, onUnmounted, ref, watch } from "vue";
import type { Map as LeafletMap, Marker as LeafletMarker, Polyline as LeafletPolyline } from "leaflet";
import { Car, Clock, LocateFixed, Plus, Minus, MapPin } from "lucide-vue-next";

const props = withDefaults(
    defineProps<{
        lat: number;
        lng: number;
        address: string;
        height?: string;
    }>(),
    { height: "460px" }
);

const mapContainerId = `profile-hero-map-${Math.random().toString(36).slice(2, 9)}`;
let L: any = null;
let map: LeafletMap | null = null;
let targetMarker: LeafletMarker | null = null;
let routePolylineBase: LeafletPolyline | null = null;
let routePolylineCore: LeafletPolyline | null = null;

const pickupPools = [
    { lat: -8.165, lng: 113.674, label: "Pool Sumbersari Utara" },
    { lat: -8.192, lng: 113.665, label: "Pool Patrang Selatan" },
    { lat: -8.162, lng: 113.702, label: "Pool Kaliwates" },
    { lat: -8.188, lng: 113.712, label: "Pool Pakusari" },
    { lat: -8.152, lng: 113.691, label: "Pool Tegalboto" },
    { lat: -8.178, lng: 113.648, label: "Pool Mangli" },
];

const routeInfo = ref<{ distanceKm: number; durationMinutes: number; poolName: string } | null>(null);
const routeLoading = ref(true);

function getNearestPool(targetLat: number, targetLng: number) {
    let closest = pickupPools[0];
    let minDist = Infinity;
    for (const pool of pickupPools) {
        const d = Math.hypot(pool.lat - targetLat, pool.lng - targetLng);
        if (d < minDist) {
            minDist = d;
            closest = pool;
        }
    }
    return closest;
}

function zoomIn() { map?.zoomIn(); }
function zoomOut() { map?.zoomOut(); }
function panToAddress() {
    if (map) map.flyTo([props.lat, props.lng], 15, { duration: 0.8 });
}

onMounted(async () => {
    if (typeof window === "undefined") return;
    try {
        const leafletModule = await import("leaflet");
        L = leafletModule.default || leafletModule;
        await import("leaflet/dist/leaflet.css");

        const greenPoolIcon = L.divIcon({
            html: `<div style="display:flex;align-items:center;justify-content:center;filter:drop-shadow(0 4px 6px rgba(0,4,55,0.3));cursor:pointer;">
                <svg xmlns="http://www.w3.org/2000/svg" width="34" height="34" viewBox="0 0 24 24" fill="#58cc02" stroke="#000437" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 10 4 10a8 8 0 0 1 16 0"/>
                    <circle cx="12" cy="10" r="3" fill="#ffffff" stroke="#000437" stroke-width="1.5"/>
                </svg>
            </div>`,
            className: "",
            iconSize: [34, 34],
            iconAnchor: [17, 34],
        });

        const redTargetIcon = L.divIcon({
            html: `<div style="display:flex;align-items:center;justify-content:center;filter:drop-shadow(0 4px 10px rgba(239,68,68,0.45));cursor:pointer;">
                <svg xmlns="http://www.w3.org/2000/svg" width="42" height="42" viewBox="0 0 24 24" fill="#ef4444" stroke="#000437" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 10 4 10a8 8 0 0 1 16 0"/>
                    <circle cx="12" cy="10" r="3" fill="#ffffff" stroke="#000437" stroke-width="1.5"/>
                </svg>
            </div>`,
            className: "",
            iconSize: [42, 42],
            iconAnchor: [21, 42],
        });

        map = L.map(mapContainerId, {
            center: [props.lat, props.lng],
            zoom: 14,
            zoomControl: false,
            scrollWheelZoom: false,
            dragging: true,
            touchZoom: true,
            doubleClickZoom: true,
            attributionControl: false,
        });

        const cartoUrl = "https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png";
        let hasFallback = false;
        const tileLayer = L.tileLayer(cartoUrl, { maxZoom: 19, subdomains: "abcd" }).addTo(map);

        tileLayer.on("tileerror", () => {
            if (!hasFallback && map) {
                hasFallback = true;
                L.tileLayer("https://tile.openstreetmap.org/{z}/{x}/{y}.png", { maxZoom: 19 }).addTo(map);
            }
        });

        for (const pool of pickupPools) {
            L.marker([pool.lat, pool.lng], { icon: greenPoolIcon })
                .addTo(map)
                .bindPopup(`<strong>${pool.label}</strong><br/><small style="color:#46a302;">Pool Penjemputan Terpilah</small>`);
        }

        targetMarker = L.marker([props.lat, props.lng], { icon: redTargetIcon })
            .addTo(map)
            .bindPopup(`<strong>🎯 Titik Alamat Utama</strong><br/>${props.address}`);

        await loadRoute(props.lat, props.lng);
    } catch (err) {
        console.error("Gagal menginisialisasi Leaflet Map Profil:", err);
        routeLoading.value = false;
    }
});

async function loadRoute(targetLat: number, targetLng: number) {
    if (!map || !L) return;
    routeLoading.value = true;
    if (routePolylineBase) map.removeLayer(routePolylineBase);
    if (routePolylineCore) map.removeLayer(routePolylineCore);

    const nearest = getNearestPool(targetLat, targetLng);
    try {
        const params = new URLSearchParams({
            start_lat: String(nearest.lat),
            start_lng: String(nearest.lng),
            end_lat: String(targetLat),
            end_lng: String(targetLng),
        });
        const res = await fetch(`/map/route?${params}`);
        const data = await res.json();

        if (data.coordinates && data.coordinates.length > 0) {
            routePolylineBase = L.polyline(data.coordinates, {
                color: "#000437", weight: 9, opacity: 0.35, lineCap: "round", lineJoin: "round",
            }).addTo(map);

            routePolylineCore = L.polyline(data.coordinates, {
                color: "#1cb0f6", weight: 6, opacity: 0.95, lineCap: "round", lineJoin: "round",
                dashArray: data.fallback ? "10 8" : undefined,
            }).addTo(map);

            routeInfo.value = {
                distanceKm: data.distance_km,
                durationMinutes: data.duration_minutes,
                poolName: nearest.label,
            };
        } else {
            throw new Error("No route");
        }
    } catch {
        routePolylineCore = L.polyline([[nearest.lat, nearest.lng], [targetLat, targetLng]], {
            color: "#1cb0f6", weight: 6, opacity: 0.8, dashArray: "8 6",
        }).addTo(map);

        routeInfo.value = { distanceKm: 2.8, durationMinutes: 6, poolName: nearest.label };
    } finally {
        routeLoading.value = false;
    }
}

watch(
    () => [props.lat, props.lng, props.address],
    ([newLat, newLng, newAddr]) => {
        if (targetMarker && typeof newLat === "number" && typeof newLng === "number") {
            targetMarker.setLatLng([newLat, newLng]);
            targetMarker.setPopupContent(`<strong>🎯 Titik Alamat Utama</strong><br/>${newAddr}`);
            map?.panTo([newLat, newLng]);
            loadRoute(newLat, newLng);
        }
    }
);

onUnmounted(() => {
    map?.remove();
    map = null;
    targetMarker = null;
    routePolylineBase = null;
    routePolylineCore = null;
});
</script>

<template>
    <div
        class="relative w-full rounded-[24px] sm:rounded-[28px] overflow-hidden border-4 border-border-gray shadow-[0px_6px_0px_0px_var(--color-border-gray)] bg-cloud-surface"
        :style="{ height }"
    >
        <!-- Map Canvas -->
        <div :id="mapContainerId" class="w-full h-full z-0 cursor-grab active:cursor-grabbing" />

        <!-- Overlay: Tactile Destination & Route Card -->
        <div
            class="group absolute bottom-4 left-4 right-4 sm:right-auto sm:bottom-6 sm:left-6 sm:w-[390px] bg-paper-white border-2 border-b-[5px] border-border-gray rounded-2xl p-4 sm:p-5 shadow-[0_8px_20px_rgba(0,0,0,0.06)] hover:border-night-ink/30 hover:border-b-night-ink/50 hover:-translate-y-1 hover:shadow-[0_16px_32px_-6px_rgba(0,4,55,0.18)] transition-all duration-300 ease-out z-[1000] cursor-default"
        >
            <div class="flex items-center justify-between gap-2 mb-1">
                <span class="font-bold text-[12px] sm:text-[13px] text-charcoal uppercase tracking-wider flex items-center gap-1.5">
                    <MapPin class="size-3.5 text-eco-green stroke-[2.5]" />
                    Alamat Utama :
                </span>
                <span v-if="routeLoading" class="text-[11px] text-charcoal animate-pulse">Menghitung rute...</span>
                <span
                    v-else-if="routeInfo"
                    class="text-[11px] font-bold text-eco-green-dark bg-sprout-tint px-2.5 py-0.5 rounded-full border border-eco-green/30"
                >
                    {{ routeInfo.poolName }}
                </span>
            </div>

            <h3 class="font-extrabold text-[15px] sm:text-[18px] text-night-ink leading-snug sm:leading-tight mb-3 line-clamp-2">
                {{ address }}
            </h3>

            <div class="flex items-center gap-5 sm:gap-6 pt-2 border-t border-border-gray/50">
                <div class="flex items-center gap-2">
                    <Car class="size-4.5 text-night-ink stroke-[2.2] transition-transform duration-200 group-hover:scale-110" />
                    <span class="font-extrabold text-[14px] sm:text-[15px] text-night-ink">
                        {{ routeInfo ? `${routeInfo.distanceKm} Km` : "-" }}
                    </span>
                </div>
                <div class="flex items-center gap-2">
                    <Clock class="size-4 text-night-ink stroke-[2.2] transition-transform duration-200 group-hover:scale-110" />
                    <span class="font-extrabold text-[14px] sm:text-[15px] text-night-ink">
                        {{ routeInfo ? `${routeInfo.durationMinutes} Menit` : "-" }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Overlay: Tactile Control Buttons -->
        <div class="absolute top-4 right-4 sm:top-auto sm:bottom-6 sm:right-6 z-[1000] flex flex-col gap-2.5 sm:gap-3">
            <button
                type="button"
                class="size-[42px] sm:size-[47px] bg-sky-blue border-2 border-b-[4px] border-sky-blue-dark rounded-xl shadow-xs active:translate-y-0.5 active:border-b-2 flex items-center justify-center cursor-pointer hover:brightness-105 hover:scale-105 transition-all duration-200"
                title="Pusatkan ke alamat saya"
                @click="panToAddress"
            >
                <LocateFixed class="size-5 sm:size-6 text-night-ink stroke-[2.2]" />
            </button>
            <button
                type="button"
                class="size-[42px] sm:size-[47px] bg-eco-green border-2 border-b-[4px] border-eco-green-dark rounded-xl shadow-xs active:translate-y-0.5 active:border-b-2 flex items-center justify-center cursor-pointer hover:brightness-105 hover:scale-105 transition-all duration-200"
                title="Perbesar Peta"
                @click="zoomIn"
            >
                <Plus class="size-5 sm:size-6 text-night-ink stroke-[2.5]" />
            </button>
            <button
                type="button"
                class="size-[42px] sm:size-[47px] bg-eco-green border-2 border-b-[4px] border-eco-green-dark rounded-xl shadow-xs active:translate-y-0.5 active:border-b-2 flex items-center justify-center cursor-pointer hover:brightness-105 hover:scale-105 transition-all duration-200"
                title="Perkecil Peta"
                @click="zoomOut"
            >
                <Minus class="size-5 sm:size-6 text-night-ink stroke-[2.5]" />
            </button>
        </div>
    </div>
</template>
