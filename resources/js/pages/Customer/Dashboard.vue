<script setup lang="ts">
import { Head, Link } from "@inertiajs/vue3";
import { computed } from "vue";
import type { User } from "@/types/auth";
import DashboardLayout from "@/layouts/DashboardLayout.vue";
import MetricCard from "@/components/Dashboard/MetricCard.vue";
import StatusChip from "@/components/Dashboard/StatusChip.vue";
import { Wallet, Recycle, Sparkles, Star, Truck } from "lucide-vue-next";

const props = defineProps<{
    auth: { user: User };
}>();

const userName = computed(() => props.auth?.user?.name ?? "Nasabah");

const badges = [
    { name: "Pilah Master", desc: "50 Kg pertama disetor", icon: "🏆", unlocked: true },
    { name: "Pejuang 25 Order", desc: "25 kali penjemputan", icon: "🌟", unlocked: true },
    { name: "Eco Warrior", desc: "100 Kg sampah dikelola", icon: "🌍", unlocked: false },
    { name: "ReValue+ Member", desc: "Berlangganan aktif", icon: "💎", unlocked: false },
];

const recentDeposits = [
    { date: "5 Nov 2024", weight: "12.4 Kg", earning: "+Rp 24.800", types: "Plastik, Kardus" },
    { date: "28 Okt 2024", weight: "8.9 Kg", earning: "+Rp 17.800", types: "Plastik, Logam" },
    { date: "20 Okt 2024", weight: "15.2 Kg", earning: "+Rp 30.400", types: "Kardus, Lainnya" },
];
</script>

<template>
    <Head title="Dashboard Nasabah - ReValue" />
    <DashboardLayout>
        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
            <div>
                <h1 class="text-[22px] sm:text-[26px] font-extrabold text-night-ink tracking-tight leading-tight">
                    Halo, {{ userName }}! 👋
                </h1>
                <p class="text-[14px] font-medium text-charcoal mt-1">
                    Sampahmu hari ini adalah langkah baik untuk bumi besok.
                </p>
            </div>
            <StatusChip label="Nasabah Aktif" tone="Green" />
        </div>

        <!-- 3 Metric Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
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
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-4 mb-6">
            <!-- Recent Deposit History -->
            <div class="lg:col-span-3 bg-paper-white border-2 border-b-[4px] border-border-gray rounded-[20px] p-5 xl:p-6">
                <h3 class="text-[15px] font-extrabold text-night-ink mb-1">Riwayat Setoran Terakhir</h3>
                <p class="text-[12px] font-medium text-charcoal mb-4">3 setoran terbaru Anda</p>
                <div class="flex flex-col gap-3">
                    <div
                        v-for="dep in recentDeposits"
                        :key="dep.date"
                        class="flex items-center justify-between gap-3 p-3 rounded-xl border border-border-gray hover:bg-cloud-surface transition-colors"
                    >
                        <div>
                            <p class="font-bold text-[13px] text-night-ink">{{ dep.weight }}</p>
                            <p class="text-[11px] font-medium text-charcoal mt-0.5">{{ dep.types }} · {{ dep.date }}</p>
                        </div>
                        <span class="font-extrabold text-[13px] text-eco-green-dark">{{ dep.earning }}</span>
                    </div>
                </div>

                <!-- Quick action CTA -->
                <div class="mt-5 pt-4 border-t border-border-gray/60">
                    <button
                        type="button"
                        class="w-full flex items-center justify-center gap-2 h-[48px] bg-eco-green text-night-ink font-extrabold text-[14px] rounded-2xl border-2 border-b-[4px] border-eco-green-dark hover:brightness-105 active:translate-y-px active:border-b-2 transition-all cursor-pointer"
                    >
                        <Truck class="size-4" />
                        Jadwalkan Penjemputan Sampah
                    </button>
                </div>
            </div>

            <!-- Celebrate Mascot + Badges -->
            <div class="lg:col-span-2 flex flex-col gap-4">
                <!-- Mascot celebrate card -->
                <div class="bg-gold-tint border-2 border-b-[4px] border-sunlight-gold/50 rounded-[20px] p-5 flex flex-col justify-between overflow-hidden relative min-h-[180px]">
                    <div class="relative z-10">
                        <StatusChip label="Valu Celebrate" tone="Amber" />
                        <h3 class="text-[15px] font-extrabold text-night-ink mt-2 leading-snug">
                            Luar biasa, {{ userName }}!
                        </h3>
                        <p class="text-[12px] font-medium text-charcoal mt-1 leading-relaxed">
                            Kamu sudah berkontribusi mengurangi 84.5 Kg sampah. Terus semangat!
                        </p>
                    </div>
                    <div class="absolute bottom-0 right-0 w-[110px] pointer-events-none select-none">
                        <img
                            src="/assets/Maskot/Pose=Celebrate-Coins.png"
                            alt="Valu Celebrate"
                            class="w-full object-contain drop-shadow-lg"
                        />
                    </div>
                </div>

                <!-- Badge showcase -->
                <div class="bg-paper-white border-2 border-b-[4px] border-border-gray rounded-[20px] p-5 flex-1">
                    <div class="flex items-center gap-2 mb-3">
                        <Star class="size-4 text-sunlight-gold" />
                        <h3 class="text-[14px] font-extrabold text-night-ink">Pencapaian</h3>
                    </div>
                    <div class="grid grid-cols-2 gap-2.5">
                        <div
                            v-for="badge in badges"
                            :key="badge.name"
                            :class="[
                                'rounded-xl p-3 border text-center transition-all',
                                badge.unlocked
                                    ? 'bg-sprout-tint border-eco-green/40'
                                    : 'bg-cloud-surface border-border-gray opacity-60 grayscale',
                            ]"
                        >
                            <div class="text-[22px] mb-1">{{ badge.icon }}</div>
                            <p class="text-[11px] font-extrabold text-night-ink leading-tight">{{ badge.name }}</p>
                            <p class="text-[10px] font-medium text-charcoal mt-0.5 leading-tight">{{ badge.desc }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
