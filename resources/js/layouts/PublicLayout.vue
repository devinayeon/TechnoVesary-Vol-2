<script setup lang="ts">
import TheNavbar from '@/components/Landing/TheNavbar.vue';
import TheFooter from '@/components/Landing/TheFooter.vue';
</script>

<template>
    <div
        class="min-h-screen bg-cloud-surface font-sans text-night-ink antialiased"
    >
        <TheNavbar />

        <main class="pt-28 pb-16">
            <div class="mx-auto w-full max-w-[1200px] px-4 sm:px-6">
                <slot />
            </div>
        </main>

        <TheFooter />
    </div>
</template>
