<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import type { User } from '@/types/auth';
import DashboardLayout from '@/layouts/DashboardLayout.vue';
import MetricCard from '@/components/Dashboard/MetricCard.vue';
import StatusChip from '@/components/Dashboard/StatusChip.vue';
import {
    Truck,
    Package,
    CheckCircle2,
    MapPin,
    Navigation,
} from 'lucide-vue-next';

const props = defineProps<{
    auth: { user: User };
}>();

const userName = computed(() => props.auth?.user?.name ?? 'Driver');

const tasks = [
    {
        name: 'Budi Santoso',
        address: 'Jl. Merdeka No. 12, Kel. Sukamaju',
        weight: '~15 Kg',
        status: 'Menunggu' as const,
        statusTone: 'Neutral' as const,
    },
    {
        name: 'Siti Rahayu',
        address: 'Jl. Pahlawan No. 5, Kel. Cilandak',
        weight: '~8 Kg',
        status: 'Dalam Perjalanan' as const,
        statusTone: 'Blue' as const,
    },
    {
        name: 'Ahmad Farhan',
        address: 'Jl. Sudirman Blok C3, Kel. Kebayoran',
        weight: '~22 Kg',
        status: 'Menunggu' as const,
        statusTone: 'Neutral' as const,
    },
];
</script>

<template>
    <Head title="Dashboard Driver - ReValue" />
    <DashboardLayout>
        <!-- Page Header -->
        <div
            class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
        >
            <div>
                <h1
                    class="text-[22px] leading-tight font-extrabold tracking-tight text-night-ink sm:text-[26px]"
                >
                    Operasional Armada
                </h1>
                <p class="mt-1 text-[14px] font-medium text-charcoal">
                    Halo, {{ userName }}. Siap menjemput kebaikan hari ini?
                </p>
            </div>
            <StatusChip label="Aktif Bertugas" tone="Green" />
        </div>

        <!-- 3 Metric Cards -->
        <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
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
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-5">
            <!-- Active Pickup Queue -->
            <div
                class="rounded-[20px] border-2 border-b-[4px] border-border-gray bg-paper-white p-5 lg:col-span-3 xl:p-6"
            >
                <h3 class="mb-1 text-[15px] font-extrabold text-night-ink">
                    Antrean Penjemputan Aktif
                </h3>
                <p class="mb-4 text-[12px] font-medium text-charcoal">
                    Prioritas hari ini
                </p>

                <div class="flex flex-col gap-3">
                    <div
                        v-for="task in tasks"
                        :key="task.name"
                        class="flex flex-col justify-between gap-3 rounded-xl border border-border-gray p-4 transition-colors hover:bg-cloud-surface sm:flex-row sm:items-center"
                    >
                        <div class="flex min-w-0 flex-1 items-start gap-3">
                            <div
                                class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-cloud-surface"
                            >
                                <MapPin class="size-4 text-eco-green-dark" />
                            </div>
                            <div class="min-w-0">
                                <p
                                    class="truncate text-[14px] font-bold text-night-ink"
                                >
                                    {{ task.name }}
                                </p>
                                <p
                                    class="mt-0.5 truncate text-[12px] font-normal text-charcoal"
                                >
                                    {{ task.address }}
                                </p>
                                <p
                                    class="mt-0.5 text-[12px] font-semibold text-eco-green-dark"
                                >
                                    Est. {{ task.weight }}
                                </p>
                            </div>
                        </div>
                        <div class="flex shrink-0 items-center gap-2">
                            <StatusChip
                                :label="task.status"
                                :tone="task.statusTone"
                            />
                            <button
                                type="button"
                                class="flex items-center gap-1.5 rounded-xl border-2 border-b-[3px] border-eco-green-dark bg-eco-green px-3 py-1.5 text-[12px] font-extrabold text-night-ink transition-all hover:brightness-105 active:translate-y-px"
                            >
                                <Navigation class="size-3.5" />
                                Buka Peta
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Driver Note Card with Mascot -->
            <div
                class="relative flex min-h-[280px] flex-col justify-between overflow-hidden rounded-[20px] border-2 border-b-[4px] border-eco-green/40 bg-sprout-tint p-5 lg:col-span-2 xl:p-6"
            >
                <div class="relative z-10">
                    <StatusChip label="Valu Insight" tone="Green" />
                    <h3
                        class="mt-3 text-[16px] leading-snug font-extrabold text-night-ink"
                    >
                        Jemput dengan semangat!
                    </h3>
                    <p
                        class="mt-2 text-[13px] leading-relaxed font-medium text-charcoal"
                    >
                        Setiap penjemputan yang kamu selesaikan adalah langkah
                        nyata menuju lingkungan yang lebih baik. Tetap semangat!
                    </p>
                    <div class="mt-4 flex items-center gap-2">
                        <div
                            class="h-2 w-full overflow-hidden rounded-full bg-eco-green/20"
                        >
                            <div
                                class="h-full rounded-full bg-eco-green"
                                style="width: 60%"
                            ></div>
                        </div>
                        <span
                            class="shrink-0 text-[12px] font-bold text-eco-green-dark"
                            >3/8</span
                        >
                    </div>
                    <p class="mt-1 text-[11px] font-medium text-charcoal">
                        Progres tugas hari ini
                    </p>
                </div>
                <!-- Mascot -->
                <div
                    class="pointer-events-none absolute right-0 bottom-0 w-[130px] select-none xl:w-[150px]"
                >
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
