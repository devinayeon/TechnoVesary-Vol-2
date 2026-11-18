<script setup lang="ts">
import { X, Calendar, User, Tag, ExternalLink } from 'lucide-vue-next';
import StatusChip from '@/components/Dashboard/StatusChip.vue';
import { ArticleContent } from '@/components/Article';

export interface ArticlePreviewData {
    id: number;
    title: string;
    slug: string;
    photo_url?: string | null;
    content: string;
    created_at: string;
    deleted_at: string | null;
    category?: { id: number; name: string } | null;
    admin?: {
        id: number;
        user?: {
            name: string;
            email: string;
        } | null;
    } | null;
}

defineProps<{
    show: boolean;
    article: ArticlePreviewData | null;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
}>();

const formatDate = (dateStr?: string | null): string => {
    if (!dateStr) return '—';
    try {
        const d = new Date(dateStr);
        return d.toLocaleDateString('id-ID', {
            day: 'numeric',
            month: 'long',
            year: 'numeric',
        });
    } catch {
        return dateStr;
    }
};
</script>

<template>
    <teleport to="body">
        <transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="show && article"
                class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-night-ink/50 p-4 backdrop-blur-xs"
                @click.self="emit('close')"
            >
                <div
                    class="animate-in fade-in zoom-in-95 relative my-8 flex max-h-[90vh] w-full max-w-[760px] flex-col rounded-[24px] border-2 border-border-gray bg-paper-white p-6 shadow-2xl duration-150 sm:p-8"
                >
                    <!-- Header -->
                    <div
                        class="flex shrink-0 items-start justify-between gap-4 border-b border-border-gray/70 pb-4"
                    >
                        <div class="space-y-1.5">
                            <div class="flex items-center gap-2">
                                <StatusChip
                                    :label="article.category?.name || 'Umum'"
                                    tone="Green"
                                />
                                <StatusChip
                                    v-if="article.deleted_at"
                                    label="Nonaktif"
                                    tone="Neutral"
                                />
                            </div>
                            <h2
                                class="text-[20px] leading-tight font-black tracking-tight text-night-ink sm:text-[24px]"
                            >
                                {{ article.title }}
                            </h2>
                            <div
                                class="flex flex-wrap items-center gap-4 pt-1 text-[12px] font-bold text-charcoal/80"
                            >
                                <span class="flex items-center gap-1.5">
                                    <User class="size-3.5 text-charcoal" />
                                    {{
                                        article.admin?.user?.name ||
                                        'Admin ReValue'
                                    }}
                                </span>
                                <span class="flex items-center gap-1.5">
                                    <Calendar class="size-3.5 text-charcoal" />
                                    {{ formatDate(article.created_at) }}
                                </span>
                            </div>
                        </div>

                        <button
                            type="button"
                            @click="emit('close')"
                            class="shrink-0 cursor-pointer rounded-xl p-1.5 text-charcoal transition-colors hover:bg-cloud-surface hover:text-night-ink"
                            aria-label="Tutup preview"
                        >
                            <X class="size-5" />
                        </button>
                    </div>

                    <!-- Scrollable Article Body -->
                    <div class="flex-1 space-y-5 overflow-y-auto py-5 pr-1">
                        <!-- Thumbnail Image -->
                        <div
                            v-if="article.photo_url"
                            class="max-h-[360px] w-full overflow-hidden rounded-2xl border-2 border-border-gray bg-cloud-surface shadow-xs"
                        >
                            <img
                                :src="article.photo_url"
                                :alt="article.title"
                                class="h-full w-full object-cover"
                            />
                        </div>

                        <!-- Article Content Render -->
                        <div class="text-[15px] leading-relaxed text-night-ink">
                            <ArticleContent :content="article.content" />
                        </div>
                    </div>

                    <!-- Footer -->
                    <div
                        class="flex shrink-0 items-center justify-between border-t border-border-gray/70 pt-4"
                    >
                        <span class="text-[12px] font-medium text-charcoal">
                            Slug:
                            <code
                                class="rounded-md bg-cloud-surface px-2 py-0.5 font-bold text-night-ink"
                                >/{{ article.slug }}</code
                            >
                        </span>
                        <button
                            type="button"
                            @click="emit('close')"
                            class="h-[42px] cursor-pointer rounded-xl border-2 border-b-[4px] border-border-gray bg-paper-white px-5 text-[13px] font-extrabold text-night-ink transition-all hover:bg-cloud-surface active:translate-y-0.5 active:border-b-2"
                        >
                            Tutup Preview
                        </button>
                    </div>
                </div>
            </div>
        </transition>
    </teleport>
</template>
