<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import type { User } from '@/types/auth';
import DashboardLayout from '@/layouts/DashboardLayout.vue';
import MetricCard from '@/components/Dashboard/MetricCard.vue';
import StatusChip from '@/components/Dashboard/StatusChip.vue';
import {
    Coins,
    Recycle,
    Crown,
    Star,
    Truck,
    PackageOpen,
    ArrowRight,
} from 'lucide-vue-next';

interface MetricItem {
    value: string;
    sublabel: string;
}

interface RecentDepositItem {
    id: number;
    date: string;
    weight: string;
    earning: string;
    types: string;
    status: string;
    status_label: string;
    status_tone: 'Green' | 'Amber' | 'Blue' | 'Neutral';
    show_url: string;
}

interface BadgeItem {
    name: string;
    desc: string;
    icon: string;
    unlocked: boolean;
}

const props = defineProps<{
    auth: { user: User };
    metrics: {
        points: MetricItem;
        waste: MetricItem;
        membership: MetricItem & { is_member: boolean };
    };
    recentDeposits: RecentDepositItem[];
    badges: BadgeItem[];
    totalWeightKg: number;
    isMember: boolean;
}>();

const userName = computed(() => props.auth?.user?.name ?? 'Customer');
</script>

<template>
    <Head title="Dashboard Customer - ReValue" />
    <DashboardLayout>
        <!-- Page Header -->
        <div
            class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
        >
            <div>
                <h1
                    class="text-[22px] leading-tight font-extrabold tracking-tight text-night-ink sm:text-[26px]"
                >
                    Halo, {{ userName }}! 👋
                </h1>
                <p class="mt-1 text-[14px] font-medium text-charcoal">
                    Sampahmu hari ini adalah langkah baik untuk bumi besok.
                </p>
            </div>
            <StatusChip
                :label="isMember ? 'ReValue+ Member' : 'Customer Aktif'"
                :tone="isMember ? 'Green' : 'Green'"
            />
        </div>

        <!-- 3 Metric Cards (Real Database Data) -->
        <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
            <MetricCard
                label="Saldo Poin Reward"
                :value="metrics.points.value"
                :sublabel="metrics.points.sublabel"
                :icon="Coins"
                iconColor="text-sunlight-gold"
            />
            <MetricCard
                label="Total Sampah Disetor"
                :value="metrics.waste.value"
                :sublabel="metrics.waste.sublabel"
                :icon="Recycle"
                iconColor="text-sky-blue-dark"
            />
            <MetricCard
                label="Status Keanggotaan"
                :value="metrics.membership.value"
                :sublabel="metrics.membership.sublabel"
                :icon="Crown"
                iconColor="text-eco-green-dark"
            />
        </div>

        <!-- CTA + History Row -->
        <div class="mb-6 grid grid-cols-1 gap-4 lg:grid-cols-5">
            <!-- Recent Deposit History -->
            <div
                class="rounded-[20px] border-2 border-b-[4px] border-border-gray bg-paper-white p-5 lg:col-span-3 xl:p-6"
            >
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <h3 class="text-[15px] font-extrabold text-night-ink">
                            Riwayat Setoran Terakhir
                        </h3>
                        <p class="mt-0.5 text-[12px] font-medium text-charcoal">
                            Aktivitas penjemputan sampah terbaru Anda
                        </p>
                    </div>
                    <Link
                        href="/customer/pickups"
                        class="text-xs font-bold text-eco-green-dark hover:underline"
                    >
                        Lihat Semua
                    </Link>
                </div>

                <!-- Daftar Riwayat Riil -->
                <div
                    v-if="recentDeposits.length > 0"
                    class="flex flex-col gap-3"
                >
                    <Link
                        v-for="dep in recentDeposits"
                        :key="dep.id"
                        :href="dep.show_url"
                        class="group flex items-center justify-between gap-3 rounded-xl border border-border-gray p-3.5 transition-colors hover:bg-cloud-surface"
                    >
                        <div class="min-w-0">
                            <p
                                class="text-[13px] font-bold text-night-ink group-hover:text-eco-green-dark"
                            >
                                {{ dep.weight }}
                            </p>
                            <p
                                class="mt-0.5 truncate text-[11px] font-medium text-charcoal"
                            >
                                {{ dep.types }} · {{ dep.date }}
                            </p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span
                                class="text-[13px] font-extrabold text-eco-green-dark"
                            >
                                {{ dep.earning }}
                            </span>
                            <StatusChip
                                :label="dep.status_label"
                                :tone="dep.status_tone"
                            />
                        </div>
                    </Link>
                </div>

                <!-- Empty State jika belum ada setoran -->
                <div
                    v-else
                    class="flex flex-col items-center justify-center rounded-xl border border-dashed border-border-gray py-8 text-center"
                >
                    <PackageOpen class="size-10 text-charcoal/40" />
                    <p class="mt-2 text-xs font-bold text-night-ink">
                        Belum ada riwayat penjemputan sampah
                    </p>
                    <p class="mt-0.5 text-[11px] text-charcoal">
                        Pilah sampah rumah tangga Anda dan mintalah penjemputan
                        pertama!
                    </p>
                </div>

                <!-- Quick action CTA -->
                <div class="mt-5 border-t border-border-gray/60 pt-4">
                    <Link
                        href="/customer/pickups/create"
                        class="flex h-[48px] w-full cursor-pointer items-center justify-center gap-2 rounded-2xl border-2 border-b-[4px] border-eco-green-dark bg-eco-green text-[14px] font-extrabold tracking-wide text-night-ink uppercase shadow-xs transition-all hover:brightness-105 active:translate-y-px active:border-b-2"
                    >
                        <Truck class="size-4" />
                        <span>Jadwalkan Penjemputan Sampah</span>
                    </Link>
                </div>
            </div>

            <!-- Celebrate Mascot + Badges -->
            <div class="flex flex-col gap-4 lg:col-span-2">
                <!-- Mascot celebrate card -->
                <div
                    class="relative flex min-h-[180px] flex-col justify-between overflow-hidden rounded-[20px] border-2 border-b-[4px] border-sunlight-gold/50 bg-gold-tint p-5"
                >
                    <div class="relative z-10 max-w-[65%]">
                        <StatusChip label="Pahlawan Bumi" tone="Amber" />
                        <h3
                            class="mt-2 text-[15px] leading-snug font-extrabold text-night-ink"
                        >
                            Luar biasa, {{ userName }}!
                        </h3>
                        <p
                            class="mt-1 text-[12px] leading-relaxed font-medium text-charcoal"
                        >
                            Kamu sudah berkontribusi menyetor
                            {{ totalWeightKg.toLocaleString('id-ID') }} Kg
                            sampah terpilah. Terus semangat!
                        </p>
                    </div>
                    <div
                        class="pointer-events-none absolute right-0 bottom-0 w-[110px] select-none"
                    >
                        <img
                            src="/assets/Maskot/Pose=Celebrate-Coins.png"
                            alt="Valu Celebrate"
                            class="w-full object-contain drop-shadow-lg"
                        />
                    </div>
                </div>

                <!-- Badge showcase -->
                <div
                    class="flex-1 rounded-[20px] border-2 border-b-[4px] border-border-gray bg-paper-white p-5"
                >
                    <div class="mb-3 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <Star class="size-4 text-sunlight-gold" />
                            <h3
                                class="text-[14px] font-extrabold text-night-ink"
                            >
                                Pencapaian
                            </h3>
                        </div>
                        <Link
                            href="/profile?tab=achievements"
                            class="text-[12px] font-extrabold text-night-ink transition-colors hover:text-eco-green-dark"
                        >
                            Lihat Semua &rarr;
                        </Link>
                    </div>
                    <div class="grid grid-cols-2 gap-2.5">
                        <div
                            v-for="badge in badges"
                            :key="badge.name"
                            :class="[
                                'rounded-xl border p-3 text-center transition-all',
                                badge.unlocked
                                    ? 'border-eco-green/40 bg-sprout-tint'
                                    : 'border-border-gray bg-cloud-surface opacity-60 grayscale',
                            ]"
                        >
                            <div class="mb-1 text-[22px]">{{ badge.icon }}</div>
                            <p
                                class="text-[11px] leading-tight font-extrabold text-night-ink"
                            >
                                {{ badge.name }}
                            </p>
                            <p
                                class="mt-0.5 text-[10px] leading-tight font-medium text-charcoal"
                            >
                                {{ badge.desc }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
