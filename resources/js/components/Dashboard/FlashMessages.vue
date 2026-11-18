<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { CheckCircle2, X, XCircle } from 'lucide-vue-next';

const page = usePage();

const showSuccess = ref(true);
const showError = ref(true);

const success = computed(() => page.props.flash?.success);
const error = computed(
    () =>
        page.props.flash?.error ||
        (Object.keys(page.props.errors ?? {}).length > 0
            ? Object.values(page.props.errors ?? {})[0]
            : null),
);

watch(
    () => page.props.flash,
    () => {
        showSuccess.value = true;
        showError.value = true;
    },
    { deep: true },
);
</script>

<template>
    <div class="flex flex-col gap-3">
        <div
            v-if="showSuccess && success"
            class="flex items-center justify-between gap-3 rounded-2xl border-2 border-eco-green/40 bg-sprout-tint p-4 text-[14px] font-bold text-night-ink shadow-2xs"
        >
            <div class="flex min-w-0 items-center gap-3">
                <CheckCircle2 class="size-5 shrink-0 text-eco-green-dark" />
                <span class="truncate">{{ success }}</span>
            </div>
            <button
                type="button"
                class="shrink-0 cursor-pointer rounded-lg p-1 text-charcoal transition-colors hover:bg-eco-green/20 hover:text-night-ink"
                aria-label="Tutup notifikasi"
                @click="showSuccess = false"
            >
                <X class="size-4" />
            </button>
        </div>

        <div
            v-if="showError && error"
            class="flex items-center justify-between gap-3 rounded-2xl border-2 border-red-300 bg-red-50 p-4 text-[14px] font-bold text-red-800 shadow-2xs"
        >
            <div class="flex min-w-0 items-center gap-3">
                <XCircle class="size-5 shrink-0 text-red-600" />
                <span class="truncate">{{ error }}</span>
            </div>
            <button
                type="button"
                class="shrink-0 cursor-pointer rounded-lg p-1 text-red-700 transition-colors hover:bg-red-100 hover:text-red-900"
                aria-label="Tutup notifikasi"
                @click="showError = false"
            >
                <X class="size-4" />
            </button>
        </div>
    </div>
</template>
