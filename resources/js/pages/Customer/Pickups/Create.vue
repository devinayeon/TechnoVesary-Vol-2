<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import DashboardLayout from '@/layouts/DashboardLayout.vue';
import PageHeader from '@/components/Dashboard/PageHeader.vue';
import FlashMessages from '@/components/Dashboard/FlashMessages.vue';
import LocationPicker from '@/components/Map/LocationPicker.vue';
import PickupCategorySelector from '@/components/Pickup/PickupCategorySelector.vue';
import PickupPhotoUpload from '@/components/Pickup/PickupPhotoUpload.vue';
import type { MembershipInfo, WasteCategoryOption } from '@/types/pickup';
import {
    ArrowLeft,
    Info,
    MapPin,
    Recycle,
    Send,
    Sparkles,
} from 'lucide-vue-next';

const props = defineProps<{
    wasteCategories: WasteCategoryOption[];
    defaultLocation: {
        address: string;
        latitude: number | null;
        longitude: number | null;
    };
    basemapApiKey?: string;
    membership: MembershipInfo;
}>();

const form = useForm({
    address: props.defaultLocation.address ?? '',
    latitude: props.defaultLocation.latitude,
    longitude: props.defaultLocation.longitude,
    notes: '',
    photo: null as File | null,
    categories: [] as number[],
});

const hasLocation = computed(
    () => form.latitude !== null && form.longitude !== null,
);

const pickerCenter = computed<[number, number]>(() => {
    if (hasLocation.value) {
        return [Number(form.latitude), Number(form.longitude)];
    }

    return [-8.1739, 113.6878];
});

const pickerInitial = computed(() =>
    hasLocation.value
        ? {
              lat: Number(form.latitude),
              lng: Number(form.longitude),
              address: form.address,
          }
        : null,
);

const selectedCategories = computed(() =>
    props.wasteCategories.filter((category) =>
        form.categories.includes(category.id),
    ),
);

const totalPointsPerKg = computed(() =>
    selectedCategories.value.reduce(
        (total, category) => total + category.points_per_kg,
        0,
    ),
);

function onLocationPicked(location: {
    lat: number;
    lng: number;
    address?: string;
}) {
    form.latitude = location.lat;
    form.longitude = location.lng;
    if (location.address) {
        form.address = location.address;
    }
}

function submit() {
    form.post('/customer/pickups', {
        forceFormData: true,
        preserveScroll: true,
    });
}
</script>

<template>
    <Head title="Ajukan Penjemputan - ReValue" />

    <DashboardLayout>
        <Link
            href="/customer/pickups"
            class="mb-4 inline-flex w-fit items-center gap-1.5 text-[13px] font-extrabold tracking-wide text-charcoal uppercase transition-colors hover:text-eco-green-dark"
        >
            <ArrowLeft class="size-4" />
            Kembali ke riwayat
        </Link>

        <PageHeader
            title="Ajukan Penjemputan"
            subtitle="Tentukan lokasi, jenis sampah, dan unggah foto bukti. Driver ReValue akan menjemput sesuai pengajuan Anda."
            badge="ReValue+"
        />

        <FlashMessages class="mb-6" />

        <form
            class="grid grid-cols-1 gap-5 xl:grid-cols-3"
            @submit.prevent="submit"
        >
            <!-- Main Form -->
            <div class="flex flex-col gap-5 xl:col-span-2">
                <!-- Location -->
                <section
                    class="rounded-[20px] border-2 border-b-[4px] border-border-gray bg-paper-white p-5 sm:p-6"
                >
                    <div class="mb-4 flex flex-col gap-1">
                        <h2
                            class="flex items-center gap-2 text-[15px] font-extrabold text-night-ink"
                        >
                            <MapPin class="size-4 text-eco-green-dark" />
                            1. Titik Lokasi Penjemputan
                        </h2>
                        <p class="text-[12px] font-medium text-charcoal">
                            Geser pin atau klik peta untuk menentukan titik
                            penjemputan yang akurat.
                        </p>
                    </div>

                    <LocationPicker
                        :center="pickerCenter"
                        :initial-value="pickerInitial"
                        :zoom="16"
                        :basemap-api-key="basemapApiKey"
                        height="340px"
                        placeholder="Cari alamat atau nama jalan..."
                        @update:model-value="onLocationPicked"
                    />

                    <div
                        v-if="!hasLocation"
                        class="mt-3 flex items-start gap-2 rounded-xl border border-sunlight-gold/50 bg-gold-tint px-4 py-3 text-[12px] font-semibold text-charcoal"
                    >
                        <Info
                            class="mt-0.5 size-4 shrink-0 text-sunlight-gold"
                        />
                        Silakan pilih titik lokasi pada peta terlebih dahulu.
                    </div>

                    <div class="mt-4 flex flex-col gap-2">
                        <label
                            for="pickup-address"
                            class="text-[14px] font-bold text-night-ink"
                        >
                            Alamat Lengkap
                            <span class="text-red-500">*</span>
                        </label>
                        <textarea
                            id="pickup-address"
                            v-model="form.address"
                            rows="3"
                            placeholder="Nama jalan, nomor rumah, RT/RW, dan patokan..."
                            class="resize-none rounded-xl border-2 border-border-gray bg-paper-white p-4 text-[14px] text-night-ink transition focus:border-eco-green focus:ring-4 focus:ring-eco-green/15 focus:outline-none"
                        />
                        <span
                            v-if="form.errors.address"
                            class="text-[12px] font-semibold text-red-500"
                        >
                            {{ form.errors.address }}
                        </span>
                    </div>

                    <div class="mt-4 flex flex-col gap-2">
                        <label
                            for="pickup-notes"
                            class="text-[14px] font-bold text-night-ink"
                        >
                            Catatan untuk Driver
                            <span class="text-[12px] font-medium text-charcoal">
                                (opsional)
                            </span>
                        </label>
                        <textarea
                            id="pickup-notes"
                            v-model="form.notes"
                            rows="2"
                            placeholder="Contoh: sampah sudah dikemas dalam 2 karung di depan pagar."
                            class="resize-none rounded-xl border-2 border-border-gray bg-paper-white p-4 text-[14px] text-night-ink transition focus:border-eco-green focus:ring-4 focus:ring-eco-green/15 focus:outline-none"
                        />
                        <span
                            v-if="form.errors.notes"
                            class="text-[12px] font-semibold text-red-500"
                        >
                            {{ form.errors.notes }}
                        </span>
                    </div>
                </section>

                <!-- Categories -->
                <section
                    class="rounded-[20px] border-2 border-b-[4px] border-border-gray bg-paper-white p-5 sm:p-6"
                >
                    <div class="mb-4 flex flex-col gap-1">
                        <h2
                            class="flex items-center gap-2 text-[15px] font-extrabold text-night-ink"
                        >
                            <Recycle class="size-4 text-sky-blue-dark" />
                            2. Jenis Sampah
                        </h2>
                        <p class="text-[12px] font-medium text-charcoal">
                            Pilih satu atau lebih jenis sampah yang akan
                            disetorkan dalam penjemputan ini.
                        </p>
                    </div>

                    <PickupCategorySelector
                        v-model="form.categories"
                        :categories="wasteCategories"
                    />

                    <span
                        v-if="form.errors.categories"
                        class="mt-2 block text-[12px] font-semibold text-red-500"
                    >
                        {{ form.errors.categories }}
                    </span>
                </section>

                <!-- Photo -->
                <section
                    class="rounded-[20px] border-2 border-b-[4px] border-border-gray bg-paper-white p-5 sm:p-6"
                >
                    <div class="mb-4 flex flex-col gap-1">
                        <h2
                            class="flex items-center gap-2 text-[15px] font-extrabold text-night-ink"
                        >
                            <Sparkles class="size-4 text-sunlight-gold" />
                            3. Foto Bukti Sampah
                        </h2>
                        <p class="text-[12px] font-medium text-charcoal">
                            Unggah foto sampah yang akan dijemput agar driver
                            dapat memverifikasi jenis dan kondisinya.
                        </p>
                    </div>

                    <PickupPhotoUpload
                        v-model="form.photo"
                        :error="form.errors.photo"
                    />
                </section>
            </div>

            <!-- Summary -->
            <aside class="xl:col-span-1">
                <div
                    class="flex flex-col gap-4 rounded-[20px] border-2 border-b-[4px] border-border-gray bg-paper-white p-5 xl:sticky xl:top-24"
                >
                    <h2 class="text-[15px] font-extrabold text-night-ink">
                        Ringkasan Pengajuan
                    </h2>

                    <div class="flex flex-col gap-3">
                        <div>
                            <p
                                class="text-[11px] font-bold tracking-wider text-charcoal uppercase"
                            >
                                Lokasi
                            </p>
                            <p
                                class="mt-1 line-clamp-3 text-[13px] leading-relaxed font-semibold text-night-ink"
                            >
                                {{ form.address || 'Belum dipilih' }}
                            </p>
                        </div>

                        <div>
                            <p
                                class="text-[11px] font-bold tracking-wider text-charcoal uppercase"
                            >
                                Jenis Sampah
                            </p>
                            <div class="mt-1 flex flex-wrap gap-1.5">
                                <span
                                    v-for="category in selectedCategories"
                                    :key="category.id"
                                    class="rounded-full border border-eco-green/30 bg-sprout-tint px-2.5 py-1 text-[11px] font-bold text-night-ink"
                                >
                                    {{ category.name }}
                                </span>
                                <span
                                    v-if="selectedCategories.length === 0"
                                    class="text-[13px] text-charcoal italic"
                                >
                                    Belum dipilih
                                </span>
                            </div>
                        </div>

                        <div
                            v-if="selectedCategories.length > 0"
                            class="rounded-xl border border-border-gray/70 bg-cloud-surface px-4 py-3"
                        >
                            <p
                                class="text-[11px] font-bold tracking-wider text-charcoal uppercase"
                            >
                                Total Poin per Kg
                            </p>
                            <p
                                class="text-[16px] font-extrabold text-eco-green-dark"
                            >
                                {{ totalPointsPerKg.toLocaleString('id-ID') }}
                                poin/kg
                            </p>
                            <p
                                class="mt-1 text-[11px] leading-relaxed text-charcoal"
                            >
                                Poin akhir dihitung dari berat aktual hasil
                                penimbangan driver.
                            </p>
                        </div>

                        <div
                            v-if="form.photo"
                            class="rounded-xl border border-eco-green/30 bg-sprout-tint px-4 py-2 text-[12px] font-semibold text-eco-green-dark"
                        >
                            Foto bukti siap diunggah
                        </div>
                    </div>

                    <button
                        type="submit"
                        class="mt-2 flex h-[48px] w-full cursor-pointer items-center justify-center gap-2 rounded-2xl border-2 border-b-[4px] border-eco-green-dark bg-eco-green text-[14px] font-extrabold text-night-ink transition-all hover:brightness-105 active:translate-y-0.5 active:border-b-2 disabled:cursor-not-allowed disabled:opacity-60"
                        :disabled="form.processing || !hasLocation"
                    >
                        <Send class="size-4" />
                        {{
                            form.processing
                                ? 'Mengirim...'
                                : 'Ajukan Penjemputan'
                        }}
                    </button>

                    <p
                        class="text-center text-[11px] leading-relaxed text-charcoal"
                    >
                        Dengan mengajukan, Anda menyetujui penimbangan dilakukan
                        oleh driver ReValue.
                    </p>
                </div>
            </aside>
        </form>
    </DashboardLayout>
</template>
