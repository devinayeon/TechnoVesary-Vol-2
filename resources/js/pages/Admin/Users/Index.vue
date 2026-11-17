<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

defineProps<{
  users: any;
  filters: any;
  roles: any;
}>();
</script>

<template>
  <Head title="Manajemen Pengguna - Admin Bank Sampah" />
  <div class="p-6">
    <h1 class="text-xl font-bold">Manajemen Pengguna</h1>
  </div>
</template>
