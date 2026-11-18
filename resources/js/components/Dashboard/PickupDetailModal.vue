<script setup lang="ts">
import { X } from 'lucide-vue-next';
import PickupDetailCard from '@/components/Pickup/PickupDetailCard.vue';
import type { PickupSummary } from '@/types/pickup';

withDefaults(
    defineProps<{
        show: boolean;
        pickup: PickupSummary | null;
        basemapApiKey?: string;
    }>(),
    { basemapApiKey: undefined },
);

const emit = defineEmits<{
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
                v-if="show && pickup"
                class="fixed inset-0 z-50 flex items-center justify-center p-4"
                @click.self="emit('close')"
            >
                <div
                    class="absolute inset-0 bg-night-ink/40 backdrop-blur-sm"
                    @click="emit('close')"
                />

                <div
                    class="relative z-10 max-h-[92vh] w-full max-w-5xl overflow-y-auto rounded-[24px] border-2 border-b-[6px] border-border-gray bg-cloud-surface p-4 shadow-2xl sm:p-6"
                >
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <h2 class="text-[16px] font-black text-night-ink">
                            Pratinjau Penjemputan #{{ pickup.id }}
                        </h2>
                        <button
                            type="button"
                            class="shrink-0 cursor-pointer rounded-xl border-2 border-b-[3px] border-border-gray bg-paper-white p-2 text-charcoal transition-all hover:bg-cloud-surface hover:text-night-ink active:translate-y-0.5 active:border-b-2"
                            aria-label="Tutup detail"
                            @click="emit('close')"
                        >
                            <X class="size-4" />
                        </button>
                    </div>

                    <PickupDetailCard
                        :pickup="pickup"
                        :basemap-api-key="basemapApiKey"
                        show-customer
                        show-driver
                    />
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
