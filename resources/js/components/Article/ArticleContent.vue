<script setup lang="ts">
withDefaults(
    defineProps<{
        content: string;
    }>(),
    { content: '' },
);
</script>

<template>
    <!-- eslint-disable-next-line vue/no-v-html -->
    <div class="article-rendered-content" v-html="content" />
</template>

<style>
.article-rendered-content {
    color: var(--color-night-ink, #000437);
    font-size: 15px;
    line-height: 1.75;
}
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
    border-left: 4px solid var(--color-eco-green, #58cc02);
    background-color: var(--color-cloud-surface, #f7f7f7);
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
