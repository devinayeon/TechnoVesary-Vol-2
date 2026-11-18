<script setup lang="ts">
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import {
    User as UserIcon,
    MapPin,
    Lock,
    FileText,
    ArrowLeft,
    Trophy,
} from 'lucide-vue-next';
import type { User } from '@/types/auth';

export type ProfileTabType =
    | 'profile'
    | 'achievements'
    | 'address'
    | 'security'
    | 'account';

const props = defineProps<{
    activeTab: ProfileTabType;
}>();

const emit = defineEmits<{
    (e: 'selectTab', tab: ProfileTabType): void;
}>();

const page = usePage();
const user = computed(() => (page.props.auth as any)?.user as User | undefined);

const userRole = computed(() => {
    const role = (user.value?.role as string) || 'customer';
    if (role === 'admin') return 'Admin';
    if (role === 'driver') return 'Driver';
    return 'Customer';
});

const dashboardRoute = computed(() => {
    const role = (user.value?.role as string) || 'customer';
    if (role === 'admin') return '/admin/dashboard';
    if (role === 'driver') return '/driver/dashboard';
    return '/customer/dashboard';
});

const navItems = computed(() => {
    const isCustomer =
        (user.value?.role as string) === 'customer' || !user.value?.role;

    const items: Array<{
        id: ProfileTabType;
        label: string;
        icon: any;
    }> = [
        {
            id: 'profile',
            label: 'Profil saya',
            icon: UserIcon,
        },
    ];

    if (isCustomer) {
        items.push({
            id: 'achievements',
            label: 'Pencapaian',
            icon: Trophy,
        });
    }

    items.push(
        {
            id: 'address',
            label: 'Alamat utama',
            icon: MapPin,
        },
        {
            id: 'security',
            label: 'Keamanan akun',
            icon: Lock,
        },
        {
            id: 'account',
            label: 'Informasi akun',
            icon: FileText,
        },
    );

    return items;
});
</script>

<template>
    <aside
        class="flex w-full shrink-0 flex-col justify-between border-b border-border-gray bg-paper-white p-6 lg:min-h-screen lg:w-[250px] lg:border-r lg:border-b-0"
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
                    <span
                        class="text-[15px] font-extrabold tracking-tight text-night-ink"
                    >
                        Pengaturan akun
                    </span>
                    <span class="text-[13px] font-medium text-charcoal">
                        {{ userRole }}
                    </span>
                </div>
            </div>

            <!-- Navigation Tabs -->
            <nav
                class="flex scrollbar-none gap-2 overflow-x-auto pb-2 lg:flex-col lg:pb-0"
            >
                <button
                    v-for="item in navItems"
                    :key="item.id"
                    type="button"
                    @click="emit('selectTab', item.id)"
                    class="flex h-12 shrink-0 cursor-pointer items-center gap-3 rounded-xl px-3.5 text-left transition select-none lg:shrink"
                    :class="[
                        activeTab === item.id
                            ? 'bg-sprout-tint font-extrabold text-night-ink shadow-xs'
                            : 'font-semibold text-night-ink hover:bg-cloud-surface',
                    ]"
                >
                    <component
                        :is="item.icon"
                        class="size-5 shrink-0"
                        :class="
                            activeTab === item.id ? 'stroke-[2.5]' : 'stroke-2'
                        "
                    />
                    <span class="text-[14px] whitespace-nowrap">{{
                        item.label
                    }}</span>
                </button>
            </nav>
        </div>

        <!-- Bottom Return & Note -->
        <div
            class="hidden flex-col gap-4 border-t border-border-gray/70 pt-6 lg:flex"
        >
            <Link
                :href="dashboardRoute"
                class="inline-flex items-center gap-2 text-xs font-bold text-charcoal transition hover:text-night-ink"
            >
                <ArrowLeft class="size-4" />
                <span>Kembali ke Dashboard</span>
            </Link>

            <div class="flex flex-col gap-1 text-[12px] leading-relaxed">
                <p class="font-bold text-night-ink">Akunmu, langkah baikmu.</p>
                <p class="font-normal text-charcoal">
                    Data pribadi hanya untuk layanan ReValue.
                </p>
            </div>
        </div>
    </aside>
</template>
