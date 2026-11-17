<script setup lang="ts">
const testimonials = [
  {
    id: 1,
    avatar: '👨',
    name: 'Bima',
    role: 'Rumah tangga',
    quote:
      'Dulu botol dan kardus menumpuk di rumah. Sekarang tiap minggu disetorkan dan saldo poin terus bertambah. Nggak nyangka bisa sebermanfaat ini.',
  },
  {
    id: 2,
    avatar: '👩',
    name: 'Ibu Sari',
    role: 'Pengrajin lokal',
    quote:
      'Senang melihat bahan bekas punya cerita baru. Produk upcycle saya laris, dan sampah berkurang dari sumbernya.',
  },
  {
    id: 3,
    avatar: '🧕',
    name: 'Maya',
    role: 'Kebiasaan berkelanjutan',
    quote:
      'Setiap setoran terasa lebih berarti. Ada angka nyata di saldo poin yang bisa saya tukarkan kapan saja.',
  },
];
</script>

<template>
  <section id="testimoni" class="bg-paper-white py-16 lg:py-20">
    <div class="max-w-[1200px] mx-auto px-6">
      <!-- Header -->
      <div class="text-center mb-12">
        <p class="font-bold text-[13px] tracking-[1.6px] uppercase text-charcoal mb-3">
          CERITA TEMAN REVALUE
        </p>
        <h2 class="font-extrabold text-3xl sm:text-5xl text-night-ink tracking-tight mb-4">
          Cerita baik, berawal dari rumah.
        </h2>
        <p class="font-medium text-[16px] sm:text-[17px] text-charcoal max-w-2xl mx-auto">
          Contoh testimoni untuk menggambarkan pengalaman nasabah kami.
        </p>
      </div>

      <!-- Testimonial Cards -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div
          v-for="t in testimonials"
          :key="t.id"
          class="bg-paper-white border border-border-gray rounded-[28px] p-7 flex flex-col justify-between hover:-translate-y-2 hover:shadow-xl transition-all duration-200 cursor-default"
        >
          <div>
            <span class="inline-block font-bold text-[12px] sm:text-[13px] tracking-[1.6px] uppercase text-charcoal mb-4">
              ILUSTRASI PENGALAMAN
            </span>
            <p class="font-medium text-[16px] sm:text-[17px] text-charcoal leading-relaxed mb-6 italic">
              “{{ t.quote }}”
            </p>
          </div>

          <!-- Author -->
          <div class="flex items-center gap-4 pt-4 border-t border-border-gray/60">
            <div
              class="size-12 rounded-full bg-cloud-surface border-2 border-border-gray flex items-center justify-center text-2xl shrink-0"
            >
              {{ t.avatar }}
            </div>
            <div>
              <p class="font-bold text-[18px] sm:text-[19px] text-night-ink leading-tight">{{ t.name }}</p>
              <p class="font-medium text-[13px] text-charcoal mt-0.5">{{ t.role }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>
