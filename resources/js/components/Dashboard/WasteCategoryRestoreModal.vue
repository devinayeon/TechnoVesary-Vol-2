<script setup lang="ts">
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { X, RotateCcw, Coins } from 'lucide-vue-next';

export interface WasteCategoryRestoreItem {
    id: number;
    name: string;
    points_per_kg: number;
}

const props = defineProps<{
    show: boolean;
    category: WasteCategoryRestoreItem | null;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
}>();

const isRestoring = ref(false);

const confirmRestore = () => {
    if (!props.category) return;
    isRestoring.value = true;
    router.post(
        `/admin/waste-categories/${props.category.id}/restore`,
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                isRestoring.value = false;
                emit('close');
            },
            onError: () => {
                isRestoring.value = false;
            },
        },
    );
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
                v-if="show && category"
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
                                class="flex size-10 shrink-0 items-center justify-center rounded-xl border border-eco-green/30 bg-sprout-tint text-eco-green-dark"
                            >
                                <RotateCcw class="size-5" />
                            </div>
                            <h3
                                class="text-[20px] leading-tight font-extrabold text-night-ink sm:text-[22px]"
                            >
                                Pulihkan kategori?
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
                        Kategori sampah ini akan diaktifkan kembali. Customer
                        akan dapat memilih kategori ini kembali saat menyetorkan
                        sampah.
                    </p>

                    <!-- Target Category Box -->
                    <div
                        class="mb-6 flex items-center justify-between gap-4 rounded-xl border border-border-gray/70 bg-cloud-surface p-4"
                    >
                        <div class="min-w-0">
                            <p
                                class="truncate text-[15px] leading-snug font-extrabold text-night-ink"
                            >
                                {{ category.name }}
                            </p>
                            <p
                                class="mt-0.5 text-[13px] leading-snug font-medium text-charcoal"
                            >
                                Kategori Sampah
                            </p>
                        </div>
                        <div
                            class="flex shrink-0 items-center gap-1.5 rounded-lg border border-sunlight-gold/30 bg-gold-tint px-3 py-1 text-[13px] font-extrabold text-night-ink"
                        >
                            <Coins class="size-3.5 text-sunlight-gold" />
                            <span
                                >{{
                                    Number(
                                        category.points_per_kg,
                                    ).toLocaleString('id-ID')
                                }}
                                Pts/kg</span
                            >
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end gap-3">
                        <button
                            type="button"
                            class="h-[44px] cursor-pointer rounded-xl border-2 border-b-[4px] border-border-gray bg-paper-white px-5 text-[14px] font-extrabold text-night-ink transition-all hover:bg-cloud-surface active:translate-y-0.5 active:border-b-2"
                            @click="emit('close')"
                            :disabled="isRestoring"
                        >
                            Batal
                        </button>
                        <button
                            type="button"
                            class="flex h-[44px] cursor-pointer items-center justify-center gap-2 rounded-xl border-2 border-b-[4px] border-eco-green-dark bg-eco-green px-5 text-[14px] font-extrabold text-white transition-all hover:brightness-105 active:translate-y-0.5 active:border-b-2 disabled:cursor-not-allowed disabled:opacity-60"
                            @click="confirmRestore"
                            :disabled="isRestoring"
                        >
                            <span v-if="isRestoring">Memulihkan...</span>
                            <span v-else>Pulihkan Kategori</span>
                        </button>
                    </div>
                </div>
            </div>
        </transition>
    </teleport>
</template>
