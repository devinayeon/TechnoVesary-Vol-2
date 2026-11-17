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
    <div class="min-h-screen bg-cloud-surface font-sans text-night-ink antialiased">
        <!-- Sidebar (fixed on desktop, drawer on mobile) -->
        <DashboardSidebar
            :isMobileOpen="isMobileOpen"
            @close="toggleMobile"
        />

        <!-- Main content area (offset by sidebar width on desktop) -->
        <div class="lg:pl-[240px] flex flex-col min-h-screen min-w-0">
            <!-- Top bar (sticky at top) -->
            <DashboardTopBar @toggleMobile="toggleMobile" />

            <!-- Page slot -->
            <main class="flex-1 p-4 sm:p-6 lg:p-8">
                <slot />
            </main>
        </div>
    </div>
</template>
