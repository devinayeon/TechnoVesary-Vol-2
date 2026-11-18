<script setup lang="ts">
defineProps<{
    title: string;
    subtitle?: string;
    badge?: string;
}>();
</script>

<template>
    <div
        class="mb-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-center"
    >
        <div>
            <!-- Optional Badge -->
            <div v-if="badge" class="mb-1 flex items-center gap-2">
                <span
                    class="rounded-full border border-eco-green/30 bg-sprout-tint px-2.5 py-0.5 text-[11px] font-extrabold tracking-wider text-eco-green-dark uppercase"
                >
                    {{ badge }}
                </span>
            </div>

            <!-- Title -->
            <h1
                class="text-[26px] leading-tight font-black text-night-ink sm:text-[30px]"
            >
                {{ title }}
            </h1>

            <!-- Subtitle -->
            <p
                v-if="subtitle"
                class="mt-1 text-[14px] font-medium text-charcoal"
            >
                {{ subtitle }}
            </p>

            <!-- Extra content slot (e.g. metadata / summary) -->
            <slot name="extra" />
        </div>

        <!-- Action Button Slot -->
        <div
            v-if="$slots.action || $slots.actions || $slots.default"
            class="flex shrink-0 items-center gap-3 self-start sm:self-auto"
        >
            <slot name="action" />
            <slot name="actions" />
            <slot />
        </div>
    </div>
</template>
