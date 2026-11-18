<script setup lang="ts">
import { computed } from "vue";
import { X, ShoppingCart, MapPin, Package, Truck, User, CreditCard } from "lucide-vue-next";
import StatusChip from "@/components/Dashboard/StatusChip.vue";
import UserAvatar from "@/components/Dashboard/UserAvatar.vue";

export interface TransactionDetailItem {
    id: number;
    transaction_id: number;
    product_id: number;
    quantity: number;
    unit_price: number;
    product?: {
        id: number;
        name: string;
        photo: string | null;
        photo_url?: string | null;
    } | null;
}

export interface ProductTransactionDetailData {
    id: number;
    status: string;
    payment_method: string;
    total_amount: number;
    points_used: number;
    points_discount_amount: number;
    payable_amount: number;
    delivery_address: string | null;
    latitude: string | null;
    longitude: string | null;
    delivery_proof_photo: string | null;
    snap_url: string | null;
    settled_at: string | null;
    delivered_at: string | null;
    canceled_at: string | null;
    created_at: string;
    updated_at: string;
    customer?: {
        id: number;
        user?: {
            id: number;
            name: string;
            email: string;
            photo: string | null;
            role: string;
        } | null;
    } | null;
    driver?: {
        id: number;
        user?: {
            id: number;
            name: string;
            email: string;
            photo: string | null;
            role: string;
        } | null;
    } | null;
    details?: TransactionDetailItem[];
}

const props = defineProps<{
    show: boolean;
    transaction: ProductTransactionDetailData | null;
}>();

const emit = defineEmits<{
    (e: "close"): void;
}>();

const statusTone = computed(() => {
    if (!props.transaction) return "Neutral";
    const map: Record<string, "Green" | "Amber" | "Blue" | "Neutral"> = {
        pending: "Amber",
        processing: "Blue",
        delivering: "Blue",
        completed: "Green",
        canceled: "Neutral",
    };
    return map[props.transaction.status] ?? "Neutral";
});

const statusLabel = computed(() => {
    if (!props.transaction) return "";
    const map: Record<string, string> = {
        pending: "Menunggu Pembayaran",
        processing: "Sedang Diproses Gudang",
        delivering: "Sedang Dikirim Driver",
        completed: "Pesanan Diterima",
        canceled: "Pesanan Dibatalkan",
    };
    return map[props.transaction.status] ?? props.transaction.status;
});

const paymentMethodLabel = computed(() => {
    if (!props.transaction) return "";
    const map: Record<string, string> = {
        midtrans: "Midtrans",
        points: "Poin Bank Sampah",
    };
    return map[props.transaction.payment_method] ?? props.transaction.payment_method;
});

const paymentTone = computed((): "Green" | "Amber" | "Blue" | "Neutral" => {
    if (!props.transaction) return "Neutral";
    return props.transaction.payment_method === "midtrans" ? "Blue" : "Green";
});

const formatDate = (dateStr: string | null | undefined) => {
    if (!dateStr) return "-";
    return new Date(dateStr).toLocaleDateString("id-ID", {
        day: "numeric",
        month: "long",
        year: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    });
};

const formatRupiah = (n: number) =>
    "Rp " + n.toLocaleString("id-ID");
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="show && transaction"
                class="fixed inset-0 z-50 flex items-center justify-center p-4"
                @click.self="emit('close')"
            >
                <!-- Backdrop -->
                <div class="absolute inset-0 bg-night-ink/40 backdrop-blur-sm" @click="emit('close')" />

                <!-- Modal Card -->
                <div
                    class="relative z-10 w-full max-w-2xl max-h-[90vh] overflow-y-auto bg-cloud-surface rounded-[24px] border-2 border-b-[6px] border-border-gray shadow-2xl"
                >
                    <!-- Header -->
                    <div class="flex items-start justify-between gap-4 p-6 border-b border-border-gray/50">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1">
                                <ShoppingCart class="size-5 text-eco-green shrink-0" />
                                <h2 class="text-lg font-black text-night-ink truncate">
                                    Transaksi #{{ transaction.id }}
                                </h2>
                            </div>
                            <div class="flex flex-wrap items-center gap-2">
                                <StatusChip :tone="statusTone" :label="statusLabel" />
                                <span class="text-sm text-muted-text">{{ formatDate(transaction.created_at) }}</span>
                            </div>
                        </div>
                        <button
                            @click="emit('close')"
                            class="shrink-0 p-2 rounded-xl border-2 border-b-[3px] border-border-gray text-muted-text hover:text-night-ink hover:bg-cloud-hover active:translate-y-0.5 active:border-b-2 transition-all"
                        >
                            <X class="size-4" />
                        </button>
                    </div>

                    <!-- Body -->
                    <div class="p-6 space-y-6">
                        <!-- Customer -->
                        <div>
                            <h3 class="text-xs font-bold text-muted-text uppercase tracking-wider mb-3 flex items-center gap-1.5">
                                <User class="size-3.5" /> Customer
                            </h3>
                            <div v-if="transaction.customer?.user" class="flex items-center gap-3 p-3 rounded-xl bg-cloud-hover border border-border-gray/50">
                                <UserAvatar :user="transaction.customer.user" size="sm" />
                                <div>
                                    <p class="font-bold text-night-ink text-sm">{{ transaction.customer.user.name }}</p>
                                    <p class="text-xs text-muted-text">{{ transaction.customer.user.email }}</p>
                                </div>
                            </div>
                            <p v-else class="text-sm text-muted-text italic">Data pembeli tidak tersedia</p>
                        </div>

                        <!-- Metode Pembayaran & Ringkasan -->
                        <div>
                            <h3 class="text-xs font-bold text-muted-text uppercase tracking-wider mb-3 flex items-center gap-1.5">
                                <CreditCard class="size-3.5" /> Pembayaran
                            </h3>
                            <div class="space-y-2 p-4 rounded-xl bg-cloud-hover border border-border-gray/50">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-muted-text">Metode</span>
                                    <StatusChip :tone="paymentTone" :label="paymentMethodLabel" />
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-muted-text">Total Produk</span>
                                    <span class="text-sm font-semibold text-night-ink">{{ formatRupiah(transaction.total_amount) }}</span>
                                </div>
                                <div v-if="transaction.points_used > 0" class="flex items-center justify-between">
                                    <span class="text-sm text-muted-text">Potongan Poin ({{ transaction.points_used.toLocaleString('id-ID') }} poin)</span>
                                    <span class="text-sm font-semibold text-eco-green">-{{ formatRupiah(transaction.points_discount_amount) }}</span>
                                </div>
                                <div class="flex items-center justify-between pt-2 border-t border-border-gray/50">
                                    <span class="text-sm font-black text-night-ink">Total Bayar</span>
                                    <span class="text-base font-black text-eco-green">{{ formatRupiah(transaction.payable_amount) }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Detail Produk -->
                        <div v-if="transaction.details && transaction.details.length > 0">
                            <h3 class="text-xs font-bold text-muted-text uppercase tracking-wider mb-3 flex items-center gap-1.5">
                                <Package class="size-3.5" /> Produk Dipesan
                            </h3>
                            <div class="border border-border-gray/60 rounded-xl overflow-hidden">
                                <table class="w-full text-sm">
                                    <thead class="bg-cloud-hover border-b border-border-gray/60">
                                        <tr>
                                            <th class="text-left px-4 py-2.5 text-xs font-bold text-muted-text">Produk</th>
                                            <th class="text-right px-4 py-2.5 text-xs font-bold text-muted-text">Qty</th>
                                            <th class="text-right px-4 py-2.5 text-xs font-bold text-muted-text">Harga</th>
                                            <th class="text-right px-4 py-2.5 text-xs font-bold text-muted-text">Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-border-gray/40">
                                        <tr v-for="detail in transaction.details" :key="detail.id">
                                            <td class="px-4 py-3">
                                                <div class="flex items-center gap-2">
                                                    <img
                                                        v-if="detail.product?.photo_url"
                                                        :src="detail.product.photo_url"
                                                        :alt="detail.product.name"
                                                        class="size-8 object-cover rounded-lg border border-border-gray shrink-0"
                                                    />
                                                    <div v-else class="size-8 rounded-lg bg-cloud-hover border border-border-gray shrink-0 flex items-center justify-center">
                                                        <Package class="size-4 text-muted-text" />
                                                    </div>
                                                    <span class="font-medium text-night-ink">{{ detail.product?.name ?? "-" }}</span>
                                                </div>
                                            </td>
                                            <td class="px-4 py-3 text-right text-muted-text">{{ detail.quantity }}</td>
                                            <td class="px-4 py-3 text-right text-muted-text">{{ formatRupiah(detail.unit_price) }}</td>
                                            <td class="px-4 py-3 text-right font-bold text-night-ink">{{ formatRupiah(detail.quantity * detail.unit_price) }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Alamat Pengiriman -->
                        <div v-if="transaction.delivery_address">
                            <h3 class="text-xs font-bold text-muted-text uppercase tracking-wider mb-3 flex items-center gap-1.5">
                                <MapPin class="size-3.5" /> Alamat Pengiriman
                            </h3>
                            <p class="text-sm text-night-ink bg-cloud-hover border border-border-gray/50 rounded-xl px-4 py-3 leading-relaxed">
                                {{ transaction.delivery_address }}
                            </p>
                        </div>

                        <!-- Driver Pengantar -->
                        <div v-if="transaction.driver?.user">
                            <h3 class="text-xs font-bold text-muted-text uppercase tracking-wider mb-3 flex items-center gap-1.5">
                                <Truck class="size-3.5" /> Driver Pengantar
                            </h3>
                            <div class="flex items-center gap-3 p-3 rounded-xl bg-cloud-hover border border-border-gray/50">
                                <UserAvatar :user="transaction.driver.user" size="sm" />
                                <div>
                                    <p class="font-bold text-night-ink text-sm">{{ transaction.driver.user.name }}</p>
                                    <p class="text-xs text-muted-text">{{ transaction.driver.user.email }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Foto Bukti Pengiriman -->
                        <div v-if="transaction.delivery_proof_photo">
                            <h3 class="text-xs font-bold text-muted-text uppercase tracking-wider mb-3">Bukti Pengiriman</h3>
                            <img
                                :src="transaction.delivery_proof_photo"
                                alt="Bukti Pengiriman"
                                class="w-full max-h-56 object-cover rounded-xl border-2 border-border-gray"
                            />
                        </div>

                        <!-- Timestamps -->
                        <div class="grid grid-cols-2 gap-3">
                            <div v-if="transaction.settled_at">
                                <p class="text-xs font-bold text-muted-text uppercase tracking-wider mb-1">Waktu Bayar</p>
                                <p class="text-sm text-night-ink">{{ formatDate(transaction.settled_at) }}</p>
                            </div>
                            <div v-if="transaction.delivered_at">
                                <p class="text-xs font-bold text-muted-text uppercase tracking-wider mb-1">Waktu Terima</p>
                                <p class="text-sm text-night-ink">{{ formatDate(transaction.delivered_at) }}</p>
                            </div>
                            <div v-if="transaction.canceled_at">
                                <p class="text-xs font-bold text-muted-text uppercase tracking-wider mb-1">Waktu Batal</p>
                                <p class="text-sm text-night-ink">{{ formatDate(transaction.canceled_at) }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="p-6 border-t border-border-gray/50 flex justify-end">
                        <button
                            @click="emit('close')"
                            class="px-6 py-2.5 text-sm font-bold text-night-ink bg-cloud-surface border-2 border-b-[4px] border-border-gray rounded-xl hover:bg-cloud-hover active:translate-y-0.5 active:border-b-2 transition-all"
                        >
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
