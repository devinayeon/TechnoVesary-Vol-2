<script setup lang="ts">
/**
 * CoverageMap.vue — Peta interaktif untuk Landing Page.
 *
 * Fitur:
 * - Menampilkan area cakupan operasional bank sampah (Circle Radius)
 * - Menampilkan titik-titik pool/pos penjemputan dengan marker kustom
 * - Peta non-interaktif (non-clickable, dekoratif) dengan animasi ringan
 * - Dapat dikustomisasi lewat props: center, radius, markers
 */
import { onMounted, onUnmounted } from 'vue';
import type { Map as LeafletMap } from 'leaflet';

interface MarkerPoint {
  lat: number;
  lng: number;
  label?: string;
  type?: 'pool' | 'droppoint' | 'hq';
}

const props = withDefaults(defineProps<{
  center?: [number, number];
  radiusKm?: number;
  markers?: MarkerPoint[];
  zoom?: number;
  height?: string;
  interactive?: boolean;
}>(), {
  center: () => [-6.2088, 106.8456],
  radiusKm: 5,
  markers: () => [],
  zoom: 12,
  height: '450px',
  interactive: false,
});

const mapContainerId = `coverage-map-${Math.random().toString(36).slice(2, 9)}`;
let map: LeafletMap | null = null;

onMounted(async () => {
  if (typeof window === 'undefined') return;

  try {
    const leafletModule = await import('leaflet');
    const L = leafletModule.default || leafletModule;
    await import('leaflet/dist/leaflet.css');

    map = L.map(mapContainerId, {
      center: props.center,
      zoom: props.zoom,
      zoomControl: props.interactive,
      dragging: props.interactive,
      touchZoom: props.interactive,
      doubleClickZoom: props.interactive,
      scrollWheelZoom: props.interactive,
      boxZoom: props.interactive,
      keyboard: props.interactive,
      attributionControl: true,
    });

    // OpenStreetMap tile layer with a slight muted style
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 18,
      attribution: '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank">OpenStreetMap</a>',
      className: 'map-tiles-muted',
    }).addTo(map);

    // Coverage Area Circle
    if (props.radiusKm > 0) {
      L.circle(props.center, {
        radius: props.radiusKm * 1000,
        color: '#16a34a',
        fillColor: '#bbf7d0',
        fillOpacity: 0.25,
        weight: 2,
        dashArray: '6 4',
      }).addTo(map);
    }

    // HQ / Headquarter Marker (Center)
    const hqIcon = L.divIcon({
      html: `
        <div style="
          width: 44px; height: 44px;
          background: #15803d;
          border: 3px solid white;
          border-radius: 50%;
          display: flex; align-items: center; justify-content: center;
          box-shadow: 0 4px 12px rgba(0,0,0,0.25);
          font-size: 20px;
        ">🏦</div>
      `,
      className: '',
      iconSize: [44, 44],
      iconAnchor: [22, 44],
    });

    L.marker(props.center, { icon: hqIcon })
      .addTo(map)
      .bindPopup('<strong>🏦 Bank Sampah TechnoVesary</strong><br>Pusat Operasional', {
        maxWidth: 200,
      });

    // Additional custom markers (pool / drop points)
    props.markers.forEach((point) => {
      const markerIcon = L.divIcon({
        html: `
          <div style="
            width: 36px; height: 36px;
            background: ${point.type === 'droppoint' ? '#2563eb' : '#f59e0b'};
            border: 3px solid white;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 3px 8px rgba(0,0,0,0.2);
            font-size: 16px;
          ">${point.type === 'droppoint' ? '📍' : '🚛'}</div>
        `,
        className: '',
        iconSize: [36, 36],
        iconAnchor: [18, 36],
      });

      L.marker([point.lat, point.lng], { icon: markerIcon })
        .addTo(map!)
        .bindPopup(point.label ?? 'Titik Penjemputan', { maxWidth: 180 });
    });
  } catch (e) {
    console.error('Failed to init CoverageMap:', e);
  }
});

onUnmounted(() => {
  map?.remove();
  map = null;
});
</script>

<template>
  <div class="relative w-full overflow-hidden rounded-2xl shadow-lg" :style="{ height }">
    <!-- Legend Overlay -->
    <div class="absolute bottom-3 left-3 z-[1000] bg-white/90 backdrop-blur-sm rounded-xl px-3 py-2 shadow text-xs text-gray-700 flex flex-col gap-1.5">
      <div class="flex items-center gap-2">
        <span class="inline-block w-3 h-3 rounded-full bg-green-600 border border-white shadow-sm"></span>
        <span>Area Layanan (~{{ radiusKm }} km)</span>
      </div>
      <div class="flex items-center gap-2">
        <span class="text-base">🏦</span>
        <span>Bank Sampah Pusat</span>
      </div>
      <div v-if="markers.length" class="flex items-center gap-2">
        <span class="text-base">🚛</span>
        <span>Titik Penjemputan</span>
      </div>
    </div>

    <!-- Map Container -->
    <div :id="mapContainerId" class="w-full h-full z-0 bg-gray-100" />
  </div>
</template>

<style scoped>
:deep(.map-tiles-muted) {
  filter: saturate(0.8) brightness(1.05);
}
</style>
