<script setup lang="ts">
import { ref } from "vue";
import { router } from "@inertiajs/vue3";
import { X, AlertTriangle, Coins } from "lucide-vue-next";

export interface WasteCategoryDeleteItem {
    id: number;
    name: string;
    points_per_kg: number;
}

const props = defineProps<{
    show: boolean;
    category: WasteCategoryDeleteItem | null;
}>();

const emit = defineEmits<{
    (e: "close"): void;
}>();

const isDeleting = ref(false);

const confirmDelete = () => {
    if (!props.category) return;
    isDeleting.value = true;
    router.delete(`/admin/waste-categories/${props.category.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            isDeleting.value = false;
            emit("close");
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
                v-if="show && category"
                class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-night-ink/50 backdrop-blur-xs select-none"
                @click.self="emit('close')"
            >
                <div
                    class="w-full max-w-[480px] bg-paper-white border-2 border-border-gray rounded-[24px] p-6 sm:p-8 shadow-xl relative animate-in fade-in zoom-in-95 duration-150"
                >
                    <!-- Header -->
                    <div class="flex items-start justify-between gap-4 mb-3">
                        <div class="flex items-center gap-3">
                            <div class="size-10 rounded-xl bg-red-50 text-red-600 flex items-center justify-center shrink-0">
                                <AlertTriangle class="size-5" />
                            </div>
                            <h3 class="text-[20px] sm:text-[22px] font-extrabold text-night-ink leading-tight">
                                Nonaktifkan kategori?
                            </h3>
                        </div>
                        <button
                            type="button"
                            class="p-1.5 rounded-lg text-charcoal hover:bg-cloud-surface hover:text-night-ink transition-colors cursor-pointer"
                            @click="emit('close')"
                            aria-label="Tutup"
                        >
                            <X class="size-5" />
                        </button>
                    </div>

                    <!-- Description -->
                    <p class="text-[14px] font-normal text-charcoal leading-relaxed mb-5">
                        Kategori sampah ini akan dinonaktifkan secara aman (soft delete). Nasabah tidak dapat lagi memilih kategori ini untuk setoran baru, namun riwayat setoran sebelumnya tetap aman dan data dapat dipulihkan kapan saja.
                    </p>

                    <!-- Target Category Box -->
                    <div class="bg-cloud-surface border border-border-gray/70 rounded-xl p-4 mb-6 flex items-center justify-between gap-4">
                        <div class="min-w-0">
                            <p class="font-extrabold text-[15px] text-night-ink leading-snug truncate">
                                {{ category.name }}
                            </p>
                            <p class="font-medium text-[13px] text-charcoal leading-snug mt-0.5">
                                Kategori Sampah
                            </p>
                        </div>
                        <div class="flex items-center gap-1.5 px-3 py-1 rounded-lg bg-gold-tint border border-sunlight-gold/30 text-night-ink font-extrabold text-[13px] shrink-0">
                            <Coins class="size-3.5 text-sunlight-gold" />
                            <span>{{ Number(category.points_per_kg).toLocaleString("id-ID") }} Pts/kg</span>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end gap-3">
                        <button
                            type="button"
                            class="px-5 h-[44px] rounded-xl border-2 border-b-[4px] border-border-gray bg-paper-white font-extrabold text-[14px] text-night-ink hover:bg-cloud-surface active:translate-y-0.5 active:border-b-2 transition-all cursor-pointer"
                            @click="emit('close')"
                            :disabled="isDeleting"
                        >
                            Batal
                        </button>
                        <button
                            type="button"
                            class="px-5 h-[44px] rounded-xl border-2 border-b-[4px] border-red-700 bg-red-600 font-extrabold text-[14px] text-white hover:brightness-105 active:translate-y-0.5 active:border-b-2 transition-all cursor-pointer disabled:opacity-60 disabled:cursor-not-allowed flex items-center justify-center gap-2"
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
