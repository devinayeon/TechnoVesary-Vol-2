<script setup lang="ts">
import { computed } from 'vue';

export interface AchievementBadge {
    id: string;
    title: string;
    description: string;
    detailed_note: string;
    category: 'pickup' | 'weight' | 'transaction';
    target: number;
    current: number;
    reward_points: number;
    milestone_pill: string;
    pill_color: 'coral' | 'green' | 'gold' | 'charcoal' | 'purple';
    badge_image: string;
    locked_badge_image: string;
    is_unlocked: boolean;
    progress_percentage: number;
    progress_text: string;
    reward_text: string;
}

const props = defineProps<{
    badge: AchievementBadge;
}>();

defineEmits<{
    (e: 'select', badge: AchievementBadge): void;
}>();

const pillClasses = computed(() => {
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
</script>

<template>
    <div
        class="flex flex-col justify-between rounded-[20px] border-2 border-b-[4px] border-border-gray bg-paper-white p-5 transition-all hover:border-night-ink/30"
    >
        <!-- Top Row: Badge Artwork + Text Details -->
        <div>
            <div class="flex items-center gap-3.5">
                <!-- Crest Wrapper -->
                <div
                    class="relative flex h-[96px] w-[84px] shrink-0 items-center justify-center"
                >
                    <img
                        :src="
                            badge.is_unlocked
                                ? badge.badge_image
                                : badge.badge_image
                        "
                        :alt="badge.title"
                        class="h-[84px] w-[74px] object-contain transition-transform hover:scale-105"
                        :class="!badge.is_unlocked ? 'opacity-85' : ''"
                        loading="lazy"
                    />

                    <!-- Milestone Pill Overlapping Crest Bottom -->
                    <div
                        class="absolute bottom-0 left-1/2 flex h-[20px] -translate-x-1/2 items-center justify-center rounded-[99px] border-[1.8px] border-paper-white px-2.5 shadow-[0px_2px_0px_0px_rgba(0,4,55,0.28)]"
                        :class="pillClasses"
                    >
                        <span class="text-[10px] leading-none font-extrabold">
                            {{ badge.milestone_pill }}
                        </span>
                    </div>
                </div>

                <!-- Badge Text Info -->
                <div class="flex min-w-0 flex-1 flex-col gap-1">
                    <h3
                        class="text-[16px] leading-snug font-extrabold text-night-ink"
                    >
                        {{ badge.title }}
                    </h3>
                    <p class="text-[13px] font-medium text-charcoal">
                        {{ badge.progress_text }}
                    </p>
                    <p
                        class="text-[13px] font-bold"
                        :class="
                            badge.is_unlocked
                                ? 'text-eco-green-dark'
                                : 'text-night-ink'
                        "
                    >
                        {{ badge.reward_text }}
                    </p>
                </div>
            </div>

            <!-- Tactile Progress Bar -->
            <div
                class="mt-4 h-[6px] w-full overflow-hidden rounded-full bg-border-gray"
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

        <!-- Bottom Action CTA -->
        <div
            class="mt-4 flex items-center justify-start border-t border-border-gray/50 pt-3"
        >
            <button
                type="button"
                @click="$emit('select', badge)"
                class="flex cursor-pointer items-center gap-1.5 text-[13px] font-extrabold text-night-ink transition-colors hover:text-eco-green-dark"
            >
                <span>Lihat pencapaian</span>
                <span aria-hidden="true">&rarr;</span>
            </button>
        </div>
    </div>
</template>
