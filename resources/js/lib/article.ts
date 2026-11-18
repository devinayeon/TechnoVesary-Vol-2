import type { ArticleSummary } from '@/types/article';

/**
 * Ubah konten HTML artikel menjadi teks polos.
 */
export function stripHtml(html?: string | null): string {
    if (!html) return '';

    return html
        .replace(/<[^>]+>/g, ' ')
        .replace(/&nbsp;/g, ' ')
        .replace(/&amp;/g, '&')
        .replace(/\s+/g, ' ')
        .trim();
}

/**
 * Ringkasan singkat artikel untuk kartu daftar.
 */
export function articleExcerpt(html?: string | null, length = 140): string {
    const text = stripHtml(html);

    return text.length > length
        ? `${text.slice(0, length).trimEnd()}...`
        : text;
}

/**
 * Estimasi waktu baca (menit) berdasarkan jumlah kata.
 */
export function articleReadingTime(html?: string | null): number {
    const words = stripHtml(html).split(' ').filter(Boolean).length;

    return Math.max(1, Math.ceil(words / 200));
}

/**
 * URL gambar sampul artikel dengan fallback path storage.
 */
export function articlePhotoUrl(article: ArticleSummary): string | null {
    if (article.photo_url) return article.photo_url;
    if (!article.photo) return null;

    return article.photo.startsWith('http') || article.photo.startsWith('/')
        ? article.photo
        : `/storage/${article.photo}`;
}

/**
 * Format tanggal publikasi artikel dalam Bahasa Indonesia.
 */
export function formatArticleDate(
    dateString?: string | null,
    style: 'short' | 'long' = 'short',
): string {
    if (!dateString) return '-';

    const date = new Date(dateString);
    if (Number.isNaN(date.getTime())) return dateString;

    return date.toLocaleDateString('id-ID', {
        day: 'numeric',
        month: style === 'long' ? 'long' : 'short',
        year: 'numeric',
    });
}
