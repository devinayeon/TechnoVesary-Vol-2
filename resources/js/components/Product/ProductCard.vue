<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { Package, ArrowRight, ShoppingBag } from 'lucide-vue-next';

export interface ProductItem {
    id: number;
    name: string;
    description: string | null;
    price_rupiah: number;
    price_points: number;
    photo: string | null;
    photo_url?: string | null;
    stock: number;
}

const props = defineProps<{
    product: ProductItem;
    href?: string;
}>();

const targetHref = computed(
    () => props.href ?? `/customer/products/${props.product.id}`,
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
    <div
        class="group flex flex-col justify-between rounded-[22px] border-2 border-b-[5px] border-border-gray bg-paper-white p-5 shadow-xs transition-all duration-300 ease-out hover:-translate-y-1 hover:border-night-ink/30 hover:border-b-night-ink/50 hover:shadow-[0_12px_24px_-6px_rgba(0,4,55,0.12)]"
    >
        <!-- Top: Photo & Stock Badge -->
        <div>
            <div
                class="relative mb-4 flex h-[170px] items-center justify-center overflow-hidden rounded-[16px] border border-border-gray/70 bg-cloud-surface p-2 sm:h-[190px]"
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
                    class="h-full w-full rounded-xl object-cover transition-transform duration-300 ease-out group-hover:scale-105"
                />
                <div
                    v-else
                    class="flex flex-col items-center justify-center text-charcoal/50"
                >
                    <Package class="mb-1 size-12" />
                    <span class="text-xs font-semibold">Produk Daur Ulang</span>
                </div>

                <!-- Stock Badge Overlay -->
                <div class="absolute top-3 right-3">
                    <span
                        v-if="product.stock > 0"
                        class="rounded-full border border-border-gray bg-paper-white/95 px-2.5 py-1 text-[11px] font-bold text-night-ink shadow-xs backdrop-blur-xs"
                    >
                        Sisa {{ product.stock }} item
                    </span>
                    <span
                        v-else
                        class="rounded-full border border-red-200 bg-red-50/95 px-2.5 py-1 text-[11px] font-bold text-red-600 shadow-xs backdrop-blur-xs"
                    >
                        Stok Habis
                    </span>
                </div>
            </div>

            <!-- Title & Description -->
            <h3
                class="mb-1.5 line-clamp-1 text-[17px] leading-snug font-extrabold text-night-ink transition-colors group-hover:text-eco-green-dark sm:text-[18px]"
                :title="product.name"
            >
                {{ product.name }}
            </h3>
            <p
                class="mb-4 line-clamp-2 text-[13px] leading-relaxed font-medium text-charcoal"
            >
                {{
                    product.description ||
                    'Karya upcycle berkualitas ramah lingkungan hasil kreasi daur ulang.'
                }}
            </p>
        </div>

        <!-- Bottom: Pricing & CTA -->
        <div class="mt-2 border-t border-border-gray/50 pt-3">
            <div class="mb-3.5 flex items-baseline justify-between gap-2">
                <div>
                    <span
                        class="text-[17px] font-black text-night-ink sm:text-[19px]"
                    >
                        {{ formatRupiah(product.price_rupiah) }}
                    </span>
                </div>
                <span
                    class="shrink-0 rounded-full border border-eco-green/30 bg-sprout-tint px-2.5 py-0.5 text-[11px] font-bold text-eco-green-dark"
                >
                    {{ product.price_rupiah.toLocaleString('id-ID') }} Poin
                </span>
            </div>

            <Link
                :href="targetHref"
                class="flex h-[44px] w-full cursor-pointer items-center justify-center gap-2 rounded-xl border-2 border-b-[4px] border-eco-green-dark bg-eco-green text-[13px] font-extrabold text-night-ink shadow-xs transition-all hover:brightness-105 active:translate-y-0.5 active:border-b-2 sm:text-[14px]"
            >
                <ShoppingBag class="size-4" />
                <span>Lihat Detail & Beli</span>
                <ArrowRight
                    class="size-3.5 transition-transform group-hover:translate-x-1"
                />
            </Link>
        </div>
    </div>
</template>
