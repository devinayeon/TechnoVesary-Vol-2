<script setup lang="ts">
defineProps<{
    month: string;
    value: string;
    heightPercentage: number;
    isHighlighted?: boolean;
}>();
</script>

<template>
    <div class="flex flex-col items-center justify-end h-full gap-2 group cursor-default">
        <!-- Value Label -->
        <span
            class="text-[12px] font-bold transition-colors"
            :class="isHighlighted ? 'text-eco-green-dark scale-105' : 'text-night-ink group-hover:text-eco-green-dark'"
        >
            {{ value }}
        </span>

        <!-- Bar Element -->
        <div class="w-[36px] sm:w-[42px] bg-cloud-surface rounded-[8px] flex items-end overflow-hidden h-[150px]">
            <div
                class="w-full rounded-[8px] transition-all duration-500 ease-out group-hover:brightness-95"
                :class="isHighlighted ? 'bg-eco-green border-2 border-eco-green-dark' : 'bg-eco-green/85'"
                :style="{ height: `${Math.min(Math.max(heightPercentage, 8), 100)}%` }"
            ></div>
        </div>

        <!-- Month Label -->
        <span
            class="text-[12px] font-semibold transition-colors"
            :class="isHighlighted ? 'text-night-ink font-bold' : 'text-charcoal group-hover:text-night-ink'"
        >
            {{ month }}
        </span>
    </div>
</template>
