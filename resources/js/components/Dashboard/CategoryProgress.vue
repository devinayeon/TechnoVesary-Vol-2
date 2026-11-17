<script setup lang="ts">
withDefaults(
    defineProps<{
        label: string;
        value: string;
        percentage: number;
        color?: string;
    }>(),
    {
        color: "bg-eco-green",
    }
);
</script>

<template>
    <div class="flex flex-col gap-2 w-full">
        <!-- Label and Value -->
        <div class="flex items-center justify-between text-[13px] leading-tight">
            <span class="font-bold text-night-ink">
                {{ label }}
            </span>
            <span class="font-semibold text-charcoal">
                {{ value }}
            </span>
        </div>

        <!-- Track & Progress Bar -->
        <div class="bg-cloud-surface border border-border-gray/60 h-[8px] w-full rounded-[4px] overflow-hidden">
            <div
                class="h-full rounded-[4px] transition-all duration-500 ease-out"
                :class="color"
                :style="{ width: `${Math.min(Math.max(percentage, 0), 100)}%` }"
            ></div>
        </div>
    </div>
</template>
