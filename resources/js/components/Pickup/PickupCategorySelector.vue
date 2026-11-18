<script setup lang="ts">
import { computed } from 'vue';
import { Check, Recycle } from 'lucide-vue-next';
import type { WasteCategoryOption } from '@/types/pickup';

const props = withDefaults(
    defineProps<{
        modelValue: number[];
        categories: WasteCategoryOption[];
        disabled?: boolean;
    }>(),
    { disabled: false },
);

const emit = defineEmits<{
    (e: 'update:modelValue', value: number[]): void;
}>();

const selected = computed(() => new Set(props.modelValue));

function toggle(id: number) {
    if (props.disabled) return;

    const next = new Set(props.modelValue);

    if (next.has(id)) {
        next.delete(id);
    } else {
        next.add(id);
    }

    emit('update:modelValue', Array.from(next));
}
</script>

<template>
    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
        <button
            v-for="category in categories"
            :key="category.id"
            type="button"
            :disabled="disabled"
            :aria-pressed="selected.has(category.id)"
            class="group flex items-start gap-3 rounded-2xl border-2 border-b-[4px] p-4 text-left transition-all duration-150 disabled:cursor-not-allowed disabled:opacity-60"
            :class="
                selected.has(category.id)
                    ? 'border-eco-green bg-sprout-tint shadow-2xs'
                    : 'border-border-gray bg-paper-white hover:border-charcoal/40 hover:bg-cloud-surface'
            "
            @click="toggle(category.id)"
        >
            <span
                class="mt-0.5 flex size-6 shrink-0 items-center justify-center rounded-lg border-2 transition-colors"
                :class="
                    selected.has(category.id)
                        ? 'border-eco-green-dark bg-eco-green text-night-ink'
                        : 'border-border-gray bg-paper-white text-transparent'
                "
            >
                <Check class="size-4" stroke-width="3" />
            </span>

            <span class="min-w-0 flex-1">
                <span
                    class="flex items-center gap-1.5 text-[14px] leading-tight font-extrabold text-night-ink"
                >
                    <Recycle
                        class="size-4 shrink-0 text-eco-green-dark"
                        aria-hidden="true"
                    />
                    <span class="truncate">{{ category.name }}</span>
                </span>
                <span
                    v-if="category.description"
                    class="mt-1 line-clamp-2 block text-[12px] leading-relaxed font-medium text-charcoal"
                >
                    {{ category.description }}
                </span>
                <span
                    class="mt-1.5 inline-block rounded-full border border-eco-green/30 bg-paper-white px-2 py-0.5 text-[11px] font-bold text-eco-green-dark"
                >
                    {{ category.points_per_kg.toLocaleString('id-ID') }} poin/kg
                </span>
            </span>
        </button>
    </div>
</template>
