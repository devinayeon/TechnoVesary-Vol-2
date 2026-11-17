<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

defineProps<{
  roles: any;
}>();
</script>

<template>
  <Head title="Tambah Pengguna Baru - Admin" />
  <div class="p-6">
    <h1 class="text-xl font-bold">Tambah Pengguna Baru</h1>
  </div>
</template>
