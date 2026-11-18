<script setup lang="ts">
import { Head } from "@inertiajs/vue3";
import { computed } from "vue";
import type { User } from "@/types/auth";
import DashboardLayout from "@/layouts/DashboardLayout.vue";
import MetricCard from "@/components/Dashboard/MetricCard.vue";
import ChartBar from "@/components/Dashboard/ChartBar.vue";
import CategoryProgress from "@/components/Dashboard/CategoryProgress.vue";
import RecentPickupItem from "@/components/Dashboard/RecentPickupItem.vue";
import StatusChip from "@/components/Dashboard/StatusChip.vue";
import { Users, Coins, Recycle, Newspaper } from "lucide-vue-next";

const props = defineProps<{
    auth: { user: User };
}>();

const userName = computed(() => props.auth?.user?.name ?? "Admin");

const chartBars = [
    { month: "Jun", value: "32jt", heightPercentage: 55 },
    { month: "Jul", value: "41jt", heightPercentage: 70 },
    { month: "Ags", value: "38jt", heightPercentage: 65 },
    { month: "Sep", value: "52jt", heightPercentage: 90 },
    { month: "Okt", value: "48jt", heightPercentage: 82, isHighlighted: true },
    { month: "Nov", value: "29jt", heightPercentage: 48 },
];

const categories = [
    { label: "Plastik", value: "7.2 Ton", percentage: 68, color: "bg-eco-green" },
    { label: "Kardus", value: "4.8 Ton", percentage: 46, color: "bg-eco-green/70" },
    { label: "Logam", value: "3.1 Ton", percentage: 30, color: "bg-sky-blue/60" },
    { label: "Lainnya", value: "2.1 Ton", percentage: 20, color: "bg-silver-tier/80" },
];

const recentPickups = [
    { name: "Budi Santoso", meta: "#JMP-20241105 · 5 Nov 2024", weight: "12.4 Kg", statusText: "Selesai", statusTone: "Green" as const },
    { name: "Siti Rahayu", meta: "#JMP-20241104 · 4 Nov 2024", weight: "8.9 Kg", statusText: "Selesai", statusTone: "Green" as const },
    { name: "Ahmad Farhan", meta: "#JMP-20241104 · 4 Nov 2024", weight: "15.2 Kg", statusText: "Selesai", statusTone: "Green" as const },
    { name: "Dewi Lestari", meta: "#JMP-20241103 · 3 Nov 2024", weight: "6.7 Kg", statusText: "Selesai", statusTone: "Green" as const },
    { name: "Rizky Pratama", meta: "#JMP-20241103 · 3 Nov 2024", weight: "10.1 Kg", statusText: "Dijemput", statusTone: "Blue" as const },
];
</script>

<template>
    <Head title="Dashboard Admin - ReValue" />
    <DashboardLayout>
        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
            <div>
                <h1 class="text-[22px] sm:text-[26px] font-extrabold text-night-ink tracking-tight leading-tight">
                    Ringkasan operasional
                </h1>
                <p class="text-[14px] font-medium text-charcoal mt-1">
                    Selamat bekerja, {{ userName }}. Pantau layanan dan kelola langkah berikutnya.
                </p>
            </div>
            <StatusChip label="Sejak awal" tone="Green" />
        </div>

        <!-- 4 Metric Cards -->
        <div class="grid grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
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
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-4 mb-6">
            <!-- Bar Chart -->
            <div class="lg:col-span-3 bg-paper-white border-2 border-b-[4px] border-border-gray rounded-[20px] p-5 xl:p-6">
                <h3 class="text-[15px] font-extrabold text-night-ink mb-1">Penerimaan Rupiah</h3>
                <p class="text-[12px] font-medium text-charcoal mb-5">Juni – November 2024</p>
                <div class="flex items-end justify-between gap-2 h-[180px]">
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
            <div class="lg:col-span-2 bg-paper-white border-2 border-b-[4px] border-border-gray rounded-[20px] p-5 xl:p-6">
                <h3 class="text-[15px] font-extrabold text-night-ink mb-1">Komposisi Sampah</h3>
                <p class="text-[12px] font-medium text-charcoal mb-5">yang kembali bernilai</p>
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
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-4">
            <!-- Recent Pickups -->
            <div class="lg:col-span-3 bg-paper-white border-2 border-b-[4px] border-border-gray rounded-[20px] p-5 xl:p-6">
                <h3 class="text-[15px] font-extrabold text-night-ink mb-1">Penjemputan terbaru</h3>
                <p class="text-[12px] font-medium text-charcoal mb-4">Aktivitas jemput 7 hari terakhir</p>
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
            <div class="lg:col-span-2 bg-sky-tint border-2 border-b-[4px] border-sky-blue/30 rounded-[20px] p-5 xl:p-6 flex flex-col justify-between overflow-hidden relative min-h-[260px]">
                <div class="relative z-10">
                    <StatusChip label="Valu Insight" tone="Blue" />
                    <h3 class="text-[16px] font-extrabold text-night-ink mt-3 leading-snug">
                        Operasional berjalan baik hari ini!
                    </h3>
                    <p class="text-[13px] font-medium text-charcoal mt-2 leading-relaxed">
                        48 penjemputan berhasil diselesaikan minggu ini. Terus pantau kategori sampah dan ajak customer baru bergabung.
                    </p>
                </div>
                <!-- Mascot -->
                <div class="absolute bottom-0 right-0 w-[140px] xl:w-[160px] pointer-events-none select-none">
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
