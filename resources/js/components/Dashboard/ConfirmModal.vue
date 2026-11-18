<script setup lang="ts">
import { AlertTriangle, X } from 'lucide-vue-next';

withDefaults(
    defineProps<{
        show: boolean;
        title: string;
        description?: string;
        confirmLabel?: string;
        cancelLabel?: string;
        tone?: 'danger' | 'primary';
        processing?: boolean;
    }>(),
    {
        description: undefined,
        confirmLabel: 'Ya, Lanjutkan',
        cancelLabel: 'Batal',
        tone: 'primary',
        processing: false,
    },
);

const emit = defineEmits<{
    (e: 'confirm'): void;
    (e: 'close'): void;
}>();
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
                v-if="show"
                class="fixed inset-0 z-50 flex items-center justify-center p-4"
                @click.self="emit('close')"
            >
                <div
                    class="absolute inset-0 bg-night-ink/40 backdrop-blur-sm"
                    @click="emit('close')"
                />

                <div
                    class="relative z-10 w-full max-w-md rounded-[24px] border-2 border-b-[6px] border-border-gray bg-paper-white p-6 shadow-2xl"
                >
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-start gap-3">
                            <div
                                class="flex size-11 shrink-0 items-center justify-center rounded-2xl border-2"
                                :class="
                                    tone === 'danger'
                                        ? 'border-red-200 bg-red-50 text-red-600'
                                        : 'border-eco-green/40 bg-sprout-tint text-eco-green-dark'
                                "
                            >
                                <AlertTriangle class="size-5" />
                            </div>
                            <div class="min-w-0">
                                <h2
                                    class="text-[18px] leading-snug font-black text-night-ink"
                                >
                                    {{ title }}
                                </h2>
                                <p
                                    v-if="description"
                                    class="mt-1 text-[13px] leading-relaxed font-medium text-charcoal"
                                >
                                    {{ description }}
                                </p>
                            </div>
                        </div>

                        <button
                            type="button"
                            class="shrink-0 cursor-pointer rounded-lg p-1 text-charcoal transition-colors hover:bg-cloud-surface hover:text-night-ink"
                            aria-label="Tutup"
                            @click="emit('close')"
                        >
                            <X class="size-4" />
                        </button>
                    </div>

                    <div
                        class="mt-6 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end"
                    >
                        <button
                            type="button"
                            class="h-[44px] cursor-pointer rounded-xl border-2 border-b-[4px] border-border-gray bg-paper-white px-5 text-[13px] font-extrabold text-night-ink transition-all hover:bg-cloud-surface active:translate-y-0.5 active:border-b-2"
                            @click="emit('close')"
                        >
                            {{ cancelLabel }}
                        </button>
                        <button
                            type="button"
                            class="h-[44px] cursor-pointer rounded-xl border-2 border-b-[4px] px-5 text-[13px] font-extrabold transition-all hover:brightness-105 active:translate-y-0.5 active:border-b-2 disabled:cursor-not-allowed disabled:opacity-60"
                            :class="
                                tone === 'danger'
                                    ? 'border-red-700 bg-red-500 text-white'
                                    : 'border-eco-green-dark bg-eco-green text-night-ink'
                            "
                            :disabled="processing"
                            @click="emit('confirm')"
                        >
                            {{ processing ? 'Memproses...' : confirmLabel }}
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
