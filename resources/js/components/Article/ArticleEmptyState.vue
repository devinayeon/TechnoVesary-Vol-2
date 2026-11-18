<script setup lang="ts">
import { RotateCcw, SearchX } from 'lucide-vue-next';

withDefaults(
    defineProps<{
        hasFilter?: boolean;
        title?: string;
        description?: string;
        filterTitle?: string;
        filterDescription?: string;
        resetLabel?: string;
    }>(),
    {
        hasFilter: false,
        title: 'Belum ada artikel',
        description:
            'Artikel edukasi akan tampil di sini setelah dipublikasikan oleh admin.',
        filterTitle: 'Tidak ada artikel yang cocok',
        filterDescription:
            'Coba ubah kata kunci pencarian atau sesuaikan filter kategori.',
        resetLabel: 'Reset Filter',
    },
);

defineEmits<{
    (e: 'reset'): void;
}>();
</script>

<template>
    <div
        class="flex flex-col items-center justify-center rounded-[20px] border-2 border-b-[4px] border-border-gray bg-paper-white p-12 text-center shadow-xs"
    >
        <div
            class="mb-4 flex size-24 items-center justify-center rounded-full bg-cloud-surface"
        >
            <img
                v-if="hasFilter"
                src="/assets/Maskot/Pose=Searching-Driver.png"
                alt="Tidak ada hasil"
                class="size-20 object-contain"
            />
            <SearchX v-else class="size-10 text-charcoal/50" />
        </div>

        <h3 class="mb-1 text-[20px] font-extrabold text-night-ink">
            {{ hasFilter ? filterTitle : title }}
        </h3>
        <p
            class="mb-6 max-w-[420px] text-[14px] leading-relaxed font-normal text-charcoal"
        >
            {{ hasFilter ? filterDescription : description }}
        </p>

        <button
            v-if="hasFilter"
            type="button"
            class="inline-flex h-[44px] cursor-pointer items-center justify-center gap-2 rounded-2xl border-2 border-b-[4px] border-eco-green-dark bg-eco-green px-6 text-[14px] font-extrabold text-night-ink transition-all hover:brightness-105 active:translate-y-0.5 active:border-b-2"
            @click="$emit('reset')"
        >
            <RotateCcw class="size-4" />
            {{ resetLabel }}
        </button>
    </div>
</template>
