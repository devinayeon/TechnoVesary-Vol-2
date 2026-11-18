<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import type { User } from '@/types/auth';
import DashboardLayout from '@/layouts/DashboardLayout.vue';
import MetricCard from '@/components/Dashboard/MetricCard.vue';
import StatusChip from '@/components/Dashboard/StatusChip.vue';
import { Wallet, Recycle, Sparkles, Star, Truck } from 'lucide-vue-next';

const props = defineProps<{
    auth: { user: User };
}>();

const userName = computed(() => props.auth?.user?.name ?? 'Customer');

const badges = [
    {
        name: 'Pilah Master',
        desc: '50 Kg pertama disetor',
        icon: '🏆',
        unlocked: true,
    },
    {
        name: 'Pejuang 25 Order',
        desc: '25 kali penjemputan',
        icon: '🌟',
        unlocked: true,
    },
    {
        name: 'Eco Warrior',
        desc: '100 Kg sampah dikelola',
        icon: '🌍',
        unlocked: false,
    },
    {
        name: 'ReValue+ Member',
        desc: 'Berlangganan aktif',
        icon: '💎',
        unlocked: false,
    },
];

const recentDeposits = [
    {
        date: '5 Nov 2024',
        weight: '12.4 Kg',
        earning: '+Rp 24.800',
        types: 'Plastik, Kardus',
    },
    {
        date: '28 Okt 2024',
        weight: '8.9 Kg',
        earning: '+Rp 17.800',
        types: 'Plastik, Logam',
    },
    {
        date: '20 Okt 2024',
        weight: '15.2 Kg',
        earning: '+Rp 30.400',
        types: 'Kardus, Lainnya',
    },
];
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
            <StatusChip label="Customer Aktif" tone="Green" />
        </div>

        <!-- 3 Metric Cards -->
        <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
            <MetricCard
                label="Saldo Tabungan Sampah"
                value="Rp 450.000"
                sublabel="+Rp 75.000 bulan ini"
                :icon="Wallet"
                iconColor="text-eco-green-dark"
            />
            <MetricCard
                label="Total Sampah Disetor"
                value="84,5 Kg"
                sublabel="+12.3 Kg bulan ini"
                :icon="Recycle"
                iconColor="text-sky-blue-dark"
            />
            <MetricCard
                label="Poin Eco-Reward"
                value="1.250 Poin"
                sublabel="Dapat ditukar voucher"
                :icon="Sparkles"
                iconColor="text-sunlight-gold"
            />
        </div>

        <!-- CTA + History Row -->
        <div class="mb-6 grid grid-cols-1 gap-4 lg:grid-cols-5">
            <!-- Recent Deposit History -->
            <div
                class="rounded-[20px] border-2 border-b-[4px] border-border-gray bg-paper-white p-5 lg:col-span-3 xl:p-6"
            >
                <h3 class="mb-1 text-[15px] font-extrabold text-night-ink">
                    Riwayat Setoran Terakhir
                </h3>
                <p class="mb-4 text-[12px] font-medium text-charcoal">
                    3 setoran terbaru Anda
                </p>
                <div class="flex flex-col gap-3">
                    <div
                        v-for="dep in recentDeposits"
                        :key="dep.date"
                        class="flex items-center justify-between gap-3 rounded-xl border border-border-gray p-3 transition-colors hover:bg-cloud-surface"
                    >
                        <div>
                            <p class="text-[13px] font-bold text-night-ink">
                                {{ dep.weight }}
                            </p>
                            <p
                                class="mt-0.5 text-[11px] font-medium text-charcoal"
                            >
                                {{ dep.types }} · {{ dep.date }}
                            </p>
                        </div>
                        <span
                            class="text-[13px] font-extrabold text-eco-green-dark"
                            >{{ dep.earning }}</span
                        >
                    </div>
                </div>

                <!-- Quick action CTA -->
                <div class="mt-5 border-t border-border-gray/60 pt-4">
                    <button
                        type="button"
                        class="flex h-[48px] w-full cursor-pointer items-center justify-center gap-2 rounded-2xl border-2 border-b-[4px] border-eco-green-dark bg-eco-green text-[14px] font-extrabold text-night-ink transition-all hover:brightness-105 active:translate-y-px active:border-b-2"
                    >
                        <Truck class="size-4" />
                        Jadwalkan Penjemputan Sampah
                    </button>
                </div>
            </div>

            <!-- Celebrate Mascot + Badges -->
            <div class="flex flex-col gap-4 lg:col-span-2">
                <!-- Mascot celebrate card -->
                <div
                    class="relative flex min-h-[180px] flex-col justify-between overflow-hidden rounded-[20px] border-2 border-b-[4px] border-sunlight-gold/50 bg-gold-tint p-5"
                >
                    <div class="relative z-10">
                        <StatusChip label="Valu Celebrate" tone="Amber" />
                        <h3
                            class="mt-2 text-[15px] leading-snug font-extrabold text-night-ink"
                        >
                            Luar biasa, {{ userName }}!
                        </h3>
                        <p
                            class="mt-1 text-[12px] leading-relaxed font-medium text-charcoal"
                        >
                            Kamu sudah berkontribusi mengurangi 84.5 Kg sampah.
                            Terus semangat!
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
                    <div class="mb-3 flex items-center gap-2">
                        <Star class="size-4 text-sunlight-gold" />
                        <h3 class="text-[14px] font-extrabold text-night-ink">
                            Pencapaian
                        </h3>
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
