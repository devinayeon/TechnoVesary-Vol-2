import type { Map as LeafletMap, TileLayer } from 'leaflet';

const CARTO_LIGHT_URL =
    'https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png';
const OSM_FALLBACK_URL = 'https://tile.openstreetmap.org/{z}/{x}/{y}.png';
const FALLBACK_BASEMAP_KEY = 'cb1_5b31_1_87452d52cb0b1e9b2c8e7a7a';

/**
 * Resolve the Carto basemap API key using the same precedence everywhere:
 * server-provided prop → Vite env → bundled fallback.
 */
export function resolveBasemapApiKey(explicitKey?: string): string {
    const key =
        explicitKey ||
        import.meta.env.VITE_BASEMAP_API_KEY ||
        FALLBACK_BASEMAP_KEY;

    return String(key ?? '').trim();
}

export function buildCartoBasemapUrl(apiKey?: string): string {
    const key = resolveBasemapApiKey(apiKey);

    return key
        ? `${CARTO_LIGHT_URL}?key=${encodeURIComponent(key)}`
        : CARTO_LIGHT_URL;
}

/**
 * Add the shared Carto light basemap to a Leaflet map.
 * Automatically falls back to OpenStreetMap if the Carto CDN rejects tiles.
 */
export function addCartoBasemap(
    L: typeof import('leaflet'),
    map: LeafletMap,
    apiKey?: string,
): TileLayer {
    const layer = L.tileLayer(buildCartoBasemapUrl(apiKey), {
        maxZoom: 19,
        subdomains: 'abcd',
    }).addTo(map);

    let fallbackAdded = false;
    layer.on('tileerror', () => {
        if (fallbackAdded) return;
        fallbackAdded = true;
        L.tileLayer(OSM_FALLBACK_URL, { maxZoom: 19 }).addTo(map);
    });

    return layer;
}
