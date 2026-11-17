<script setup lang="ts">
import { ref, onMounted, onUnmounted, nextTick } from 'vue';
import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import AppButton from './AppButton.vue';

interface Product {
  id: number;
  name: string;
  description: string | null;
  price_rupiah: number;
  price_points: number;
  photo: string | null;
  nama_produk?: string;
  deskripsi?: string;
  harga_rupiah?: number;
  harga_poin?: number;
  foto?: string | null;
}

defineProps<{ products: Product[] }>();

const sectionRef = ref<HTMLElement | null>(null);
let gsapCtx: gsap.Context | null = null;

function formatRupiah(amount: number): string {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
  }).format(amount);
}

onMounted(async () => {
  if (typeof window === 'undefined' || !sectionRef.value) return;
  await nextTick();
  gsap.registerPlugin(ScrollTrigger);

  gsapCtx = gsap.context(() => {
    const root = sectionRef.value;
    if (!root) return;

    const cards = root.querySelectorAll('.upcycle-bento-card');
    if (cards.length > 0) {
      gsap.fromTo(
        cards,
        { y: 55, opacity: 0, scale: 0.95 },
        {
          y: 0,
          opacity: 1,
          scale: 1,
          duration: 0.7,
          stagger: 0.12,
          ease: 'power3.out',
          scrollTrigger: {
            trigger: cards[0],
            start: 'top 85%',
            toggleActions: 'play none none none',
          },
        }
      );
    }
  }, sectionRef.value);
});

onUnmounted(() => {
  gsapCtx?.revert();
});
</script>

<template>
  <section id="upcycle" ref="sectionRef" class="bg-paper-white py-16 lg:py-20 overflow-hidden scroll-mt-28">
    <div class="max-w-[1200px] mx-auto px-6">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-4 gap-3">
        <h2 class="font-extrabold text-3xl sm:text-5xl text-night-ink tracking-tight">
          Nilai baru, cerita baru.
        </h2>
        <a
          href="#"
          class="font-bold text-[15px] tracking-wider uppercase text-night-ink hover:text-eco-green transition-colors inline-flex items-center gap-1.5 group"
        >
          Jelajahi toko <span class="transition-transform group-hover:translate-x-1">→</span>
        </a>
      </div>
      <p class="font-medium text-[16px] sm:text-[17px] text-charcoal max-w-4xl mb-10 leading-relaxed">
        Dari bahan terbuang jadi barang tersayang. Belanja dengan poin, Rupiah, atau keduanya. Pilih karya yang berguna, lalu teruskan cerita baik dari setiap setoranmu.
      </p>

      <!-- Bento Grid (3 Columns) -->
      <div class="upcycle-grid grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Bento Top Left: Info Card (Span 2 cols on desktop) -->
        <div
          class="upcycle-bento-card lg:col-span-2 bg-cloud-surface rounded-[24px] p-8 flex flex-col justify-center min-h-[200px] hover:shadow-md transition-all duration-200"
        >
          <p class="font-bold text-[13px] tracking-[1.6px] uppercase text-charcoal mb-2">
            PILIH KARYANYA &nbsp;/&nbsp; GUNAKAN POINMU &nbsp;/&nbsp; PAKAI KEMBALI
          </p>
          <h3 class="font-bold text-2xl sm:text-[32px] text-night-ink tracking-tight mb-3">
            Setoranmu tidak berhenti di poin.
          </h3>
          <p class="font-medium text-[16px] sm:text-[17px] text-charcoal leading-relaxed">
            Poin yang kamu kumpulkan bisa kembali menjadi produk upcycle. Pilih barang yang berguna, dukung karya lokal, dan teruskan usia pakai bahan.
          </p>
        </div>

        <!-- Product 1 -->
        <div
          v-if="products.length > 0"
          class="upcycle-bento-card bg-paper-white border-2 border-border-gray rounded-[20px] drop-shadow-[0px_4px_0px_var(--color-border-gray)] p-5 flex flex-col justify-between hover:-translate-y-1.5 hover:shadow-lg transition-all duration-200 cursor-default"
        >
          <div class="bg-gold-tint rounded-[14px] h-[140px] flex items-center justify-center overflow-hidden mb-4 p-2">
            <img
              v-if="products[0]?.photo || products[0]?.foto"
              :src="`/storage/${products[0]?.photo || products[0]?.foto}`"
              :alt="products[0]?.name || products[0]?.nama_produk || 'Produk'"
              class="w-full h-full object-cover rounded-lg"
            />
            <div v-else class="text-5xl">🛍️</div>
          </div>
          <div>
            <h4 class="font-extrabold text-[20px] text-night-ink leading-snug mb-1">
              {{ products[0]?.name ?? products[0]?.nama_produk }}
            </h4>
            <p class="text-[14px] text-night-ink/80 line-clamp-2 leading-relaxed mb-4">
              {{ products[0]?.description ?? products[0]?.deskripsi }}
            </p>
          </div>
          <div class="mt-auto">
            <div class="flex items-center justify-between mb-4">
              <span class="font-extrabold text-[18px] text-night-ink">
                {{ formatRupiah(products[0]?.price_rupiah ?? products[0]?.harga_rupiah ?? 0) }}
              </span>
              <span
                v-if="products[0]?.price_points ?? products[0]?.harga_poin"
                class="font-bold text-xs bg-sprout-tint text-night-ink px-2.5 py-1 rounded-full"
              >
                {{ (products[0]?.price_points ?? products[0]?.harga_poin ?? 0).toLocaleString('id-ID') }} Poin
              </span>
            </div>
            <AppButton variant="blue" size="md" class="w-full h-11 text-[14px]">
              LIHAT PRODUK →
            </AppButton>
          </div>
        </div>

        <!-- Product 2 -->
        <div
          v-if="products.length > 1"
          class="upcycle-bento-card bg-paper-white border-2 border-border-gray rounded-[20px] drop-shadow-[0px_4px_0px_var(--color-border-gray)] p-5 flex flex-col justify-between hover:-translate-y-1.5 hover:shadow-lg transition-all duration-200 cursor-default"
        >
          <div class="bg-gold-tint rounded-[14px] h-[140px] flex items-center justify-center overflow-hidden mb-4 p-2">
            <img
              v-if="products[1]?.photo || products[1]?.foto"
              :src="`/storage/${products[1]?.photo || products[1]?.foto}`"
              :alt="products[1]?.name || products[1]?.nama_produk || 'Produk'"
              class="w-full h-full object-cover rounded-lg"
            />
            <div v-else class="text-5xl">🪴</div>
          </div>
          <div>
            <h4 class="font-extrabold text-[20px] text-night-ink leading-snug mb-1">
              {{ products[1]?.name ?? products[1]?.nama_produk }}
            </h4>
            <p class="text-[14px] text-night-ink/80 line-clamp-2 leading-relaxed mb-4">
              {{ products[1]?.description ?? products[1]?.deskripsi }}
            </p>
          </div>
          <div class="mt-auto">
            <div class="flex items-center justify-between mb-4">
              <span class="font-extrabold text-[18px] text-night-ink">
                {{ formatRupiah(products[1]?.price_rupiah ?? products[1]?.harga_rupiah ?? 0) }}
              </span>
              <span
                v-if="products[1]?.price_points ?? products[1]?.harga_poin"
                class="font-bold text-xs bg-sprout-tint text-night-ink px-2.5 py-1 rounded-full"
              >
                {{ (products[1]?.price_points ?? products[1]?.harga_poin ?? 0).toLocaleString('id-ID') }} Poin
              </span>
            </div>
            <AppButton variant="blue" size="md" class="w-full h-11 text-[14px]">
              LIHAT PRODUK →
            </AppButton>
          </div>
        </div>

        <!-- Product 3 -->
        <div
          v-if="products.length > 2"
          class="upcycle-bento-card bg-paper-white border-2 border-border-gray rounded-[20px] drop-shadow-[0px_4px_0px_var(--color-border-gray)] p-5 flex flex-col justify-between hover:-translate-y-1.5 hover:shadow-lg transition-all duration-200 cursor-default"
        >
          <div class="bg-gold-tint rounded-[14px] h-[140px] flex items-center justify-center overflow-hidden mb-4 p-2">
            <img
              v-if="products[2]?.photo || products[2]?.foto"
              :src="`/storage/${products[2]?.photo || products[2]?.foto}`"
              :alt="products[2]?.name || products[2]?.nama_produk || 'Produk'"
              class="w-full h-full object-cover rounded-lg"
            />
            <div v-else class="text-5xl">💡</div>
          </div>
          <div>
            <h4 class="font-extrabold text-[20px] text-night-ink leading-snug mb-1">
              {{ products[2]?.name ?? products[2]?.nama_produk }}
            </h4>
            <p class="text-[14px] text-night-ink/80 line-clamp-2 leading-relaxed mb-4">
              {{ products[2]?.description ?? products[2]?.deskripsi }}
            </p>
          </div>
          <div class="mt-auto">
            <div class="flex items-center justify-between mb-4">
              <span class="font-extrabold text-[18px] text-night-ink">
                {{ formatRupiah(products[2]?.price_rupiah ?? products[2]?.harga_rupiah ?? 0) }}
              </span>
              <span
                v-if="products[2]?.price_points ?? products[2]?.harga_poin"
                class="font-bold text-xs bg-sprout-tint text-night-ink px-2.5 py-1 rounded-full"
              >
                {{ (products[2]?.price_points ?? products[2]?.harga_poin ?? 0).toLocaleString('id-ID') }} Poin
              </span>
            </div>
            <AppButton variant="blue" size="md" class="w-full h-11 text-[14px]">
              LIHAT PRODUK →
            </AppButton>
          </div>
        </div>

        <!-- Bento Bottom Right: Circular Banner -->
        <div
          class="upcycle-bento-card bg-night-ink text-paper-white rounded-[24px] p-8 flex flex-col justify-center min-h-[200px] hover:shadow-xl transition-all duration-200"
        >
          <p class="font-bold text-[13px] tracking-[1.6px] uppercase text-sprout-tint mb-2">
            SATU PUTARAN BARU
          </p>
          <h3 class="font-bold text-2xl sm:text-[26px] text-paper-white tracking-tight mb-3">
            Pakai. Hargai. Ulangi.
          </h3>
          <p class="font-medium text-[16px] sm:text-[17px] text-paper-white/80 leading-relaxed">
            Rawat barang pilihanmu. Lalu, mulai lagi dari memilah sampah di rumah.
          </p>
        </div>
      </div>
    </div>
  </section>
</template>
