<script setup lang="ts">
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { X, RotateCcw, BookOpen } from 'lucide-vue-next';

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
    (e: 'close'): void;
}>();

const isRestoring = ref(false);

const confirmRestore = () => {
    if (!props.article) return;
    isRestoring.value = true;
    router.post(
        `/admin/articles/${props.article.id}/restore`,
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
                v-if="show && article"
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
                                Pulihkan artikel?
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
                        Artikel edukasi ini akan diaktifkan kembali. Customer
                        akan dapat membaca kembali artikel ini di halaman utama
                        dan platform ReValue.
                    </p>

                    <!-- Target Article Box -->
                    <div
                        class="mb-6 flex items-center gap-3.5 rounded-xl border border-border-gray/70 bg-cloud-surface p-3.5"
                    >
                        <div
                            class="flex size-12 shrink-0 items-center justify-center overflow-hidden rounded-lg border border-border-gray bg-paper-white"
                        >
                            <img
                                v-if="article.photo_url"
                                :src="article.photo_url"
                                :alt="article.title"
                                class="h-full w-full object-cover"
                            />
                            <BookOpen v-else class="size-5 text-charcoal/60" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <p
                                class="line-clamp-2 text-[14px] leading-snug font-extrabold text-night-ink"
                            >
                                {{ article.title }}
                            </p>
                            <p
                                class="mt-0.5 truncate text-[12px] leading-snug font-medium text-charcoal/80"
                            >
                                {{
                                    article.category?.name ||
                                    'Edukasi Lingkungan'
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
                            <span v-else>Pulihkan Artikel</span>
                        </button>
                    </div>
                </div>
            </div>
        </transition>
    </teleport>
</template>
