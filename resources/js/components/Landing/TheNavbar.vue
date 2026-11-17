<script setup lang="ts">
import { ref, onMounted, onUnmounted } from "vue";
import { Link } from "@inertiajs/vue3";
import AppButton from "./AppButton.vue";

const isScrolled = ref(false);
const isMobileMenuOpen = ref(false);

function handleScroll() {
    if (typeof window !== "undefined") {
        isScrolled.value = window.scrollY > 20;
    }
}

onMounted(() => {
    if (typeof window !== "undefined") {
        window.addEventListener("scroll", handleScroll, { passive: true });
    }
});

onUnmounted(() => {
    if (typeof window !== "undefined") {
        window.removeEventListener("scroll", handleScroll);
    }
});

const navLinks = [
    { label: "Beranda", href: "#" },
    { label: "Tentang", href: "#cara-kerja" },
    { label: "Edukasi", href: "#artikel" },
    { label: "Kontak", href: "#faq" },
];
</script>

<template>
    <header
        class="fixed top-0 left-0 right-0 z-50 pt-4 px-4 sm:px-6 transition-all duration-300"
    >
        <div
            class="max-w-[1200px] mx-auto h-[72px] bg-paper-white border-2 border-b-4 border-border-gray rounded-2xl px-6 sm:px-8 flex items-center justify-between shadow-xs transition-shadow duration-300"
            :class="isScrolled ? 'shadow-md border-border-gray' : ''"
        >
            <!-- Logo -->
            <a href="/" class="flex items-center gap-2.5 group">
                <img
                    src="/assets/Logo/logo_hijau_biru.png"
                    alt="ReValue"
                    class="w-10 h-10 object-contain transition-transform duration-200 group-hover:scale-105"
                />
                <span
                    class="text-[28px] tracking-tight font-extrabold leading-none"
                >
                    <span class="text-night-ink">Re</span
                    ><span class="text-eco-green-dark">Value</span>
                </span>
            </a>

            <!-- Desktop Nav -->
            <nav
                class="hidden lg:flex items-center gap-8 font-bold text-[15px]"
            >
                <a
                    v-for="(link, idx) in navLinks"
                    :key="link.label"
                    :href="link.href"
                    class="transition-colors duration-150 py-1"
                    :class="
                        idx === 0
                            ? 'text-night-ink'
                            : 'text-charcoal hover:text-night-ink'
                    "
                >
                    {{ link.label }}
                </a>
            </nav>

            <!-- Desktop Auth CTA -->
            <div class="hidden lg:flex items-center gap-3">
                <Link href="/login">
                    <AppButton variant="outline" size="sm" class="h-10 px-5">
                        MASUK
                    </AppButton>
                </Link>
                <Link href="/register">
                    <AppButton variant="primary" size="sm" class="h-10 px-5">
                        DAFTAR
                    </AppButton>
                </Link>
            </div>

            <!-- Mobile Hamburger -->
            <button
                class="lg:hidden p-2 rounded-xl text-night-ink hover:bg-cloud-surface cursor-pointer"
                type="button"
                @click="isMobileMenuOpen = !isMobileMenuOpen"
            >
                <svg
                    v-if="!isMobileMenuOpen"
                    class="w-6 h-6"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2.5"
                        d="M4 6h16M4 12h16M4 18h16"
                    />
                </svg>
                <svg
                    v-else
                    class="w-6 h-6"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2.5"
                        d="M6 18L18 6M6 6l12 12"
                    />
                </svg>
            </button>
        </div>

        <!-- Mobile Drawer -->
        <div
            v-if="isMobileMenuOpen"
            class="lg:hidden max-w-[1200px] mx-auto mt-2 bg-paper-white border-2 border-b-4 border-border-gray rounded-2xl p-5 shadow-lg flex flex-col gap-4 animate-in fade-in duration-200"
        >
            <nav class="flex flex-col gap-2">
                <a
                    v-for="link in navLinks"
                    :key="link.label"
                    :href="link.href"
                    class="px-4 py-2 text-base font-bold text-night-ink hover:bg-cloud-surface rounded-xl transition-colors"
                    @click="isMobileMenuOpen = false"
                >
                    {{ link.label }}
                </a>
            </nav>
            <div class="flex flex-col gap-2.5 pt-2 border-t border-border-gray">
                <Link href="/login" class="w-full">
                    <AppButton variant="outline" size="md" class="w-full"
                        >MASUK</AppButton
                    >
                </Link>
                <Link href="/register" class="w-full">
                    <AppButton variant="primary" size="md" class="w-full"
                        >DAFTAR</AppButton
                    >
                </Link>
            </div>
        </div>
    </header>
</template>
