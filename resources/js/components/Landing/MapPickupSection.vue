<script setup lang="ts">
import { onMounted, onUnmounted, ref } from 'vue';
import type { Map as LeafletMap } from 'leaflet';
import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { Car, Clock, LocateFixed, Plus, Minus } from 'lucide-vue-next';

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
const routeInfo = ref<{ distanceKm: number; durationMinutes: number } | null>(null);
const routeLoading = ref(true);

function zoomIn() {
  map?.zoomIn();
}

function zoomOut() {
  map?.zoomOut();
}

function panToCurrentLocation() {
  if (map) {
    map.flyTo([currentLocation.lat, currentLocation.lng], 14, { duration: 0.8 });
  }
}

onMounted(async () => {
  if (typeof window === 'undefined') return;

  try {
    const leafletModule = await import('leaflet');
    const L = leafletModule.default || leafletModule;
    await import('leaflet/dist/leaflet.css');

    // 1. Marker Lucide MapPin Hijau untuk Titik Pool
    const greenPoolIcon = L.divIcon({
      html: `
        <div style="display:flex;align-items:center;justify-content:center;filter:drop-shadow(0 4px 6px rgba(0,4,55,0.3));cursor:pointer;">
          <svg xmlns="http://www.w3.org/2000/svg" width="34" height="34" viewBox="0 0 24 24" fill="#58cc02" stroke="#000437" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 10 4 10a8 8 0 0 1 16 0"/>
            <circle cx="12" cy="10" r="3" fill="#ffffff" stroke="#000437" stroke-width="1.5"/>
          </svg>
        </div>
      `,
      className: '',
      iconSize: [34, 34],
      iconAnchor: [17, 34],
    });

    // 2. Marker Lucide MapPin Merah untuk Titik Tujuan (1 data statis)
    const redTargetIcon = L.divIcon({
      html: `
        <div style="display:flex;align-items:center;justify-content:center;filter:drop-shadow(0 4px 10px rgba(239,68,68,0.45));cursor:pointer;">
          <svg xmlns="http://www.w3.org/2000/svg" width="42" height="42" viewBox="0 0 24 24" fill="#ef4444" stroke="#000437" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 10 4 10a8 8 0 0 1 16 0"/>
            <circle cx="12" cy="10" r="3" fill="#ffffff" stroke="#000437" stroke-width="1.5"/>
          </svg>
        </div>
      `,
      className: '',
      iconSize: [42, 42],
      iconAnchor: [21, 42],
    });

    // 3. Marker Lucide Navigation Panah Merah dengan Stroke Hitam untuk Lokasi Saat Ini
    const redArrowCurrentIcon = L.divIcon({
      html: `
        <div style="position:relative;width:44px;height:44px;display:flex;align-items:center;justify-content:center;">
          <span style="position:absolute;width:40px;height:40px;background:rgba(239,68,68,0.22);border-radius:50%;animation:ping 2s cubic-bezier(0,0,0.2,1) infinite;"></span>
          <div style="filter:drop-shadow(0 4px 6px rgba(0,4,55,0.4));transform:rotate(-15deg);display:flex;align-items:center;justify-content:center;">
            <svg xmlns="http://www.w3.org/2000/svg" width="34" height="34" viewBox="0 0 24 24" fill="#ef4444" stroke="#000437" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
              <polygon points="3 11 22 2 13 21 11 13 3 11"/>
            </svg>
          </div>
        </div>
      `,
      className: '',
      iconSize: [44, 44],
      iconAnchor: [22, 22],
    });

    // Inisialisasi Peta Leaflet: hanya bisa bergeser (drag/pan) dan zoom in/out
    map = L.map(mapContainerId, {
      center: [-8.181, 113.675],
      zoom: 13,
      zoomControl: false,
      scrollWheelZoom: false, // Tidak mengganggu scrolling halaman
      dragging: true,         // Interaksi bergeser diperbolehkan
      touchZoom: true,
      doubleClickZoom: true,
      attributionControl: false,
    });

    // Basemap Carto CDN dengan API Key dari .env (BASEMAP_API_KEY)
    const rawKey = props.basemapApiKey || (import.meta as any).env?.VITE_BASEMAP_API_KEY || 'cb1_5b31_1_87452d52cb0b1e9b2c8e7a7a';
    const apiKey = String(rawKey).trim();

    const cartoUrl = apiKey
      ? `https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png?key=${encodeURIComponent(apiKey)}`
      : 'https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png';

    let hasFallback = false;
    const tileLayer = L.tileLayer(cartoUrl, {
      maxZoom: 19,
      subdomains: 'abcd',
    }).addTo(map);

    // Fallback otomatis jika terjadi kendala pada Carto CDN
    tileLayer.on('tileerror', () => {
      if (!hasFallback) {
        hasFallback = true;
        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map!);
      }
    });

    // Tambahkan Green Pool Markers (Hanya informasi popup, tidak memindah rute)
    for (const point of dummyPickupPoints) {
      L.marker([point.lat, point.lng], { icon: greenPoolIcon })
        .addTo(map)
        .bindPopup(`<strong>${point.label}</strong><br/><small style="color:#46a302;">Pool Penjemputan Terpilah</small>`);
    }

    // Tambahkan Red Target Destination Marker (Statis, 1 titik tidak dapat diubah)
    L.marker([targetPoint.lat, targetPoint.lng], { icon: redTargetIcon })
      .addTo(map)
      .bindPopup(`<strong>🎯 Titik Jemput</strong><br/>${targetPoint.label}`)
      .openPopup();

    // Tambahkan Red Arrow Current Location Marker (Statis: Panah Merah, Stroke Hitam)
    L.marker([currentLocation.lat, currentLocation.lng], { icon: redArrowCurrentIcon })
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
      L.polyline([
        [currentLocation.lat, currentLocation.lng],
        [targetPoint.lat, targetPoint.lng],
      ], {
        color: '#1cb0f6',
        weight: 6,
        opacity: 0.8,
        dashArray: '8 6',
      }).addTo(map);

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
          }
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
            }
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
  <section ref="sectionRef" class="bg-paper-white py-12 lg:py-16 overflow-hidden">
    <div class="max-w-[1200px] mx-auto px-4 sm:px-6">
      <!-- Section Title & Subtitle -->
      <div class="flex flex-col sm:flex-row sm:items-baseline justify-between mb-6 gap-2">
        <h2 class="font-bold text-2xl sm:text-[26px] text-night-ink tracking-tight">
          Dekat rumahmu, siap menjemput.
        </h2>
        <span class="font-bold text-[13px] tracking-[1.6px] uppercase text-charcoal">
          JEMBER · PETA ILUSTRASI
        </span>
      </div>

      <!-- Tactile Card Container -->
      <div
        class="map-card-wrapper relative h-[480px] sm:h-[530px] rounded-[24px] sm:rounded-[28px] overflow-hidden border-4 border-border-gray shadow-[0px_6px_0px_0px_var(--color-border-gray)] bg-cloud-surface"
      >
        <!-- Map Canvas (Mendukung drag/bergeser dan zoom) -->
        <div :id="mapContainerId" class="w-full h-full z-0 cursor-grab active:cursor-grabbing" />

        <!-- Overlay: Destination Card (100% Responsif di Mobile & Desktop) -->
        <div
          ref="destinationCardRef"
          class="group absolute bottom-4 left-4 right-4 sm:right-auto sm:bottom-6 sm:left-6 sm:w-[411px] bg-paper-white border-2 border-b-[5px] border-border-gray rounded-2xl p-4 sm:p-5 shadow-[0_8px_20px_rgba(0,0,0,0.06)] hover:border-night-ink/30 hover:border-b-night-ink/50 hover:-translate-y-1 hover:shadow-[0_16px_32px_-6px_rgba(0,4,55,0.18)] transition-all duration-300 ease-out z-[1000] cursor-default"
        >
          <div class="flex items-center justify-between mb-1">
            <p class="font-bold text-[13px] sm:text-[15px] text-night-ink">
              Tujuan :
            </p>
            <span v-if="routeLoading" class="text-[11px] text-charcoal animate-pulse">
              Menghitung rute...
            </span>
          </div>

          <h3 class="font-extrabold text-[16px] sm:text-[20px] text-night-ink leading-snug sm:leading-tight mb-3 sm:mb-4 line-clamp-2">
            {{ targetPoint.label }}
          </h3>

          <div class="flex items-center gap-5 sm:gap-6">
            <!-- Distance (Lucide Car) -->
            <div class="flex items-center gap-2">
              <Car class="size-4.5 sm:size-5 text-night-ink stroke-[2.2] transition-transform duration-200 group-hover:scale-110" />
              <span class="font-extrabold text-[14px] sm:text-[15px] text-night-ink">
                {{ routeInfo ? `${routeInfo.distanceKm} Km` : '3 Km' }}
              </span>
            </div>
            <!-- Duration (Lucide Clock) -->
            <div class="flex items-center gap-2">
              <Clock class="size-4 sm:size-4.5 text-night-ink stroke-[2.2] transition-transform duration-200 group-hover:scale-110" />
              <span class="font-extrabold text-[14px] sm:text-[15px] text-night-ink">
                {{ routeInfo ? `${routeInfo.durationMinutes} Menit` : '5 Menit' }}
              </span>
            </div>
          </div>
        </div>

        <!-- Overlay: Tactile Control Buttons (Mobile di Atas Kanan, Desktop di Bawah Kanan) -->
        <div
          class="absolute top-4 right-4 sm:top-auto sm:bottom-6 sm:right-6 z-[1000] flex flex-col gap-2.5 sm:gap-3"
        >
          <!-- Current Location GPS Button (Sky Blue Tactile + Lucide LocateFixed) -->
          <button
            type="button"
            class="size-[42px] sm:size-[47px] bg-sky-blue border-2 border-b-[4px] border-sky-blue-dark rounded-xl shadow-xs active:translate-y-0.5 active:border-b-2 flex items-center justify-center cursor-pointer hover:brightness-105 hover:scale-105 transition-all duration-200"
            title="Pusatkan ke lokasi"
            @click="panToCurrentLocation"
          >
            <LocateFixed class="size-5 sm:size-6 text-night-ink stroke-[2.2]" />
          </button>

          <!-- Zoom In Button (Eco Green Tactile + Lucide Plus) -->
          <button
            type="button"
            class="size-[42px] sm:size-[47px] bg-eco-green border-2 border-b-[4px] border-eco-green-dark rounded-xl shadow-xs active:translate-y-0.5 active:border-b-2 flex items-center justify-center cursor-pointer hover:brightness-105 hover:scale-105 transition-all duration-200"
            title="Perbesar Peta"
            @click="zoomIn"
          >
            <Plus class="size-5 sm:size-6 text-night-ink stroke-[2.5]" />
          </button>

          <!-- Zoom Out Button (Eco Green Tactile + Lucide Minus) -->
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

      <!-- Caption -->
      <p class="font-bold text-[12px] sm:text-[13px] tracking-[1.6px] text-charcoal mt-4">
        Peta dan estimasi perjalanan ditampilkan sebagai ilustrasi layanan penjemputan.
      </p>
    </div>
  </section>
</template>
