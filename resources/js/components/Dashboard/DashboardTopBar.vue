<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from "vue";
import { Link, usePage } from "@inertiajs/vue3";
import { Menu, ChevronDown, User, LogOut } from "lucide-vue-next";

const emit = defineEmits<{ (e: "toggleMobile"): void }>();

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
    document.addEventListener("click", closeDropdown);
});

onUnmounted(() => {
    document.removeEventListener("click", closeDropdown);
});

const getRolePlaceholder = (role?: string) => {
    const r = (role || "").toLowerCase();
    if (r === "admin") {
        return "/assets/placeholder/Admin/65.png";
    }
    if (r === "driver") {
        return "/assets/placeholder/Driver/65.png";
    }
    return "/assets/placeholder/Customer/65.png";
};

const userPhoto = computed(() => {
    const photo = user.value?.photo;
    if (photo && typeof photo === "string" && photo.trim() !== "") {
        if (
            photo.startsWith("http://") ||
            photo.startsWith("https://") ||
            photo.startsWith("/")
        ) {
            return photo;
        }
        return `/storage/${photo}`;
    }
    return getRolePlaceholder(user.value?.role);
});

const onAvatarError = (event: Event) => {
    const target = event.target as HTMLImageElement;
    target.src = getRolePlaceholder(user.value?.role);
};

const roleLabel = computed(() => {
    const r: string = (user.value?.role || "").toLowerCase();
    if (r === "admin") return "Admin";
    if (r === "driver") return "Driver";
    return "Nasabah";
});

const currentPageTitle = computed(() => {
    const url: string = page.url;
    if (url.includes("dashboard")) return "Dashboard";
    if (url.includes("users")) return "Pengguna";
    if (url.includes("waste-categories")) return "Kategori Sampah";
    if (url.includes("profile")) return "Profil Saya";
    return "Dashboard";
});
</script>

<template>
    <header
        class="sticky top-0 z-30 flex items-center justify-between h-[80px] px-6 lg:px-8 border-b border-border-gray bg-paper-white/95 backdrop-blur-xs shrink-0 select-none"
    >
        <!-- Left: Hamburger (Mobile) + Breadcrumb (Figma Node 482:1009) -->
        <div class="flex items-center gap-3">
            <!-- Mobile hamburger button -->
            <button
                type="button"
                class="lg:hidden p-2 -ml-2 rounded-xl text-charcoal hover:bg-cloud-surface transition-colors cursor-pointer"
                @click="emit('toggleMobile')"
                aria-label="Buka Menu"
            >
                <Menu class="size-5" />
            </button>

            <!-- Breadcrumb lockup -->
        </div>

        <!-- Right: act:account User Dropdown Lockup (Figma Node 482:1009) -->
        <div ref="dropdownRef" class="relative">
            <button
                type="button"
                class="flex items-center gap-3 p-1.5 -mr-1.5 rounded-2xl hover:bg-cloud-surface/70 transition-colors cursor-pointer group"
                @click.stop="toggleDropdown"
                :aria-expanded="isDropdownOpen"
            >
                <!-- Avatar image using user photo or role placeholder -->
                <div
                    class="size-10 md:size-12 rounded-full overflow-hidden bg-sprout-tint border border-eco-green/40 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform"
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
                    class="hidden sm:flex flex-col items-start leading-[1.3] text-left"
                >
                    <span
                        class="font-extrabold text-[14px] text-night-ink group-hover:text-eco-green-dark transition-colors truncate max-w-[150px]"
                    >
                        {{ user?.name ?? "Pengguna" }}
                    </span>
                    <span class="font-normal text-[12px] text-charcoal">
                        {{ roleLabel }}
                    </span>
                </div>

                <!-- ChevronDown indicator -->
                <ChevronDown
                    class="size-[18px] text-charcoal shrink-0 transition-transform duration-200"
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
                    class="absolute right-0 mt-2 w-56 rounded-2xl bg-paper-white border-2 border-border-gray shadow-lg py-2 z-50 overflow-hidden"
                >
                    <!-- User Header Info -->
                    <div class="px-4 py-2.5 border-b border-border-gray/70">
                        <p
                            class="text-[13px] font-bold text-night-ink truncate"
                        >
                            {{ user?.name }}
                        </p>
                        <p
                            class="text-[11px] font-medium text-charcoal truncate mt-0.5"
                        >
                            {{ user?.email }}
                        </p>
                    </div>

                    <!-- Menu Links -->
                    <div class="py-1">
                        <Link
                            href="/profile"
                            class="flex items-center gap-2.5 px-4 py-2 text-[13px] font-bold text-charcoal hover:text-night-ink hover:bg-cloud-surface transition-colors"
                            @click="isDropdownOpen = false"
                        >
                            <User class="size-4 text-charcoal/80" />
                            <span>Profil Saya</span>
                        </Link>

                        <Link
                            href="/logout"
                            method="post"
                            as="button"
                            class="w-full flex items-center gap-2.5 px-4 py-2 text-[13px] font-bold text-red-500 hover:bg-red-50 transition-colors text-left cursor-pointer"
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
