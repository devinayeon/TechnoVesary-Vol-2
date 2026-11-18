<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
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
    PackageOpen,
} from 'lucide-vue-next';

interface MetricItem {
    value: string;
    sublabel: string;
}

interface DriverTaskItem {
    id: number;
    task_type: 'pickup' | 'delivery';
    type_label: string;
    name: string;
    address: string;
    detail_info: string;
    status: string;
    status_label: string;
    status_tone: 'Green' | 'Amber' | 'Blue' | 'Neutral';
    action_url: string;
    action_label: string;
}

interface ProgressData {
    completed: number;
    total: number;
    percentage: number;
}

const props = defineProps<{
    auth: { user: User };
    metrics: {
        activeTasks: MetricItem;
        weightCollected: MetricItem;
        deliveriesCompleted: MetricItem;
    };
    tasks: DriverTaskItem[];
    progress: ProgressData;
    isAvailable: boolean;
}>();

const userName = computed(() => props.auth?.user?.name ?? 'Driver');
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
            <StatusChip
                :label="isAvailable ? 'Aktif Bertugas' : 'Armada Istirahat'"
                :tone="isAvailable ? 'Green' : 'Amber'"
            />
        </div>

        <!-- 3 Metric Cards (Real Database Data) -->
        <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
            <MetricCard
                label="Tugas Aktif Armada"
                :value="metrics.activeTasks.value"
                :sublabel="metrics.activeTasks.sublabel"
                :icon="Truck"
                iconColor="text-night-ink"
            />
            <MetricCard
                label="Total Sampah Terkumpul"
                :value="metrics.weightCollected.value"
                :sublabel="metrics.weightCollected.sublabel"
                :icon="Package"
                iconColor="text-eco-green-dark"
            />
            <MetricCard
                label="Pengantaran Produk Upcycle"
                :value="metrics.deliveriesCompleted.value"
                :sublabel="metrics.deliveriesCompleted.sublabel"
                :icon="CheckCircle2"
                iconColor="text-sky-blue-dark"
            />
        </div>

        <!-- Task Queue + Mascot Note -->
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-5">
            <!-- Active Pickup & Delivery Queue -->
            <div
                class="rounded-[20px] border-2 border-b-[4px] border-border-gray bg-paper-white p-5 lg:col-span-3 xl:p-6"
            >
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <h3 class="text-[15px] font-extrabold text-night-ink">
                            Antrean Tugas Operasional
                        </h3>
                        <p class="mt-0.5 text-[12px] font-medium text-charcoal">
                            Penjemputan sampah dan pengantaran produk aktif
                        </p>
                    </div>
                    <Link
                        href="/driver/pickups"
                        class="text-xs font-bold text-eco-green-dark hover:underline"
                    >
                        Lihat Semua
                    </Link>
                </div>

                <!-- Daftar Tugas Riil -->
                <div v-if="tasks.length > 0" class="flex flex-col gap-3">
                    <div
                        v-for="task in tasks"
                        :key="`${task.task_type}-${task.id}`"
                        class="flex flex-col justify-between gap-3 rounded-xl border border-border-gray p-4 transition-colors hover:bg-cloud-surface sm:flex-row sm:items-center"
                    >
                        <div class="flex min-w-0 flex-1 items-start gap-3">
                            <div
                                class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-cloud-surface"
                            >
                                <MapPin class="size-4 text-eco-green-dark" />
                            </div>
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <span
                                        class="rounded-md border border-border-gray bg-cloud-surface px-1.5 py-0.5 text-[10px] font-bold text-night-ink"
                                    >
                                        {{ task.type_label }}
                                    </span>
                                    <p
                                        class="truncate text-[14px] font-bold text-night-ink"
                                    >
                                        {{ task.name }}
                                    </p>
                                </div>
                                <p
                                    class="mt-1 truncate text-[12px] font-normal text-charcoal"
                                >
                                    {{ task.address }}
                                </p>
                                <p
                                    class="mt-0.5 text-[12px] font-semibold text-eco-green-dark"
                                >
                                    {{ task.detail_info }}
                                </p>
                            </div>
                        </div>
                        <div class="flex shrink-0 items-center gap-2">
                            <StatusChip
                                :label="task.status_label"
                                :tone="task.status_tone"
                            />
                            <Link
                                :href="task.action_url"
                                class="flex items-center gap-1.5 rounded-xl border-2 border-b-[3px] border-eco-green-dark bg-eco-green px-3 py-1.5 text-[12px] font-extrabold text-night-ink shadow-xs transition-all hover:brightness-105 active:translate-y-px"
                            >
                                <Navigation class="size-3.5" />
                                <span>{{ task.action_label }}</span>
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Empty State jika belum ada tugas -->
                <div
                    v-else
                    class="flex flex-col items-center justify-center rounded-xl border border-dashed border-border-gray py-10 text-center"
                >
                    <PackageOpen class="size-10 text-charcoal/40" />
                    <p class="mt-2 text-xs font-bold text-night-ink">
                        Tidak ada antrean tugas aktif saat ini
                    </p>
                    <p class="mt-0.5 text-[11px] text-charcoal">
                        Semua tugas penjemputan dan pengantaran telah selesai
                        atau belum tersedia penugasan baru.
                    </p>
                </div>
            </div>

            <!-- Driver Note Card with Mascot -->
            <div
                class="relative flex min-h-[280px] flex-col justify-between overflow-hidden rounded-[20px] border-2 border-b-[4px] border-eco-green/40 bg-sprout-tint p-5 lg:col-span-2 xl:p-6"
            >
                <div class="relative z-10 max-w-[70%]">
                    <StatusChip label="Semangat Armada" tone="Green" />
                    <h3
                        class="mt-3 text-[16px] leading-snug font-extrabold text-night-ink"
                    >
                        Jemput dengan semangat!
                    </h3>
                    <p
                        class="mt-2 text-[13px] leading-relaxed font-medium text-charcoal"
                    >
                        Setiap kilogram sampah terpilah yang Anda angkut
                        menggerakkan masa depan Indonesia yang lebih hijau.
                    </p>
                    <div class="mt-4 flex items-center gap-2">
                        <div
                            class="h-2.5 w-full overflow-hidden rounded-full bg-eco-green/20"
                        >
                            <div
                                class="h-full rounded-full bg-eco-green transition-all duration-300"
                                :style="{ width: `${progress.percentage}%` }"
                            ></div>
                        </div>
                        <span
                            class="shrink-0 text-[12px] font-black text-eco-green-dark"
                        >
                            {{ progress.completed }}/{{ progress.total }}
                        </span>
                    </div>
                    <p class="mt-1 text-[11px] font-semibold text-charcoal">
                        Progres penyelesaian hari ini ({{
                            progress.percentage
                        }}%)
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
