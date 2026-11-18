<script setup lang="ts">
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { X, AlertTriangle, ShoppingBag } from 'lucide-vue-next';

export interface ProductDeleteItem {
    id: number;
    name: string;
    photo_url?: string | null;
    price_rupiah: number;
}

const props = defineProps<{
    show: boolean;
    product: ProductDeleteItem | null;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
}>();

const isDeleting = ref(false);

const confirmDelete = () => {
    if (!props.product) return;
    isDeleting.value = true;
    router.delete(`/admin/products/${props.product.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            isDeleting.value = false;
            emit('close');
        },
        onError: () => {
            isDeleting.value = false;
        },
    });
};
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
                v-if="show && product"
                class="fixed inset-0 z-50 flex items-center justify-center bg-night-ink/50 p-4 backdrop-blur-xs select-none"
                @click.self="emit('close')"
            >
                <div
                    class="animate-in fade-in zoom-in-95 relative w-full max-w-[480px] rounded-[24px] border-2 border-border-gray bg-paper-white p-6 shadow-xl duration-150 sm:p-8"
                >
                    <!-- Header -->
                    <div class="mb-3 flex items-start justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <div
                                class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-red-50 text-red-600"
                            >
                                <AlertTriangle class="size-5" />
                            </div>
                            <h3
                                class="text-[20px] leading-tight font-extrabold text-night-ink sm:text-[22px]"
                            >
                                Nonaktifkan produk?
                            </h3>
                        </div>
                        <button
                            type="button"
                            class="cursor-pointer rounded-lg p-1.5 text-charcoal transition-colors hover:bg-cloud-surface hover:text-night-ink"
                            @click="emit('close')"
                            aria-label="Tutup"
                        >
                            <X class="size-5" />
                        </button>
                    </div>

                    <!-- Description -->
                    <p
                        class="mb-5 text-[14px] leading-relaxed font-normal text-charcoal"
                    >
                        Produk ini akan dinonaktifkan secara aman (soft delete).
                        Produk tidak akan lagi tampil di Upcycle Store, namun
                        data dan stoknya tetap tersimpan serta dapat dipulihkan
                        kapan saja.
                    </p>

                    <!-- Target Product Box -->
                    <div
                        class="mb-6 flex items-center gap-3.5 rounded-xl border border-border-gray/70 bg-cloud-surface p-3.5"
                    >
                        <div
                            class="flex size-12 shrink-0 items-center justify-center overflow-hidden rounded-lg border border-border-gray bg-paper-white"
                        >
                            <img
                                v-if="product.photo_url"
                                :src="product.photo_url"
                                :alt="product.name"
                                class="h-full w-full object-cover"
                            />
                            <ShoppingBag
                                v-else
                                class="size-5 text-charcoal/60"
                            />
                        </div>
                        <div class="min-w-0 flex-1">
                            <p
                                class="line-clamp-2 text-[14px] leading-snug font-extrabold text-night-ink"
                            >
                                {{ product.name }}
                            </p>
                            <p
                                class="mt-0.5 truncate text-[12px] leading-snug font-medium text-charcoal/80"
                            >
                                Rp
                                {{
                                    product.price_rupiah.toLocaleString('id-ID')
                                }}
                            </p>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end gap-3">
                        <button
                            type="button"
                            class="h-[44px] cursor-pointer rounded-xl border-2 border-b-[4px] border-border-gray bg-paper-white px-5 text-[14px] font-extrabold text-night-ink transition-all hover:bg-cloud-surface active:translate-y-0.5 active:border-b-2"
                            @click="emit('close')"
                            :disabled="isDeleting"
                        >
                            Batal
                        </button>
                        <button
                            type="button"
                            class="flex h-[44px] cursor-pointer items-center justify-center gap-2 rounded-xl border-2 border-b-[4px] border-red-700 bg-red-600 px-5 text-[14px] font-extrabold text-white transition-all hover:brightness-105 active:translate-y-0.5 active:border-b-2 disabled:cursor-not-allowed disabled:opacity-60"
                            @click="confirmDelete"
                            :disabled="isDeleting"
                        >
                            <span v-if="isDeleting">Menonaktifkan...</span>
                            <span v-else>Nonaktifkan</span>
                        </button>
                    </div>
                </div>
            </div>
        </transition>
    </teleport>
</template>
