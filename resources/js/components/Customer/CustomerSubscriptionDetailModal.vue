<script setup lang="ts">
import { computed } from 'vue';
import {
    X,
    CreditCard,
    Coins,
    AlertCircle,
    RotateCcw,
    XCircle,
} from 'lucide-vue-next';
import StatusChip from '@/components/Dashboard/StatusChip.vue';

export interface CustomerSubscriptionTransactionData {
    id: number;
    customer_id: number;
    subscription_id: number;
    package_key: string | null;
    duration_months: number;
    total_amount: number;
    points_used: number;
    points_discount_amount: number;
    payable_amount: number;
    payment_method: string;
    status: string;
    midtrans_order_id: string | null;
    snap_token: string | null;
    snap_url: string | null;
    settled_at: string | null;
    canceled_at: string | null;
    created_at: string;
    updated_at: string;
    subscription?: {
        id: number;
        name: string;
        price: number;
        description: string | null;
    } | null;
}

const props = defineProps<{
    show: boolean;
    transaction: CustomerSubscriptionTransactionData | null;
    isCanceling?: boolean;
    isRepaying?: boolean;
    isCheckingStatus?: boolean;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'cancel', transaction: CustomerSubscriptionTransactionData): void;
    (e: 'repay', transaction: CustomerSubscriptionTransactionData): void;
    (e: 'checkStatus', transaction: CustomerSubscriptionTransactionData): void;
}>();

const statusTone = computed((): 'Green' | 'Amber' | 'Blue' | 'Neutral' => {
    if (!props.transaction) return 'Neutral';
    const map: Record<string, 'Green' | 'Amber' | 'Blue' | 'Neutral'> = {
        pending: 'Amber',
        settled: 'Green',
        canceled: 'Neutral',
        failed: 'Neutral',
        expired: 'Neutral',
    };
    return map[props.transaction.status] ?? 'Neutral';
});

const statusLabel = computed(() => {
    if (!props.transaction) return '';
    const map: Record<string, string> = {
        pending: 'Menunggu Pembayaran',
        settled: 'Lunas & Aktif',
        canceled: 'Dibatalkan',
        failed: 'Gagal',
        expired: 'Kedaluwarsa',
    };
    return map[props.transaction.status] ?? props.transaction.status;
});

const packageName = computed(() => {
    if (!props.transaction) return 'ReValue+';
    if (props.transaction.package_key === '3_months') return 'ReValue+ 3 Bulan';
    if (props.transaction.package_key === '1_year') return 'ReValue+ 1 Tahun';
    if (props.transaction.package_key === '1_month') return 'ReValue+ 1 Bulan';
    return props.transaction.subscription?.name ?? 'ReValue+';
});

const packageDurationDays = computed(() => {
    if (!props.transaction) return 30;
    if (props.transaction.package_key === '3_months') return 90;
    if (props.transaction.package_key === '1_year') return 365;
    return 30;
});

const paymentMethodLabel = computed(() => {
    if (!props.transaction) return '';
    return props.transaction.payment_method === 'points'
        ? 'Poin Bank Sampah (Full)'
        : 'Midtrans Payment Gateway';
});

const formatDate = (dateStr: string | null | undefined) => {
    if (!dateStr) return '-';
    return new Date(dateStr).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const formatRupiah = (n: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(n);
};
</script>

<template>
    <div
        v-if="show && transaction"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6"
    >
        <!-- Backdrop -->
        <div
            class="fixed inset-0 bg-night-ink/60 backdrop-blur-xs transition-opacity"
            @click="emit('close')"
        ></div>

        <!-- Modal Dialog -->
        <div
            class="relative z-10 max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-[28px] border-2 border-b-[6px] border-border-gray bg-paper-white p-6 shadow-2xl sm:p-8"
        >
            <!-- Tombol Close -->
            <button
                type="button"
                @click="emit('close')"
                class="absolute top-5 right-5 flex size-9 cursor-pointer items-center justify-center rounded-xl border-2 border-b-2 border-border-gray bg-cloud-surface text-charcoal transition-colors hover:bg-border-gray hover:text-night-ink"
            >
                <X class="size-5" />
            </button>

            <!-- Header Modal -->
            <div class="border-b border-border-gray/70 pb-4">
                <div class="flex flex-wrap items-center gap-2">
                    <span
                        class="rounded-lg border border-border-gray bg-cloud-surface px-2.5 py-0.5 text-xs font-black text-night-ink"
                    >
                        #SUB-{{ transaction.id }}
                    </span>
                    <StatusChip :tone="statusTone" :label="statusLabel" />
                </div>
                <h2 class="mt-2 text-xl font-black text-night-ink sm:text-2xl">
                    Detail Transaksi Langganan
                </h2>
                <p class="text-xs font-medium text-charcoal">
                    Dibuat pada {{ formatDate(transaction.created_at) }}
                </p>
            </div>

            <!-- Content Modal -->
            <div class="mt-6 space-y-6">
                <!-- Info Banner Paket -->
                <div
                    class="flex flex-col gap-4 rounded-2xl border-2 border-border-gray/70 bg-cloud-surface p-4 sm:flex-row sm:items-center"
                >
                    <div
                        class="flex size-16 shrink-0 items-center justify-center rounded-xl border border-border-gray bg-paper-white p-1.5 shadow-2xs"
                    >
                        <img
                            src="/assets/Badge/ReValuePass.png"
                            alt="ReValue Pass"
                            class="h-12 w-12 object-contain"
                        />
                    </div>
                    <div class="min-w-0 flex-1 space-y-1">
                        <div class="flex items-center gap-2">
                            <span
                                class="rounded-md border border-eco-green/30 bg-sprout-tint px-2 py-0.5 text-[11px] font-black text-night-ink"
                            >
                                {{ packageDurationDays }} Hari
                            </span>
                            <span class="text-xs font-bold text-charcoal">
                                Periode Keanggotaan
                            </span>
                        </div>
                        <h4 class="text-lg font-black text-night-ink">
                            {{ packageName }}
                        </h4>
                        <p class="text-xs font-medium text-charcoal">
                            Akses jemputan gratis tanpa batas & bonus pengali
                            reward poin 1.5x.
                        </p>
                    </div>
                </div>

                <!-- Alert Pembayaran Pending -->
                <div
                    v-if="transaction.status === 'pending'"
                    class="rounded-xl border-2 border-sunlight-gold/50 bg-gold-tint p-4 text-xs font-semibold text-night-ink"
                >
                    <div class="flex items-start gap-3">
                        <AlertCircle
                            class="mt-0.5 size-5 shrink-0 text-night-ink"
                        />
                        <div class="space-y-1">
                            <strong class="font-extrabold text-night-ink"
                                >Menunggu Pembayaran</strong
                            >
                            <p class="font-medium text-charcoal">
                                Tagihan Anda belum diselesaikan. Silakan klik
                                tombol <strong>Lanjutkan Pembayaran</strong> di
                                bawah atau <strong>Cek Status</strong> jika Anda
                                sudah mentransfer melalui Midtrans.
                            </p>
                            <p
                                v-if="transaction.points_used > 0"
                                class="text-[11px] font-bold text-eco-green-dark"
                            >
                                * Catatan: Jika dibatalkan, poin sebanyak
                                {{
                                    transaction.points_used.toLocaleString(
                                        'id-ID',
                                    )
                                }}
                                poin akan dikembalikan ke akun Anda.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Rincian Biaya -->
                <div
                    class="rounded-xl border-2 border-border-gray/70 bg-cloud-surface p-4"
                >
                    <h5
                        class="text-xs font-bold tracking-wider text-charcoal uppercase"
                    >
                        Rincian Tagihan
                    </h5>
                    <div class="mt-3 space-y-2.5 text-xs sm:text-sm">
                        <div
                            class="flex items-center justify-between font-medium text-charcoal"
                        >
                            <span>Harga Paket</span>
                            <span class="font-bold text-night-ink">
                                {{ formatRupiah(transaction.total_amount) }}
                            </span>
                        </div>

                        <div
                            v-if="transaction.points_discount_amount > 0"
                            class="flex items-center justify-between font-semibold text-eco-green-dark"
                        >
                            <span class="flex items-center gap-1">
                                <Coins class="size-3.5 text-sunlight-gold" />
                                Potongan Poin ({{
                                    transaction.points_used.toLocaleString(
                                        'id-ID',
                                    )
                                }})
                            </span>
                            <span class="font-black">
                                -{{
                                    formatRupiah(
                                        transaction.points_discount_amount,
                                    )
                                }}
                            </span>
                        </div>

                        <div class="border-t border-border-gray/70 pt-2.5">
                            <div
                                class="flex items-center justify-between text-base font-black"
                            >
                                <span class="text-night-ink"
                                    >Total Tagihan</span
                                >
                                <span class="text-night-ink">
                                    {{
                                        formatRupiah(transaction.payable_amount)
                                    }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Detail Metadata Transaksi -->
                <div class="grid grid-cols-1 gap-4 text-xs sm:grid-cols-2">
                    <div
                        class="space-y-1 rounded-xl border border-border-gray/70 bg-cloud-surface p-3"
                    >
                        <span class="block font-semibold text-charcoal"
                            >Metode Pembayaran</span
                        >
                        <div class="font-extrabold text-night-ink">
                            {{ paymentMethodLabel }}
                        </div>
                        <div
                            v-if="transaction.midtrans_order_id"
                            class="text-[11px] font-medium text-charcoal"
                        >
                            Order ID: {{ transaction.midtrans_order_id }}
                        </div>
                    </div>

                    <div
                        class="space-y-1 rounded-xl border border-border-gray/70 bg-cloud-surface p-3"
                    >
                        <span class="block font-semibold text-charcoal"
                            >Status Waktu</span
                        >
                        <div
                            v-if="transaction.settled_at"
                            class="font-extrabold text-eco-green-dark"
                        >
                            Lunas pada {{ formatDate(transaction.settled_at) }}
                        </div>
                        <div
                            v-else-if="transaction.canceled_at"
                            class="font-extrabold text-red-600"
                        >
                            Dibatalkan pada
                            {{ formatDate(transaction.canceled_at) }}
                        </div>
                        <div v-else class="font-extrabold text-charcoal">
                            Menunggu Penyelesaian
                        </div>
                    </div>
                </div>

                <!-- Action Buttons Khusus Transaksi Pending -->
                <div
                    v-if="transaction.status === 'pending'"
                    class="space-y-3 border-t border-border-gray/70 pt-4"
                >
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <!-- Tombol Cek Status Midtrans -->
                        <button
                            type="button"
                            @click="emit('checkStatus', transaction)"
                            :disabled="isCheckingStatus"
                            class="flex cursor-pointer items-center justify-center gap-2 rounded-xl border-2 border-b-[4px] border-sky-blue-dark bg-sky-blue px-4 py-2.5 text-xs font-black text-night-ink shadow-xs transition hover:brightness-105 active:translate-y-0.5 active:border-b-2 disabled:opacity-50"
                        >
                            <RotateCcw
                                class="size-4"
                                :class="{ 'animate-spin': isCheckingStatus }"
                            />
                            <span>{{
                                isCheckingStatus
                                    ? 'Memeriksa...'
                                    : 'Cek Status Pembayaran'
                            }}</span>
                        </button>

                        <!-- Tombol Lanjutkan Pembayaran (Snap) -->
                        <button
                            type="button"
                            @click="emit('repay', transaction)"
                            :disabled="isRepaying"
                            class="flex cursor-pointer items-center justify-center gap-2 rounded-xl border-2 border-b-[4px] border-eco-green-dark bg-eco-green px-4 py-2.5 text-xs font-black text-night-ink shadow-xs transition hover:brightness-105 active:translate-y-0.5 active:border-b-2 disabled:opacity-50"
                        >
                            <CreditCard class="size-4" />
                            <span>{{
                                isRepaying
                                    ? 'Membuka Gateway...'
                                    : 'Lanjutkan Pembayaran'
                            }}</span>
                        </button>
                    </div>

                    <!-- Tombol Batalkan Transaksi -->
                    <button
                        type="button"
                        @click="emit('cancel', transaction)"
                        :disabled="isCanceling"
                        class="flex w-full cursor-pointer items-center justify-center gap-2 rounded-xl border-2 border-b-[4px] border-red-200 bg-red-50 px-4 py-2.5 text-xs font-black text-red-700 shadow-xs transition hover:bg-red-100 active:translate-y-0.5 active:border-b-2 disabled:opacity-50"
                    >
                        <XCircle class="size-4 text-red-600" />
                        <span>{{
                            isCanceling
                                ? 'Membatalkan...'
                                : 'Batalkan Transaksi Ini'
                        }}</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
