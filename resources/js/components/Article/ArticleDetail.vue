<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { ArrowLeft, CalendarDays, Clock3, ImageOff } from 'lucide-vue-next';
import type { ArticleSummary } from '@/types/article';
import ArticleCard from './ArticleCard.vue';
import ArticleContent from './ArticleContent.vue';
import UserAvatar from '@/components/Dashboard/UserAvatar.vue';
import {
    articlePhotoUrl,
    articleReadingTime,
    formatArticleDate,
} from '@/lib/article';

const props = withDefaults(
    defineProps<{
        article: ArticleSummary;
        related?: ArticleSummary[];
        detailHrefPrefix?: string;
        backHref: string;
        backLabel?: string;
    }>(),
    {
        related: () => [],
        detailHrefPrefix: '/artikel',
        backLabel: 'Kembali',
    },
);

const photo = computed(() => articlePhotoUrl(props.article));
const authorName = computed(
    () => props.article.admin?.user?.name ?? 'Tim Edukasi ReValue',
);
const authorPhoto = computed(() => props.article.admin?.user?.photo ?? null);
const readingTime = computed(() => articleReadingTime(props.article.content));
</script>

<template>
    <div class="flex flex-col gap-6">
        <!-- Back Link -->
        <Link
            :href="backHref"
            class="inline-flex w-fit items-center gap-1.5 text-[13px] font-extrabold tracking-wide text-charcoal uppercase transition-colors hover:text-eco-green-dark"
        >
            <ArrowLeft class="size-4" />
            {{ backLabel }}
        </Link>

        <article
            class="overflow-hidden rounded-[22px] border-2 border-b-[4px] border-border-gray bg-paper-white shadow-xs"
        >
            <!-- Header -->
            <header class="flex flex-col gap-4 p-6 sm:p-8">
                <span
                    class="inline-flex w-fit items-center rounded-full border border-eco-green/30 bg-sprout-tint px-2.5 py-1 text-[11px] font-extrabold tracking-wider text-eco-green-dark uppercase"
                >
                    {{ article.category?.name ?? 'Artikel' }}
                </span>

                <h1
                    class="text-[26px] leading-tight font-black tracking-tight text-night-ink sm:text-[32px]"
                >
                    {{ article.title }}
                </h1>

                <div
                    class="flex flex-wrap items-center gap-x-5 gap-y-2 text-[12px] font-bold text-charcoal/80"
                >
                    <span class="flex items-center gap-2">
                        <UserAvatar
                            :photo="authorPhoto"
                            :role="article.admin?.user?.role ?? 'admin'"
                            :name="authorName"
                            size="sm"
                        />
                        {{ authorName }}
                    </span>
                    <span class="flex items-center gap-1.5">
                        <CalendarDays class="size-3.5" />
                        {{ formatArticleDate(article.created_at, 'long') }}
                    </span>
                    <span class="flex items-center gap-1.5">
                        <Clock3 class="size-3.5" />
                        {{ readingTime }} menit baca
                    </span>
                </div>
            </header>

            <!-- Hero Image -->
            <div
                v-if="photo"
                class="border-y-2 border-border-gray/60 bg-cloud-surface"
            >
                <img
                    :src="photo"
                    :alt="article.title"
                    class="mx-auto max-h-[420px] w-full object-cover"
                />
            </div>

            <!-- Body -->
            <div class="p-6 sm:p-8">
                <ArticleContent :content="article.content" />
            </div>
        </article>

        <!-- Related Articles -->
        <section v-if="related.length > 0" class="flex flex-col gap-4">
            <div class="flex flex-col gap-1">
                <h2
                    class="text-[20px] font-extrabold text-night-ink sm:text-[22px]"
                >
                    Artikel terkait
                </h2>
                <p class="text-[13px] font-medium text-charcoal">
                    Lanjutkan membaca topik serupa dari kategori
                    {{ article.category?.name ?? 'edukasi' }}.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-3">
                <ArticleCard
                    v-for="item in related"
                    :key="item.id"
                    :article="item"
                    :href="`${detailHrefPrefix}/${item.slug}`"
                    variant="compact"
                />
            </div>
        </section>
    </div>
</template>
