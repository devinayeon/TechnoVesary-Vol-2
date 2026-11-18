<script setup lang="ts">
import { Search, X } from 'lucide-vue-next';

withDefaults(
    defineProps<{
        modelValue: string;
        placeholder?: string;
        loading?: boolean;
        disabled?: boolean;
    }>(),
    {
        placeholder: 'Cari...',
        loading: false,
        disabled: false,
    },
);

const emit = defineEmits<{
    (e: 'update:modelValue', value: string): void;
}>();
</script>

<template>
    <div class="relative w-full">
        <Search
            class="pointer-events-none absolute top-1/2 left-3.5 size-4.5 -translate-y-1/2 text-charcoal/70"
        />
        <input
            :value="modelValue"
            type="text"
            :placeholder="placeholder"
            :disabled="disabled"
            class="h-[44px] w-full rounded-xl border-2 border-border-gray bg-paper-white pr-10 pl-10 text-[14px] font-medium text-night-ink shadow-2xs transition-all placeholder:text-charcoal/60 focus:border-eco-green focus:ring-4 focus:ring-eco-green/15 focus:outline-none disabled:opacity-60"
            @input="
                emit(
                    'update:modelValue',
                    ($event.target as HTMLInputElement).value,
                )
            "
        />
        <span
            v-if="loading"
            class="absolute top-1/2 right-9 -translate-y-1/2 text-[11px] font-semibold text-charcoal/70"
        >
            Mencari...
        </span>
        <button
            v-if="modelValue"
            type="button"
            class="absolute top-1/2 right-3 -translate-y-1/2 cursor-pointer rounded-md p-1 text-charcoal transition-colors hover:bg-cloud-surface hover:text-night-ink"
            aria-label="Bersihkan pencarian"
            @click="emit('update:modelValue', '')"
        >
            <X class="size-3.5" />
        </button>
    </div>
</template>
