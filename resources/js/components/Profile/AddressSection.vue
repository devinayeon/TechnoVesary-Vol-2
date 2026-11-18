<script setup lang="ts">
import { computed, ref, watch } from "vue";
import { useForm } from "@inertiajs/vue3";
import type { User } from "@/types/auth";
import AppButton from "@/components/Landing/AppButton.vue";
import DetailField from "./DetailField.vue";
import LocationPicker from "@/components/Map/LocationPicker.vue";
import ProfileHeroMap from "./ProfileHeroMap.vue";

const props = defineProps<{
    user: User;
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
        "JL. Kalimantan No. 12, Sumbersari, Jember"
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
                "JL. Kalimantan No. 12, Sumbersari, Jember";
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
    { deep: true }
);

const onLocationPicked = (loc: { lat: number; lng: number; address?: string }) => {
    form.latitude = loc.lat;
    form.longitude = loc.lng;
    if (loc.address) {
        form.address = loc.address;
    }
};

const saveAddress = () => {
    form.patch("/profile", {
        preserveScroll: true,
        onSuccess: () => {
            isEditing.value = false;
        },
    });
};
</script>

<template>
    <div class="flex flex-col gap-6 w-full">
        <!-- Heading Row -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex flex-col gap-1">
                <h1 class="text-[28px] font-extrabold text-night-ink tracking-tight">
                    Alamat utama
                </h1>
                <p class="text-[14px] text-charcoal">
                    Alamat dan titik peta operasional penjemputan sampah terpilah.
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
        <div v-if="!isEditing" class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            <!-- Left: Hero Map Preview & Details -->
            <div class="lg:col-span-8 flex flex-col gap-5">
                <div class="flex flex-col gap-2">
                    <ProfileHeroMap
                        :lat="lat"
                        :lng="lng"
                        :address="fullAddress"
                        height="440px"
                    />
                    <p class="px-2 pt-1 text-[12px] text-charcoal font-bold tracking-wide uppercase">
                        JEMBER · PETA ILUSTRASI ALAMAT UTAMA
                    </p>
                </div>

                <!-- Address Details Card -->
                <div class="bg-paper-white border-2 border-b-[4px] border-border-gray rounded-[22px] p-6 flex flex-col gap-5 shadow-xs">
                    <DetailField label="Alamat lengkap" :value="fullAddress" />

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <DetailField
                            label="Latitude"
                            :value="lat.toFixed(8)"
                        />
                        <DetailField
                            label="Longitude"
                            :value="lng.toFixed(8)"
                        />
                    </div>

                    <p class="pt-2 text-[12px] text-charcoal leading-relaxed font-normal border-t border-border-gray/50">
                        Perubahan profil berlaku untuk pesanan baru; alamat pesanan lama tetap tersimpan.
                    </p>
                </div>
            </div>

            <!-- Right: Guidance Card -->
            <div class="lg:col-span-4 bg-paper-white border-2 border-b-[4px] border-border-gray rounded-[22px] p-6 flex flex-col gap-5 shadow-xs">
                <template v-if="user.role !== 'admin'">
                    <h3 class="text-[20px] font-extrabold text-night-ink leading-snug">
                        Pastikan titik sesuai alamat.
                    </h3>
                    <p class="text-[13px] text-charcoal leading-relaxed">
                        Koordinat membantu driver dan petugas menentukan rute penjemputan sampahmu dengan akurat.
                    </p>
                </template>
                <template v-else>
                    <h3 class="text-[20px] font-extrabold text-night-ink leading-snug">
                        Alamat bersifat opsional.
                    </h3>
                    <p class="text-[13px] text-charcoal leading-relaxed">
                        Kamu dapat menambahkan alamat utama bila dibutuhkan. Alamat operasional memudahkan koordinasi.
                    </p>
                </template>

                <AppButton
                    variant="outline"
                    size="md"
                    class="w-full mt-2"
                    @click="isEditing = true"
                >
                    Ubah titik alamat
                </AppButton>
            </div>
        </div>

        <!-- Edit Address Mode -->
        <div v-else class="bg-paper-white border-2 border-b-[4px] border-border-gray rounded-[22px] p-6 sm:p-8 flex flex-col gap-6 shadow-xs">
            <div class="flex items-center justify-between pb-4 border-b border-border-gray/70">
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
                    height="380px"
                    placeholder="Ketik alamat atau nama jalan..."
                    @update:model-value="onLocationPicked"
                />
            </div>

            <!-- Address Input Textarea -->
            <div class="flex flex-col gap-2">
                <label for="address-text" class="text-[14px] font-bold text-night-ink">
                    Alamat Lengkap *
                </label>
                <textarea
                    id="address-text"
                    v-model="form.address"
                    rows="3"
                    class="p-4 rounded-xl border-2 border-border-gray bg-paper-white text-[15px] text-night-ink focus:outline-none focus:border-eco-green focus:ring-2 focus:ring-eco-green/20 transition resize-none"
                    placeholder="Tuliskan nama jalan, nomor rumah, RT/RW, dan patokan..."
                />
                <span v-if="form.errors.address" class="text-xs text-rose-500 font-semibold">
                    {{ form.errors.address }}
                </span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="flex flex-col gap-1">
                    <span class="text-xs font-semibold text-charcoal">Latitude Terpilih</span>
                    <span class="text-sm font-bold text-night-ink font-mono">{{ form.latitude }}</span>
                </div>
                <div class="flex flex-col gap-1">
                    <span class="text-xs font-semibold text-charcoal">Longitude Terpilih</span>
                    <span class="text-sm font-bold text-night-ink font-mono">{{ form.longitude }}</span>
                </div>
            </div>

            <!-- Actions -->
            <div class="pt-4 flex flex-col-reverse sm:flex-row items-center justify-end gap-3 border-t border-border-gray/70">
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
                    {{ form.processing ? "Menyimpan..." : "Simpan Alamat" }}
                </AppButton>
            </div>
        </div>
    </div>
</template>
