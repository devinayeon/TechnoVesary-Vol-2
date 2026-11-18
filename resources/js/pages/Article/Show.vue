<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import PublicLayout from '@/layouts/PublicLayout.vue';
import { ArticleDetail } from '@/components/Article';
import type { ArticleSummary } from '@/types/article';

defineProps<{
    article: ArticleSummary;
    related: ArticleSummary[];
}>();
</script>

<template>
    <Head :title="`${article.title} - ReValue`" />

    <PublicLayout>
        <ArticleDetail
            :article="article"
            :related="related"
            detail-href-prefix="/artikel"
            back-href="/#artikel"
            back-label="Kembali ke pusat edukasi"
        />
    </PublicLayout>
</template>
