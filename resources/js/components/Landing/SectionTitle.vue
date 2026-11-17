<script setup lang="ts">
withDefaults(defineProps<{
  title: string;
  align?: 'left' | 'center';
}>(), { align: 'left' });
</script>
<template>
  <h2
    class="text-3xl lg:text-4xl font-bold text-gray-900 leading-tight"
    :class="align === 'center' ? 'text-center' : 'text-left'"
  >{{ title }}</h2>
</template>
