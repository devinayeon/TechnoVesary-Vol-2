import type { PointTypeValue } from '@/types/point';

const TYPE_LABELS: Record<PointTypeValue, string> = {
    in: 'Poin Masuk',
    out: 'Poin Keluar',
};

const TYPE_TONES: Record<
    PointTypeValue,
    'Green' | 'Blue' | 'Amber' | 'Neutral'
> = {
    in: 'Green',
    out: 'Amber',
};

export function pointTypeLabel(type?: string | null): string {
    if (!type) return '-';

    return TYPE_LABELS[type as PointTypeValue] ?? type;
}

export function pointTypeTone(
    type?: string | null,
): 'Green' | 'Blue' | 'Amber' | 'Neutral' {
    if (!type) return 'Neutral';

    return TYPE_TONES[type as PointTypeValue] ?? 'Neutral';
}

/**
 * Jumlah mutasi poin dengan tanda +/- sesuai tipe.
 */
export function formatPointMutation(
    amount: number | string | null | undefined,
    type?: string | null,
): string {
    const numeric = Number(amount ?? 0);
    const formatted = Math.abs(numeric).toLocaleString('id-ID');

    return type === 'out' ? `-${formatted}` : `+${formatted}`;
}

export function formatPointDate(dateString?: string | null): string {
    if (!dateString) return '-';

    const date = new Date(dateString);
    if (Number.isNaN(date.getTime())) return dateString;

    return date.toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
}

export function formatPointDateTime(dateString?: string | null): string {
    if (!dateString) return '-';

    const date = new Date(dateString);
    if (Number.isNaN(date.getTime())) return dateString;

    return date.toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}
