<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';
import type { User } from '@/types/auth';
import UserAvatar from '@/components/Dashboard/UserAvatar.vue';

const props = defineProps<{
    user: User;
    currentTabTitle: string;
}>();

const initials = computed(() => {
    if (!props.user?.name) return 'U';
    return props.user.name
        .split(' ')
        .slice(0, 2)
        .map((w) => w[0]?.toUpperCase())
        .join('');
});

const roleLabel = computed(() => {
    const role = (props.user?.role as string) || 'customer';
    if (role === 'admin') return 'Admin';
    if (role === 'driver') return 'Driver';
    return 'Customer';
});

const userPhoto = computed(() => {
    return props.user.photo || (props.user as any).foto || null;
});

const dashboardRoute = computed(() => {
    const role = (props.user?.role as string) || 'customer';
    if (role === 'admin') return '/admin/dashboard';
    if (role === 'driver') return '/driver/dashboard';
    return '/customer/dashboard';
});
</script>

<template>
    <header
        class="flex h-20 shrink-0 items-center justify-between border-b border-border-gray bg-paper-white px-6 lg:px-8"
    >
        <!-- Breadcrumb & Mobile back -->
        <div class="flex items-center gap-3">
            <Link
                :href="dashboardRoute"
                class="rounded-lg p-1.5 text-charcoal transition hover:bg-cloud-surface hover:text-night-ink lg:hidden"
                title="Kembali ke Dashboard"
            >
                <ArrowLeft class="size-5" />
            </Link>

            <nav
                aria-label="Breadcrumb"
                class="flex items-center gap-2 text-[13px] whitespace-nowrap"
            >
                <span class="font-medium text-charcoal">Pengaturan akun</span>
                <span class="text-charcoal/50">/</span>
                <span class="font-bold text-night-ink">{{
                    currentTabTitle
                }}</span>
            </nav>
        </div>

        <!-- Header Profile Identity -->
        <div class="flex items-center gap-3">
            <div
                v-if="!userPhoto"
                class="flex size-10 items-center justify-center rounded-full bg-sprout-tint text-[14px] font-extrabold text-night-ink shadow-xs select-none"
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

            <div class="hidden flex-col text-left leading-tight sm:flex">
                <span
                    class="max-w-[150px] truncate text-[14px] font-bold text-night-ink"
                >
                    {{ user.name }}
                </span>
                <span class="text-[12px] font-normal text-charcoal">
                    {{ roleLabel }}
                </span>
            </div>
        </div>
    </header>
</template>
