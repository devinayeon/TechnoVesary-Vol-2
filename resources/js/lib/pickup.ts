import type {
    PickupDetailItem,
    PickupStatusValue,
    PickupSummary,
} from '@/types/pickup';

const STATUS_LABELS: Record<PickupStatusValue, string> = {
    pending: 'Menunggu Driver',
    on_the_way: 'Driver Menuju',
    picked_up: 'Sedang Ditimbang',
    completed: 'Selesai',
    canceled: 'Dibatalkan',
};

const STATUS_DESCRIPTIONS: Record<PickupStatusValue, string> = {
    pending: 'Pengajuan menunggu driver ditugaskan.',
    on_the_way: 'Driver sedang menuju lokasi penjemputan.',
    picked_up: 'Sampah sedang ditimbang oleh driver.',
    completed: 'Penjemputan selesai dan poin telah diterbitkan.',
    canceled: 'Pengajuan penjemputan dibatalkan.',
};

const STATUS_TONES: Record<
    PickupStatusValue,
    'Green' | 'Blue' | 'Amber' | 'Neutral'
> = {
    pending: 'Amber',
    on_the_way: 'Blue',
    picked_up: 'Amber',
    completed: 'Green',
    canceled: 'Neutral',
};

export function pickupStatusLabel(status?: string | null): string {
    if (!status) return '-';

    return STATUS_LABELS[status as PickupStatusValue] ?? status;
}

export function pickupStatusDescription(status?: string | null): string {
    if (!status) return '';

    return STATUS_DESCRIPTIONS[status as PickupStatusValue] ?? '';
}

export function pickupStatusTone(
    status?: string | null,
): 'Green' | 'Blue' | 'Amber' | 'Neutral' {
    if (!status) return 'Neutral';

    return STATUS_TONES[status as PickupStatusValue] ?? 'Neutral';
}

/**
 * Driver sudah ditugaskan pada status ini.
 */
export function pickupHasDriver(status?: string | null): boolean {
    return ['on_the_way', 'picked_up', 'completed'].includes(status ?? '');
}

/**
 * Poin sudah diterbitkan pada status ini.
 */
export function pickupIsSettled(status?: string | null): boolean {
    return status === 'completed';
}

/**
 * Customer masih boleh membatalkan pengajuan.
 */
export function pickupIsCancelable(status?: string | null): boolean {
    return status === 'pending';
}

export function pickupPhotoUrl(pickup: PickupSummary): string | null {
    if (pickup.photo_url) return pickup.photo_url;
    if (!pickup.photo) return null;

    return pickup.photo.startsWith('http') || pickup.photo.startsWith('/')
        ? pickup.photo
        : `/storage/${pickup.photo}`;
}

export function pickupProofPhotoUrl(pickup: PickupSummary): string | null {
    if (pickup.pickup_proof_photo_url) return pickup.pickup_proof_photo_url;
    if (!pickup.pickup_proof_photo) return null;

    return pickup.pickup_proof_photo.startsWith('http') ||
        pickup.pickup_proof_photo.startsWith('/')
        ? pickup.pickup_proof_photo
        : `/storage/${pickup.pickup_proof_photo}`;
}

export function formatWeight(value?: number | string | null): string {
    if (value === null || value === undefined || value === '') return '-';

    const numeric = Number(value);
    if (Number.isNaN(numeric)) return '-';

    return `${numeric.toLocaleString('id-ID', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    })} kg`;
}

export function formatPoints(value?: number | null): string {
    if (value === null || value === undefined) return '-';

    return `${Number(value).toLocaleString('id-ID')} poin`;
}

export function formatPickupDate(
    dateString?: string | null,
    withTime = false,
): string {
    if (!dateString) return '-';

    const date = new Date(dateString);
    if (Number.isNaN(date.getTime())) return dateString;

    return date.toLocaleDateString('id-ID', {
        day: 'numeric',
        month: withTime ? 'long' : 'short',
        year: 'numeric',
        ...(withTime ? { hour: '2-digit', minute: '2-digit' } : {}),
    });
}

export function pickupTotalPoints(details?: PickupDetailItem[] | null): number {
    if (!details) return 0;

    return details.reduce(
        (total, detail) => total + Number(detail.subtotal_points ?? 0),
        0,
    );
}

export function pickupTotalWeight(details?: PickupDetailItem[] | null): number {
    if (!details) return 0;

    return details.reduce(
        (total, detail) => total + Number(detail.weight ?? 0),
        0,
    );
}

export function pickupCategoryNames(pickup: PickupSummary): string {
    const names = (pickup.details ?? [])
        .map((detail) => detail.waste_category?.name)
        .filter(Boolean) as string[];

    return names.length > 0 ? names.join(', ') : '-';
}
