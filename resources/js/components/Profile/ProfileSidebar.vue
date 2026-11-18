<script setup lang="ts">
import { computed } from "vue";
import { Link, usePage } from "@inertiajs/vue3";
import {
    User as UserIcon,
    MapPin,
    Lock,
    FileText,
    ArrowLeft,
} from "lucide-vue-next";
import type { User } from "@/types/auth";

const props = defineProps<{
    activeTab: "profile" | "address" | "security" | "account";
}>();

const emit = defineEmits<{
    (e: "selectTab", tab: "profile" | "address" | "security" | "account"): void;
}>();

const page = usePage();
const user = computed(() => (page.props.auth as any)?.user as User | undefined);

const userRole = computed(() => {
    const role = (user.value?.role as string) || "customer";
    if (role === "admin") return "Admin";
    if (role === "driver") return "Driver";
    return "Customer";
});

const dashboardRoute = computed(() => {
    const role = (user.value?.role as string) || "customer";
    if (role === "admin") return "/admin/dashboard";
    if (role === "driver") return "/driver/dashboard";
    return "/customer/dashboard";
});

const navItems = [
    {
        id: "profile" as const,
        label: "Profil saya",
        icon: UserIcon,
    },
    {
        id: "address" as const,
        label: "Alamat utama",
        icon: MapPin,
    },
    {
        id: "security" as const,
        label: "Keamanan akun",
        icon: Lock,
    },
    {
        id: "account" as const,
        label: "Informasi akun",
        icon: FileText,
    },
];
</script>

<template>
    <aside
        class="w-full lg:w-[250px] shrink-0 bg-paper-white border-b lg:border-b-0 lg:border-r border-border-gray flex flex-col justify-between p-6 lg:min-h-screen"
    >
        <div class="flex flex-col gap-6">
            <!-- Brand Logo & Scope -->
            <div class="flex flex-col gap-4">
                <Link
                    :href="dashboardRoute"
                    class="inline-flex items-center gap-2.5 transition hover:opacity-90"
                >
                    <img
                        src="/assets/Logo/logo_hijau_biru.png"
                        alt="ReValue Logo"
                        class="h-9 w-auto object-contain"
                    />
                </Link>

                <div class="flex flex-col">
                    <span class="text-[15px] font-extrabold text-night-ink tracking-tight">
                        Pengaturan akun
                    </span>
                    <span class="text-[13px] font-medium text-charcoal">
                        {{ userRole }}
                    </span>
                </div>
            </div>

            <!-- Navigation Tabs -->
            <nav class="flex lg:flex-col gap-2 overflow-x-auto pb-2 lg:pb-0 scrollbar-none">
                <button
                    v-for="item in navItems"
                    :key="item.id"
                    type="button"
                    @click="emit('selectTab', item.id)"
                    class="flex items-center gap-3 h-12 px-3.5 rounded-xl text-left transition select-none cursor-pointer shrink-0 lg:shrink"
                    :class="[
                        activeTab === item.id
                            ? 'bg-sprout-tint text-night-ink font-extrabold shadow-xs'
                            : 'text-night-ink hover:bg-cloud-surface font-semibold',
                    ]"
                >
                    <component
                        :is="item.icon"
                        class="size-5 shrink-0"
                        :class="activeTab === item.id ? 'stroke-[2.5]' : 'stroke-2'"
                    />
                    <span class="text-[14px] whitespace-nowrap">{{ item.label }}</span>
                </button>
            </nav>
        </div>

        <!-- Bottom Return & Note -->
        <div class="hidden lg:flex flex-col gap-4 pt-6 border-t border-border-gray/70">
            <Link
                :href="dashboardRoute"
                class="inline-flex items-center gap-2 text-xs font-bold text-charcoal hover:text-night-ink transition"
            >
                <ArrowLeft class="size-4" />
                <span>Kembali ke Dashboard</span>
            </Link>

            <div class="flex flex-col gap-1 text-[12px] leading-relaxed">
                <p class="font-bold text-night-ink">Akunmu, langkah baikmu.</p>
                <p class="text-charcoal font-normal">
                    Data pribadi hanya untuk layanan ReValue.
                </p>
            </div>
        </div>
    </aside>
</template>
