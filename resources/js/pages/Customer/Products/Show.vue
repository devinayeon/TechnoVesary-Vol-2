<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/layouts/DashboardLayout.vue';
import type { ProductItem } from '@/components/Product/ProductCard.vue';
import {
    ArrowLeft,
    Package,
    ShoppingBag,
    Plus,
    Minus,
    Sparkles,
    ShieldCheck,
    Truck,
    CheckCircle2,
} from 'lucide-vue-next';

const props = defineProps<{
    product: ProductItem;
}>();

const quantity = ref(1);

const isOutOfStock = computed(() => props.product.stock <= 0);

const incrementQty = () => {
    if (quantity.value < props.product.stock) {
        quantity.value++;
    }
};

const decrementQty = () => {
    if (quantity.value > 1) {
        quantity.value--;
    }
};

const subtotalRupiah = computed(
    () => props.product.price_rupiah * quantity.value,
);
const subtotalPoints = computed(
    () => props.product.price_rupiah * quantity.value,
);

const formatRupiah = (amount: number): string => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(amount);
};
</script>

<template>
    <Head :title="`${product.name} - Upcycle Store ReValue`" />

    <DashboardLayout>
        <!-- Back Navigation -->
        <div class="mb-6">
            <Link
                href="/customer/products"
                class="group inline-flex cursor-pointer items-center gap-2 text-sm font-bold text-charcoal transition-colors hover:text-night-ink"
            >
                <div
                    class="rounded-lg border-2 border-border-gray bg-paper-white p-1.5 transition-colors group-hover:bg-cloud-surface"
                >
                    <ArrowLeft class="size-4" />
                </div>
                <span>Kembali ke Katalog Produk</span>
            </Link>
        </div>

        <!-- 2-Column Showcase Grid -->
        <div class="grid grid-cols-1 items-start gap-8 lg:grid-cols-12">
            <!-- Left Column: Product Image Gallery -->
            <div
                class="rounded-[26px] border-2 border-b-[5px] border-border-gray bg-paper-white p-6 shadow-xs sm:p-8 lg:col-span-6"
            >
                <div
                    class="relative flex aspect-square w-full items-center justify-center overflow-hidden rounded-[20px] border-2 border-border-gray/70 bg-cloud-surface p-4"
                >
                    <img
                        v-if="product.photo_url || product.photo"
                        :src="
                            product.photo_url ||
                            (product.photo?.startsWith('http')
                                ? product.photo
                                : `/storage/${product.photo}`)
                        "
                        :alt="product.name"
                        class="h-full w-full rounded-xl object-cover"
                    />
                    <div
                        v-else
                        class="flex flex-col items-center justify-center text-charcoal/50"
                    >
                        <Package class="mb-2 size-20" />
                        <span class="text-sm font-bold"
                            >Produk Upcycle Store</span
                        >
                    </div>

                    <!-- Stock Badge -->
                    <div class="absolute top-4 right-4">
                        <span
                            v-if="product.stock > 0"
                            class="rounded-full border-2 border-border-gray bg-paper-white/95 px-3.5 py-1 text-xs font-black text-night-ink shadow-xs backdrop-blur-xs"
                        >
                            Tersedia {{ product.stock }} item
                        </span>
                        <span
                            v-else
                            class="rounded-full border-2 border-red-200 bg-red-50/95 px-3.5 py-1 text-xs font-black text-red-600 shadow-xs backdrop-blur-xs"
                        >
                            Stok Habis
                        </span>
                    </div>
                </div>

                <!-- Product Value Badges -->
                <div
                    class="mt-6 grid grid-cols-3 gap-3 border-t border-border-gray/60 pt-6 text-center"
                >
                    <div
                        class="flex flex-col items-center rounded-xl border border-border-gray/60 bg-cloud-surface p-3"
                    >
                        <Sparkles class="mb-1 size-5 text-eco-green-dark" />
                        <span
                            class="text-[11px] leading-tight font-extrabold text-night-ink"
                            >100% Upcycle</span
                        >
                        <span class="text-[10px] text-charcoal"
                            >Dari limbah daur ulang</span
                        >
                    </div>
                    <div
                        class="flex flex-col items-center rounded-xl border border-border-gray/60 bg-cloud-surface p-3"
                    >
                        <ShieldCheck class="mb-1 size-5 text-sky-blue-dark" />
                        <span
                            class="text-[11px] leading-tight font-extrabold text-night-ink"
                            >Kualitas Terjamin</span
                        >
                        <span class="text-[10px] text-charcoal"
                            >Karya pengrajin lokal</span
                        >
                    </div>
                    <div
                        class="flex flex-col items-center rounded-xl border border-border-gray/60 bg-cloud-surface p-3"
                    >
                        <Truck class="mb-1 size-5 text-sunlight-gold" />
                        <span
                            class="text-[11px] leading-tight font-extrabold text-night-ink"
                            >Antar Cepat</span
                        >
                        <span class="text-[10px] text-charcoal"
                            >Oleh Driver ReValue</span
                        >
                    </div>
                </div>
            </div>

            <!-- Right Column: Product Info & Purchase Form -->
            <div class="flex flex-col gap-6 lg:col-span-6">
                <!-- Info Card -->
                <div
                    class="flex flex-col gap-5 rounded-[26px] border-2 border-b-[5px] border-border-gray bg-paper-white p-6 shadow-xs sm:p-8"
                >
                    <div>
                        <div
                            class="mb-3 inline-flex items-center gap-1.5 rounded-full border border-eco-green/40 bg-sprout-tint px-3 py-1 text-[12px] font-extrabold text-night-ink"
                        >
                            <Sparkles class="size-3.5 text-eco-green-dark" />
                            <span>Katalog Upcycle Store</span>
                        </div>
                        <h1
                            class="text-[24px] leading-tight font-black text-night-ink sm:text-[30px]"
                        >
                            {{ product.name }}
                        </h1>
                    </div>

                    <!-- Price Block -->
                    <div
                        class="flex flex-wrap items-baseline justify-between gap-3 rounded-2xl border-2 border-border-gray/80 bg-cloud-surface p-4"
                    >
                        <div>
                            <span
                                class="text-muted-text mb-0.5 block text-xs font-bold tracking-wider uppercase"
                                >Harga Produk</span
                            >
                            <span
                                class="text-[26px] font-black text-night-ink sm:text-[32px]"
                            >
                                {{ formatRupiah(product.price_rupiah) }}
                            </span>
                        </div>
                        <div class="flex items-center gap-2 self-center">
                            <span class="text-xs font-bold text-charcoal"
                                >atau setara</span
                            >
                            <span
                                class="rounded-full border border-eco-green-dark/40 bg-eco-green/15 px-3.5 py-1.5 text-sm font-black text-night-ink"
                            >
                                {{
                                    product.price_rupiah.toLocaleString('id-ID')
                                }}
                                Poin
                            </span>
                        </div>
                    </div>

                    <!-- Description -->
                    <div>
                        <h3
                            class="text-muted-text mb-2 text-xs font-bold tracking-wider uppercase"
                        >
                            Deskripsi Produk
                        </h3>
                        <p
                            class="text-[14px] leading-relaxed font-medium whitespace-pre-line text-charcoal"
                        >
                            {{
                                product.description ||
                                'Produk daur ulang kreatif ini dibuat secara teliti dari material berkualitas untuk mendukung gerakan sirkular ekonomi dan kelestarian lingkungan hidup.'
                            }}
                        </p>
                    </div>

                    <!-- Quantity Selector & Subtotal -->
                    <div class="space-y-4 border-t border-border-gray/60 pt-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-bold text-night-ink"
                                >Jumlah Pembelian</span
                            >
                            <div class="flex items-center gap-3">
                                <button
                                    type="button"
                                    @click="decrementQty"
                                    :disabled="quantity <= 1 || isOutOfStock"
                                    class="hover:bg-cloud-hover flex size-10 cursor-pointer items-center justify-center rounded-xl border-2 border-b-[3px] border-border-gray bg-cloud-surface font-bold text-night-ink transition-all active:translate-y-0.5 active:border-b-2 disabled:cursor-not-allowed disabled:opacity-40"
                                >
                                    <Minus class="size-4" />
                                </button>
                                <span
                                    class="w-10 text-center text-base font-black text-night-ink"
                                >
                                    {{ quantity }}
                                </span>
                                <button
                                    type="button"
                                    @click="incrementQty"
                                    :disabled="
                                        quantity >= product.stock ||
                                        isOutOfStock
                                    "
                                    class="hover:bg-cloud-hover flex size-10 cursor-pointer items-center justify-center rounded-xl border-2 border-b-[3px] border-border-gray bg-cloud-surface font-bold text-night-ink transition-all active:translate-y-0.5 active:border-b-2 disabled:cursor-not-allowed disabled:opacity-40"
                                >
                                    <Plus class="size-4" />
                                </button>
                            </div>
                        </div>

                        <!-- Subtotal Box -->
                        <div
                            class="flex items-center justify-between rounded-xl border border-border-gray/70 bg-cloud-surface p-3.5"
                        >
                            <span class="text-xs font-bold text-charcoal"
                                >Total Sementara:</span
                            >
                            <div class="text-right">
                                <span
                                    class="block text-base leading-none font-black text-night-ink"
                                >
                                    {{ formatRupiah(subtotalRupiah) }}
                                </span>
                                <span
                                    class="text-[11px] font-bold text-eco-green-dark"
                                >
                                    ({{
                                        subtotalPoints.toLocaleString('id-ID')
                                    }}
                                    Poin)
                                </span>
                            </div>
                        </div>

                        <!-- CTA Button -->
                        <div class="pt-2">
                            <Link
                                v-if="!isOutOfStock"
                                :href="`/customer/checkout?product_id=${product.id}&quantity=${quantity}`"
                                class="flex h-[52px] w-full cursor-pointer items-center justify-center gap-2 rounded-2xl border-2 border-b-[4px] border-eco-green-dark bg-eco-green text-[15px] font-black text-night-ink shadow-xs transition-all hover:brightness-105 active:translate-y-0.5 active:border-b-2 sm:text-[16px]"
                            >
                                <ShoppingBag class="size-5" />
                                <span>Beli Sekarang</span>
                            </Link>

                            <button
                                v-else
                                disabled
                                class="flex h-[52px] w-full cursor-not-allowed items-center justify-center gap-2 rounded-2xl border-2 border-border-gray bg-cloud-surface text-[15px] font-black text-charcoal/60"
                            >
                                Stok Produk Sedang Habis
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Guarantee Card -->
                <div
                    class="flex items-start gap-3.5 rounded-2xl border-2 border-eco-green/40 bg-sprout-tint/60 p-5"
                >
                    <CheckCircle2
                        class="mt-0.5 size-5 shrink-0 text-eco-green-dark"
                    />
                    <p
                        class="text-xs leading-relaxed font-medium text-night-ink"
                    >
                        <strong>Metode Pembayaran Fleksibel:</strong> Anda dapat
                        menggunakan saldo Poin ReValue Anda untuk mengurangi
                        atau melunasi penuh pesanan ini tanpa perlu transfer
                        uang di halaman checkout.
                    </p>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
