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
import { Users, Coins, Recycle, Newspaper } from 'lucide-vue-next';

const props = defineProps<{
    auth: { user: User };
}>();

const userName = computed(() => props.auth?.user?.name ?? 'Admin');

const chartBars = [
    { month: 'Jun', value: '32jt', heightPercentage: 55 },
    { month: 'Jul', value: '41jt', heightPercentage: 70 },
    { month: 'Ags', value: '38jt', heightPercentage: 65 },
    { month: 'Sep', value: '52jt', heightPercentage: 90 },
    { month: 'Okt', value: '48jt', heightPercentage: 82, isHighlighted: true },
    { month: 'Nov', value: '29jt', heightPercentage: 48 },
];

const categories = [
    {
        label: 'Plastik',
        value: '7.2 Ton',
        percentage: 68,
        color: 'bg-eco-green',
    },
    {
        label: 'Kardus',
        value: '4.8 Ton',
        percentage: 46,
        color: 'bg-eco-green/70',
    },
    {
        label: 'Logam',
        value: '3.1 Ton',
        percentage: 30,
        color: 'bg-sky-blue/60',
    },
    {
        label: 'Lainnya',
        value: '2.1 Ton',
        percentage: 20,
        color: 'bg-silver-tier/80',
    },
];

const recentPickups = [
    {
        name: 'Budi Santoso',
        meta: '#JMP-20241105 · 5 Nov 2024',
        weight: '12.4 Kg',
        statusText: 'Selesai',
        statusTone: 'Green' as const,
    },
    {
        name: 'Siti Rahayu',
        meta: '#JMP-20241104 · 4 Nov 2024',
        weight: '8.9 Kg',
        statusText: 'Selesai',
        statusTone: 'Green' as const,
    },
    {
        name: 'Ahmad Farhan',
        meta: '#JMP-20241104 · 4 Nov 2024',
        weight: '15.2 Kg',
        statusText: 'Selesai',
        statusTone: 'Green' as const,
    },
    {
        name: 'Dewi Lestari',
        meta: '#JMP-20241103 · 3 Nov 2024',
        weight: '6.7 Kg',
        statusText: 'Selesai',
        statusTone: 'Green' as const,
    },
    {
        name: 'Rizky Pratama',
        meta: '#JMP-20241103 · 3 Nov 2024',
        weight: '10.1 Kg',
        statusText: 'Dijemput',
        statusTone: 'Blue' as const,
    },
];
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
            <StatusChip label="Sejak awal" tone="Green" />
        </div>

        <!-- 4 Metric Cards -->
        <div class="mb-6 grid grid-cols-2 gap-4 xl:grid-cols-4">
            <MetricCard
                label="Total Pengguna"
                value="1.482"
                sublabel="Seluruh role aktif"
                :icon="Users"
                iconColor="text-night-ink"
            />
            <MetricCard
                label="Total Penghasilan"
                value="Rp48,65jt"
                sublabel="Penerimaan rupiah"
                :icon="Coins"
                iconColor="text-eco-green-dark"
            />
            <MetricCard
                label="Kategori Sampah"
                value="8 Kategori"
                sublabel="Semua aktif"
                :icon="Recycle"
                iconColor="text-sky-blue-dark"
            />
            <MetricCard
                label="Total Artikel"
                value="24 Materi"
                sublabel="Konten tersedia"
                :icon="Newspaper"
                iconColor="text-charcoal"
            />
        </div>

        <!-- Chart Row -->
        <div class="mb-6 grid grid-cols-1 gap-4 lg:grid-cols-5">
            <!-- Bar Chart -->
            <div
                class="rounded-[20px] border-2 border-b-[4px] border-border-gray bg-paper-white p-5 lg:col-span-3 xl:p-6"
            >
                <h3 class="mb-1 text-[15px] font-extrabold text-night-ink">
                    Penerimaan Rupiah
                </h3>
                <p class="mb-5 text-[12px] font-medium text-charcoal">
                    Juni – November 2024
                </p>
                <div class="flex h-[180px] items-end justify-between gap-2">
                    <ChartBar
                        v-for="bar in chartBars"
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
                <div class="flex flex-col gap-4">
                    <CategoryProgress
                        v-for="cat in categories"
                        :key="cat.label"
                        :label="cat.label"
                        :value="cat.value"
                        :percentage="cat.percentage"
                        :color="cat.color"
                    />
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
                    Aktivitas jemput 7 hari terakhir
                </p>
                <div class="flex flex-col">
                    <RecentPickupItem
                        v-for="pickup in recentPickups"
                        :key="pickup.name + pickup.meta"
                        :name="pickup.name"
                        :meta="pickup.meta"
                        :weight="pickup.weight"
                        :statusText="pickup.statusText"
                        :statusTone="pickup.statusTone"
                    />
                </div>
            </div>

            <!-- Operational Note Card -->
            <div
                class="relative flex min-h-[260px] flex-col justify-between overflow-hidden rounded-[20px] border-2 border-b-[4px] border-sky-blue/30 bg-sky-tint p-5 lg:col-span-2 xl:p-6"
            >
                <div class="relative z-10">
                    <StatusChip label="Valu Insight" tone="Blue" />
                    <h3
                        class="mt-3 text-[16px] leading-snug font-extrabold text-night-ink"
                    >
                        Operasional berjalan baik hari ini!
                    </h3>
                    <p
                        class="mt-2 text-[13px] leading-relaxed font-medium text-charcoal"
                    >
                        48 penjemputan berhasil diselesaikan minggu ini. Terus
                        pantau kategori sampah dan ajak customer baru bergabung.
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
