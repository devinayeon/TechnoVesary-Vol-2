import type { DivIcon } from 'leaflet';

const PIN_PATH =
    'M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 10 4 10a8 8 0 0 1 16 0';

export interface PinIconOptions {
    size?: number;
    fill?: string;
    stroke?: string;
    dropShadow?: string;
    cursor?: string;
}

/**
 * Shared Lucide-style map pin marker. Used by landing, profile and picker maps
 * so every pin renders with the same visual language.
 */
export function createPinIcon(
    L: typeof import('leaflet'),
    {
        size = 42,
        fill = '#ef4444',
        stroke = '#000437',
        dropShadow = '0 4px 10px rgba(239,68,68,0.45)',
        cursor = 'pointer',
    }: PinIconOptions = {},
): DivIcon {
    return L.divIcon({
        html: `
            <div style="display:flex;align-items:center;justify-content:center;filter:drop-shadow(${dropShadow});cursor:${cursor};">
                <svg xmlns="http://www.w3.org/2000/svg" width="${size}" height="${size}" viewBox="0 0 24 24" fill="${fill}" stroke="${stroke}" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="${PIN_PATH}"/>
                    <circle cx="12" cy="10" r="3" fill="#ffffff" stroke="${stroke}" stroke-width="1.5"/>
                </svg>
            </div>
        `,
        className: '',
        iconSize: [size, size],
        iconAnchor: [size / 2, size],
    });
}

/**
 * Shared "current location" marker (red navigation arrow with pulse).
 */
export function createCurrentLocationIcon(
    L: typeof import('leaflet'),
    size = 44,
): DivIcon {
    return L.divIcon({
        html: `
            <div style="position:relative;width:${size}px;height:${size}px;display:flex;align-items:center;justify-content:center;">
                <span style="position:absolute;width:${size - 4}px;height:${size - 4}px;background:rgba(239,68,68,0.22);border-radius:50%;animation:ping 2s cubic-bezier(0,0,0.2,1) infinite;"></span>
                <div style="filter:drop-shadow(0 4px 6px rgba(0,4,55,0.4));transform:rotate(-15deg);display:flex;align-items:center;justify-content:center;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="${size - 10}" height="${size - 10}" viewBox="0 0 24 24" fill="#ef4444" stroke="#000437" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polygon points="3 11 22 2 13 21 11 13 3 11"/>
                    </svg>
                </div>
            </div>
        `,
        className: '',
        iconSize: [size, size],
        iconAnchor: [size / 2, size / 2],
    });
}
