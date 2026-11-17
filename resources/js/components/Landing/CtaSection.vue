<script setup lang="ts">
import { Link } from "@inertiajs/vue3";
import AppButton from "./AppButton.vue";
</script>

<template>
    <section id="cta" class="bg-paper-white py-12 lg:py-16">
        <div class="max-w-[1200px] mx-auto px-6">
            <div
                class="bg-sprout-tint rounded-[32px] p-8 lg:p-12 relative overflow-hidden flex flex-col lg:flex-row items-center justify-between gap-8 min-h-[330px] hover:shadow-xl transition-all duration-300"
            >
                <!-- Text & Action -->
                <div class="max-w-[650px] z-10">
                    <h2
                        class="font-extrabold text-3xl sm:text-[48px] text-night-ink leading-[1.2] tracking-tight mb-4"
                    >
                        Bumi lebih baik.<br />Dimulai dari rumahmu.
                    </h2>
                    <p
                        class="font-medium text-[16px] sm:text-[17px] text-charcoal mb-8"
                    >
                        Yuk, kasih kesempatan kedua untuk sampahmu.
                    </p>
                    <div>
                        <Link href="/register">
                            <AppButton
                                variant="primary"
                                size="lg"
                                class="h-12 px-8 text-[14px]"
                            >
                                DAFTAR GRATIS
                            </AppButton>
                        </Link>
                    </div>
                </div>

                <!-- Mascot Valu Instant ReValue Plus -->
                <div
                    class="relative w-[280px] sm:w-[360px] lg:w-[408px] shrink-0 z-10 flex items-center justify-center"
                >
                    <img
                        src="assets\Maskot\Pose=Instant-ReValuePlus.png"
                        alt="Valu Plus"
                        class="w-full h-auto object-contain filter drop-shadow-xl transition-transform duration-300 hover:scale-105"
                    />
                </div>
            </div>
        </div>
    </section>
</template>
