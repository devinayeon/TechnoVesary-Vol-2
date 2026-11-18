<script setup lang="ts">
import type { Component } from 'vue';

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
        class="group flex min-h-[132px] cursor-default flex-col justify-between rounded-[20px] border-2 border-b-[4px] border-border-gray bg-paper-white p-5 transition-all duration-200 hover:-translate-y-1 hover:border-night-ink/20 hover:shadow-md xl:p-6"
    >
        <!-- Top: Label & Icon -->
        <div class="mb-1 flex w-full items-center justify-between gap-3">
            <span class="text-[14px] leading-snug font-semibold text-charcoal">
                {{ label }}
            </span>
            <div
                v-if="icon"
                class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-cloud-surface transition-transform group-hover:scale-105"
                :class="iconColor || 'text-charcoal'"
            >
                <component :is="icon" class="size-4.5" />
            </div>
        </div>

        <!-- Middle: Primary Value -->
        <div>
            <div
                class="text-[26px] leading-tight font-extrabold tracking-tight text-night-ink xl:text-[28px]"
            >
                {{ value }}
            </div>

            <!-- Bottom: Sublabel / Meta -->
            <p
                v-if="sublabel"
                class="mt-1 text-[12px] font-medium text-charcoal/80"
            >
                {{ sublabel }}
            </p>
        </div>
    </div>
</template>
