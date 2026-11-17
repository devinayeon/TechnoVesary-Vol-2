<script setup lang="ts">
import StatusChip from "./StatusChip.vue";

withDefaults(
    defineProps<{
        name: string;
        meta: string;
        weight: string;
        statusText?: string;
        statusTone?: "Green" | "Neutral" | "Blue" | "Amber";
    }>(),
    {
        statusText: "Selesai",
        statusTone: "Green",
    }
);
</script>

<template>
    <div
        class="flex items-center justify-between gap-4 py-3 px-2 border-b border-border-gray/70 hover:bg-cloud-surface/50 rounded-xl transition-colors cursor-default"
    >
        <!-- Identity & Code/Date -->
        <div class="flex-1 min-w-0">
            <p class="font-bold text-[14px] text-night-ink truncate leading-tight">
                {{ name }}
            </p>
            <p class="text-[12px] font-normal text-charcoal truncate mt-0.5">
                {{ meta }}
            </p>
        </div>

        <!-- Weight Amount -->
        <div class="font-bold text-[14px] text-night-ink shrink-0">
            {{ weight }}
        </div>

        <!-- Status Chip -->
        <div class="shrink-0">
            <StatusChip :label="statusText" :tone="statusTone" />
        </div>
    </div>
</template>
