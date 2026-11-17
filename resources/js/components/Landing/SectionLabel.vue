<script setup lang="ts">
defineProps<{ text: string }>();
</script>
<template>
  <span class="text-xs font-bold tracking-widest uppercase text-green-700">{{ text }}</span>
</template>
