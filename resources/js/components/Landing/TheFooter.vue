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
</script>

<template>
  <footer class="bg-night-ink text-paper-white pt-16 pb-12">
    <div class="max-w-[1200px] mx-auto px-6">
      <div class="grid grid-cols-1 md:grid-cols-12 gap-10 lg:gap-16">
        <!-- Brand Info (Span 5) -->
        <div class="md:col-span-6 lg:col-span-5">
          <div class="flex items-center gap-3 mb-4">
            <img
              src="/images/landing/brand-mark-white.svg"
              alt="ReValue"
              class="w-11 h-11 object-contain"
            />
            <span class="font-bold text-3xl text-paper-white tracking-tight">
              ReValue
            </span>
          </div>
          <p class="font-medium text-[16px] sm:text-[17px] text-paper-white/80 leading-relaxed max-w-sm">
            Beri nilai baru.<br />Untuk bumi, untuk kita.
          </p>
        </div>

        <!-- Jelajahi (Span 3) -->
        <div class="md:col-span-3 lg:col-span-3">
          <p class="font-bold text-[13px] tracking-[1.6px] uppercase text-paper-white mb-5">
            JELAJAHI
          </p>
          <ul class="space-y-3 font-medium text-[16px] sm:text-[17px] text-paper-white/80">
            <li v-for="link in exploreLinks" :key="link.label">
              <a :href="link.href" class="hover:text-eco-green transition-colors">
                {{ link.label }}
              </a>
            </li>
          </ul>
        </div>

        <!-- Butuh Bantuan? (Span 4) -->
        <div class="md:col-span-3 lg:col-span-4">
          <p class="font-bold text-[13px] tracking-[1.6px] uppercase text-paper-white mb-5">
            BUTUH BANTUAN?
          </p>
          <ul class="space-y-3 font-medium text-[16px] sm:text-[17px] text-paper-white/80">
            <li v-for="link in helpLinks" :key="link.label">
              <a :href="link.href" class="hover:text-eco-green transition-colors">
                {{ link.label }}
              </a>
            </li>
          </ul>
        </div>
      </div>

      <!-- Divider -->
      <div class="h-px bg-charcoal/60 my-10" />

      <!-- Bottom Bar -->
      <div class="flex flex-col sm:flex-row items-center justify-between gap-4 font-bold text-[13px] text-paper-white/70">
        <p>© {{ year }} ReValue. Langkah kecil untuk masa depan besar.</p>
        <div class="flex gap-6">
          <a href="#" class="hover:text-paper-white transition-colors">Kebijakan Privasi</a>
          <a href="#" class="hover:text-paper-white transition-colors">Syarat &amp; Ketentuan</a>
        </div>
      </div>
    </div>
  </footer>
</template>
