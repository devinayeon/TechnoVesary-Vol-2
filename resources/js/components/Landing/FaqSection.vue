<script setup lang="ts">
import { ref } from 'vue';

const openIndex = ref<number | null>(0);

const faqs = [
  {
    question: 'Bagaimana poin saya dihitung?',
    answer:
      'Berat sampah per kategori dikalikan nilai poin per kilogram. Kamu bisa melihat rincian hasil timbang setelah penjemputan selesai.',
  },
  {
    question: 'Apakah harus berlangganan?',
    answer:
      'Berlangganan diperlukan untuk layanan penjemputan berkala dari rumah. Kamu bisa mulai dari paket 1 bulan untuk mencoba kepraktisannya.',
  },
  {
    question: 'Poin bisa dipakai untuk apa?',
    answer:
      'Poin bisa ditukarkan dengan produk karya pengrajin lokal di Upcycle Store atau digunakan untuk memperpanjang paket langgananmu.',
  },
  {
    question: 'Kapan poin masuk ke saldo?',
    answer:
      'Poin langsung masuk otomatis ke akunmu begitu driver menyelesaikan penimbangan di tempat dan mengonfirmasi setoran.',
  },
];

function toggle(index: number) {
  openIndex.value = openIndex.value === index ? null : index;
}
</script>

<template>
  <section id="faq" class="bg-paper-white py-16 lg:py-20">
    <div class="max-w-[1200px] mx-auto px-6">
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-start">
        <!-- Left Side -->
        <div class="lg:col-span-5">
          <p class="font-bold text-[13px] tracking-[1.6px] uppercase text-charcoal mb-3">
            TANYA REVALUE
          </p>
          <h2 class="font-extrabold text-3xl sm:text-5xl text-night-ink leading-[1.2] tracking-tight mb-5">
            Masih<br />penasaran?
          </h2>
          <p class="font-medium text-[16px] sm:text-[17px] text-charcoal mb-6">
            Kami bantu mulai dari hal kecil.
          </p>
          <a
            href="#"
            class="font-bold text-[15px] tracking-wider uppercase text-night-ink hover:text-eco-green transition-colors inline-flex items-center gap-2"
          >
            Hubungi kami →
          </a>
        </div>

        <!-- Right Side: Accordion -->
        <div class="lg:col-span-7 flex flex-col gap-3.5">
          <div
            v-for="(faq, index) in faqs"
            :key="index"
            class="bg-cloud-surface rounded-2xl p-5 sm:p-6 transition-all duration-200 hover:shadow-xs border border-transparent hover:border-border-gray/50 cursor-pointer"
            @click="toggle(index)"
          >
            <!-- Question & Toggle -->
            <div class="flex items-center justify-between gap-4 select-none">
              <h3 class="font-bold text-[17px] sm:text-[19px] text-night-ink leading-snug">
                {{ faq.question }}
              </h3>
              <span
                class="font-bold text-2xl text-night-ink size-8 flex items-center justify-center shrink-0 transition-transform duration-200"
              >
                {{ openIndex === index ? '−' : '+' }}
              </span>
            </div>

            <!-- Answer -->
            <div
              v-show="openIndex === index"
              class="font-medium text-[15px] sm:text-[16px] text-charcoal leading-relaxed mt-3 pt-3 border-t border-border-gray/60 animate-in fade-in duration-150"
            >
              {{ faq.answer }}
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>
