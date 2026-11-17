<script setup lang="ts">
import { ref, onMounted, onUnmounted, nextTick } from 'vue';
import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

const openIndex = ref<number | null>(0);
const sectionRef = ref<HTMLElement | null>(null);
let gsapCtx: gsap.Context | null = null;

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

onMounted(async () => {
  if (typeof window === 'undefined' || !sectionRef.value) return;
  await nextTick();
  gsap.registerPlugin(ScrollTrigger);

  gsapCtx = gsap.context(() => {
    const root = sectionRef.value;
    if (!root) return;

    const items = root.querySelectorAll('.faq-item');
    if (items.length > 0) {
      gsap.fromTo(
        items,
        { y: 35, opacity: 0 },
        {
          y: 0,
          opacity: 1,
          duration: 0.6,
          stagger: 0.1,
          ease: 'power3.out',
          scrollTrigger: {
            trigger: items[0],
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
  <section id="faq" ref="sectionRef" class="bg-paper-white py-16 lg:py-20 overflow-hidden scroll-mt-28">
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
            class="font-bold text-[15px] tracking-wider uppercase text-night-ink hover:text-eco-green transition-colors inline-flex items-center gap-2 group"
          >
            Hubungi kami <span class="transition-transform group-hover:translate-x-1">→</span>
          </a>
        </div>

        <!-- Right Side: Accordion -->
        <div class="faq-list lg:col-span-7 flex flex-col gap-3.5">
          <div
            v-for="(faq, index) in faqs"
            :key="faq.question"
            class="faq-item group bg-cloud-surface border-2 border-b-[4px] border-border-gray rounded-2xl overflow-hidden transition-all duration-300 ease-out hover:border-night-ink/30 hover:border-b-night-ink/50 hover:-translate-y-0.5 hover:shadow-md cursor-pointer"
            :class="openIndex === index ? 'bg-paper-white border-eco-green border-b-eco-green-dark shadow-sm' : ''"
          >
            <button
              type="button"
              class="w-full text-left px-6 py-5 flex items-center justify-between gap-4 font-bold text-[17px] sm:text-[18px] text-night-ink cursor-pointer select-none"
              @click="toggle(index)"
            >
              <span class="transition-colors duration-200 group-hover:text-night-ink">{{ faq.question }}</span>
              <span
                class="size-8.5 rounded-full bg-paper-white border-2 border-b-[3px] border-border-gray group-hover:border-eco-green flex items-center justify-center text-lg font-bold shrink-0 transition-all duration-300"
                :class="openIndex === index ? 'rotate-45 bg-sprout-tint border-eco-green text-eco-green-dark' : 'group-hover:bg-sprout-tint/50'"
              >
                +
              </span>
            </button>
            <div
              v-show="openIndex === index"
              class="px-6 pb-5 pt-1 text-[15px] sm:text-[16px] font-medium text-charcoal leading-relaxed border-t border-border-gray/40 animate-in fade-in duration-200"
            >
              {{ faq.answer }}
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>
