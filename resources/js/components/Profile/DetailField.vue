<script setup lang="ts">
defineProps<{
    label: string;
    value?: string | number | null;
    helper?: string;
}>();
</script>

<template>
    <div class="flex flex-col gap-1 py-2">
        <span class="text-[12px] font-semibold text-charcoal leading-tight">
            {{ label }}
        </span>
        <div class="text-[15px] font-bold text-night-ink break-words leading-snug">
            <slot>{{ value ?? '-' }}</slot>
        </div>
        <span v-if="helper" class="text-[11px] text-charcoal/80 font-normal">
            {{ helper }}
        </span>
    </div>
</template>
