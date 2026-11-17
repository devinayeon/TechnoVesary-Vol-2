<script setup lang="ts"></script>

<template>
    <section class="bg-paper-white py-12 lg:py-16">
        <div class="max-w-[1200px] mx-auto px-6">
            <div
                class="bg-lavender-tint rounded-[32px] p-8 lg:p-12 min-h-[470px] relative overflow-hidden flex flex-col lg:flex-row items-center justify-between gap-10 hover:shadow-md transition-shadow"
            >
                <!-- Left Content -->
                <div class="max-w-[570px] z-10">
                    <p
                        class="font-bold text-[13px] tracking-[1.6px] uppercase text-night-ink mb-4"
                    >
                        POIN &amp; PENCAPAIAN
                    </p>
                    <h2
                        class="font-extrabold text-3xl sm:text-[48px] text-night-ink leading-[1.2] tracking-tight mb-5"
                    >
                        Kebiasaan baikmu<br />layak dirayakan.
                    </h2>
                    <p
                        class="font-medium text-[16px] sm:text-[17px] text-charcoal leading-relaxed mb-6"
                    >
                        Setiap sampah yang ditimbang punya nilai. Kumpulkan poin
                        dari setoranmu dan buka badge di setiap pencapaian.
                    </p>
                    <p
                        class="font-bold text-[18px] sm:text-[19px] text-night-ink mb-6"
                    >
                        5, 10, 25 kali setor. Terus bertumbuh!
                    </p>
                    <a
                        href="#"
                        class="font-bold text-[15px] tracking-wider uppercase text-night-ink hover:text-eco-green transition-colors inline-flex items-center gap-2"
                    >
                        Kenali hadiahmu →
                    </a>
                </div>

                <!-- Right Illustration & Badges -->
                <div
                    class="relative w-full lg:w-[500px] h-[380px] sm:h-[400px] flex items-center justify-center"
                >
                    <!-- Shield Badge Milestone 1 (Rotated) -->
                    <div
                        class="absolute left-4 top-2 transform rotate-[9.15deg] transition-transform duration-300 hover:rotate-0 z-10"
                    >
                        <div class="relative w-[112px] h-[128px]">
                            <img
                                src="assets\Badge\Badge=Sultan Mager, Tier=Sky.png"
                                alt=""
                                class="absolute inset-0 w-full h-full"
                            />
                        </div>
                    </div>

                    <!-- Central Mascot Valu Celebrate Coins -->
                    <div class="relative size-[280px] sm:size-[308px] z-20">
                        <img
                            src="assets\Maskot\Pose=Celebrate-Coins.png"
                            alt="Valu Celebrate"
                            class="w-full h-full object-contain filter drop-shadow-lg transition-transform duration-300 hover:scale-105"
                        />
                    </div>

                    <!-- Shield Badge Milestone 25 (Gold) -->
                    <div
                        class="absolute right-4 top-20 transform -rotate-[6deg] transition-transform duration-300 hover:rotate-0 z-10"
                    >
                        <div class="relative w-[112px] h-[126px]">
                            <img
                                src="\assets\Badge\Badge=Pejuang 25 Order, Tier=Gold.png"
                                alt=""
                                class="absolute inset-0 w-full h-full"
                            />
                        </div>
                    </div>

                    <!-- Overlay Pill: SETORAN SELESAI -->
                    <div
                        class="absolute bottom-2 left-1/2 -translate-x-1/2 z-30 w-[280px] sm:w-[292px] bg-paper-white rounded-2xl p-4 shadow-md border border-border-gray/50 hover:shadow-lg transition-all"
                    >
                        <p
                            class="font-bold text-[12px] sm:text-[13px] tracking-wide text-charcoal mb-0.5"
                        >
                            SETORAN SELESAI
                        </p>
                        <p
                            class="font-bold text-[17px] sm:text-[19px] text-night-ink leading-tight"
                        >
                            Poin masuk ke saldomu!
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
