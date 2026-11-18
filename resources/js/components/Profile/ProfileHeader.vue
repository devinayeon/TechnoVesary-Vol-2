<script setup lang="ts">
import { computed } from "vue";
import { Link } from "@inertiajs/vue3";
import { ArrowLeft } from "lucide-vue-next";
import type { User } from "@/types/auth";
import UserAvatar from "@/components/Dashboard/UserAvatar.vue";

const props = defineProps<{
    user: User;
    currentTabTitle: string;
}>();

const initials = computed(() => {
    if (!props.user?.name) return "U";
    return props.user.name
        .split(" ")
        .slice(0, 2)
        .map((w) => w[0]?.toUpperCase())
        .join("");
});

const roleLabel = computed(() => {
    const role = (props.user?.role as string) || "customer";
    if (role === "admin") return "Admin";
    if (role === "driver") return "Driver";
    return "Customer";
});

const userPhoto = computed(() => {
    return props.user.photo || (props.user as any).foto || null;
});

const dashboardRoute = computed(() => {
    const role = (props.user?.role as string) || "customer";
    if (role === "admin") return "/admin/dashboard";
    if (role === "driver") return "/driver/dashboard";
    return "/customer/dashboard";
});
</script>

<template>
    <header
        class="bg-paper-white border-b border-border-gray h-20 px-6 lg:px-8 flex items-center justify-between shrink-0"
    >
        <!-- Breadcrumb & Mobile back -->
        <div class="flex items-center gap-3">
            <Link
                :href="dashboardRoute"
                class="lg:hidden p-1.5 rounded-lg text-charcoal hover:bg-cloud-surface hover:text-night-ink transition"
                title="Kembali ke Dashboard"
            >
                <ArrowLeft class="size-5" />
            </Link>

            <nav aria-label="Breadcrumb" class="flex items-center gap-2 text-[13px] whitespace-nowrap">
                <span class="text-charcoal font-medium">Pengaturan akun</span>
                <span class="text-charcoal/50">/</span>
                <span class="text-night-ink font-bold">{{ currentTabTitle }}</span>
            </nav>
        </div>

        <!-- Header Profile Identity -->
        <div class="flex items-center gap-3">
            <div
                v-if="!userPhoto"
                class="size-10 rounded-full bg-sprout-tint text-night-ink flex items-center justify-center font-extrabold text-[14px] select-none shadow-xs"
            >
                {{ initials }}
            </div>
            <UserAvatar
                v-else
                :photo="userPhoto"
                :role="user.role"
                :name="user.name"
                size="md"
            />

            <div class="hidden sm:flex flex-col text-left leading-tight">
                <span class="text-[14px] font-bold text-night-ink truncate max-w-[150px]">
                    {{ user.name }}
                </span>
                <span class="text-[12px] font-normal text-charcoal">
                    {{ roleLabel }}
                </span>
            </div>
        </div>
    </header>
</template>
