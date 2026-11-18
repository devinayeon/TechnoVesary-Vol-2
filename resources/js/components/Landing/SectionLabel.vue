<script setup lang="ts">
defineProps<{ text: string }>();
</script>
<template>
    <span class="text-xs font-bold tracking-widest text-green-700 uppercase">{{
        text
    }}</span>
</template>
