<script setup lang="ts">
const exploreLinks = [
    { label: 'Tentang ReValue', href: '#cara-kerja' },
    { label: 'Cara kerja', href: '#cara-kerja' },
    { label: 'Upcycle Store', href: '#upcycle' },
];

const helpLinks = [
    { label: 'Pusat edukasi', href: '#artikel' },
    { label: 'Pertanyaan umum', href: '#faq' },
    { label: 'Hubungi kami', href: '#faq' },
];

const year = new Date().getFullYear();

function handleFooterClick(e: MouseEvent, href: string) {
    if (typeof window === 'undefined') return;
    if (href === '#' || href === '#beranda') {
        e.preventDefault();
        if (window.location.pathname === '/') {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        } else {
            window.location.href = '/';
        }
        return;
    }
    if (href.startsWith('#')) {
        const target =
            window.location.pathname === '/'
                ? document.querySelector(href)
                : null;
        if (target) {
            e.preventDefault();
            target.scrollIntoView({ behavior: 'smooth' });
            return;
        }

        e.preventDefault();
        window.location.href = `/${href}`;
    }
}
</script>

<template>
    <footer class="bg-night-ink pt-16 pb-12 text-paper-white">
        <div class="mx-auto max-w-[1200px] px-6">
            <div class="grid grid-cols-1 gap-10 md:grid-cols-12 lg:gap-16">
                <!-- Brand Info (Span 5) -->
                <div class="md:col-span-6 lg:col-span-5">
                    <div
                        class="mb-4 flex inline-flex cursor-pointer items-center gap-3"
                        @click="handleFooterClick($event, '#beranda')"
                    >
                        <img
                            src="assets/Logo/logo_white.png"
                            alt="ReValue"
                            class="h-11 w-11 object-contain"
                        />
                        <span
                            class="text-3xl font-bold tracking-tight text-paper-white"
                        >
                            ReValue
                        </span>
                    </div>
                    <p
                        class="max-w-sm text-[16px] leading-relaxed font-medium text-paper-white/80 sm:text-[17px]"
                    >
                        Beri nilai baru.<br />Untuk bumi, untuk kita.
                    </p>
                </div>

                <!-- Jelajahi (Span 3) -->
                <div class="md:col-span-3 lg:col-span-3">
                    <p
                        class="mb-5 text-[13px] font-bold tracking-[1.6px] text-paper-white uppercase"
                    >
                        JELAJAHI
                    </p>
                    <ul
                        class="space-y-3 text-[16px] font-medium text-paper-white/80 sm:text-[17px]"
                    >
                        <li v-for="link in exploreLinks" :key="link.label">
                            <a
                                :href="link.href"
                                class="cursor-pointer transition-colors hover:text-eco-green"
                                @click="handleFooterClick($event, link.href)"
                            >
                                {{ link.label }}
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Butuh Bantuan? (Span 4) -->
                <div class="md:col-span-3 lg:col-span-4">
                    <p
                        class="mb-5 text-[13px] font-bold tracking-[1.6px] text-paper-white uppercase"
                    >
                        BUTUH BANTUAN?
                    </p>
                    <ul
                        class="space-y-3 text-[16px] font-medium text-paper-white/80 sm:text-[17px]"
                    >
                        <li v-for="link in helpLinks" :key="link.label">
                            <a
                                :href="link.href"
                                class="cursor-pointer transition-colors hover:text-eco-green"
                                @click="handleFooterClick($event, link.href)"
                            >
                                {{ link.label }}
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Divider -->
            <div class="my-10 h-px bg-charcoal/60" />

            <!-- Bottom Bar -->
            <div
                class="flex flex-col items-center justify-between gap-4 text-[13px] font-bold text-paper-white/70 sm:flex-row"
            >
                <p>
                    © {{ year }} ReValue. Langkah kecil untuk masa depan besar.
                </p>
                <div class="flex gap-6">
                    <a href="#" class="transition-colors hover:text-paper-white"
                        >Kebijakan Privasi</a
                    >
                    <a href="#" class="transition-colors hover:text-paper-white"
                        >Syarat &amp; Ketentuan</a
                    >
                </div>
            </div>
        </div>
    </footer>
</template>
