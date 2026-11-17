<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

defineProps<{
  user: any;
  roles: any;
}>();
</script>

<template>
  <Head title="Ubah Data Pengguna - Admin" />
  <div class="p-6">
    <h1 class="text-xl font-bold">Ubah Data Pengguna</h1>
  </div>
</template>
