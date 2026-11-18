<script setup lang="ts">
import { computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { X, Coins } from 'lucide-vue-next';
import type { AchievementBadge } from './AchievementBadgeCard.vue';

const props = defineProps<{
    show: boolean;
    badge: AchievementBadge | null;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
}>();

const pillClasses = computed(() => {
    if (!props.badge) return 'bg-eco-green-dark text-paper-white';
    switch (props.badge.pill_color) {
        case 'coral':
            return 'bg-coral-danger text-paper-white';
        case 'green':
            return 'bg-eco-green-dark text-paper-white';
        case 'gold':
            return 'bg-sunlight-gold text-night-ink';
        case 'charcoal':
            return 'bg-charcoal text-paper-white';
        case 'purple':
            return 'bg-royal-purple-dark text-paper-white';
        default:
            return 'bg-eco-green-dark text-paper-white';
    }
});

const formattedReward = computed(() => {
    if (!props.badge) return '0';
    return Number(props.badge.reward_points).toLocaleString('id-ID');
});

const statusLabel = computed(() => {
    if (!props.badge) return '';
    if (props.badge.is_unlocked) {
        return 'Pencapaian Terbuka!';
    }
    return `${props.badge.current} dari ${props.badge.target} target`;
});

const navigateTo = (url: string) => {
    emit('close');
    router.visit(url);
};
</script>

<template>
    <div
        v-if="show && badge"
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
    >
        <!-- Backdrop -->
        <div
            class="fixed inset-0 bg-night-ink/50 backdrop-blur-xs transition-opacity"
            @click="emit('close')"
        />

        <!-- Modal Dialog Box -->
        <div
            class="relative z-10 flex w-full max-w-lg flex-col items-center rounded-[24px] border-2 border-b-[6px] border-border-gray bg-paper-white p-6 text-center shadow-xl sm:p-8"
        >
            <!-- Close Button -->
            <button
                type="button"
                @click="emit('close')"
                class="absolute top-4 right-4 flex size-9 cursor-pointer items-center justify-center rounded-xl border-2 border-border-gray bg-paper-white text-night-ink transition-colors hover:bg-cloud-surface"
            >
                <X class="size-5" />
            </button>

            <!-- Large Badge Crest Artwork (140x160px) -->
            <div
                class="relative mt-2 flex h-[160px] w-[140px] items-center justify-center"
            >
                <img
                    :src="badge.badge_image"
                    :alt="badge.title"
                    class="h-[140px] w-[120px] object-contain"
                />

                <!-- Milestone Pill Overlapping Crest Bottom -->
                <div
                    class="absolute bottom-1 left-1/2 flex h-[28px] -translate-x-1/2 items-center justify-center rounded-[99px] border-[2.5px] border-paper-white px-3.5 shadow-[0px_3px_0px_0px_rgba(0,4,55,0.28)]"
                    :class="pillClasses"
                >
                    <span class="text-[13px] leading-none font-extrabold">
                        {{ badge.milestone_pill }}
                    </span>
                </div>
            </div>

            <!-- Badge Title -->
            <h2
                class="mt-5 text-[24px] leading-tight font-extrabold text-night-ink sm:text-[28px]"
            >
                {{ badge.title }}
            </h2>

            <!-- Status Chip -->
            <div class="mt-3">
                <span
                    class="inline-flex items-center rounded-lg px-3 py-1 text-[12px] font-bold"
                    :class="
                        badge.is_unlocked
                            ? 'border border-eco-green/40 bg-sprout-tint text-night-ink'
                            : 'border border-border-gray bg-cloud-surface text-charcoal'
                    "
                >
                    {{ statusLabel }}
                </span>
            </div>

            <!-- Reward Points -->
            <div class="mt-3 flex items-center justify-center gap-1.5">
                <Coins class="size-5 text-sunlight-gold" />
                <span
                    class="text-[20px] font-extrabold text-night-ink sm:text-[22px]"
                >
                    {{ formattedReward }} poin
                </span>
            </div>

            <!-- Description / Detailed Guide -->
            <p
                class="mt-3 max-w-[420px] text-[14px] leading-relaxed font-medium text-charcoal"
            >
                {{ badge.detailed_note }}
            </p>

            <!-- Progress Bar in Modal -->
            <div class="mt-5 w-full max-w-[380px]">
                <div
                    class="flex justify-between text-[12px] font-bold text-charcoal"
                >
                    <span>Progres Saat Ini</span>
                    <span class="text-night-ink">{{
                        badge.progress_text
                    }}</span>
                </div>
                <div
                    class="mt-1.5 h-[8px] w-full overflow-hidden rounded-full bg-border-gray"
                >
                    <div
                        class="h-full rounded-full bg-eco-green transition-all duration-500"
                        :style="{
                            width: badge.is_unlocked
                                ? '100%'
                                : `${badge.progress_percentage}%`,
                        }"
                    />
                </div>
            </div>

            <!-- Tactile 3D Action Buttons (Figma 551:3470) -->
            <div
                class="mt-6 flex w-full flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-center"
            >
                <button
                    type="button"
                    @click="navigateTo('/customer/points')"
                    class="flex h-[44px] cursor-pointer items-center justify-center rounded-2xl border-2 border-b-[4px] border-border-gray bg-paper-white px-5 text-[14px] font-extrabold text-night-ink shadow-xs transition-all hover:bg-cloud-surface active:translate-y-0.5 active:border-b-2 sm:min-w-[170px]"
                >
                    Lihat riwayat poin
                </button>

                <button
                    type="button"
                    @click="navigateTo('/customer/pickups/create')"
                    class="flex h-[44px] cursor-pointer items-center justify-center rounded-2xl border-2 border-b-[4px] border-eco-green border-b-eco-green-dark bg-eco-green px-5 text-[14px] font-extrabold text-night-ink shadow-xs transition-all hover:brightness-105 active:translate-y-0.5 active:border-b-2 sm:min-w-[180px]"
                >
                    Pesan penjemputan
                </button>
            </div>
        </div>
    </div>
</template>
