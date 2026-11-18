<script setup lang="ts">
import { ArrowDownLeft, ArrowUpRight, Coins } from 'lucide-vue-next';
import AppTable from '@/components/Dashboard/AppTable.vue';
import AppPagination from '@/components/Dashboard/AppPagination.vue';
import StatusChip from '@/components/Dashboard/StatusChip.vue';
import type { PaginatedPointHistories, PointHistoryItem } from '@/types/point';
import {
    formatPointDate,
    formatPointDateTime,
    formatPointMutation,
    pointTypeLabel,
    pointTypeTone,
} from '@/lib/point';

withDefaults(
    defineProps<{
        histories: PaginatedPointHistories;
        hasFilter?: boolean;
        emptyTitle?: string;
        emptyDescription?: string;
    }>(),
    {
        hasFilter: false,
        emptyTitle: 'Belum ada riwayat poin',
        emptyDescription:
            'Mutasi poin dari penjemputan sampah dan penukaran di Upcycle Store akan tampil di sini.',
    },
);

const emit = defineEmits<{
    (e: 'reset-filter'): void;
}>();

function isIn(history: PointHistoryItem): boolean {
    return history.type === 'in';
}
</script>

<template>
    <AppTable
        :is-empty="histories.data.length === 0"
        :has-filter="hasFilter"
        :empty-title="emptyTitle"
        :empty-description="emptyDescription"
        empty-filter-title="Tidak ada mutasi poin yang cocok"
        empty-filter-description="Coba ubah kata kunci pencarian atau sesuaikan filter tipe mutasi poin."
        empty-action-label=""
        min-width="min-w-[760px]"
        @reset-filter="emit('reset-filter')"
    >
        <!-- Mobile Cards -->
        <template #mobile>
            <div
                v-for="history in histories.data"
                :key="`m-${history.id}`"
                class="flex flex-col gap-2.5 p-4 transition-colors hover:bg-cloud-surface/30"
            >
                <div class="flex items-center justify-between gap-2">
                    <StatusChip
                        :label="pointTypeLabel(history.type)"
                        :tone="pointTypeTone(history.type)"
                    />
                    <span class="text-[12px] font-bold text-charcoal/70">
                        {{ formatPointDate(history.created_at) }}
                    </span>
                </div>

                <div class="flex items-start justify-between gap-3">
                    <div class="flex min-w-0 items-start gap-2.5">
                        <span
                            class="mt-0.5 flex size-8 shrink-0 items-center justify-center rounded-xl border"
                            :class="
                                isIn(history)
                                    ? 'border-eco-green/30 bg-sprout-tint text-eco-green-dark'
                                    : 'border-red-200 bg-red-50 text-red-500'
                            "
                        >
                            <ArrowDownLeft
                                v-if="isIn(history)"
                                class="size-4"
                            />
                            <ArrowUpRight v-else class="size-4" />
                        </span>
                        <p
                            class="line-clamp-2 text-[13px] leading-snug font-bold text-night-ink"
                        >
                            {{ history.source }}
                        </p>
                    </div>

                    <span
                        class="shrink-0 text-[15px] font-black"
                        :class="
                            isIn(history)
                                ? 'text-eco-green-dark'
                                : 'text-red-500'
                        "
                    >
                        {{ formatPointMutation(history.amount, history.type) }}
                        <span class="text-[11px] font-bold">poin</span>
                    </span>
                </div>
            </div>
        </template>

        <!-- Desktop Header -->
        <template #header>
            <th class="w-40 px-4 py-3.5 text-left">Tanggal</th>
            <th class="w-36 px-4 py-3.5 text-left">Tipe</th>
            <th class="px-4 py-3.5 text-left">Sumber Mutasi</th>
            <th class="w-40 px-4 py-3.5 text-right">Mutasi Poin</th>
        </template>

        <!-- Desktop Body -->
        <template #body>
            <tr
                v-for="history in histories.data"
                :key="history.id"
                class="transition-colors hover:bg-cloud-surface/50"
            >
                <td class="px-4 py-4 whitespace-nowrap">
                    <span class="text-[13px] font-semibold text-charcoal">
                        {{ formatPointDate(history.created_at) }}
                    </span>
                </td>

                <td class="px-4 py-4 whitespace-nowrap">
                    <StatusChip
                        :label="pointTypeLabel(history.type)"
                        :tone="pointTypeTone(history.type)"
                    />
                </td>

                <td class="px-4 py-4">
                    <div class="flex items-center gap-2.5">
                        <span
                            class="flex size-9 shrink-0 items-center justify-center rounded-xl border"
                            :class="
                                isIn(history)
                                    ? 'border-eco-green/30 bg-sprout-tint text-eco-green-dark'
                                    : 'border-red-200 bg-red-50 text-red-500'
                            "
                        >
                            <ArrowDownLeft
                                v-if="isIn(history)"
                                class="size-4"
                            />
                            <ArrowUpRight v-else class="size-4" />
                        </span>
                        <div class="min-w-0">
                            <p
                                class="max-w-[420px] truncate text-[13px] font-bold text-night-ink"
                                :title="history.source"
                            >
                                {{ history.source }}
                            </p>
                            <p class="text-[11px] font-medium text-charcoal/70">
                                {{ formatPointDateTime(history.created_at) }}
                            </p>
                        </div>
                    </div>
                </td>

                <td class="px-4 py-4 text-right whitespace-nowrap">
                    <span
                        class="text-[15px] font-black"
                        :class="
                            isIn(history)
                                ? 'text-eco-green-dark'
                                : 'text-red-500'
                        "
                    >
                        {{ formatPointMutation(history.amount, history.type) }}
                    </span>
                    <span class="ml-1 text-[11px] font-bold text-charcoal">
                        poin
                    </span>
                </td>
            </tr>
        </template>

        <!-- Pagination -->
        <template #pagination>
            <AppPagination :pagination="histories" item-label="mutasi poin" />
        </template>
    </AppTable>
</template>
