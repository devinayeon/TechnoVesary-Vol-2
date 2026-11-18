<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/layouts/DashboardLayout.vue';
import PageHeader from '@/components/Dashboard/PageHeader.vue';
import {
    Sparkles,
    CheckCircle2,
    Calendar,
    Crown,
    ArrowRight,
    Truck,
    Coins,
    ShieldCheck,
    History,
    Zap,
} from 'lucide-vue-next';

export interface PackageItem {
    key: string;
    name: string;
    duration_months: number;
    duration_days: number;
    multiplier: number;
    price: number;
    badge: string;
    badge_tone: string;
    description: string;
    expires_at_preview: string;
    is_recommended: boolean;
}

export interface BaseSubscription {
    id: number;
    name: string;
    price: number;
    description: string | null;
}

export interface Membership {
    is_active: boolean;
    expires_at_formatted: string | null;
    expires_at_raw: string | null;
}

const props = defineProps<{
    baseSubscription: BaseSubscription;
    packages: PackageItem[];
    membership: Membership;
    customerPoints: number;
}>();

const formatRupiah = (amount: number): string => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(amount);
};
</script>

<template>
    <Head title="Langganan ReValue+" />

    <DashboardLayout>
        <div class="space-y-8">
            <!-- Header Halaman -->
            <div
                class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
            >
                <PageHeader
                    title="Langganan ReValue+"
                    subtitle="Pilih paket langganan penjemputan sampah terpilah rutin dan nikmati bonus poin berlipat."
                    badge="Keanggotaan Premium"
                />
                <div>
                    <Link
                        href="/customer/subscription/history"
                        class="inline-flex cursor-pointer items-center gap-2 rounded-xl border-2 border-b-4 border-border-gray bg-paper-white px-4 py-2.5 text-sm font-extrabold tracking-wide text-night-ink uppercase shadow-xs transition-colors hover:bg-cloud-surface active:translate-y-0.5 active:border-b-2"
                    >
                        <History class="size-4 text-eco-green-dark" />
                        <span>Riwayat Langganan</span>
                    </Link>
                </div>
            </div>

            <!-- Kartu Status Keanggotaan Saat Ini -->
            <div
                v-if="membership.is_active"
                class="relative overflow-hidden rounded-[24px] border-2 border-b-[5px] border-eco-green bg-sprout-tint/35 p-6 shadow-xs"
            >
                <div
                    class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div class="flex items-start gap-4">
                        <div
                            class="flex size-14 shrink-0 items-center justify-center rounded-2xl bg-eco-green text-night-ink shadow-[0px_4px_0px_0px_var(--color-eco-green-dark)]"
                        >
                            <Crown class="size-7" />
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <span
                                    class="rounded-full bg-eco-green px-3 py-0.5 text-[11px] font-black tracking-wider text-night-ink uppercase"
                                >
                                    ReValue+ Aktif
                                </span>
                                <span
                                    class="flex items-center gap-1 text-xs font-bold text-night-ink"
                                >
                                    <Sparkles
                                        class="size-3.5 text-sunlight-gold"
                                    />
                                    Member Prioritas
                                </span>
                            </div>
                            <h3 class="mt-1 text-xl font-black text-night-ink">
                                Anda adalah Anggota Resmi ReValue+
                            </h3>
                            <div
                                class="mt-1.5 flex flex-wrap items-center gap-x-4 gap-y-1 text-sm font-medium text-charcoal"
                            >
                                <div class="flex items-center gap-1.5">
                                    <Calendar
                                        class="size-4 text-eco-green-dark"
                                    />
                                    <span>Berlaku hingga:</span>
                                    <strong
                                        class="font-extrabold text-night-ink"
                                        >{{
                                            membership.expires_at_formatted
                                        }}</strong
                                    >
                                </div>
                                <div
                                    class="flex items-center gap-1 text-xs font-bold text-eco-green-dark"
                                >
                                    <CheckCircle2 class="size-3.5" />
                                    <span
                                        >Paket baru yang dibeli otomatis
                                        menambah sisa masa aktif</span
                                    >
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <div
                            class="rounded-2xl border-2 border-border-gray/70 bg-paper-white px-5 py-3 text-right shadow-xs"
                        >
                            <span
                                class="block text-[11px] font-bold tracking-wider text-charcoal uppercase"
                                >Saldo Poin Anda</span
                            >
                            <div class="flex items-center justify-end gap-1.5">
                                <Coins class="size-4 text-sunlight-gold" />
                                <span class="text-xl font-black text-night-ink"
                                    >{{
                                        customerPoints.toLocaleString('id-ID')
                                    }}
                                    Poin</span
                                >
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div
                v-else
                class="relative overflow-hidden rounded-[24px] border-2 border-b-[5px] border-border-gray bg-cloud-surface p-6 shadow-xs"
            >
                <div
                    class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div class="flex items-start gap-4">
                        <div
                            class="flex size-14 shrink-0 items-center justify-center rounded-2xl border-2 border-border-gray bg-paper-white text-charcoal shadow-xs"
                        >
                            <Crown class="size-7" />
                        </div>
                        <div>
                            <span
                                class="rounded-full border border-border-gray bg-paper-white px-3 py-0.5 text-[11px] font-bold text-charcoal"
                            >
                                Belum Berlangganan
                            </span>
                            <h3 class="mt-1 text-xl font-black text-night-ink">
                                Nikmati Keistimewaan ReValue+
                            </h3>
                            <p class="mt-1 text-sm font-medium text-charcoal">
                                Dapatkan akses penjemputan sampah terpilah
                                instan tanpa batas dan bonus pengali poin
                                reward.
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <div
                            class="rounded-2xl border-2 border-border-gray bg-paper-white px-5 py-3 text-right shadow-xs"
                        >
                            <span
                                class="block text-[11px] font-bold tracking-wider text-charcoal uppercase"
                                >Saldo Poin</span
                            >
                            <div class="flex items-center justify-end gap-1.5">
                                <Coins class="size-4 text-sunlight-gold" />
                                <span class="text-xl font-black text-night-ink"
                                    >{{
                                        customerPoints.toLocaleString('id-ID')
                                    }}
                                    Poin</span
                                >
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bento Card Pilihan Paket (1 Bulan, 3 Bulan, 1 Tahun) -->
            <div>
                <div class="mb-8 text-center">
                    <p
                        class="mb-2 text-[13px] font-bold tracking-[1.6px] text-charcoal uppercase"
                    >
                        PILIH LANGKAH BAIK YANG PAS
                    </p>
                    <h2
                        class="text-3xl font-extrabold tracking-tight text-night-ink sm:text-4xl"
                    >
                        Pilihan Paket Langganan ReValue+
                    </h2>
                    <p class="mt-2 text-[15px] font-medium text-charcoal">
                        Pilihan durasi fleksibel. Bayar dengan Rupiah, saldo
                        Poin ReValue, atau kombinasi keduanya.
                    </p>
                </div>

                <!-- 3 Kartu Paket Langganan Sesuai Desain Landing Page & Product Page -->
                <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                    <div
                        v-for="pkg in packages"
                        :key="pkg.key"
                        class="group relative flex min-h-[500px] cursor-default flex-col justify-between rounded-[28px] p-7 transition-all duration-300 ease-out hover:-translate-y-2.5"
                        :class="[
                            pkg.is_recommended
                                ? 'border-2 border-b-[6px] border-night-ink bg-night-ink text-paper-white shadow-[0px_6px_0px_0px_#00021c] hover:shadow-[0_20px_40px_-10px_rgba(0,4,55,0.45),0px_8px_0px_0px_#00021c]'
                                : 'border-2 border-b-[6px] border-border-gray bg-paper-white text-night-ink shadow-[0px_4px_0px_0px_var(--color-border-gray)] hover:border-b-[6px] hover:border-eco-green hover:border-b-eco-green-dark hover:shadow-[0_16px_36px_-8px_rgba(88,204,2,0.22),0px_6px_0px_0px_var(--color-eco-green-dark)]',
                        ]"
                    >
                        <!-- Top Info -->
                        <div>
                            <!-- Tag / Badge Atas -->
                            <p
                                class="mb-4 text-[13px] font-bold tracking-[1.6px] uppercase transition-colors duration-200"
                                :class="[
                                    pkg.is_recommended
                                        ? 'text-sprout-tint'
                                        : 'text-charcoal group-hover:text-eco-green-dark',
                                ]"
                            >
                                {{
                                    pkg.is_recommended
                                        ? '⭐ PALING POPULER'
                                        : pkg.key === '1_year'
                                          ? '🏆 PALING HEMAT'
                                          : 'LANGKAH PERTAMA'
                                }}
                            </p>

                            <!-- Artwork Kartu ReValuePass -->
                            <div
                                class="mb-5 flex h-32 w-full items-center justify-center overflow-hidden rounded-[20px] p-3 transition-transform duration-300 group-hover:scale-105"
                                :class="[
                                    pkg.is_recommended
                                        ? 'bg-paper-white/10'
                                        : 'border border-border-gray/70 bg-cloud-surface',
                                ]"
                            >
                                <img
                                    src="/assets/Badge/ReValuePass.png"
                                    alt="ReValue Pass Card"
                                    class="h-24 w-auto object-contain drop-shadow-md"
                                />
                            </div>

                            <!-- Nama Paket -->
                            <h3
                                class="mb-4 text-2xl font-bold tracking-tight sm:text-[26px]"
                                :class="[
                                    pkg.is_recommended
                                        ? 'text-paper-white'
                                        : 'text-night-ink',
                                ]"
                            >
                                {{ pkg.name }}
                            </h3>

                            <!-- Price (Harga dikali lama bulan) -->
                            <div class="mb-5">
                                <div
                                    class="text-3xl leading-tight font-extrabold tracking-tight sm:text-[38px]"
                                    :class="[
                                        pkg.is_recommended
                                            ? 'text-paper-white'
                                            : 'text-night-ink',
                                    ]"
                                >
                                    {{ formatRupiah(pkg.price) }}
                                </div>
                                <div
                                    class="mt-1 text-[15px] font-bold tracking-wide uppercase"
                                    :class="[
                                        pkg.is_recommended
                                            ? 'text-paper-white/80'
                                            : 'text-night-ink',
                                    ]"
                                >
                                    / {{ pkg.duration_days }} hari
                                </div>
                                <div
                                    class="mt-1 text-[15px] font-bold tracking-wide uppercase"
                                    :class="[
                                        pkg.is_recommended
                                            ? 'text-sprout-tint'
                                            : 'text-charcoal',
                                    ]"
                                >
                                    atau
                                    {{ pkg.price.toLocaleString('id-ID') }} poin
                                </div>
                            </div>

                            <!-- Preview Tanggal Aktif -->
                            <div
                                class="mb-5 rounded-xl p-3 text-xs font-bold"
                                :class="[
                                    pkg.is_recommended
                                        ? 'border border-paper-white/20 bg-paper-white/10 text-sprout-tint'
                                        : 'border border-border-gray bg-cloud-surface text-night-ink',
                                ]"
                            >
                                <span>{{
                                    membership.is_active
                                        ? 'Perpanjangan aktif hingga: '
                                        : 'Aktif hingga: '
                                }}</span>
                                <span class="font-extrabold">{{
                                    pkg.expires_at_preview
                                }}</span>
                            </div>

                            <!-- Keuntungan / Benefits -->
                            <div
                                class="mb-8 flex flex-col gap-2.5 text-[15px] font-medium"
                                :class="[
                                    pkg.is_recommended
                                        ? 'text-paper-white/90'
                                        : 'text-night-ink',
                                ]"
                            >
                                <p>✓ Jemput sampah terpilah tanpa batas</p>
                                <p>✓ Prioritas penjemputan oleh driver</p>
                                <p>
                                    ✓ Bonus pengali poin reward
                                    <strong>1.5x</strong>
                                </p>
                                <p>✓ Lencana eksklusif profil ReValue+</p>
                            </div>
                        </div>

                        <!-- Tactile Action Button -->
                        <div>
                            <Link
                                :href="`/customer/subscription/checkout?package=${pkg.key}`"
                                class="flex h-12 w-full cursor-pointer items-center justify-center gap-2 rounded-2xl bg-eco-green text-[14px] font-extrabold tracking-wide text-night-ink uppercase shadow-[0px_4px_0px_0px_var(--color-eco-green-dark)] transition-all hover:brightness-105 active:translate-y-1 active:shadow-none"
                            >
                                <span>Pilih Paket</span>
                                <ArrowRight class="size-4" />
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bento Card: Keuntungan Eksklusif ReValue+ & Maskot -->
            <div
                class="overflow-hidden rounded-[28px] border-2 border-b-[6px] border-border-gray bg-paper-white p-7 shadow-xs sm:p-9"
            >
                <div
                    class="grid grid-cols-1 items-center gap-8 lg:grid-cols-12"
                >
                    <div class="space-y-6 lg:col-span-8">
                        <div>
                            <span
                                class="inline-flex items-center gap-1.5 rounded-full border border-eco-green/30 bg-sprout-tint px-3 py-1 text-xs font-black tracking-wider text-night-ink uppercase"
                            >
                                <Zap class="size-3.5 text-eco-green-dark" />
                                Keistimewaan Member
                            </span>
                            <h3
                                class="mt-3 text-2xl font-black text-night-ink sm:text-3xl"
                            >
                                Bebas Repot, Lebih Untung, dan Berdampak Nyata
                            </h3>
                            <p class="mt-2 text-sm font-medium text-charcoal">
                                Member ReValue+ mendapatkan layanan penjemputan
                                terbaik langsung ke pintu rumah Anda tanpa biaya
                                tambahan, plus keuntungan poin maksimal.
                            </p>
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div
                                class="flex items-start gap-3 rounded-[18px] border border-border-gray/70 bg-cloud-surface p-4"
                            >
                                <div
                                    class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-sprout-tint text-night-ink"
                                >
                                    <Truck class="size-5 text-eco-green-dark" />
                                </div>
                                <div>
                                    <h4
                                        class="text-sm font-extrabold text-night-ink"
                                    >
                                        Penjemputan Instan Bebas Biaya
                                    </h4>
                                    <p
                                        class="mt-0.5 text-xs font-medium text-charcoal"
                                    >
                                        Driver menjemput sampah rumah tangga
                                        Anda tanpa antre dan bebas ongkir.
                                    </p>
                                </div>
                            </div>

                            <div
                                class="flex items-start gap-3 rounded-[18px] border border-border-gray/70 bg-cloud-surface p-4"
                            >
                                <div
                                    class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-gold-tint text-night-ink"
                                >
                                    <Coins class="size-5 text-sunlight-gold" />
                                </div>
                                <div>
                                    <h4
                                        class="text-sm font-extrabold text-night-ink"
                                    >
                                        Bonus Pengali Poin 1.5x
                                    </h4>
                                    <p
                                        class="mt-0.5 text-xs font-medium text-charcoal"
                                    >
                                        Kumpulkan poin daur ulang lebih cepat
                                        untuk ditukarkan di Upcycle Store.
                                    </p>
                                </div>
                            </div>

                            <div
                                class="flex items-start gap-3 rounded-[18px] border border-border-gray/70 bg-cloud-surface p-4"
                            >
                                <div
                                    class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-sky-tint text-night-ink"
                                >
                                    <Crown class="size-5 text-sky-blue-dark" />
                                </div>
                                <div>
                                    <h4
                                        class="text-sm font-extrabold text-night-ink"
                                    >
                                        Lencana Profil Eksklusif
                                    </h4>
                                    <p
                                        class="mt-0.5 text-xs font-medium text-charcoal"
                                    >
                                        Tanda kehormatan pahlawan sirkular
                                        berkelanjutan di komunitas ReValue.
                                    </p>
                                </div>
                            </div>

                            <div
                                class="flex items-start gap-3 rounded-[18px] border border-border-gray/70 bg-cloud-surface p-4"
                            >
                                <div
                                    class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-lavender-tint text-night-ink"
                                >
                                    <ShieldCheck class="size-5 text-charcoal" />
                                </div>
                                <div>
                                    <h4
                                        class="text-sm font-extrabold text-night-ink"
                                    >
                                        Layanan Terpercaya
                                    </h4>
                                    <p
                                        class="mt-0.5 text-xs font-medium text-charcoal"
                                    >
                                        Penimbangan akurat dan sampah disalurkan
                                        ke mitra daur ulang tersertifikasi.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-center lg:col-span-4">
                        <div class="flex flex-col items-center text-center">
                            <img
                                src="/assets/Maskot/Pose=Instant-ReValuePlus.png"
                                alt="Maskot ReValue+"
                                class="h-52 w-auto object-contain drop-shadow-lg"
                            />
                            <div
                                class="mt-4 rounded-xl border border-border-gray bg-cloud-surface px-4 py-2 text-xs font-extrabold text-night-ink shadow-xs"
                            >
                                🎉 Solusi praktis gaya hidup minim sampah!
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
