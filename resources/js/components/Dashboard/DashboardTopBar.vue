<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { Menu, ChevronDown, User, LogOut } from 'lucide-vue-next';

const emit = defineEmits<{ (e: 'toggleMobile'): void }>();

const page = usePage();
const user = computed(() => (page.props.auth as any)?.user ?? {});

const isDropdownOpen = ref(false);
const dropdownRef = ref<HTMLElement | null>(null);

const toggleDropdown = () => {
    isDropdownOpen.value = !isDropdownOpen.value;
};

const closeDropdown = (e: MouseEvent) => {
    if (dropdownRef.value && !dropdownRef.value.contains(e.target as Node)) {
        isDropdownOpen.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', closeDropdown);
});

onUnmounted(() => {
    document.removeEventListener('click', closeDropdown);
});

const getRolePlaceholder = (role?: string) => {
    const r = (role || '').toLowerCase();
    if (r === 'admin') {
        return '/assets/placeholder/Admin/65.png';
    }
    if (r === 'driver') {
        return '/assets/placeholder/Driver/65.png';
    }
    return '/assets/placeholder/Customer/65.png';
};

const userPhoto = computed(() => {
    const photo = user.value?.photo || user.value?.foto;
    if (photo && typeof photo === 'string' && photo.trim() !== '') {
        if (photo.startsWith('http://') || photo.startsWith('https://')) {
            return photo;
        }
        if (photo.startsWith('/')) {
            return photo;
        }
        const clean = photo.startsWith('storage/') ? photo.slice(8) : photo;
        return `/storage/${clean}`;
    }
    return getRolePlaceholder(user.value?.role);
});

const onAvatarError = (event: Event) => {
    const target = event.target as HTMLImageElement;
    target.src = getRolePlaceholder(user.value?.role);
};

const roleLabel = computed(() => {
    const r: string = (user.value?.role || '').toLowerCase();
    if (r === 'admin') return 'Admin';
    if (r === 'driver') return 'Driver';
    return 'Customer';
});

const currentPageTitle = computed(() => {
    const url: string = page.url;
    if (url.includes('articles')) return 'Artikel';
    if (url.includes('/driver/pickups/history')) return 'Riwayat Penjemputan';
    if (url.includes('pickups')) return 'Penjemputan';
    if (url.includes('deliveries')) return 'Antar Pesanan';
    if (url.includes('points')) return 'Riwayat Poin';
    if (url.includes('products') || url.includes('checkout'))
        return 'Upcycle Store';
    if (url.includes('transactions')) return 'Pesanan Saya';
    if (url.includes('dashboard')) return 'Dashboard';
    if (url.includes('users')) return 'Pengguna';
    if (url.includes('waste-categories')) return 'Kategori Sampah';
    if (url.includes('profile')) return 'Profil Saya';
    return 'Dashboard';
});
</script>

<template>
    <header
        class="sticky top-0 z-30 flex h-[80px] shrink-0 items-center justify-between border-b border-border-gray bg-paper-white/95 px-6 backdrop-blur-xs select-none lg:px-8"
    >
        <!-- Left: Hamburger (Mobile) + Breadcrumb (Figma Node 482:1009) -->
        <div class="flex items-center gap-3">
            <!-- Mobile hamburger button -->
            <button
                type="button"
                class="-ml-2 cursor-pointer rounded-xl p-2 text-charcoal transition-colors hover:bg-cloud-surface lg:hidden"
                @click="emit('toggleMobile')"
                aria-label="Buka Menu"
            >
                <Menu class="size-5" />
            </button>

            <!-- Breadcrumb lockup -->
            <span
                class="hidden text-[14px] font-extrabold text-night-ink sm:block"
            >
                {{ currentPageTitle }}
            </span>
        </div>

        <!-- Right: act:account User Dropdown Lockup (Figma Node 482:1009) -->
        <div ref="dropdownRef" class="relative">
            <button
                type="button"
                class="group -mr-1.5 flex cursor-pointer items-center gap-3 rounded-2xl p-1.5 transition-colors hover:bg-cloud-surface/70"
                @click.stop="toggleDropdown"
                :aria-expanded="isDropdownOpen"
            >
                <!-- Avatar image using user photo or role placeholder -->
                <div
                    class="flex size-10 shrink-0 items-center justify-center overflow-hidden rounded-full border border-eco-green/40 bg-sprout-tint transition-transform group-hover:scale-105 md:size-12"
                >
                    <img
                        :src="userPhoto"
                        :alt="user?.name ?? 'Avatar'"
                        class="size-full object-cover"
                        @error="onAvatarError"
                    />
                </div>

                <!-- Identity text (Name & Role) -->
                <div
                    class="hidden flex-col items-start text-left leading-[1.3] sm:flex"
                >
                    <span
                        class="max-w-[150px] truncate text-[14px] font-extrabold text-night-ink transition-colors group-hover:text-eco-green-dark"
                    >
                        {{ user?.name ?? 'Pengguna' }}
                    </span>
                    <span class="text-[12px] font-normal text-charcoal">
                        {{ roleLabel }}
                    </span>
                </div>

                <!-- ChevronDown indicator -->
                <ChevronDown
                    class="size-[18px] shrink-0 text-charcoal transition-transform duration-200"
                    :class="{ 'rotate-180 text-night-ink': isDropdownOpen }"
                />
            </button>

            <!-- Dropdown Menu -->
            <transition
                enter-active-class="transition duration-150 ease-out"
                enter-from-class="transform scale-95 opacity-0"
                enter-to-class="transform scale-100 opacity-100"
                leave-active-class="transition duration-100 ease-in"
                leave-from-class="transform scale-100 opacity-100"
                leave-to-class="transform scale-95 opacity-0"
            >
                <div
                    v-if="isDropdownOpen"
                    class="absolute right-0 z-50 mt-2 w-56 overflow-hidden rounded-2xl border-2 border-border-gray bg-paper-white py-2 shadow-lg"
                >
                    <!-- User Header Info -->
                    <div class="border-b border-border-gray/70 px-4 py-2.5">
                        <p
                            class="truncate text-[13px] font-bold text-night-ink"
                        >
                            {{ user?.name }}
                        </p>
                        <p
                            class="mt-0.5 truncate text-[11px] font-medium text-charcoal"
                        >
                            {{ user?.email }}
                        </p>
                    </div>

                    <!-- Menu Links -->
                    <div class="py-1">
                        <Link
                            href="/profile"
                            class="flex items-center gap-2.5 px-4 py-2 text-[13px] font-bold text-charcoal transition-colors hover:bg-cloud-surface hover:text-night-ink"
                            @click="isDropdownOpen = false"
                        >
                            <User class="size-4 text-charcoal/80" />
                            <span>Profil Saya</span>
                        </Link>

                        <Link
                            href="/logout"
                            method="post"
                            as="button"
                            class="flex w-full cursor-pointer items-center gap-2.5 px-4 py-2 text-left text-[13px] font-bold text-red-500 transition-colors hover:bg-red-50"
                        >
                            <LogOut class="size-4 text-red-500" />
                            <span>Keluar dari Akun</span>
                        </Link>
                    </div>
                </div>
            </transition>
        </div>
    </header>
</template>
