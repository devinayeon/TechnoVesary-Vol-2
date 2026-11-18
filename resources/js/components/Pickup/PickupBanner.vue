<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { ArrowRight, Crown, Truck } from 'lucide-vue-next';
import AppButton from '@/components/Landing/AppButton.vue';
import StatusChip from '@/components/Dashboard/StatusChip.vue';
import { formatPickupDate } from '@/lib/pickup';

const props = withDefaults(
    defineProps<{
        isMember: boolean;
        expiresAt?: string | null;
        createHref?: string;
    }>(),
    {
        expiresAt: null,
        createHref: '/customer/pickups/create',
    },
);

const expiresLabel = computed(() =>
    props.expiresAt ? formatPickupDate(props.expiresAt) : null,
);
</script>

<template>
    <div
        class="relative flex flex-col justify-between gap-4 overflow-hidden rounded-[20px] border-2 border-b-[4px] p-5 sm:flex-row sm:items-center sm:p-6"
        :class="
            isMember
                ? 'border-eco-green/40 bg-sprout-tint'
                : 'border-sunlight-gold/50 bg-gold-tint'
        "
    >
        <div class="relative z-10 flex min-w-0 flex-col gap-2">
            <div class="flex flex-wrap items-center gap-2">
                <StatusChip
                    :label="isMember ? 'ReValue+ Aktif' : 'Belum Berlangganan'"
                    :tone="isMember ? 'Green' : 'Amber'"
                />
                <span
                    v-if="isMember && expiresLabel"
                    class="text-[12px] font-semibold text-charcoal"
                >
                    Berlaku hingga {{ expiresLabel }}
                </span>
            </div>

            <h2
                class="text-[18px] leading-snug font-extrabold text-night-ink sm:text-[22px]"
            >
                {{
                    isMember
                        ? 'Sampahmu menumpuk? Ajukan penjemputan sekarang.'
                        : 'Aktifkan ReValue+ untuk mulai menjadwalkan penjemputan.'
                }}
            </h2>

            <p
                class="max-w-[560px] text-[13px] leading-relaxed font-medium text-charcoal"
            >
                {{
                    isMember
                        ? 'Pilih lokasi, jenis sampah, dan unggah foto bukti. Driver ReValue akan menjemput sesuai jadwal.'
                        : 'Pelanggan ReValue+ dapat mengajukan penjemputan sampah terpilah dan mendapatkan poin dari setiap kilogram yang disetor.'
                }}
            </p>
        </div>

        <div class="relative z-10 flex shrink-0 items-center gap-3">
            <Link v-if="isMember" :href="createHref">
                <AppButton variant="primary" size="md">
                    <span class="inline-flex items-center gap-2">
                        <Truck class="size-4" />
                        Ajukan Penjemputan
                    </span>
                </AppButton>
            </Link>
            <Link v-else href="/#langganan">
                <AppButton variant="primary" size="md">
                    <span class="inline-flex items-center gap-2">
                        <Crown class="size-4" />
                        Lihat Paket ReValue+
                        <ArrowRight class="size-4" />
                    </span>
                </AppButton>
            </Link>
        </div>

        <!-- Mascot -->
        <img
            src="/assets/Maskot/Pose=Pickup-Sprint.png"
            alt="Valu siap menjemput"
            class="pointer-events-none absolute -right-2 -bottom-6 w-[120px] object-contain opacity-90 select-none sm:-right-1 sm:w-[140px]"
        />
    </div>
</template>
