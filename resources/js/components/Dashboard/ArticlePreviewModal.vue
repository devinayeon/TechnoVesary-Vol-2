<script setup lang="ts">
import { X, Calendar, User, Tag, ExternalLink } from "lucide-vue-next";
import StatusChip from "@/components/Dashboard/StatusChip.vue";

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
    (e: "close"): void;
}>();

const formatDate = (dateStr?: string | null): string => {
    if (!dateStr) return "—";
    try {
        const d = new Date(dateStr);
        return d.toLocaleDateString("id-ID", {
            day: "numeric",
            month: "long",
            year: "numeric",
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
                class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-night-ink/50 backdrop-blur-xs overflow-y-auto"
                @click.self="emit('close')"
            >
                <div
                    class="w-full max-w-[760px] bg-paper-white border-2 border-border-gray rounded-[24px] p-6 sm:p-8 shadow-2xl relative my-8 animate-in fade-in zoom-in-95 duration-150 max-h-[90vh] flex flex-col"
                >
                    <!-- Header -->
                    <div
                        class="flex items-start justify-between gap-4 pb-4 border-b border-border-gray/70 shrink-0"
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
                                class="text-[20px] sm:text-[24px] font-black text-night-ink leading-tight tracking-tight"
                            >
                                {{ article.title }}
                            </h2>
                            <div
                                class="flex flex-wrap items-center gap-4 text-[12px] font-bold text-charcoal/80 pt-1"
                            >
                                <span class="flex items-center gap-1.5">
                                    <User class="size-3.5 text-charcoal" />
                                    {{ article.admin?.user?.name || "Admin ReValue" }}
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
                            class="p-1.5 rounded-xl text-charcoal hover:bg-cloud-surface hover:text-night-ink transition-colors cursor-pointer shrink-0"
                            aria-label="Tutup preview"
                        >
                            <X class="size-5" />
                        </button>
                    </div>

                    <!-- Scrollable Article Body -->
                    <div class="overflow-y-auto py-5 pr-1 flex-1 space-y-5">
                        <!-- Thumbnail Image -->
                        <div
                            v-if="article.photo_url"
                            class="w-full max-h-[360px] rounded-2xl overflow-hidden border-2 border-border-gray bg-cloud-surface shadow-xs"
                        >
                            <img
                                :src="article.photo_url"
                                :alt="article.title"
                                class="w-full h-full object-cover"
                            />
                        </div>

                        <!-- Article Content Render -->
                        <div
                            class="article-rendered-content text-[15px] leading-relaxed text-night-ink"
                            v-html="article.content"
                        />
                    </div>

                    <!-- Footer -->
                    <div
                        class="pt-4 border-t border-border-gray/70 flex items-center justify-between shrink-0"
                    >
                        <span class="text-[12px] font-medium text-charcoal">
                            Slug: <code class="px-2 py-0.5 rounded-md bg-cloud-surface text-night-ink font-bold">/{{ article.slug }}</code>
                        </span>
                        <button
                            type="button"
                            @click="emit('close')"
                            class="px-5 h-[42px] rounded-xl border-2 border-b-[4px] border-border-gray bg-paper-white font-extrabold text-[13px] text-night-ink hover:bg-cloud-surface active:translate-y-0.5 active:border-b-2 transition-all cursor-pointer"
                        >
                            Tutup Preview
                        </button>
                    </div>
                </div>
            </div>
        </transition>
    </teleport>
</template>

<style>
.article-rendered-content h1 {
    font-size: 24px;
    font-weight: 900;
    margin-top: 1.2em;
    margin-bottom: 0.5em;
    line-height: 1.3;
}
.article-rendered-content h2 {
    font-size: 20px;
    font-weight: 800;
    margin-top: 1.1em;
    margin-bottom: 0.4em;
    line-height: 1.3;
}
.article-rendered-content h3 {
    font-size: 17px;
    font-weight: 800;
    margin-top: 0.9em;
    margin-bottom: 0.3em;
    line-height: 1.4;
}
.article-rendered-content p {
    margin-bottom: 0.9em;
}
.article-rendered-content ul {
    list-style-type: disc;
    padding-left: 1.5em;
    margin: 0.75em 0;
}
.article-rendered-content ol {
    list-style-type: decimal;
    padding-left: 1.5em;
    margin: 0.75em 0;
}
.article-rendered-content blockquote {
    border-left: 4px solid var(--color-eco-green, #10b981);
    background-color: var(--color-cloud-surface, #f9fafb);
    padding: 0.75em 1.2em;
    margin: 1em 0;
    border-radius: 0 12px 12px 0;
    font-style: italic;
}
.article-rendered-content img {
    max-width: 100%;
    border-radius: 12px;
    margin: 1em 0;
}
.article-rendered-content iframe {
    width: 100%;
    aspect-ratio: 16 / 9;
    border-radius: 12px;
    margin: 1em 0;
}
</style>
