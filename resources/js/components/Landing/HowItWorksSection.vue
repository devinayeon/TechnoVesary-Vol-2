<script setup lang="ts">
const steps = [
    {
        num: "01",
        title: "Pilah sampahmu",
        desc: "Pisahkan plastik, kertas, logam, dan kaca. Pastikan bersih dan kering, ya.",
        bg: "bg-night-ink",
        numColor: "text-paper-white",
        titleColor: "text-paper-white",
        descColor: "text-paper-white/90",
        illustration: "/assets/Maskot/Pose=Sorting-Helper.png",
    },
    {
        num: "02",
        title: "Kami jemput ke rumah",
        desc: "Aktifkan langganan, tentukan titik jemput, lalu temukan driver terdekat.",
        bg: "bg-silver-tier",
        numColor: "text-night-ink",
        titleColor: "text-night-ink",
        descColor: "text-charcoal",
        illustration: "/assets/Maskot/Pose=Pickup-Sprint.png",
    },
    {
        num: "03",
        title: "Jadi poin, jadi berarti",
        desc: "Sampah ditimbang per kategori. Poin masuk setelah penjemputan selesai.",
        bg: "bg-sunlight-gold",
        numColor: "text-night-ink",
        titleColor: "text-night-ink",
        descColor: "text-charcoal",
        illustration: "/assets/Maskot/Pose=Celebrate-Coins.png",
    },
];
</script>

<template>
    <section id="cara-kerja" class="bg-paper-white py-16 lg:py-20">
        <div class="max-w-[1200px] mx-auto px-6">
            <!-- Header -->
            <div class="text-center mb-12">
                <p
                    class="font-bold text-[13px] tracking-[1.6px] uppercase text-charcoal mb-3"
                >
                    DARI RUMAH, UNTUK BUMI
                </p>
                <h2
                    class="font-extrabold text-3xl sm:text-5xl text-night-ink tracking-tight"
                >
                    Berbuat baik, nggak perlu ribet.
                </h2>
            </div>

            <!-- 3 Step Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div
                    v-for="step in steps"
                    :key="step.num"
                    class="rounded-[28px] p-7 flex flex-col min-h-[400px] transition-all duration-200 hover:-translate-y-2 hover:shadow-xl cursor-default"
                    :class="step.bg"
                >
                    <!-- Step Number -->
                    <div
                        class="font-bold text-[19px] mb-4"
                        :class="step.numColor"
                    >
                        {{ step.num }}
                    </div>

                    <!-- Valu Mascot Illustration -->
                    <div
                        class="size-40 mx-auto my-auto flex items-center justify-center"
                    >
                        <img
                            :src="step.illustration"
                            :alt="step.title"
                            class="w-full h-full object-contain filter drop-shadow-md transition-transform duration-300 hover:scale-105"
                        />
                    </div>

                    <!-- Content -->
                    <div class="mt-auto pt-4">
                        <h3
                            class="font-bold text-[24px] sm:text-[26px] tracking-tight mb-2 leading-tight"
                            :class="step.titleColor"
                        >
                            {{ step.title }}
                        </h3>
                        <p
                            class="font-medium text-[16px] sm:text-[17px] leading-relaxed"
                            :class="step.descColor"
                        >
                            {{ step.desc }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
