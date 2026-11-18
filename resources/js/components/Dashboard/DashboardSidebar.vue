<script setup lang="ts">
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
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
    Package,
} from 'lucide-vue-next';

defineProps<{
    isMobileOpen?: boolean;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
}>();

const page = usePage();
const currentPath = computed(() => page.url);
const role = computed(() => {
    return ((page.props.auth as any)?.user?.role as string) || 'customer';
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

    if (r === 'admin') {
        return [
            {
                label: 'Dashboard',
                href: '/admin/dashboard',
                icon: LayoutDashboard,
                isActive: url.startsWith('/admin/dashboard'),
            },
            {
                label: 'Pengguna',
                href: '/admin/users',
                icon: Users,
                isActive: url.startsWith('/admin/users'),
            },
            {
                label: 'Kategori Sampah',
                href: '/admin/waste-categories',
                icon: Recycle,
                isActive: url.startsWith('/admin/waste-categories'),
            },

            {
                label: 'Artikel',
                href: '/admin/articles',
                icon: Newspaper,
                isActive: url.startsWith('/admin/articles'),
            },
            {
                label: 'Upcycle Store',
                href: '/admin/products',
                icon: ShoppingBag,
                isActive: url.startsWith('/admin/products'),
            },
            {
                label: 'Langganan',
                href: '/admin/subscriptions',
                icon: CreditCard,
                isActive: url.startsWith('/admin/subscriptions'),
            },
            {
                label: 'Riwayat Penjemputan',
                href: '/admin/pickup-history',
                icon: Truck,
                isActive: url.startsWith('/admin/pickup-history'),
            },
            {
                label: 'Transaksi Toko',
                href: '/admin/product-transactions',
                icon: ShoppingCart,
                isActive: url.startsWith('/admin/product-transactions'),
            },
        ];
    } else if (r === 'driver') {
        return [
            {
                label: 'Dashboard',
                href: '/driver/dashboard',
                icon: LayoutDashboard,
                isActive: url.startsWith('/driver/dashboard'),
            },
            {
                label: 'Tugas Jemput',
                href: '/driver/pickups',
                icon: Truck,
                isActive:
                    url.startsWith('/driver/pickups') &&
                    !url.startsWith('/driver/pickups/history'),
            },
            {
                label: 'Antar Pesanan Toko',
                href: '/driver/deliveries',
                icon: Package,
                isActive: url.startsWith('/driver/deliveries'),
            },
            {
                label: 'Riwayat Selesai',
                href: '/driver/pickups/history',
                icon: Recycle,
                isActive: url.startsWith('/driver/pickups/history'),
            },
            {
                label: 'Profil Saya',
                href: '/profile',
                icon: User,
                isActive: url.startsWith('/profile'),
            },
        ];
    } else {
        // Customer
        return [
            {
                label: 'Dashboard',
                href: '/customer/dashboard',
                icon: LayoutDashboard,
                isActive: url.startsWith('/customer/dashboard'),
            },
            {
                label: 'Artikel',
                href: '/customer/articles',
                icon: Newspaper,
                isActive: url.startsWith('/customer/articles'),
            },
            {
                label: 'Penjemputan',
                href: '/customer/pickups',
                icon: Truck,
                isActive: url.startsWith('/customer/pickups'),
            },
            {
                label: 'Riwayat Poin',
                href: '/customer/points',
                icon: Coins,
                isActive: url.startsWith('/customer/points'),
            },
            {
                label: 'ReValue+',
                href: '/customer/subscription',
                icon: Sparkles,
                isActive:
                    url.startsWith('/customer/subscription') &&
                    !url.startsWith('/customer/subscription/history'),
            },
            {
                label: 'Riwayat Langganan',
                href: '/customer/subscription/history',
                icon: CreditCard,
                isActive: url.startsWith('/customer/subscription/history'),
            },
            {
                label: 'Upcycle Store',
                href: '/customer/products',
                icon: ShoppingBag,
                isActive:
                    url.startsWith('/customer/products') ||
                    url.startsWith('/customer/checkout'),
            },
            {
                label: 'Pesanan Saya',
                href: '/customer/transactions',
                icon: ShoppingCart,
                isActive: url.startsWith('/customer/transactions'),
            },
            {
                label: 'Profil Saya',
                href: '/profile',
                icon: User,
                isActive: url.startsWith('/profile'),
            },
        ];
    }
});

const roleTitle = computed(() => {
    switch (role.value) {
        case 'admin':
            return 'Ruang Admin';
        case 'driver':
            return 'Portal Driver';
        default:
            return 'Portal Customer';
    }
});

const dashboardHref = computed(() => {
    switch (role.value) {
        case 'admin':
            return '/admin/dashboard';
        case 'driver':
            return '/driver/dashboard';
        default:
            return '/customer/dashboard';
    }
});
</script>

<template>
    <!-- Mobile Backdrop -->
    <div
        v-if="isMobileOpen"
        class="fixed inset-0 z-40 bg-night-ink/40 backdrop-blur-xs transition-opacity lg:hidden"
        @click="emit('close')"
    ></div>

    <!-- Sidebar Container (Fixed on desktop & slide drawer on mobile) -->
    <aside
        :class="[
            'fixed top-0 bottom-0 left-0 z-40 flex h-screen w-[240px] flex-col justify-between border-r border-border-gray bg-paper-white transition-transform duration-300 ease-in-out',
            isMobileOpen
                ? 'translate-x-0'
                : '-translate-x-full lg:translate-x-0',
        ]"
    >
        <!-- Top Section: Brand Lockup & Navigation -->
        <div class="flex flex-col gap-6 overflow-y-auto p-4">
            <!-- Brand Logo Lockup -->
            <div
                class="flex items-center justify-between border-b border-border-gray/60 pb-2"
            >
                <Link
                    :href="dashboardHref"
                    class="group flex items-center gap-2.5"
                >
                    <img
                        src="/assets/Logo/logo_hijau_biru.png"
                        alt="ReValue"
                        class="h-8 w-8 object-contain transition-transform group-hover:scale-105"
                    />
                    <div class="flex flex-col">
                        <span
                            class="text-[20px] leading-none font-extrabold tracking-tight"
                        >
                            <span class="text-night-ink">Re</span
                            ><span class="text-eco-green-dark">Value</span>
                        </span>
                        <span
                            class="mt-0.5 text-[11px] font-bold tracking-wider text-eco-green-dark uppercase"
                        >
                            {{ roleTitle }}
                        </span>
                    </div>
                </Link>

                <button
                    type="button"
                    class="rounded-lg p-1.5 text-charcoal hover:bg-cloud-surface lg:hidden"
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
                        'flex h-[44px] items-center justify-between rounded-xl px-3 text-[14px] transition-all duration-150',
                        item.isActive
                            ? 'bg-sprout-tint font-extrabold text-night-ink shadow-2xs'
                            : 'font-semibold text-charcoal hover:bg-cloud-surface hover:text-night-ink',
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
                            'rounded-full px-1.5 py-0.5 text-[10px] font-extrabold tracking-wider uppercase',
                            item.isActive
                                ? 'bg-eco-green-dark text-white'
                                : 'border border-border-gray bg-cloud-surface text-charcoal',
                        ]"
                    >
                        {{ item.badge }}
                    </span>
                </Link>
            </nav>
        </div>

        <!-- Bottom Section: Workspace Note & Logout -->
        <div
            class="flex flex-col gap-3 border-t border-border-gray/60 bg-paper-white p-4"
        >
            <!-- Workspace Slogan Box -->
            <div
                class="flex items-start gap-2.5 rounded-2xl border border-border-gray/60 bg-cloud-surface p-3"
            >
                <Sparkles class="mt-0.5 size-4 shrink-0 text-sunlight-gold" />
                <div class="text-[12px] leading-snug">
                    <p class="font-extrabold text-night-ink">ReValue Space</p>
                    <p class="font-medium text-charcoal/80">
                        Kelola langkah baik hari ini.
                    </p>
                </div>
            </div>

            <!-- Logout Link -->
            <Link
                href="/logout"
                method="post"
                as="button"
                class="flex h-[42px] w-full cursor-pointer items-center gap-3 rounded-xl px-3 text-left text-[14px] font-bold text-red-500 transition-colors hover:bg-red-50 hover:text-red-600"
            >
                <LogOut class="size-5 shrink-0" />
                <span>Keluar dari Akun</span>
            </Link>
        </div>
    </aside>
</template>
