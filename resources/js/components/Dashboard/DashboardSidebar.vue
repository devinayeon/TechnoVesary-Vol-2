<script setup lang="ts">
import { computed } from "vue";
import { Link, usePage } from "@inertiajs/vue3";
import {
    LayoutDashboard,
    Users,
    Recycle,
    Truck,
    CreditCard,
    Coins,
    ShoppingBag,
    ShoppingCart,
    User,
    LogOut,
    Sparkles,
    Newspaper,
} from "lucide-vue-next";

defineProps<{
    isMobileOpen?: boolean;
}>();

const emit = defineEmits<{
    (e: "close"): void;
}>();

const page = usePage();
const currentPath = computed(() => page.url);
const role = computed(() => {
    return ((page.props.auth as any)?.user?.role as string) || "customer";
});

interface NavItem {
    label: string;
    href: string;
    icon: any;
    isActive: boolean;
    badge?: string;
}

const navItems = computed<NavItem[]>(() => {
    const r = role.value;
    const url = currentPath.value;

    if (r === "admin") {
        return [
            {
                label: "Dashboard",
                href: "/admin/dashboard",
                icon: LayoutDashboard,
                isActive: url.startsWith("/admin/dashboard"),
            },
            {
                label: "Pengguna",
                href: "/admin/users",
                icon: Users,
                isActive: url.startsWith("/admin/users"),
            },
            {
                label: "Kategori Sampah",
                href: "/admin/waste-categories",
                icon: Recycle,
                isActive: url.startsWith("/admin/waste-categories"),
            },

            {
                label: "Artikel",
                href: "/admin/articles",
                icon: Newspaper,
                isActive: url.startsWith("/admin/articles"),
            },
            {
                label: "Upcycle Store",
                href: "/admin/products",
                icon: ShoppingBag,
                isActive: url.startsWith("/admin/products"),
            },
            {
                label: "Langganan",
                href: "/admin/subscriptions",
                icon: CreditCard,
                isActive: url.startsWith("/admin/subscriptions"),
            },
            {
                label: "Riwayat Penjemputan",
                href: "/admin/pickup-history",
                icon: Truck,
                isActive: url.startsWith("/admin/pickup-history"),
            },
            {
                label: "Transaksi Toko",
                href: "/admin/product-transactions",
                icon: ShoppingCart,
                isActive: url.startsWith("/admin/product-transactions"),
            },
        ];
    } else if (r === "driver") {
        return [
            {
                label: "Dashboard",
                href: "/driver/dashboard",
                icon: LayoutDashboard,
                isActive: url.startsWith("/driver/dashboard"),
            },
            {
                label: "Tugas Jemput",
                href: "#",
                icon: Truck,
                isActive: false,
                badge: "3 Baru",
            },
            {
                label: "Riwayat Selesai",
                href: "#",
                icon: Recycle,
                isActive: false,
            },
            {
                label: "Profil Saya",
                href: "/profile",
                icon: User,
                isActive: url.startsWith("/profile"),
            },
        ];
    } else {
        // Customer
        return [
            {
                label: "Dashboard",
                href: "/customer/dashboard",
                icon: LayoutDashboard,
                isActive: url.startsWith("/customer/dashboard"),
            },
            {
                label: "Setor Sampah",
                href: "#",
                icon: Recycle,
                isActive: false,
                badge: "Pilah",
            },
            {
                label: "Saldo & Poin",
                href: "#",
                icon: Coins,
                isActive: false,
            },
            {
                label: "Upcycle Store",
                href: "#",
                icon: ShoppingBag,
                isActive: false,
                badge: "Eco",
            },
            {
                label: "Profil Saya",
                href: "/profile",
                icon: User,
                isActive: url.startsWith("/profile"),
            },
        ];
    }
});

const roleTitle = computed(() => {
    switch (role.value) {
        case "admin":
            return "Ruang Admin";
        case "driver":
            return "Portal Driver";
        default:
            return "Portal Customer";
    }
});

const dashboardHref = computed(() => {
    switch (role.value) {
        case "admin":
            return "/admin/dashboard";
        case "driver":
            return "/driver/dashboard";
        default:
            return "/customer/dashboard";
    }
});
</script>

<template>
    <!-- Mobile Backdrop -->
    <div
        v-if="isMobileOpen"
        class="fixed inset-0 z-40 bg-night-ink/40 backdrop-blur-xs lg:hidden transition-opacity"
        @click="emit('close')"
    ></div>

    <!-- Sidebar Container (Fixed on desktop & slide drawer on mobile) -->
    <aside
        :class="[
            'fixed top-0 bottom-0 left-0 z-40 flex flex-col justify-between w-[240px] h-screen bg-paper-white border-r border-border-gray transition-transform duration-300 ease-in-out',
            isMobileOpen
                ? 'translate-x-0'
                : '-translate-x-full lg:translate-x-0',
        ]"
    >
        <!-- Top Section: Brand Lockup & Navigation -->
        <div class="p-4 flex flex-col gap-6 overflow-y-auto">
            <!-- Brand Logo Lockup -->
            <div
                class="flex items-center justify-between pb-2 border-b border-border-gray/60"
            >
                <Link
                    :href="dashboardHref"
                    class="flex items-center gap-2.5 group"
                >
                    <img
                        src="/assets/Logo/logo_hijau_biru.png"
                        alt="ReValue"
                        class="w-8 h-8 object-contain transition-transform group-hover:scale-105"
                    />
                    <div class="flex flex-col">
                        <span
                            class="text-[20px] font-extrabold tracking-tight leading-none"
                        >
                            <span class="text-night-ink">Re</span
                            ><span class="text-eco-green-dark">Value</span>
                        </span>
                        <span
                            class="text-[11px] font-bold text-eco-green-dark uppercase tracking-wider mt-0.5"
                        >
                            {{ roleTitle }}
                        </span>
                    </div>
                </Link>

                <button
                    type="button"
                    class="lg:hidden p-1.5 rounded-lg text-charcoal hover:bg-cloud-surface"
                    @click="emit('close')"
                >
                    <span class="sr-only">Tutup Menu</span>
                    ✕
                </button>
            </div>

            <!-- Navigation Links -->
            <nav class="flex flex-col gap-1.5" aria-label="Sidebar Navigation">
                <Link
                    v-for="item in navItems"
                    :key="item.label"
                    :href="item.href"
                    :class="[
                        'flex items-center justify-between px-3 h-[44px] rounded-xl text-[14px] transition-all duration-150',
                        item.isActive
                            ? 'bg-sprout-tint text-night-ink font-extrabold shadow-2xs'
                            : 'text-charcoal hover:text-night-ink hover:bg-cloud-surface font-semibold',
                    ]"
                    @click="emit('close')"
                >
                    <div class="flex items-center gap-3">
                        <component
                            :is="item.icon"
                            :class="[
                                'size-5 shrink-0',
                                item.isActive
                                    ? 'text-eco-green-dark'
                                    : 'text-charcoal/80',
                            ]"
                        />
                        <span class="truncate">{{ item.label }}</span>
                    </div>

                    <span
                        v-if="item.badge"
                        :class="[
                            'text-[10px] font-extrabold px-1.5 py-0.5 rounded-full uppercase tracking-wider',
                            item.isActive
                                ? 'bg-eco-green-dark text-white'
                                : 'bg-cloud-surface text-charcoal border border-border-gray',
                        ]"
                    >
                        {{ item.badge }}
                    </span>
                </Link>
            </nav>
        </div>

        <!-- Bottom Section: Workspace Note & Logout -->
        <div
            class="p-4 border-t border-border-gray/60 flex flex-col gap-3 bg-paper-white"
        >
            <!-- Workspace Slogan Box -->
            <div
                class="bg-cloud-surface rounded-2xl p-3 border border-border-gray/60 flex items-start gap-2.5"
            >
                <Sparkles class="size-4 text-sunlight-gold shrink-0 mt-0.5" />
                <div class="text-[12px] leading-snug">
                    <p class="font-extrabold text-night-ink">ReValue Space</p>
                    <p class="text-charcoal/80 font-medium">
                        Kelola langkah baik hari ini.
                    </p>
                </div>
            </div>

            <!-- Logout Link -->
            <Link
                href="/logout"
                method="post"
                as="button"
                class="w-full flex items-center gap-3 px-3 h-[42px] rounded-xl text-[14px] font-bold text-red-500 hover:bg-red-50 hover:text-red-600 transition-colors cursor-pointer text-left"
            >
                <LogOut class="size-5 shrink-0" />
                <span>Keluar dari Akun</span>
            </Link>
        </div>
    </aside>
</template>
