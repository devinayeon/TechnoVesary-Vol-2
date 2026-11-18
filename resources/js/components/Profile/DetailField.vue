<script setup lang="ts">
defineProps<{
    label: string;
    value?: string | number | null;
    helper?: string;
}>();
</script>

<template>
    <div class="flex flex-col gap-1 py-2">
        <span class="text-[12px] leading-tight font-semibold text-charcoal">
            {{ label }}
        </span>
        <div
            class="text-[15px] leading-snug font-bold break-words text-night-ink"
        >
            <slot>{{ value ?? '-' }}</slot>
        </div>
        <span v-if="helper" class="text-[11px] font-normal text-charcoal/80">
            {{ helper }}
        </span>
    </div>
</template>
