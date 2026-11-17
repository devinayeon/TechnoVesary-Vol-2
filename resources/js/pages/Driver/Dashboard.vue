<script setup lang="ts">
import { Head } from "@inertiajs/vue3";
import { computed } from "vue";
import type { User } from "@/types/auth";
import DashboardLayout from "@/layouts/DashboardLayout.vue";
import MetricCard from "@/components/Dashboard/MetricCard.vue";
import StatusChip from "@/components/Dashboard/StatusChip.vue";
import { Truck, Package, CheckCircle2, MapPin, Navigation } from "lucide-vue-next";

const props = defineProps<{
    auth: { user: User };
}>();

const userName = computed(() => props.auth?.user?.name ?? "Driver");

const tasks = [
    { name: "Budi Santoso", address: "Jl. Merdeka No. 12, Kel. Sukamaju", weight: "~15 Kg", status: "Menunggu" as const, statusTone: "Neutral" as const },
    { name: "Siti Rahayu", address: "Jl. Pahlawan No. 5, Kel. Cilandak", weight: "~8 Kg", status: "Dalam Perjalanan" as const, statusTone: "Blue" as const },
    { name: "Ahmad Farhan", address: "Jl. Sudirman Blok C3, Kel. Kebayoran", weight: "~22 Kg", status: "Menunggu" as const, statusTone: "Neutral" as const },
];
</script>

<template>
    <Head title="Dashboard Driver - ReValue" />
    <DashboardLayout>
        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
            <div>
                <h1 class="text-[22px] sm:text-[26px] font-extrabold text-night-ink tracking-tight leading-tight">
                    Operasional Armada
                </h1>
                <p class="text-[14px] font-medium text-charcoal mt-1">
                    Halo, {{ userName }}. Siap menjemput kebaikan hari ini?
                </p>
            </div>
            <StatusChip label="Aktif Bertugas" tone="Green" />
        </div>

        <!-- 3 Metric Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
            <MetricCard
                label="Tugas Jemput Hari Ini"
                value="8 Titik"
                sublabel="3 Selesai · 5 Dalam Perjalanan"
                :icon="Truck"
                iconColor="text-night-ink"
            />
            <MetricCard
                label="Total Muatan Armada"
                value="340 Kg"
                sublabel="Kapasitas maks. 500 Kg"
                :icon="Package"
                iconColor="text-eco-green-dark"
            />
            <MetricCard
                label="Status Armada"
                value="Truk 02"
                sublabel="Rute Wilayah Barat"
                :icon="CheckCircle2"
                iconColor="text-sky-blue-dark"
            />
        </div>

        <!-- Task Queue + Mascot Note -->
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-4">
            <!-- Active Pickup Queue -->
            <div class="lg:col-span-3 bg-paper-white border-2 border-b-[4px] border-border-gray rounded-[20px] p-5 xl:p-6">
                <h3 class="text-[15px] font-extrabold text-night-ink mb-1">Antrean Penjemputan Aktif</h3>
                <p class="text-[12px] font-medium text-charcoal mb-4">Prioritas hari ini</p>

                <div class="flex flex-col gap-3">
                    <div
                        v-for="task in tasks"
                        :key="task.name"
                        class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4 rounded-xl border border-border-gray hover:bg-cloud-surface transition-colors"
                    >
                        <div class="flex items-start gap-3 flex-1 min-w-0">
                            <div class="size-9 shrink-0 bg-cloud-surface rounded-xl flex items-center justify-center">
                                <MapPin class="size-4 text-eco-green-dark" />
                            </div>
                            <div class="min-w-0">
                                <p class="font-bold text-[14px] text-night-ink truncate">{{ task.name }}</p>
                                <p class="text-[12px] font-normal text-charcoal truncate mt-0.5">{{ task.address }}</p>
                                <p class="text-[12px] font-semibold text-eco-green-dark mt-0.5">Est. {{ task.weight }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <StatusChip :label="task.status" :tone="task.statusTone" />
                            <button
                                type="button"
                                class="flex items-center gap-1.5 px-3 py-1.5 bg-eco-green text-night-ink text-[12px] font-extrabold rounded-xl border-2 border-b-[3px] border-eco-green-dark hover:brightness-105 active:translate-y-px transition-all"
                            >
                                <Navigation class="size-3.5" />
                                Buka Peta
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Driver Note Card with Mascot -->
            <div class="lg:col-span-2 bg-sprout-tint border-2 border-b-[4px] border-eco-green/40 rounded-[20px] p-5 xl:p-6 flex flex-col justify-between overflow-hidden relative min-h-[280px]">
                <div class="relative z-10">
                    <StatusChip label="Valu Insight" tone="Green" />
                    <h3 class="text-[16px] font-extrabold text-night-ink mt-3 leading-snug">
                        Jemput dengan semangat!
                    </h3>
                    <p class="text-[13px] font-medium text-charcoal mt-2 leading-relaxed">
                        Setiap penjemputan yang kamu selesaikan adalah langkah nyata menuju lingkungan yang lebih baik. Tetap semangat!
                    </p>
                    <div class="mt-4 flex items-center gap-2">
                        <div class="h-2 w-full bg-eco-green/20 rounded-full overflow-hidden">
                            <div class="h-full bg-eco-green rounded-full" style="width: 60%"></div>
                        </div>
                        <span class="text-[12px] font-bold text-eco-green-dark shrink-0">3/8</span>
                    </div>
                    <p class="text-[11px] font-medium text-charcoal mt-1">Progres tugas hari ini</p>
                </div>
                <!-- Mascot -->
                <div class="absolute bottom-0 right-0 w-[130px] xl:w-[150px] pointer-events-none select-none">
                    <img
                        src="/assets/Maskot/Pose=Pickup-Sprint.png"
                        alt="Valu Pickup Sprint"
                        class="w-full object-contain drop-shadow-lg"
                    />
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
