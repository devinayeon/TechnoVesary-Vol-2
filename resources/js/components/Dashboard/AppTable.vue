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
        minWidth: "min-w-[760px]",
        emptyTitle: "Belum ada data",
        emptyDescription: "Mulai tambahkan data baru untuk mengisi daftar ini.",
        emptyFilterTitle: "Tidak ada hasil yang cocok",
        emptyFilterDescription: "Coba kata kunci lain atau hapus filter untuk melihat seluruh data.",
        emptyActionLabel: "Tambah Data",
    }
);

defineEmits<{
    (e: "reset-filter"): void;
    (e: "create"): void;
}>();
</script>

<template>
    <div
        class="bg-paper-white border-2 border-b-[4px] border-border-gray rounded-[20px] overflow-hidden shadow-xs"
    >
        <!-- Mobile Cards View (< md) -->
        <div v-if="!isEmpty" class="block md:hidden divide-y divide-border-gray/70">
            <slot name="mobile" />
        </div>

        <!-- Desktop & Tablet Table (>= md) -->
        <div v-if="!isEmpty" class="hidden md:block overflow-x-auto">
            <table class="w-full text-left border-collapse" :class="minWidth">
                <!-- Table Header -->
                <thead>
                    <tr
                        class="bg-cloud-surface border-b border-border-gray text-[12px] font-extrabold text-charcoal uppercase tracking-wider"
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
                    class="size-24 rounded-full bg-cloud-surface flex items-center justify-center mb-4"
                >
                    <img
                        src="/assets/Maskot/Pose=Searching-Driver.png"
                        alt="Tidak ada hasil"
                        class="size-20 object-contain"
                    />
                </div>
                <h3 class="text-[20px] font-extrabold text-night-ink mb-1">
                    {{ emptyFilterTitle }}
                </h3>
                <p
                    class="text-[14px] font-normal text-charcoal max-w-[420px] mb-6 leading-relaxed"
                >
                    {{ emptyFilterDescription }}
                </p>
                <button
                    type="button"
                    class="px-6 h-[44px] bg-eco-green text-night-ink font-extrabold text-[14px] rounded-2xl border-2 border-b-[4px] border-eco-green-dark hover:brightness-105 active:translate-y-0.5 active:border-b-2 transition-all cursor-pointer"
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
                    class="size-24 rounded-full bg-cloud-surface flex items-center justify-center mb-4"
                >
                    <img
                        src="/assets/Maskot/Valu · In the neighborhood.png"
                        alt="Belum ada data"
                        class="size-20 object-contain"
                    />
                </div>
                <h3 class="text-[20px] font-extrabold text-night-ink mb-1">
                    {{ emptyTitle }}
                </h3>
                <p
                    class="text-[14px] font-normal text-charcoal max-w-[420px] mb-6 leading-relaxed"
                >
                    {{ emptyDescription }}
                </p>
                <button
                    v-if="emptyActionLabel"
                    type="button"
                    class="px-6 h-[44px] bg-eco-green text-night-ink font-extrabold text-[14px] rounded-2xl border-2 border-b-[4px] border-eco-green-dark hover:brightness-105 active:translate-y-0.5 active:border-b-2 transition-all cursor-pointer inline-flex items-center justify-center"
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
