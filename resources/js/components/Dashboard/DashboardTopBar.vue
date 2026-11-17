<script setup lang="ts">
import { computed } from "vue";
import { Link, usePage } from "@inertiajs/vue3";
import { Menu, User } from "lucide-vue-next";

const emit = defineEmits<{ (e: "toggleMobile"): void }>();

const page = usePage();
const user = computed(() => (page.props.auth as any)?.user ?? {});
const userInitials = computed(() => {
    const n: string = user.value?.name ?? "?";
    return n
        .split(" ")
        .slice(0, 2)
        .map((w: string) => w[0])
        .join("")
        .toUpperCase();
});
const roleBadge = computed(() => {
    const r: string = user.value?.role ?? "";
    if (r === "admin") return "Ruang Admin";
    if (r === "driver") return "Portal Driver";
    return "Portal Nasabah";
});
const breadcrumb = computed(() => {
    const url: string = page.url;
    if (url.includes("dashboard")) return "Ruang kerja / Dashboard";
    if (url.includes("users")) return "Ruang kerja / Pengguna";
    if (url.includes("waste-categories")) return "Ruang kerja / Kategori Sampah";
    if (url.includes("profile")) return "Ruang kerja / Profil Saya";
    return "Ruang kerja";
});
</script>

<template>
    <header
        class="sticky top-0 z-30 flex items-center justify-between h-[72px] px-4 lg:px-6 border-b border-border-gray bg-paper-white shrink-0"
    >
        <!-- Left: Hamburger + Breadcrumb -->
        <div class="flex items-center gap-3">
            <!-- Mobile hamburger -->
            <button
                type="button"
                class="lg:hidden p-2 rounded-xl text-charcoal hover:bg-cloud-surface transition-colors"
                @click="emit('toggleMobile')"
            >
                <Menu class="size-5" />
            </button>
            <!-- Breadcrumb -->
            <span class="text-[13px] sm:text-[14px] font-semibold text-charcoal/70">
                {{ breadcrumb }}
            </span>
        </div>

        <!-- Right: User identity chip -->
        <div class="flex items-center gap-3">
            <!-- Role badge -->
            <span class="hidden sm:block text-[12px] font-bold text-eco-green-dark bg-sprout-tint border border-eco-green/30 px-2.5 py-1 rounded-full">
                {{ roleBadge }}
            </span>

            <!-- Avatar + name -->
            <Link
                href="/profile"
                class="flex items-center gap-2.5 group cursor-pointer"
            >
                <!-- Initials circle -->
                <div
                    class="size-9 rounded-full bg-sprout-tint border-2 border-eco-green/40 flex items-center justify-center text-[13px] font-extrabold text-night-ink shrink-0 group-hover:scale-105 transition-transform"
                >
                    {{ userInitials }}
                </div>
                <div class="hidden sm:block text-right">
                    <p class="text-[13px] font-bold text-night-ink leading-tight">
                        {{ user?.name }}
                    </p>
                </div>
            </Link>
        </div>
    </header>
</template>
