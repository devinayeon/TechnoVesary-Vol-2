<script setup lang="ts">
withDefaults(
    defineProps<{
        title: string;
        align?: 'left' | 'center';
    }>(),
    { align: 'left' },
);
</script>
<template>
    <h2
        class="text-3xl leading-tight font-bold text-gray-900 lg:text-4xl"
        :class="align === 'center' ? 'text-center' : 'text-left'"
    >
        {{ title }}
    </h2>
</template>
