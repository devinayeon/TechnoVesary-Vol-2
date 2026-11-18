<script setup lang="ts">
import { computed } from "vue";
import {
    X,
    Receipt,
    User,
    Calendar,
    CreditCard,
    Coins,
    CheckCircle2,
    Clock,
    XCircle,
    ExternalLink,
} from "lucide-vue-next";
import StatusChip from "@/components/Dashboard/StatusChip.vue";
import UserAvatar from "@/components/Dashboard/UserAvatar.vue";

export interface SubscriptionTransactionItem {
    id: number;
    customer_id: number;
    subscription_id: number;
    total_amount: number;
    points_used: number;
    points_discount_amount: number;
    payable_amount: number;
    payment_method: string;
    snap_url: string | null;
    status: string;
    settled_at: string | null;
    canceled_at: string | null;
    created_at: string;
    customer?: {
        id: number;
        user?: {
            id: number;
            name: string;
            email: string;
            phone_number: string | null;
            photo: string | null;
            role: string;
        };
    };
    subscription?: {
        id: number;
        name: string;
        price: number;
    };
}

const props = defineProps<{
    show: boolean;
    transaction: SubscriptionTransactionItem | null;
}>();

const emit = defineEmits<{
    (e: "close"): void;
}>();

const formatNumber = (num: number): string => {
    return Number(num || 0).toLocaleString("id-ID");
};

const formatDate = (dateString?: string | null): string => {
    if (!dateString) return "—";
    try {
        const d = new Date(dateString);
        return d.toLocaleDateString("id-ID", {
            day: "numeric",
            month: "short",
            year: "numeric",
            hour: "2-digit",
            minute: "2-digit",
        });
    } catch {
        return dateString;
    }
};

const statusTone = computed((): "Green" | "Amber" | "Neutral" => {
    switch (props.transaction?.status?.toLowerCase()) {
        case "settled":
            return "Green";
        case "pending":
            return "Amber";
        default:
            return "Neutral";
    }
});

const statusLabel = computed((): string => {
    switch (props.transaction?.status?.toLowerCase()) {
        case "settled":
            return "Berhasil / Lunas";
        case "pending":
            return "Menunggu Pembayaran";
        case "failed":
            return "Gagal";
        case "canceled":
            return "Dibatalkan";
        default:
            return props.transaction?.status || "—";
    }
});

const paymentMethodLabel = computed((): string => {
    switch (props.transaction?.payment_method?.toLowerCase()) {
        case "midtrans":
            return "Midtrans Payment Gateway";
        case "points":
            return "Poin Bank Sampah (Full)";
        default:
            return props.transaction?.payment_method || "Midtrans";
    }
});
</script>

<template>
    <teleport to="body">
        <transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="show && transaction"
                class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-night-ink/50 backdrop-blur-xs overflow-y-auto"
                @click.self="emit('close')"
            >
                <div
                    class="w-full max-w-[560px] bg-paper-white border-2 border-border-gray rounded-[24px] p-6 sm:p-8 shadow-2xl relative my-8 animate-in fade-in zoom-in-95 duration-150 space-y-5"
                >
                    <!-- Header -->
                    <div
                        class="flex items-start justify-between gap-4 pb-4 border-b border-border-gray/70"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                class="size-11 rounded-2xl bg-cloud-surface border-2 border-border-gray text-night-ink flex items-center justify-center shrink-0 shadow-2xs"
                            >
                                <Receipt class="size-5 text-eco-green-dark" />
                            </div>
                            <div>
                                <h3
                                    class="text-[20px] font-extrabold text-night-ink leading-tight"
                                >
                                    Detail Transaksi
                                </h3>
                                <p class="text-[13px] font-bold text-charcoal/70 mt-0.5">
                                    ID: #TRX-SUB-{{ String(transaction.id).padStart(4, '0') }}
                                </p>
                            </div>
                        </div>
                        <button
                            type="button"
                            class="p-2 rounded-xl text-charcoal hover:bg-cloud-surface hover:text-night-ink transition-colors cursor-pointer"
                            @click="emit('close')"
                            aria-label="Tutup Detail"
                        >
                            <X class="size-5" />
                        </button>
                    </div>

                    <!-- Customer Section -->
                    <div
                        class="p-4 rounded-2xl bg-cloud-surface/70 border border-border-gray flex items-center justify-between gap-3"
                    >
                        <div class="flex items-center gap-3 min-w-0">
                            <UserAvatar
                                :photo="transaction.customer?.user?.photo || null"
                                :role="transaction.customer?.user?.role || 'customer'"
                                :name="transaction.customer?.user?.name || 'Customer'"
                                size="md"
                            />
                            <div class="min-w-0">
                                <h4
                                    class="font-extrabold text-[15px] text-night-ink truncate"
                                >
                                    {{ transaction.customer?.user?.name || "Customer" }}
                                </h4>
                                <p class="text-[12px] font-medium text-charcoal truncate">
                                    {{ transaction.customer?.user?.email || "—" }}
                                </p>
                                <p
                                    v-if="transaction.customer?.user?.phone_number"
                                    class="text-[11px] font-bold text-charcoal/80 truncate mt-0.5"
                                >
                                    {{ transaction.customer?.user?.phone_number }}
                                </p>
                            </div>
                        </div>
                        <StatusChip :label="statusLabel" :tone="statusTone" />
                    </div>

                    <!-- Package & Timeline Info -->
                    <div class="grid grid-cols-2 gap-3 text-[13px]">
                        <div class="p-3.5 rounded-xl border border-border-gray bg-paper-white">
                            <span class="text-[11px] font-bold text-charcoal uppercase tracking-wider block">
                                Paket Langganan
                            </span>
                            <span class="font-extrabold text-night-ink block mt-1">
                                {{ transaction.subscription?.name || "ReValue+ Bulanan" }}
                            </span>
                            <span class="text-[11px] font-medium text-charcoal">
                                Durasi: 30 Hari Layanan
                            </span>
                        </div>
                        <div class="p-3.5 rounded-xl border border-border-gray bg-paper-white">
                            <span class="text-[11px] font-bold text-charcoal uppercase tracking-wider block">
                                Waktu Transaksi
                            </span>
                            <span class="font-extrabold text-night-ink block mt-1">
                                {{ formatDate(transaction.created_at) }}
                            </span>
                            <span
                                v-if="transaction.settled_at"
                                class="text-[11px] font-bold text-eco-green-dark flex items-center gap-1 mt-0.5"
                            >
                                <CheckCircle2 class="size-3" />
                                Lunas: {{ formatDate(transaction.settled_at) }}
                            </span>
                            <span
                                v-else-if="transaction.canceled_at"
                                class="text-[11px] font-bold text-red-600 flex items-center gap-1 mt-0.5"
                            >
                                <XCircle class="size-3" />
                                Batal: {{ formatDate(transaction.canceled_at) }}
                            </span>
                            <span
                                v-else
                                class="text-[11px] font-medium text-sunlight-gold-dark flex items-center gap-1 mt-0.5"
                            >
                                <Clock class="size-3" /> Menunggu bayar
                            </span>
                        </div>
                    </div>

                    <!-- Financial Breakdown Table -->
                    <div
                        class="p-4 rounded-2xl bg-paper-white border-2 border-border-gray space-y-2.5"
                    >
                        <h5 class="text-[12px] font-black uppercase tracking-wider text-charcoal mb-2">
                            Rincian Pembayaran
                        </h5>
                        <div class="flex items-center justify-between text-[13px]">
                            <span class="font-medium text-charcoal">Harga Paket</span>
                            <span class="font-bold text-night-ink">
                                Rp {{ formatNumber(transaction.total_amount) }}
                            </span>
                        </div>
                        <div
                            v-if="transaction.points_used > 0"
                            class="flex items-center justify-between text-[13px]"
                        >
                            <span class="font-medium text-charcoal flex items-center gap-1">
                                <Coins class="size-3.5 text-sunlight-gold" />
                                Potongan ({{ formatNumber(transaction.points_used) }} Poin)
                            </span>
                            <span class="font-extrabold text-eco-green-dark">
                                - Rp {{ formatNumber(transaction.points_discount_amount) }}
                            </span>
                        </div>
                        <div class="pt-2 border-t border-border-gray/70 flex items-center justify-between">
                            <span class="font-extrabold text-[14px] text-night-ink">
                                Total Dibayar
                            </span>
                            <span class="font-black text-[16px] text-night-ink">
                                Rp {{ formatNumber(transaction.payable_amount) }}
                            </span>
                        </div>
                        <div class="pt-2 border-t border-border-gray/50 flex items-center justify-between text-[12px]">
                            <span class="text-charcoal font-medium">Metode Pembayaran:</span>
                            <span class="font-extrabold text-night-ink flex items-center gap-1">
                                <CreditCard class="size-3.5 text-charcoal" />
                                {{ paymentMethodLabel }}
                            </span>
                        </div>
                    </div>

                    <!-- Snap URL / Payment Link if Pending -->
                    <div
                        v-if="transaction.status === 'pending' && transaction.snap_url"
                        class="p-3.5 rounded-xl bg-gold-tint/50 border border-sunlight-gold/30 flex items-center justify-between gap-3 text-[13px]"
                    >
                        <span class="font-medium text-night-ink">Link Tagihan Pembayaran:</span>
                        <a
                            :href="transaction.snap_url"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="font-extrabold text-night-ink hover:text-eco-green-dark inline-flex items-center gap-1 hover:underline"
                        >
                            <span>Buka Midtrans</span>
                            <ExternalLink class="size-3.5" />
                        </a>
                    </div>

                    <!-- Footer Action -->
                    <div class="flex items-center justify-end pt-3 border-t border-border-gray/70">
                        <button
                            type="button"
                            class="px-5 h-[44px] rounded-xl border-2 border-b-[4px] border-border-gray bg-paper-white font-extrabold text-[14px] text-night-ink hover:bg-cloud-surface active:translate-y-0.5 active:border-b-2 transition-all cursor-pointer"
                            @click="emit('close')"
                        >
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </transition>
    </teleport>
</template>
