<script setup lang="ts">
import StatusChip from './StatusChip.vue';

withDefaults(
    defineProps<{
        name: string;
        meta: string;
        weight: string;
        statusText?: string;
        statusTone?: 'Green' | 'Neutral' | 'Blue' | 'Amber';
    }>(),
    {
        statusText: 'Selesai',
        statusTone: 'Green',
    },
);
</script>

<template>
    <div
        class="flex cursor-default items-center justify-between gap-4 rounded-xl border-b border-border-gray/70 px-2 py-3 transition-colors hover:bg-cloud-surface/50"
    >
        <!-- Identity & Code/Date -->
        <div class="min-w-0 flex-1">
            <p
                class="truncate text-[14px] leading-tight font-bold text-night-ink"
            >
                {{ name }}
            </p>
            <p class="mt-0.5 truncate text-[12px] font-normal text-charcoal">
                {{ meta }}
            </p>
        </div>

        <!-- Weight Amount -->
        <div class="shrink-0 text-[14px] font-bold text-night-ink">
            {{ weight }}
        </div>

        <!-- Status Chip -->
        <div class="shrink-0">
            <StatusChip :label="statusText" :tone="statusTone" />
        </div>
    </div>
</template>
