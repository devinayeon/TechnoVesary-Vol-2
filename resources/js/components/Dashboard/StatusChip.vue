<script setup lang="ts">
withDefaults(
    defineProps<{
        label: string;
        tone?: "Neutral" | "Green" | "Blue" | "Amber";
    }>(),
    {
        tone: "Neutral",
    }
);
</script>

<template>
    <div
        class="inline-flex items-center justify-center px-2.5 py-1 rounded-[8px] text-[12px] font-bold tracking-wide whitespace-nowrap select-none"
        :class="{
            'bg-sprout-tint text-night-ink border border-eco-green/30': tone === 'Green',
            'bg-cloud-surface text-night-ink border border-border-gray': tone === 'Neutral',
            'bg-sky-tint text-night-ink border border-sky-blue/30': tone === 'Blue',
            'bg-gold-tint text-night-ink border border-sunlight-gold/30': tone === 'Amber',
        }"
    >
        {{ label }}
    </div>
</template>
