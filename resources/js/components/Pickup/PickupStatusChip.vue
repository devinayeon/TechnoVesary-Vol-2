<script setup lang="ts">
import { computed } from 'vue';
import StatusChip from '@/components/Dashboard/StatusChip.vue';
import {
    pickupStatusDescription,
    pickupStatusLabel,
    pickupStatusTone,
} from '@/lib/pickup';

const props = withDefaults(
    defineProps<{
        status?: string | null;
        label?: string;
    }>(),
    { status: null, label: undefined },
);

const tone = computed(() => pickupStatusTone(props.status));
const text = computed(() => props.label ?? pickupStatusLabel(props.status));
const description = computed(() => pickupStatusDescription(props.status));
</script>

<template>
    <StatusChip :tone="tone" :label="text" :title="description" />
</template>
