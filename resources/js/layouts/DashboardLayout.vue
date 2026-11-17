<script setup lang="ts">
import { ref } from "vue";
import DashboardSidebar from "@/components/Dashboard/DashboardSidebar.vue";
import DashboardTopBar from "@/components/Dashboard/DashboardTopBar.vue";

const isMobileOpen = ref(false);
const toggleMobile = () => {
    isMobileOpen.value = !isMobileOpen.value;
};
</script>

<template>
    <div class="min-h-screen flex bg-cloud-surface font-sans text-night-ink antialiased">
        <!-- Sidebar (fixed on desktop, drawer on mobile) -->
        <DashboardSidebar
            :isMobileOpen="isMobileOpen"
            @close="toggleMobile"
        />

        <!-- Main content area -->
        <div class="flex-1 flex flex-col min-w-0 lg:ml-0">
            <!-- Top bar -->
            <DashboardTopBar @toggleMobile="toggleMobile" />

            <!-- Page slot -->
            <main class="flex-1 p-4 sm:p-6 lg:p-8 overflow-y-auto">
                <slot />
            </main>
        </div>
    </div>
</template>
