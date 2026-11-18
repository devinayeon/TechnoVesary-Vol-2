<script setup lang="ts">
import { ref } from "vue";
import { router } from "@inertiajs/vue3";
import { X, RotateCcw, BookOpen } from "lucide-vue-next";

export interface ArticleRestoreItem {
    id: number;
    title: string;
    photo_url?: string | null;
    category?: { id: number; name: string } | null;
}

const props = defineProps<{
    show: boolean;
    article: ArticleRestoreItem | null;
}>();

const emit = defineEmits<{
    (e: "close"): void;
}>();

const isRestoring = ref(false);

const confirmRestore = () => {
    if (!props.article) return;
    isRestoring.value = true;
    router.post(`/admin/articles/${props.article.id}/restore`, {}, {
        preserveScroll: true,
        onSuccess: () => {
            isRestoring.value = false;
            emit("close");
        },
        onError: () => {
            isRestoring.value = false;
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
                v-if="show && article"
                class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-night-ink/50 backdrop-blur-xs select-none"
                @click.self="emit('close')"
            >
                <div
                    class="w-full max-w-[480px] bg-paper-white border-2 border-border-gray rounded-[24px] p-6 sm:p-8 shadow-xl relative animate-in fade-in zoom-in-95 duration-150"
                >
                    <!-- Header -->
                    <div class="flex items-start justify-between gap-4 mb-3">
                        <div class="flex items-center gap-3">
                            <div
                                class="size-10 rounded-xl bg-sprout-tint text-eco-green-dark flex items-center justify-center shrink-0 border border-eco-green/30"
                            >
                                <RotateCcw class="size-5" />
                            </div>
                            <h3
                                class="text-[20px] sm:text-[22px] font-extrabold text-night-ink leading-tight"
                            >
                                Pulihkan artikel?
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
                        Artikel edukasi ini akan diaktifkan kembali. Nasabah akan dapat membaca kembali artikel ini di halaman utama dan platform ReValue.
                    </p>

                    <!-- Target Article Box -->
                    <div
                        class="bg-cloud-surface border border-border-gray/70 rounded-xl p-3.5 mb-6 flex items-center gap-3.5"
                    >
                        <div
                            class="size-12 rounded-lg bg-paper-white border border-border-gray flex items-center justify-center overflow-hidden shrink-0"
                        >
                            <img
                                v-if="article.photo_url"
                                :src="article.photo_url"
                                :alt="article.title"
                                class="w-full h-full object-cover"
                            />
                            <BookOpen v-else class="size-5 text-charcoal/60" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <p
                                class="font-extrabold text-[14px] text-night-ink leading-snug line-clamp-2"
                            >
                                {{ article.title }}
                            </p>
                            <p
                                class="font-medium text-[12px] text-charcoal/80 leading-snug mt-0.5 truncate"
                            >
                                {{ article.category?.name || "Edukasi Lingkungan" }}
                            </p>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end gap-3">
                        <button
                            type="button"
                            class="px-5 h-[44px] rounded-xl border-2 border-b-[4px] border-border-gray bg-paper-white font-extrabold text-[14px] text-night-ink hover:bg-cloud-surface active:translate-y-0.5 active:border-b-2 transition-all cursor-pointer"
                            @click="emit('close')"
                            :disabled="isRestoring"
                        >
                            Batal
                        </button>
                        <button
                            type="button"
                            class="px-5 h-[44px] rounded-xl border-2 border-b-[4px] border-eco-green-dark bg-eco-green font-extrabold text-[14px] text-white hover:brightness-105 active:translate-y-0.5 active:border-b-2 transition-all cursor-pointer disabled:opacity-60 disabled:cursor-not-allowed flex items-center justify-center gap-2"
                            @click="confirmRestore"
                            :disabled="isRestoring"
                        >
                            <span v-if="isRestoring">Memulihkan...</span>
                            <span v-else>Pulihkan Artikel</span>
                        </button>
                    </div>
                </div>
            </div>
        </transition>
    </teleport>
</template>
