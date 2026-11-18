<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import gsap from 'gsap';
import AppButton from './AppButton.vue';

const page = usePage();
const authUser = computed(() => (page.props.auth as any)?.user ?? null);

const dashboardHref = computed(() => {
    switch ((authUser.value?.role ?? '').toLowerCase()) {
        case 'admin':
            return '/admin/dashboard';
        case 'driver':
            return '/driver/dashboard';
        default:
            return '/customer/dashboard';
    }
});

const isScrolled = ref(false);
const isMobileMenuOpen = ref(false);
const headerRef = ref<HTMLElement | null>(null);
let gsapCtx: gsap.Context | null = null;

function handleScroll() {
    if (typeof window !== 'undefined') {
        isScrolled.value = window.scrollY > 20;
    }
}

onMounted(() => {
    if (typeof window !== 'undefined') {
        window.addEventListener('scroll', handleScroll, { passive: true });

        if (headerRef.value) {
            gsapCtx = gsap.context(() => {
                gsap.fromTo(
                    headerRef.value,
                    { y: -50, opacity: 0 },
                    {
                        y: 0,
                        opacity: 1,
                        duration: 0.8,
                        ease: 'power3.out',
                    },
                );
            }, headerRef.value);
        }
    }
});

onUnmounted(() => {
    if (typeof window !== 'undefined') {
        window.removeEventListener('scroll', handleScroll);
        gsapCtx?.revert();
    }
});

const navLinks = [
    { label: 'Beranda', href: '#beranda' },
    { label: 'Tentang', href: '#cara-kerja' },
    { label: 'Subscribtion', href: '#langganan' },
    { label: 'Produk', href: '#upcycle' },
    { label: 'Edukasi', href: '#artikel' },
    { label: 'Kontak', href: '#faq' },
];

function handleNavClick(e: MouseEvent, href: string) {
    if (typeof window === 'undefined') return;

    const onLandingPage = window.location.pathname === '/';

    if (href === '#' || href === '#beranda') {
        e.preventDefault();
        isMobileMenuOpen.value = false;

        if (onLandingPage) {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        } else {
            window.location.href = '/';
        }
        return;
    }

    if (href.startsWith('#')) {
        const target = onLandingPage ? document.querySelector(href) : null;

        if (target) {
            e.preventDefault();
            target.scrollIntoView({ behavior: 'smooth' });
            isMobileMenuOpen.value = false;
            return;
        }

        // Anchor tidak tersedia (mis. di halaman detail artikel):
        // kembali ke landing page pada section terkait.
        e.preventDefault();
        window.location.href = `/${href}`;
    }
}
</script>

<template>
    <header
        class="fixed top-0 right-0 left-0 z-50 px-4 pt-4 transition-all duration-300 sm:px-6"
    >
        <div
            class="mx-auto flex h-[72px] max-w-[1200px] items-center justify-between rounded-2xl border-2 border-b-4 border-border-gray bg-paper-white px-6 shadow-xs transition-shadow duration-300 sm:px-8"
            :class="isScrolled ? 'border-border-gray shadow-md' : ''"
        >
            <!-- Logo -->
            <a
                href="/"
                class="group flex cursor-pointer items-center gap-2.5"
                @click="handleNavClick($event, '#beranda')"
            >
                <img
                    src="/assets/Logo/logo_hijau_biru.png"
                    alt="ReValue"
                    class="h-10 w-10 object-contain transition-transform duration-200 group-hover:scale-105"
                />
                <span
                    class="text-[28px] leading-none font-extrabold tracking-tight"
                >
                    <span class="text-night-ink">Re</span
                    ><span class="text-eco-green-dark">Value</span>
                </span>
            </a>

            <!-- Desktop Nav -->
            <nav
                class="hidden items-center gap-8 text-[15px] font-bold lg:flex"
            >
                <a
                    v-for="(link, idx) in navLinks"
                    :key="link.label"
                    :href="link.href"
                    class="py-1 transition-colors duration-150"
                    :class="
                        idx === 0
                            ? 'text-night-ink'
                            : 'text-charcoal hover:text-night-ink'
                    "
                    @click="handleNavClick($event, link.href)"
                >
                    {{ link.label }}
                </a>
            </nav>

            <!-- Desktop Auth CTA -->
            <div class="hidden items-center gap-3 lg:flex">
                <Link v-if="authUser" :href="dashboardHref">
                    <AppButton variant="primary" size="sm" class="h-10 px-5">
                        DASHBOARD
                    </AppButton>
                </Link>
                <template v-else>
                    <Link href="/login">
                        <AppButton
                            variant="outline"
                            size="sm"
                            class="h-10 px-5"
                        >
                            MASUK
                        </AppButton>
                    </Link>
                    <Link href="/register">
                        <AppButton
                            variant="primary"
                            size="sm"
                            class="h-10 px-5"
                        >
                            DAFTAR
                        </AppButton>
                    </Link>
                </template>
            </div>

            <!-- Mobile Hamburger -->
            <button
                class="cursor-pointer rounded-xl p-2 text-night-ink hover:bg-cloud-surface lg:hidden"
                type="button"
                @click="isMobileMenuOpen = !isMobileMenuOpen"
            >
                <svg
                    v-if="!isMobileMenuOpen"
                    class="h-6 w-6"
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
                    class="h-6 w-6"
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
            class="animate-in fade-in mx-auto mt-2 flex max-w-[1200px] flex-col gap-4 rounded-2xl border-2 border-b-4 border-border-gray bg-paper-white p-5 shadow-lg duration-200 lg:hidden"
        >
            <nav class="flex flex-col gap-2">
                <a
                    v-for="link in navLinks"
                    :key="link.label"
                    :href="link.href"
                    class="cursor-pointer rounded-xl px-4 py-2 text-base font-bold text-night-ink transition-colors hover:bg-cloud-surface"
                    @click="handleNavClick($event, link.href)"
                >
                    {{ link.label }}
                </a>
            </nav>
            <div class="flex flex-col gap-2.5 border-t border-border-gray pt-2">
                <Link v-if="authUser" :href="dashboardHref" class="w-full">
                    <AppButton variant="primary" size="md" class="w-full"
                        >DASHBOARD</AppButton
                    >
                </Link>
                <template v-else>
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
                </template>
            </div>
        </div>
    </header>
</template>
