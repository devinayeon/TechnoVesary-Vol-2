<script setup lang="ts">
import type { Component } from "vue";

defineProps<{
    label: string;
    value: string;
    sublabel?: string;
    icon?: Component;
    iconColor?: string;
}>();
</script>

<template>
    <div
        class="bg-paper-white border-2 border-b-[4px] border-border-gray rounded-[20px] p-5 xl:p-6 flex flex-col justify-between min-h-[132px] hover:-translate-y-1 hover:border-night-ink/20 hover:shadow-md transition-all duration-200 cursor-default group"
    >
        <!-- Top: Label & Icon -->
        <div class="flex items-center justify-between gap-3 w-full mb-1">
            <span class="text-[14px] font-semibold text-charcoal leading-snug">
                {{ label }}
            </span>
            <div
                v-if="icon"
                class="size-8 rounded-lg bg-cloud-surface flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform"
                :class="iconColor || 'text-charcoal'"
            >
                <component :is="icon" class="size-4.5" />
            </div>
        </div>

        <!-- Middle: Primary Value -->
        <div>
            <div
                class="text-[26px] xl:text-[28px] font-extrabold text-night-ink tracking-tight leading-tight"
            >
                {{ value }}
            </div>

            <!-- Bottom: Sublabel / Meta -->
            <p v-if="sublabel" class="text-[12px] font-medium text-charcoal/80 mt-1">
                {{ sublabel }}
            </p>
        </div>
    </div>
</template>
