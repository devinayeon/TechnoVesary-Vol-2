<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

defineProps<{
  categories: any;
  filters: any;
}>();
</script>

<template>
  <Head title="Manajemen Jenis Sampah - Admin Bank Sampah" />
  <div class="p-6">
    <h1 class="text-xl font-bold">Manajemen Jenis Sampah</h1>
  </div>
</template>
