<script setup lang="ts">
import { Link } from "@inertiajs/vue3";
import { ChevronLeft, ChevronRight } from "lucide-vue-next";

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
        itemLabel: "data",
    }
);
</script>

<template>
    <div
        v-if="pagination.total > 0"
        class="flex flex-col sm:flex-row items-center justify-between gap-4 px-4 sm:px-6 py-4 border-t border-border-gray bg-paper-white select-none"
    >
        <p class="text-[13px] font-normal text-charcoal text-center sm:text-left">
            Menampilkan
            <span class="font-bold text-night-ink">{{ pagination.from || 0 }}–{{ pagination.to || 0 }}</span>
            dari
            <span class="font-bold text-night-ink">{{ pagination.total }}</span>
            {{ itemLabel }}
        </p>

        <div class="flex items-center gap-1 sm:gap-1.5 flex-wrap justify-center">
            <!-- Prev Button -->
            <Link
                v-if="pagination.prev_page_url"
                :href="pagination.prev_page_url"
                preserve-scroll
                class="h-[36px] px-3 rounded-xl border border-border-gray text-[13px] font-extrabold text-night-ink hover:bg-cloud-surface transition-colors inline-flex items-center gap-1 cursor-pointer"
            >
                <ChevronLeft class="size-4" />
                <span>Sebelumnya</span>
            </Link>
            <span
                v-else
                class="h-[36px] px-3 rounded-xl border border-border-gray/50 text-[13px] font-semibold text-charcoal/40 inline-flex items-center gap-1 opacity-50 cursor-not-allowed"
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
                        'size-9 rounded-xl flex items-center justify-center text-[13px] font-extrabold transition-all cursor-pointer',
                        link.active
                            ? 'bg-sprout-tint text-night-ink border-2 border-eco-green/40 shadow-2xs'
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
                class="h-[36px] px-3 rounded-xl border border-border-gray text-[13px] font-extrabold text-night-ink hover:bg-cloud-surface transition-colors inline-flex items-center gap-1 cursor-pointer"
            >
                <span>Berikutnya</span>
                <ChevronRight class="size-4" />
            </Link>
            <span
                v-else
                class="h-[36px] px-3 rounded-xl border border-border-gray/50 text-[13px] font-semibold text-charcoal/40 inline-flex items-center gap-1 opacity-50 cursor-not-allowed"
            >
                <span>Berikutnya</span>
                <ChevronRight class="size-4" />
            </span>
        </div>
    </div>
</template>
