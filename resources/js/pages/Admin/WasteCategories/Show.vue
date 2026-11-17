<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

defineProps<{
  category: any;
}>();
</script>

<template>
  <Head title="Detail Jenis Sampah - Admin Bank Sampah" />
  <div class="p-6">
    <h1 class="text-xl font-bold">Detail Jenis Sampah</h1>
  </div>
</template>
