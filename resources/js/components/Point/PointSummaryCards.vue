<script setup lang="ts">
import { Coins, TrendingDown, TrendingUp } from 'lucide-vue-next';
import MetricCard from '@/components/Dashboard/MetricCard.vue';
import type { PointSummary } from '@/types/point';

defineProps<{
    summary: PointSummary;
}>();

const format = (value: number): string =>
    `${Number(value ?? 0).toLocaleString('id-ID')} Poin`;
</script>

<template>
    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
        <MetricCard
            label="Total Poin Masuk"
            :value="format(summary.total_in)"
            sublabel="Dari penjemputan sampah & reward"
            :icon="TrendingUp"
            iconColor="text-eco-green-dark"
        />
        <MetricCard
            label="Total Poin Keluar"
            :value="format(summary.total_out)"
            sublabel="Penukaran di Upcycle Store & langganan"
            :icon="TrendingDown"
            iconColor="text-red-500"
        />
        <MetricCard
            label="Saldo Poin Saat Ini"
            :value="format(summary.balance)"
            sublabel="Siap ditukar kapan saja"
            :icon="Coins"
            iconColor="text-sunlight-gold"
        />
    </div>
</template>
