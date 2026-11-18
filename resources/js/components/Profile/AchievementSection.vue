<script setup lang="ts">
import { ref } from 'vue';
import { Award, CheckCircle2, Flame, Sparkles } from 'lucide-vue-next';
import AchievementBadgeCard, {
    type AchievementBadge,
} from './AchievementBadgeCard.vue';
import AchievementDetailModal from './AchievementDetailModal.vue';

export interface CustomerAchievementsData {
    summary: {
        total_unlocked: number;
        total_badges: number;
        unlocked_text: string;
        unlocked_subtext: string;
        completed_pickups: number;
        next_pickup_target_text: string;
        total_weight_kg: string;
        total_weight_raw: number;
        next_weight_target_text: string;
    };
    earned_badges: AchievementBadge[];
    in_progress_badges: AchievementBadge[];
    all_badges: AchievementBadge[];
}

const props = defineProps<{
    achievements?: CustomerAchievementsData | null;
}>();

const selectedBadge = ref<AchievementBadge | null>(null);

const openDetail = (badge: AchievementBadge) => {
    selectedBadge.value = badge;
};
</script>

<template>
    <div class="space-y-8">
        <!-- 1. Header Section -->
        <div class="flex flex-col gap-1">
            <h2
                class="text-[24px] leading-tight font-extrabold tracking-tight text-night-ink sm:text-[28px]"
            >
                Pencapaianmu
            </h2>
            <p class="text-[14px] font-medium text-charcoal sm:text-[15px]">
                Setiap lencana mencatat kebiasaan baik yang sudah kamu lakukan.
            </p>
        </div>

        <!-- Fallback if no achievements data provided (e.g. non-customer) -->
        <div
            v-if="!achievements"
            class="flex flex-col items-center justify-center rounded-[20px] border-2 border-border-gray bg-paper-white p-8 text-center"
        >
            <Award class="size-10 text-charcoal/40" />
            <p class="mt-2 text-sm font-bold text-night-ink">
                Pencapaian khusus untuk akun nasabah (Customer).
            </p>
        </div>

        <template v-else>
            <!-- 2. Summary Metric Cards Row (Figma 551:3131) -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <!-- Card 1: Sudah terbuka -->
                <div
                    class="rounded-[20px] border-2 border-b-[4px] border-border-gray bg-paper-white p-5 sm:p-6"
                >
                    <div class="flex items-center justify-between">
                        <span class="text-[13px] font-bold text-charcoal">
                            Sudah terbuka
                        </span>
                        <Sparkles class="size-4 text-sunlight-gold" />
                    </div>
                    <p
                        class="mt-2 text-[26px] leading-none font-extrabold text-night-ink sm:text-[28px]"
                    >
                        {{ achievements.summary.unlocked_text }}
                    </p>
                    <p class="mt-2 text-[12px] font-medium text-charcoal">
                        {{ achievements.summary.unlocked_subtext }}
                    </p>
                </div>

                <!-- Card 2: Penjemputan selesai -->
                <div
                    class="rounded-[20px] border-2 border-b-[4px] border-border-gray bg-paper-white p-5 sm:p-6"
                >
                    <div class="flex items-center justify-between">
                        <span class="text-[13px] font-bold text-charcoal">
                            Penjemputan selesai
                        </span>
                        <CheckCircle2 class="size-4 text-eco-green-dark" />
                    </div>
                    <p
                        class="mt-2 text-[26px] leading-none font-extrabold text-night-ink sm:text-[28px]"
                    >
                        {{ achievements.summary.completed_pickups }}
                    </p>
                    <p class="mt-2 text-[12px] font-medium text-charcoal">
                        {{ achievements.summary.next_pickup_target_text }}
                    </p>
                </div>

                <!-- Card 3: Berat terkumpul -->
                <div
                    class="rounded-[20px] border-2 border-b-[4px] border-border-gray bg-paper-white p-5 sm:p-6"
                >
                    <div class="flex items-center justify-between">
                        <span class="text-[13px] font-bold text-charcoal">
                            Berat terkumpul
                        </span>
                        <Flame class="text-coral-danger size-4" />
                    </div>
                    <p
                        class="mt-2 text-[26px] leading-none font-extrabold text-night-ink sm:text-[28px]"
                    >
                        {{ achievements.summary.total_weight_kg }}
                    </p>
                    <p class="mt-2 text-[12px] font-medium text-charcoal">
                        {{ achievements.summary.next_weight_target_text }}
                    </p>
                </div>
            </div>

            <!-- 3. Section: Sudah Terbuka (Earned Badges) -->
            <div class="space-y-4">
                <div class="flex items-center gap-2">
                    <span
                        class="flex size-6 items-center justify-center rounded-full bg-sprout-tint text-[12px] font-extrabold text-eco-green-dark"
                    >
                        ✓
                    </span>
                    <h3 class="text-[18px] font-extrabold text-night-ink">
                        Sudah terbuka ({{ achievements.earned_badges.length }})
                    </h3>
                </div>

                <div
                    v-if="achievements.earned_badges.length > 0"
                    class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3"
                >
                    <AchievementBadgeCard
                        v-for="badge in achievements.earned_badges"
                        :key="badge.id"
                        :badge="badge"
                        @select="openDetail"
                    />
                </div>

                <div
                    v-else
                    class="rounded-[20px] border-2 border-dashed border-border-gray bg-cloud-surface/50 p-6 text-center"
                >
                    <p class="text-[14px] font-semibold text-charcoal">
                        Belum ada lencana yang terbuka. Mulai dengan memesan
                        penjemputan sampah terpilah pertamamu!
                    </p>
                </div>
            </div>

            <!-- 4. Section: Sedang Berjalan (In Progress Badges) -->
            <div
                v-if="achievements.in_progress_badges.length > 0"
                class="space-y-4 pt-2"
            >
                <div class="flex items-center gap-2">
                    <span
                        class="flex size-6 items-center justify-center rounded-full bg-cloud-surface text-[12px] font-extrabold text-charcoal"
                    >
                        ⏳
                    </span>
                    <h3 class="text-[18px] font-extrabold text-night-ink">
                        Sedang berjalan ({{
                            achievements.in_progress_badges.length
                        }})
                    </h3>
                </div>

                <div
                    class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3"
                >
                    <AchievementBadgeCard
                        v-for="badge in achievements.in_progress_badges"
                        :key="badge.id"
                        :badge="badge"
                        @select="openDetail"
                    />
                </div>
            </div>

            <!-- 5. Detail Modal Dialog -->
            <AchievementDetailModal
                :show="selectedBadge !== null"
                :badge="selectedBadge"
                @close="selectedBadge = null"
            />
        </template>
    </div>
</template>
