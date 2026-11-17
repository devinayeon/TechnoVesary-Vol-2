<script setup lang="ts">
interface Article {
  id: number;
  title: string;
  excerpt?: string;
  content?: string;
  thumbnail?: string | null;
  photo?: string | null;
  slug: string;
  category?: { id: number; name: string } | null;
}

defineProps<{ articles: Article[] }>();
</script>

<template>
  <section id="artikel" class="bg-cloud-surface py-16 lg:py-20">
    <div class="max-w-[1200px] mx-auto px-6">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-4 gap-3">
        <h2 class="font-extrabold text-3xl sm:text-5xl text-night-ink tracking-tight">
          Mulai dari tahu, jadi kebiasaan.
        </h2>
        <a
          href="#"
          class="font-bold text-[15px] tracking-wider uppercase text-night-ink hover:text-eco-green transition-colors inline-flex items-center gap-1.5"
        >
          Semua artikel →
        </a>
      </div>
      <p class="font-medium text-[16px] sm:text-[17px] text-charcoal max-w-4xl mb-10 leading-relaxed">
        Produk upcycle berawal dari bahan yang terpilah dengan baik. Kenali cara menyiapkan plastik, kardus, dan kaca agar setoran berikutnya lebih mudah dan aman.
      </p>

      <!-- Bento Grid (3 Columns) -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Article 1 (Left on desktop) -->
        <article
          v-if="articles.length > 0"
          class="bg-paper-white border-2 border-border-gray rounded-[20px] drop-shadow-[0px_4px_0px_var(--color-border-gray)] p-5 flex flex-col justify-between hover:-translate-y-1.5 hover:shadow-lg transition-all duration-200 cursor-default"
        >
          <div>
            <!-- Thumbnail / Image wrap -->
            <div class="bg-sky-tint rounded-[14px] h-[160px] flex items-center justify-center overflow-hidden mb-4 p-2">
              <img
                v-if="articles[0]?.thumbnail || articles[0]?.photo"
                :src="`/storage/${articles[0]?.thumbnail || articles[0]?.photo}`"
                :alt="articles[0]?.title"
                class="w-full h-full object-cover rounded-lg"
              />
              <div v-else class="text-5xl">🧴</div>
            </div>

            <!-- Tag -->
            <div class="mb-3">
              <span class="inline-block bg-sprout-tint text-night-ink font-bold text-[11px] px-2.5 py-1 rounded-full uppercase tracking-wider">
                {{ articles[0]?.category?.name ?? 'PANDUAN PILAH · 4 MNT' }}
              </span>
            </div>

            <h3 class="font-extrabold text-[18px] text-night-ink leading-snug mb-2">
              {{ articles[0]?.title }}
            </h3>
            <p class="font-medium text-[14px] text-charcoal line-clamp-3 leading-relaxed mb-4">
              {{ articles[0]?.excerpt || articles[0]?.content }}
            </p>
          </div>

          <a
            :href="`/artikel/${articles[0]?.slug}`"
            class="font-bold text-[14px] text-night-ink hover:text-eco-green transition-colors inline-flex items-center gap-1 mt-auto"
          >
            BACA ARTIKEL →
          </a>
        </article>

        <!-- Bento Info Card 1: Pilah Lebih Tepat (Span 2 cols on desktop) -->
        <div
          class="lg:col-span-2 bg-sky-tint rounded-[24px] p-8 flex flex-col justify-center min-h-[200px] hover:shadow-md transition-all duration-200"
        >
          <h3 class="font-bold text-2xl sm:text-[32px] text-night-ink tracking-tight mb-3">
            Pilah lebih tepat, mulai dari tahu.
          </h3>
          <p class="font-medium text-[16px] sm:text-[17px] text-charcoal leading-relaxed">
            Mulai dari botol plastik yang paling sering dijumpai. Lanjutkan ke kardus dan kaca untuk memahami cara penyimpanan serta penanganan yang berbeda.
          </p>
        </div>

        <!-- Bento Info Card 2: Tiga Kebiasaan Sebelum Setor -->
        <div
          class="bg-paper-white border border-border-gray rounded-[24px] p-8 flex flex-col justify-center min-h-[200px] hover:shadow-md transition-all duration-200"
        >
          <p class="font-bold text-[13px] tracking-[1.6px] uppercase text-charcoal mb-2">
            PRAKTIKKAN DARI RUMAH
          </p>
          <h3 class="font-bold text-xl sm:text-[26px] text-night-ink tracking-tight mb-3">
            Kecil, tapi konsisten.
          </h3>
          <div class="font-medium text-[15px] sm:text-[16px] text-charcoal leading-relaxed space-y-1">
            <p>01 &nbsp;Bilas kemasan plastik</p>
            <p>02 &nbsp;Simpan kardus tetap kering</p>
            <p>03 &nbsp;Pisahkan kaca dengan aman</p>
          </div>
        </div>

        <!-- Article 2 -->
        <article
          v-if="articles.length > 1"
          class="bg-paper-white border-2 border-border-gray rounded-[20px] drop-shadow-[0px_4px_0px_var(--color-border-gray)] p-5 flex flex-col justify-between hover:-translate-y-1.5 hover:shadow-lg transition-all duration-200 cursor-default"
        >
          <div>
            <div class="bg-sky-tint rounded-[14px] h-[160px] flex items-center justify-center overflow-hidden mb-4 p-2">
              <img
                v-if="articles[1]?.thumbnail || articles[1]?.photo"
                :src="`/storage/${articles[1]?.thumbnail || articles[1]?.photo}`"
                :alt="articles[1]?.title"
                class="w-full h-full object-cover rounded-lg"
              />
              <div v-else class="text-5xl">📦</div>
            </div>

            <div class="mb-3">
              <span class="inline-block bg-sprout-tint text-night-ink font-bold text-[11px] px-2.5 py-1 rounded-full uppercase tracking-wider">
                {{ articles[1]?.category?.name ?? 'TIPS RUMAH · 3 MNT' }}
              </span>
            </div>

            <h3 class="font-extrabold text-[18px] text-night-ink leading-snug mb-2">
              {{ articles[1]?.title }}
            </h3>
            <p class="font-medium text-[14px] text-charcoal line-clamp-3 leading-relaxed mb-4">
              {{ articles[1]?.excerpt || articles[1]?.content }}
            </p>
          </div>

          <a
            :href="`/artikel/${articles[1]?.slug}`"
            class="font-bold text-[14px] text-night-ink hover:text-eco-green transition-colors inline-flex items-center gap-1 mt-auto"
          >
            BACA ARTIKEL →
          </a>
        </article>

        <!-- Article 3 -->
        <article
          v-if="articles.length > 2"
          class="bg-paper-white border-2 border-border-gray rounded-[20px] drop-shadow-[0px_4px_0px_var(--color-border-gray)] p-5 flex flex-col justify-between hover:-translate-y-1.5 hover:shadow-lg transition-all duration-200 cursor-default"
        >
          <div>
            <div class="bg-sky-tint rounded-[14px] h-[160px] flex items-center justify-center overflow-hidden mb-4 p-2">
              <img
                v-if="articles[2]?.thumbnail || articles[2]?.photo"
                :src="`/storage/${articles[2]?.thumbnail || articles[2]?.photo}`"
                :alt="articles[2]?.title"
                class="w-full h-full object-cover rounded-lg"
              />
              <div v-else class="text-5xl">🍾</div>
            </div>

            <div class="mb-3">
              <span class="inline-block bg-sprout-tint text-night-ink font-bold text-[11px] px-2.5 py-1 rounded-full uppercase tracking-wider">
                {{ articles[2]?.category?.name ?? 'DAUR ULANG · 5 MNT' }}
              </span>
            </div>

            <h3 class="font-extrabold text-[18px] text-night-ink leading-snug mb-2">
              {{ articles[2]?.title }}
            </h3>
            <p class="font-medium text-[14px] text-charcoal line-clamp-3 leading-relaxed mb-4">
              {{ articles[2]?.excerpt || articles[2]?.content }}
            </p>
          </div>

          <a
            :href="`/artikel/${articles[2]?.slug}`"
            class="font-bold text-[14px] text-night-ink hover:text-eco-green transition-colors inline-flex items-center gap-1 mt-auto"
          >
            BACA ARTIKEL →
          </a>
        </article>
      </div>
    </div>
  </section>
</template>
