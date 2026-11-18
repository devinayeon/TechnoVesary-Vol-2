<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import type { User } from '@/types/auth';
import DashboardLayout from '@/layouts/DashboardLayout.vue';
import MetricCard from '@/components/Dashboard/MetricCard.vue';
import ChartBar from '@/components/Dashboard/ChartBar.vue';
import CategoryProgress from '@/components/Dashboard/CategoryProgress.vue';
import RecentPickupItem from '@/components/Dashboard/RecentPickupItem.vue';
import StatusChip from '@/components/Dashboard/StatusChip.vue';
import { Users, Coins, Recycle, Newspaper, PackageOpen } from 'lucide-vue-next';

export interface MetricData {
    value: string;
    sublabel: string;
}

export interface BarChartItem {
    month: string;
    value: string;
    heightPercentage: number;
    isHighlighted: boolean;
}

export interface WasteCompositionItem {
    label: string;
    value: string;
    percentage: number;
    color: string;
}

export interface RecentPickupData {
    name: string;
    meta: string;
    weight: string;
    statusText: string;
    statusTone: 'Green' | 'Blue' | 'Amber' | 'Neutral';
}

export interface OperationalInsight {
    completedThisWeek: number;
    message: string;
}

const props = defineProps<{
    auth: { user: User };
    metrics: {
        totalUsers: MetricData;
        totalRevenue: MetricData;
        totalCategories: MetricData;
        totalArticles: MetricData;
    };
    chartBars: {
        bars: BarChartItem[];
        dateRangeText: string;
    };
    wasteComposition: WasteCompositionItem[];
    recentPickups: RecentPickupData[];
    insight: OperationalInsight;
}>();

const userName = computed(() => props.auth?.user?.name ?? 'Admin');
</script>

<template>
    <Head title="Dashboard Admin - ReValue" />
    <DashboardLayout>
        <!-- Page Header -->
        <div
            class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
        >
            <div>
                <h1
                    class="text-[22px] leading-tight font-extrabold tracking-tight text-night-ink sm:text-[26px]"
                >
                    Ringkasan operasional
                </h1>
                <p class="mt-1 text-[14px] font-medium text-charcoal">
                    Selamat bekerja, {{ userName }}. Pantau layanan dan kelola
                    langkah berikutnya.
                </p>
            </div>
            <StatusChip label="Data Terkini" tone="Green" />
        </div>

        <!-- 4 Metric Cards (Data Riil Database) -->
        <div class="mb-6 grid grid-cols-2 gap-4 xl:grid-cols-4">
            <MetricCard
                label="Total Pengguna"
                :value="metrics?.totalUsers?.value ?? '0'"
                :sublabel="
                    metrics?.totalUsers?.sublabel ?? 'Seluruh role aktif'
                "
                :icon="Users"
                iconColor="text-night-ink"
            />
            <MetricCard
                label="Total Penghasilan"
                :value="metrics?.totalRevenue?.value ?? 'Rp 0'"
                :sublabel="
                    metrics?.totalRevenue?.sublabel ??
                    'Penerimaan toko & langganan'
                "
                :icon="Coins"
                iconColor="text-eco-green-dark"
            />
            <MetricCard
                label="Kategori Sampah"
                :value="metrics?.totalCategories?.value ?? '0 Kategori'"
                :sublabel="
                    metrics?.totalCategories?.sublabel ?? 'Katalog sampah aktif'
                "
                :icon="Recycle"
                iconColor="text-sky-blue-dark"
            />
            <MetricCard
                label="Total Artikel"
                :value="metrics?.totalArticles?.value ?? '0 Materi'"
                :sublabel="
                    metrics?.totalArticles?.sublabel ??
                    'Konten edukasi tersedia'
                "
                :icon="Newspaper"
                iconColor="text-charcoal"
            />
        </div>

        <!-- Chart Row -->
        <div class="mb-6 grid grid-cols-1 gap-4 lg:grid-cols-5">
            <!-- Bar Chart Penerimaan Rupiah -->
            <div
                class="rounded-[20px] border-2 border-b-[4px] border-border-gray bg-paper-white p-5 lg:col-span-3 xl:p-6"
            >
                <h3 class="mb-1 text-[15px] font-extrabold text-night-ink">
                    Penerimaan Rupiah
                </h3>
                <p class="mb-5 text-[12px] font-medium text-charcoal">
                    {{ chartBars?.dateRangeText ?? '6 Bulan Terakhir' }}
                </p>
                <div class="flex h-[180px] items-end justify-between gap-2">
                    <ChartBar
                        v-for="bar in chartBars?.bars ?? []"
                        :key="bar.month"
                        :month="bar.month"
                        :value="bar.value"
                        :heightPercentage="bar.heightPercentage"
                        :isHighlighted="bar.isHighlighted"
                    />
                </div>
            </div>

            <!-- Waste Composition -->
            <div
                class="rounded-[20px] border-2 border-b-[4px] border-border-gray bg-paper-white p-5 lg:col-span-2 xl:p-6"
            >
                <h3 class="mb-1 text-[15px] font-extrabold text-night-ink">
                    Komposisi Sampah
                </h3>
                <p class="mb-5 text-[12px] font-medium text-charcoal">
                    yang kembali bernilai
                </p>
                <div
                    v-if="wasteComposition && wasteComposition.length > 0"
                    class="flex flex-col gap-4"
                >
                    <CategoryProgress
                        v-for="cat in wasteComposition"
                        :key="cat.label"
                        :label="cat.label"
                        :value="cat.value"
                        :percentage="cat.percentage"
                        :color="cat.color"
                    />
                </div>
                <div
                    v-else
                    class="flex flex-col items-center justify-center py-8 text-center"
                >
                    <PackageOpen class="size-8 text-charcoal/40" />
                    <p class="mt-2 text-xs font-semibold text-charcoal">
                        Belum ada data setoran sampah
                    </p>
                </div>
            </div>
        </div>

        <!-- Activity Row -->
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-5">
            <!-- Recent Pickups -->
            <div
                class="rounded-[20px] border-2 border-b-[4px] border-border-gray bg-paper-white p-5 lg:col-span-3 xl:p-6"
            >
                <h3 class="mb-1 text-[15px] font-extrabold text-night-ink">
                    Penjemputan terbaru
                </h3>
                <p class="mb-4 text-[12px] font-medium text-charcoal">
                    Aktivitas penjemputan sampah terkini
                </p>
                <div
                    v-if="recentPickups && recentPickups.length > 0"
                    class="flex flex-col"
                >
                    <RecentPickupItem
                        v-for="pickup in recentPickups"
                        :key="pickup.meta"
                        :name="pickup.name"
                        :meta="pickup.meta"
                        :weight="pickup.weight"
                        :statusText="pickup.statusText"
                        :statusTone="pickup.statusTone"
                    />
                </div>
                <div
                    v-else
                    class="flex flex-col items-center justify-center py-8 text-center"
                >
                    <PackageOpen class="size-8 text-charcoal/40" />
                    <p class="mt-2 text-xs font-semibold text-charcoal">
                        Belum ada aktivitas penjemputan
                    </p>
                </div>
            </div>

            <!-- Operational Note Card -->
            <div
                class="relative flex min-h-[260px] flex-col justify-between overflow-hidden rounded-[20px] border-2 border-b-[4px] border-sky-blue/30 bg-sky-tint p-5 lg:col-span-2 xl:p-6"
            >
                <div class="relative z-10 max-w-[70%]">
                    <StatusChip label="Valu Insight" tone="Blue" />
                    <h3
                        class="mt-3 text-[16px] leading-snug font-extrabold text-night-ink"
                    >
                        Operasional berjalan baik hari ini!
                    </h3>
                    <p
                        class="mt-2 text-[13px] leading-relaxed font-medium text-charcoal"
                    >
                        {{
                            insight?.message ??
                            'Terus pantau kategori sampah dan ajak customer baru bergabung.'
                        }}
                    </p>
                </div>
                <!-- Mascot -->
                <div
                    class="pointer-events-none absolute right-0 bottom-0 w-[140px] select-none xl:w-[160px]"
                >
                    <img
                        src="/assets/Maskot/Pose=Admin-Analyst.png"
                        alt="Valu Admin Analyst"
                        class="w-full object-contain drop-shadow-lg"
                    />
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
