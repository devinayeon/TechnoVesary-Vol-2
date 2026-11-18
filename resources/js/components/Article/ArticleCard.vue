<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { CalendarDays, Clock3, ImageOff } from 'lucide-vue-next';
import type { ArticleSummary } from '@/types/article';
import {
    articleExcerpt,
    articlePhotoUrl,
    articleReadingTime,
    formatArticleDate,
} from '@/lib/article';

const props = withDefaults(
    defineProps<{
        article: ArticleSummary;
        href: string;
        variant?: 'default' | 'compact';
    }>(),
    { variant: 'default' },
);

const photo = computed(() => articlePhotoUrl(props.article));
const excerpt = computed(() => articleExcerpt(props.article.content, 130));
const readingTime = computed(() => articleReadingTime(props.article.content));
</script>

<template>
    <Link
        :href="href"
        class="group flex flex-col justify-between rounded-[20px] border-2 border-b-[5px] border-border-gray bg-paper-white p-4 transition-all duration-300 ease-out hover:-translate-y-2 hover:border-eco-green hover:border-b-eco-green-dark hover:shadow-[0_16px_32px_-8px_rgba(88,204,2,0.22)] sm:p-5"
    >
        <div>
            <!-- Thumbnail -->
            <div
                class="mb-4 flex h-[160px] items-center justify-center overflow-hidden rounded-[14px] bg-sky-tint p-2"
                :class="variant === 'compact' ? 'sm:h-[130px]' : ''"
            >
                <img
                    v-if="photo"
                    :src="photo"
                    :alt="article.title"
                    loading="lazy"
                    class="h-full w-full rounded-lg object-cover transition-transform duration-300 ease-out group-hover:scale-105"
                />
                <ImageOff v-else class="size-10 text-charcoal/40" />
            </div>

            <!-- Category -->
            <span
                class="mb-3 inline-block rounded-full bg-sprout-tint px-2.5 py-1 text-[11px] font-bold tracking-wider text-night-ink uppercase"
            >
                {{ article.category?.name ?? 'Artikel' }}
            </span>

            <!-- Title -->
            <h3
                class="mb-2 line-clamp-2 text-[18px] leading-snug font-extrabold text-night-ink transition-colors duration-200 group-hover:text-eco-green-dark"
            >
                {{ article.title }}
            </h3>

            <!-- Excerpt -->
            <p
                class="mb-4 line-clamp-3 text-[14px] leading-relaxed font-medium text-charcoal"
            >
                {{ excerpt }}
            </p>
        </div>

        <!-- Meta -->
        <div
            class="mt-auto flex items-center justify-between gap-2 border-t border-border-gray/60 pt-3 text-[12px] font-semibold text-charcoal"
        >
            <span class="flex items-center gap-1.5">
                <CalendarDays class="size-3.5 text-charcoal/70" />
                {{ formatArticleDate(article.created_at) }}
            </span>
            <span class="flex items-center gap-1.5">
                <Clock3 class="size-3.5 text-charcoal/70" />
                {{ readingTime }} menit baca
            </span>
        </div>
    </Link>
</template>
