<script setup lang="ts">
withDefaults(
    defineProps<{
        label: string;
        tone?: 'Neutral' | 'Green' | 'Blue' | 'Amber';
    }>(),
    {
        tone: 'Neutral',
    },
);
</script>

<template>
    <div
        class="inline-flex items-center justify-center rounded-[8px] px-2.5 py-1 text-[12px] font-bold tracking-wide whitespace-nowrap select-none"
        :class="{
            'border border-eco-green/30 bg-sprout-tint text-night-ink':
                tone === 'Green',
            'border border-border-gray bg-cloud-surface text-night-ink':
                tone === 'Neutral',
            'border border-sky-blue/30 bg-sky-tint text-night-ink':
                tone === 'Blue',
            'border border-sunlight-gold/30 bg-gold-tint text-night-ink':
                tone === 'Amber',
        }"
    >
        {{ label }}
    </div>
</template>
