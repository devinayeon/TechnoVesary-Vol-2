<script setup lang="ts">
import { computed } from 'vue';
import { Scale, Sparkles } from 'lucide-vue-next';
import type { PickupDetailItem } from '@/types/pickup';
import { formatPoints, formatWeight } from '@/lib/pickup';

const props = withDefaults(
    defineProps<{
        details: PickupDetailItem[];
        showWeights?: boolean;
        title?: string;
    }>(),
    {
        showWeights: true,
        title: 'Rincian Poin Penjemputan',
    },
);

const totalPoints = computed(() =>
    props.details.reduce(
        (total, detail) => total + Number(detail.subtotal_points ?? 0),
        0,
    ),
);

const totalWeight = computed(() =>
    props.details.reduce(
        (total, detail) => total + Number(detail.weight ?? 0),
        0,
    ),
);
</script>

<template>
    <div class="flex flex-col gap-3">
        <div class="flex items-center gap-2">
            <Sparkles class="size-4 text-sunlight-gold" />
            <h3 class="text-[15px] font-extrabold text-night-ink">
                {{ title }}
            </h3>
        </div>

        <div
            class="overflow-hidden rounded-2xl border-2 border-border-gray bg-paper-white"
        >
            <div class="overflow-x-auto">
                <table class="w-full min-w-[420px] text-left">
                    <thead>
                        <tr
                            class="border-b border-border-gray bg-cloud-surface text-[11px] font-extrabold tracking-wider text-charcoal uppercase"
                        >
                            <th class="px-4 py-2.5">Kategori</th>
                            <th class="px-4 py-2.5 text-right">Poin/kg</th>
                            <th
                                v-if="showWeights"
                                class="px-4 py-2.5 text-right"
                            >
                                Berat
                            </th>
                            <th class="px-4 py-2.5 text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border-gray/70">
                        <tr
                            v-for="detail in details"
                            :key="detail.id"
                            class="text-[13px]"
                        >
                            <td class="px-4 py-3 font-bold text-night-ink">
                                {{ detail.waste_category?.name ?? 'Sampah' }}
                            </td>
                            <td
                                class="px-4 py-3 text-right font-medium text-charcoal"
                            >
                                {{
                                    Number(detail.points_per_kg).toLocaleString(
                                        'id-ID',
                                    )
                                }}
                            </td>
                            <td
                                v-if="showWeights"
                                class="px-4 py-3 text-right font-medium text-charcoal"
                            >
                                {{ formatWeight(detail.weight) }}
                            </td>
                            <td
                                class="px-4 py-3 text-right font-extrabold text-eco-green-dark"
                            >
                                {{ formatPoints(detail.subtotal_points) }}
                            </td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr
                            class="border-t-2 border-border-gray bg-cloud-surface/70"
                        >
                            <td
                                :colspan="showWeights ? 3 : 2"
                                class="px-4 py-3 text-right text-[13px] font-black text-night-ink"
                            >
                                Total
                            </td>
                            <td
                                class="px-4 py-3 text-right text-[14px] font-black text-eco-green-dark"
                            >
                                {{ formatPoints(totalPoints) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div
            class="flex flex-wrap items-center gap-3 text-[12px] font-semibold text-charcoal"
        >
            <span class="inline-flex items-center gap-1.5">
                <Scale class="size-3.5 text-charcoal/70" />
                Total berat: {{ formatWeight(totalWeight) }}
            </span>
            <span class="inline-flex items-center gap-1.5">
                <Sparkles class="size-3.5 text-sunlight-gold" />
                Total poin: {{ formatPoints(totalPoints) }}
            </span>
        </div>
    </div>
</template>
