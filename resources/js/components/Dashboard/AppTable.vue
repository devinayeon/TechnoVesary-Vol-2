<script setup lang="ts">
withDefaults(
    defineProps<{
        isEmpty?: boolean;
        hasFilter?: boolean;
        minWidth?: string;
        emptyTitle?: string;
        emptyDescription?: string;
        emptyFilterTitle?: string;
        emptyFilterDescription?: string;
        emptyActionLabel?: string;
    }>(),
    {
        isEmpty: false,
        hasFilter: false,
        minWidth: 'min-w-[760px]',
        emptyTitle: 'Belum ada data',
        emptyDescription: 'Mulai tambahkan data baru untuk mengisi daftar ini.',
        emptyFilterTitle: 'Tidak ada hasil yang cocok',
        emptyFilterDescription:
            'Coba kata kunci lain atau hapus filter untuk melihat seluruh data.',
        emptyActionLabel: 'Tambah Data',
    },
);

defineEmits<{
    (e: 'reset-filter'): void;
    (e: 'create'): void;
}>();
</script>

<template>
    <div
        class="overflow-hidden rounded-[20px] border-2 border-b-[4px] border-border-gray bg-paper-white shadow-xs"
    >
        <!-- Mobile Cards View (< md) -->
        <div
            v-if="!isEmpty"
            class="block divide-y divide-border-gray/70 md:hidden"
        >
            <slot name="mobile" />
        </div>

        <!-- Desktop & Tablet Table (>= md) -->
        <div v-if="!isEmpty" class="hidden overflow-x-auto md:block">
            <table class="w-full border-collapse text-left" :class="minWidth">
                <!-- Table Header -->
                <thead>
                    <tr
                        class="border-b border-border-gray bg-cloud-surface text-[12px] font-extrabold tracking-wider text-charcoal uppercase"
                    >
                        <slot name="header" />
                    </tr>
                </thead>

                <!-- Table Body -->
                <tbody class="divide-y divide-border-gray/70">
                    <slot name="body" />
                </tbody>
            </table>
        </div>

        <!-- Custom Empty Slot (if provided) -->
        <slot v-if="isEmpty" name="empty">
            <!-- Default Empty State: No Filter Results (Figma Node 493:14534) -->
            <div
                v-if="hasFilter"
                class="flex flex-col items-center justify-center p-12 text-center"
            >
                <div
                    class="mb-4 flex size-24 items-center justify-center rounded-full bg-cloud-surface"
                >
                    <img
                        src="/assets/Maskot/Pose=Searching-Driver.png"
                        alt="Tidak ada hasil"
                        class="size-20 object-contain"
                    />
                </div>
                <h3 class="mb-1 text-[20px] font-extrabold text-night-ink">
                    {{ emptyFilterTitle }}
                </h3>
                <p
                    class="mb-6 max-w-[420px] text-[14px] leading-relaxed font-normal text-charcoal"
                >
                    {{ emptyFilterDescription }}
                </p>
                <button
                    type="button"
                    class="h-[44px] cursor-pointer rounded-2xl border-2 border-b-[4px] border-eco-green-dark bg-eco-green px-6 text-[14px] font-extrabold text-night-ink transition-all hover:brightness-105 active:translate-y-0.5 active:border-b-2"
                    @click="$emit('reset-filter')"
                >
                    Reset Filter
                </button>
            </div>

            <!-- Default Empty State: No Data At All (Figma Node 493:13806) -->
            <div
                v-else
                class="flex flex-col items-center justify-center p-12 text-center"
            >
                <div
                    class="mb-4 flex size-24 items-center justify-center rounded-full bg-cloud-surface"
                >
                    <img
                        src="/assets/Maskot/Valu · In the neighborhood.png"
                        alt="Belum ada data"
                        class="size-20 object-contain"
                    />
                </div>
                <h3 class="mb-1 text-[20px] font-extrabold text-night-ink">
                    {{ emptyTitle }}
                </h3>
                <p
                    class="mb-6 max-w-[420px] text-[14px] leading-relaxed font-normal text-charcoal"
                >
                    {{ emptyDescription }}
                </p>
                <button
                    v-if="emptyActionLabel"
                    type="button"
                    class="inline-flex h-[44px] cursor-pointer items-center justify-center rounded-2xl border-2 border-b-[4px] border-eco-green-dark bg-eco-green px-6 text-[14px] font-extrabold text-night-ink transition-all hover:brightness-105 active:translate-y-0.5 active:border-b-2"
                    @click="$emit('create')"
                >
                    {{ emptyActionLabel }}
                </button>
            </div>
        </slot>

        <!-- Pagination / Footer Bar -->
        <slot name="pagination" />
    </div>
</template>
