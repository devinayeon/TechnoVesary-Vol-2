<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ChevronLeft, ChevronRight } from 'lucide-vue-next';

export interface PaginationLinks {
    url: string | null;
    label: string;
    active: boolean;
}

export interface PaginationMeta {
    from: number | null;
    to: number | null;
    total: number;
    links: PaginationLinks[];
    prev_page_url: string | null;
    next_page_url: string | null;
}

withDefaults(
    defineProps<{
        pagination: PaginationMeta;
        itemLabel?: string;
    }>(),
    {
        itemLabel: 'data',
    },
);
</script>

<template>
    <div
        v-if="pagination.total > 0"
        class="flex flex-col items-center justify-between gap-4 border-t border-border-gray bg-paper-white px-4 py-4 select-none sm:flex-row sm:px-6"
    >
        <p
            class="text-center text-[13px] font-normal text-charcoal sm:text-left"
        >
            Menampilkan
            <span class="font-bold text-night-ink"
                >{{ pagination.from || 0 }}–{{ pagination.to || 0 }}</span
            >
            dari
            <span class="font-bold text-night-ink">{{ pagination.total }}</span>
            {{ itemLabel }}
        </p>

        <div
            class="flex flex-wrap items-center justify-center gap-1 sm:gap-1.5"
        >
            <!-- Prev Button -->
            <Link
                v-if="pagination.prev_page_url"
                :href="pagination.prev_page_url"
                preserve-scroll
                class="inline-flex h-[36px] cursor-pointer items-center gap-1 rounded-xl border border-border-gray px-3 text-[13px] font-extrabold text-night-ink transition-colors hover:bg-cloud-surface"
            >
                <ChevronLeft class="size-4" />
                <span>Sebelumnya</span>
            </Link>
            <span
                v-else
                class="inline-flex h-[36px] cursor-not-allowed items-center gap-1 rounded-xl border border-border-gray/50 px-3 text-[13px] font-semibold text-charcoal/40 opacity-50"
            >
                <ChevronLeft class="size-4" />
                <span>Sebelumnya</span>
            </span>

            <!-- Numeric Page Numbers -->
            <template v-for="(link, i) in pagination.links" :key="i">
                <Link
                    v-if="
                        link.url &&
                        !link.label.includes('Previous') &&
                        !link.label.includes('Next') &&
                        !link.label.includes('&laquo;') &&
                        !link.label.includes('&raquo;')
                    "
                    :href="link.url"
                    preserve-scroll
                    :class="[
                        'flex size-9 cursor-pointer items-center justify-center rounded-xl text-[13px] font-extrabold transition-all',
                        link.active
                            ? 'border-2 border-eco-green/40 bg-sprout-tint text-night-ink shadow-2xs'
                            : 'border border-border-gray text-charcoal hover:bg-cloud-surface hover:text-night-ink',
                    ]"
                    v-html="link.label"
                />
            </template>

            <!-- Next Button -->
            <Link
                v-if="pagination.next_page_url"
                :href="pagination.next_page_url"
                preserve-scroll
                class="inline-flex h-[36px] cursor-pointer items-center gap-1 rounded-xl border border-border-gray px-3 text-[13px] font-extrabold text-night-ink transition-colors hover:bg-cloud-surface"
            >
                <span>Berikutnya</span>
                <ChevronRight class="size-4" />
            </Link>
            <span
                v-else
                class="inline-flex h-[36px] cursor-not-allowed items-center gap-1 rounded-xl border border-border-gray/50 px-3 text-[13px] font-semibold text-charcoal/40 opacity-50"
            >
                <span>Berikutnya</span>
                <ChevronRight class="size-4" />
            </span>
        </div>
    </div>
</template>
