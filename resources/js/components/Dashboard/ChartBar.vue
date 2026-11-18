<script setup lang="ts">
defineProps<{
    month: string;
    value: string;
    heightPercentage: number;
    isHighlighted?: boolean;
}>();
</script>

<template>
    <div
        class="group flex h-full cursor-default flex-col items-center justify-end gap-2"
    >
        <!-- Value Label -->
        <span
            class="text-[12px] font-bold transition-colors"
            :class="
                isHighlighted
                    ? 'scale-105 text-eco-green-dark'
                    : 'text-night-ink group-hover:text-eco-green-dark'
            "
        >
            {{ value }}
        </span>

        <!-- Bar Element -->
        <div
            class="flex h-[150px] w-[36px] items-end overflow-hidden rounded-[8px] bg-cloud-surface sm:w-[42px]"
        >
            <div
                class="w-full rounded-[8px] transition-all duration-500 ease-out group-hover:brightness-95"
                :class="
                    isHighlighted
                        ? 'border-2 border-eco-green-dark bg-eco-green'
                        : 'bg-eco-green/85'
                "
                :style="{
                    height: `${Math.min(Math.max(heightPercentage, 8), 100)}%`,
                }"
            ></div>
        </div>

        <!-- Month Label -->
        <span
            class="text-[12px] font-semibold transition-colors"
            :class="
                isHighlighted
                    ? 'font-bold text-night-ink'
                    : 'text-charcoal group-hover:text-night-ink'
            "
        >
            {{ month }}
        </span>
    </div>
</template>
