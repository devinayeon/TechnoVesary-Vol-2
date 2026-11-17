<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

defineProps<{
  user: any;
}>();
</script>

<template>
  <Head title="Detail Pengguna - Admin" />
  <div class="p-6">
    <h1 class="text-xl font-bold">Detail Pengguna</h1>
  </div>
</template>
