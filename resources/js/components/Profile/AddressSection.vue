<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import type { User } from '@/types/auth';
import AppButton from '@/components/Landing/AppButton.vue';
import DetailField from './DetailField.vue';
import LocationPicker from '@/components/Map/LocationPicker.vue';
import ProfileHeroMap from './ProfileHeroMap.vue';

const props = defineProps<{
    user: User;
    basemapApiKey?: string;
}>();

const isEditing = ref(false);

const lat = computed(() => {
    if (props.user.latitude !== null && props.user.latitude !== undefined) {
        return Number(props.user.latitude);
    }
    return -8.1739;
});

const lng = computed(() => {
    if (props.user.longitude !== null && props.user.longitude !== undefined) {
        return Number(props.user.longitude);
    }
    return 113.6878;
});

const fullAddress = computed(() => {
    return (
        props.user.address ||
        (props.user as any).alamat ||
        'JL. Kalimantan No. 12, Sumbersari, Jember'
    );
});

const form = useForm({
    name: props.user.name,
    email: props.user.email,
    address: fullAddress.value,
    latitude: lat.value,
    longitude: lng.value,
});

watch(
    () => props.user,
    (u) => {
        if (u) {
            form.name = u.name;
            form.email = u.email;
            form.address =
                u.address ||
                (u as any).alamat ||
                'JL. Kalimantan No. 12, Sumbersari, Jember';
            form.latitude =
                u.latitude !== null && u.latitude !== undefined
                    ? Number(u.latitude)
                    : -8.1739;
            form.longitude =
                u.longitude !== null && u.longitude !== undefined
                    ? Number(u.longitude)
                    : 113.6878;
        }
    },
    { deep: true },
);

const onLocationPicked = (loc: {
    lat: number;
    lng: number;
    address?: string;
}) => {
    form.latitude = loc.lat;
    form.longitude = loc.lng;
    if (loc.address) {
        form.address = loc.address;
    }
};

const saveAddress = () => {
    form.patch('/profile', {
        preserveScroll: true,
        onSuccess: () => {
            isEditing.value = false;
        },
    });
};
</script>

<template>
    <div class="flex w-full flex-col gap-6">
        <!-- Heading Row -->
        <div
            class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center"
        >
            <div class="flex flex-col gap-1">
                <h1
                    class="text-[28px] font-extrabold tracking-tight text-night-ink"
                >
                    Alamat utama
                </h1>
                <p class="text-[14px] text-charcoal">
                    Alamat dan titik peta operasional penjemputan sampah
                    terpilah.
                </p>
            </div>

            <AppButton
                v-if="!isEditing"
                variant="primary"
                size="md"
                class="self-start sm:self-auto"
                @click="isEditing = true"
            >
                Ubah alamat
            </AppButton>
        </div>

        <!-- Normal / Display View -->
        <div
            v-if="!isEditing"
            class="grid grid-cols-1 items-start gap-6 lg:grid-cols-12"
        >
            <!-- Left: Hero Map Preview & Details -->
            <div class="flex flex-col gap-5 lg:col-span-8">
                <div class="flex flex-col gap-2">
                    <ProfileHeroMap
                        :lat="lat"
                        :lng="lng"
                        :address="fullAddress"
                        :basemap-api-key="basemapApiKey"
                        height="440px"
                    />
                    <p
                        class="px-2 pt-1 text-[12px] font-bold tracking-wide text-charcoal uppercase"
                    >
                        JEMBER · PETA ILUSTRASI ALAMAT UTAMA
                    </p>
                </div>

                <!-- Address Details Card -->
                <div
                    class="flex flex-col gap-5 rounded-[22px] border-2 border-b-[4px] border-border-gray bg-paper-white p-6 shadow-xs"
                >
                    <DetailField label="Alamat lengkap" :value="fullAddress" />

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <DetailField label="Latitude" :value="lat.toFixed(8)" />
                        <DetailField
                            label="Longitude"
                            :value="lng.toFixed(8)"
                        />
                    </div>

                    <p
                        class="border-t border-border-gray/50 pt-2 text-[12px] leading-relaxed font-normal text-charcoal"
                    >
                        Perubahan profil berlaku untuk pesanan baru; alamat
                        pesanan lama tetap tersimpan.
                    </p>
                </div>
            </div>

            <!-- Right: Guidance Card -->
            <div
                class="flex flex-col gap-5 rounded-[22px] border-2 border-b-[4px] border-border-gray bg-paper-white p-6 shadow-xs lg:col-span-4"
            >
                <template v-if="user.role !== 'admin'">
                    <h3
                        class="text-[20px] leading-snug font-extrabold text-night-ink"
                    >
                        Pastikan titik sesuai alamat.
                    </h3>
                    <p class="text-[13px] leading-relaxed text-charcoal">
                        Koordinat membantu driver dan petugas menentukan rute
                        penjemputan sampahmu dengan akurat.
                    </p>
                </template>
                <template v-else>
                    <h3
                        class="text-[20px] leading-snug font-extrabold text-night-ink"
                    >
                        Alamat bersifat opsional.
                    </h3>
                    <p class="text-[13px] leading-relaxed text-charcoal">
                        Kamu dapat menambahkan alamat utama bila dibutuhkan.
                        Alamat operasional memudahkan koordinasi.
                    </p>
                </template>

                <AppButton
                    variant="outline"
                    size="md"
                    class="mt-2 w-full"
                    @click="isEditing = true"
                >
                    Ubah titik alamat
                </AppButton>
            </div>
        </div>

        <!-- Edit Address Mode -->
        <div
            v-else
            class="flex flex-col gap-6 rounded-[22px] border-2 border-b-[4px] border-border-gray bg-paper-white p-6 shadow-xs sm:p-8"
        >
            <div
                class="flex items-center justify-between border-b border-border-gray/70 pb-4"
            >
                <h3 class="text-[22px] font-extrabold text-night-ink">
                    Tentukan Titik & Alamat Utama
                </h3>
            </div>

            <!-- Interactive Map Picker -->
            <div class="flex flex-col gap-2">
                <label class="text-[14px] font-bold text-night-ink">
                    Pilih titik pada peta atau cari alamat
                </label>
                <LocationPicker
                    :center="[lat, lng]"
                    :zoom="15"
                    :basemap-api-key="basemapApiKey"
                    height="380px"
                    placeholder="Ketik alamat atau nama jalan..."
                    @update:model-value="onLocationPicked"
                />
            </div>

            <!-- Address Input Textarea -->
            <div class="flex flex-col gap-2">
                <label
                    for="address-text"
                    class="text-[14px] font-bold text-night-ink"
                >
                    Alamat Lengkap *
                </label>
                <textarea
                    id="address-text"
                    v-model="form.address"
                    rows="3"
                    class="resize-none rounded-xl border-2 border-border-gray bg-paper-white p-4 text-[15px] text-night-ink transition focus:border-eco-green focus:ring-2 focus:ring-eco-green/20 focus:outline-none"
                    placeholder="Tuliskan nama jalan, nomor rumah, RT/RW, dan patokan..."
                />
                <span
                    v-if="form.errors.address"
                    class="text-xs font-semibold text-rose-500"
                >
                    {{ form.errors.address }}
                </span>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="flex flex-col gap-1">
                    <span class="text-xs font-semibold text-charcoal"
                        >Latitude Terpilih</span
                    >
                    <span class="font-mono text-sm font-bold text-night-ink">{{
                        form.latitude
                    }}</span>
                </div>
                <div class="flex flex-col gap-1">
                    <span class="text-xs font-semibold text-charcoal"
                        >Longitude Terpilih</span
                    >
                    <span class="font-mono text-sm font-bold text-night-ink">{{
                        form.longitude
                    }}</span>
                </div>
            </div>

            <!-- Actions -->
            <div
                class="flex flex-col-reverse items-center justify-end gap-3 border-t border-border-gray/70 pt-4 sm:flex-row"
            >
                <AppButton
                    variant="outline"
                    size="md"
                    type="button"
                    class="w-full sm:w-auto"
                    @click="isEditing = false"
                >
                    Batal
                </AppButton>
                <AppButton
                    variant="primary"
                    size="md"
                    type="button"
                    :disabled="form.processing"
                    class="w-full sm:w-auto"
                    @click="saveAddress"
                >
                    {{ form.processing ? 'Menyimpan...' : 'Simpan Alamat' }}
                </AppButton>
            </div>
        </div>
    </div>
</template>
