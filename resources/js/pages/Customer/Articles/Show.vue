<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import DashboardLayout from '@/layouts/DashboardLayout.vue';
import { ArticleDetail } from '@/components/Article';
import type { ArticleSummary } from '@/types/article';

defineProps<{
    article: ArticleSummary;
    related: ArticleSummary[];
}>();
</script>

<template>
    <Head :title="`${article.title} - ReValue`" />

    <DashboardLayout>
        <ArticleDetail
            :article="article"
            :related="related"
            detail-href-prefix="/customer/articles"
            back-href="/customer/articles"
            back-label="Kembali ke daftar artikel"
        />
    </DashboardLayout>
</template>
