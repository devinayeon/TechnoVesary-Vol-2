import { onUnmounted, ref } from 'vue';

export interface DriverCoords {
    latitude: number;
    longitude: number;
}

interface UseDriverLocationOptions {
    /** Jarak minimum (meter) sebelum posisi baru dikirim ke server. */
    minDistanceMeters?: number;
    /** Interval maksimum (ms) untuk mengirim heartbeat walau driver diam. */
    heartbeatMs?: number;
}

const DEFAULT_MIN_DISTANCE_METERS = 30;
const DEFAULT_HEARTBEAT_MS = 60_000;

/**
 * Hitung jarak dua koordinat dalam meter (Haversine).
 */
function distanceInMeters(a: DriverCoords, b: DriverCoords): number {
    const earthRadius = 6_371_000;
    const toRad = (value: number) => (value * Math.PI) / 180;

    const dLat = toRad(b.latitude - a.latitude);
    const dLng = toRad(b.longitude - a.longitude);

    const lat1 = toRad(a.latitude);
    const lat2 = toRad(b.latitude);

    const h =
        Math.sin(dLat / 2) ** 2 +
        Math.cos(lat1) * Math.cos(lat2) * Math.sin(dLng / 2) ** 2;

    return 2 * earthRadius * Math.asin(Math.sqrt(h));
}

function readXsrfToken(): string | null {
    if (typeof document === 'undefined') return null;

    const match = document.cookie.match(/(?:^|;\s*)XSRF-TOKEN=([^;]+)/);

    return match ? decodeURIComponent(match[1]) : null;
}

async function pushLocation(coords: DriverCoords): Promise<boolean> {
    const token = readXsrfToken();

    const response = await fetch('/driver/location', {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            ...(token ? { 'X-XSRF-TOKEN': token } : {}),
        },
        body: JSON.stringify(coords),
    });

    return response.ok;
}

/**
 * Ambil koordinat sekali (one-shot) untuk aksi seperti ambil tugas.
 */
export function getCurrentCoords(timeout = 8000): Promise<DriverCoords | null> {
    if (typeof navigator === 'undefined' || !navigator.geolocation) {
        return Promise.resolve(null);
    }

    return new Promise((resolve) => {
        navigator.geolocation.getCurrentPosition(
            (position) =>
                resolve({
                    latitude: position.coords.latitude,
                    longitude: position.coords.longitude,
                }),
            () => resolve(null),
            { timeout, enableHighAccuracy: true, maximumAge: 10_000 },
        );
    });
}

/**
 * Pantau posisi driver secara kontinu dan kirim ke server hanya ketika
 * driver benar-benar berpindah (>= minDistanceMeters) atau saat heartbeat.
 *
 * Menggunakan `watchPosition` agar pembaruan mengikuti pergerakan armada,
 * bukan polling timer tetap yang membuang baterai/kuota.
 */
export function useDriverLocation(options: UseDriverLocationOptions = {}) {
    const minDistance =
        options.minDistanceMeters ?? DEFAULT_MIN_DISTANCE_METERS;
    const heartbeatMs = options.heartbeatMs ?? DEFAULT_HEARTBEAT_MS;

    const isTracking = ref(false);
    const isSending = ref(false);
    const lastSentAt = ref<Date | null>(null);
    const error = ref<string | null>(null);

    let watchId: number | null = null;
    let lastSentCoords: DriverCoords | null = null;
    let lastSentTime = 0;

    function shouldSend(coords: DriverCoords): boolean {
        if (!lastSentCoords) return true;
        if (Date.now() - lastSentTime >= heartbeatMs) return true;

        return distanceInMeters(lastSentCoords, coords) >= minDistance;
    }

    async function send(coords: DriverCoords) {
        lastSentCoords = coords;
        lastSentTime = Date.now();
        isSending.value = true;

        try {
            const ok = await pushLocation(coords);

            if (ok) {
                lastSentAt.value = new Date();
                error.value = null;
            } else {
                error.value = 'Gagal mengirim lokasi.';
            }
        } catch {
            error.value = 'Gagal mengirim lokasi.';
        } finally {
            isSending.value = false;
        }
    }

    function handlePosition(position: GeolocationPosition) {
        const coords: DriverCoords = {
            latitude: position.coords.latitude,
            longitude: position.coords.longitude,
        };

        if (shouldSend(coords)) {
            void send(coords);
        }
    }

    function handleError(geoError: GeolocationPositionError) {
        error.value =
            geoError.code === geoError.PERMISSION_DENIED
                ? 'Izin lokasi ditolak. Aktifkan izin lokasi pada browser.'
                : 'Lokasi tidak dapat dibaca saat ini.';
    }

    function start() {
        if (watchId !== null) return;

        if (typeof navigator === 'undefined' || !navigator.geolocation) {
            error.value = 'Perangkat tidak mendukung GPS.';
            return;
        }

        error.value = null;
        isTracking.value = true;

        watchId = navigator.geolocation.watchPosition(
            handlePosition,
            handleError,
            {
                enableHighAccuracy: true,
                maximumAge: 5_000,
                timeout: 20_000,
            },
        );
    }

    function stop() {
        if (watchId !== null && typeof navigator !== 'undefined') {
            navigator.geolocation.clearWatch(watchId);
        }

        watchId = null;
        isTracking.value = false;
    }

    async function sendOnce(): Promise<DriverCoords | null> {
        const coords = await getCurrentCoords();

        if (coords) {
            await send(coords);
        }

        return coords;
    }

    onUnmounted(stop);

    return {
        isTracking,
        isSending,
        lastSentAt,
        error,
        start,
        stop,
        sendOnce,
    };
}
