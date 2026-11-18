<script setup lang="ts">
defineProps<{
    title: string;
    subtitle?: string;
    badge?: string;
}>();
</script>

<template>
    <div
        class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6"
    >
        <div>
            <!-- Optional Badge -->
            <div v-if="badge" class="flex items-center gap-2 mb-1">
                <span
                    class="px-2.5 py-0.5 rounded-full bg-sprout-tint border border-eco-green/30 text-eco-green-dark font-extrabold text-[11px] uppercase tracking-wider"
                >
                    {{ badge }}
                </span>
            </div>

            <!-- Title -->
            <h1
                class="text-[26px] sm:text-[30px] font-black text-night-ink leading-tight"
            >
                {{ title }}
            </h1>

            <!-- Subtitle -->
            <p v-if="subtitle" class="text-[14px] font-medium text-charcoal mt-1">
                {{ subtitle }}
            </p>

            <!-- Extra content slot (e.g. metadata / summary) -->
            <slot name="extra" />
        </div>

        <!-- Action Button Slot -->
        <div
            v-if="$slots.action || $slots.actions || $slots.default"
            class="flex items-center gap-3 shrink-0 self-start sm:self-auto"
        >
            <slot name="action" />
            <slot name="actions" />
            <slot />
        </div>
    </div>
</template>
