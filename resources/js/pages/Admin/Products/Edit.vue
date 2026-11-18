<script setup lang="ts">
import { ref } from "vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import DashboardLayout from "@/layouts/DashboardLayout.vue";
import {
    ArrowLeft,
    UploadCloud,
    Info,
    AlertCircle,
    Check,
    X,
} from "lucide-vue-next";

export interface ProductEditData {
    id: number;
    name: string;
    description: string | null;
    price_rupiah: number;
    stock: number;
    photo: string | null;
    photo_url?: string | null;
    created_at: string;
    updated_at: string;
}

const props = defineProps<{
    product: ProductEditData;
}>();

const form = useForm({
    _method: "PUT",
    name: props.product.name,
    description: props.product.description ?? "",
    price_rupiah: props.product.price_rupiah as string | number,
    stock: props.product.stock as string | number,
    photo: null as File | null,
    remove_photo: false,
});

const thumbnailPreview = ref<string | null>(null);
const thumbnailError = ref<string | null>(null);
const thumbnailInputRef = ref<HTMLInputElement | null>(null);

const triggerThumbnailSelect = () => {
    thumbnailInputRef.value?.click();
};

const handleThumbnailChange = (e: Event) => {
    const target = e.target as HTMLInputElement;
    const file = target.files?.[0];
    if (!file) return;

    if (!file.type.startsWith("image/")) {
        thumbnailError.value = "File harus berupa format gambar (JPG, PNG, atau WebP).";
        if (target) target.value = "";
        return;
    }

    if (file.size > 2 * 1024 * 1024) {
        thumbnailError.value = "Ukuran gambar terlalu besar (maksimal 2MB).";
        if (target) target.value = "";
        return;
    }

    thumbnailError.value = null;
    form.remove_photo = false;
    form.photo = file;
    thumbnailPreview.value = URL.createObjectURL(file);
};

const removeThumbnail = () => {
    form.photo = null;
    form.remove_photo = true;
    thumbnailPreview.value = null;
    thumbnailError.value = null;
    if (thumbnailInputRef.value) {
        thumbnailInputRef.value.value = "";
    }
};

// Current photo (existing from server)
const hasExistingPhoto = ref(!!props.product.photo_url && !form.remove_photo);

const removeExistingPhoto = () => {
    form.remove_photo = true;
    hasExistingPhoto.value = false;
};

const submit = () => {
    form.post(`/admin/products/${props.product.id}`, {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head :title="`Edit Produk: ${product.name}`" />

    <DashboardLayout>
        <!-- Back Link & Page Heading -->
        <div class="mb-6">
            <Link
                href="/admin/products"
                class="inline-flex items-center gap-2 text-[13px] font-bold text-charcoal hover:text-night-ink transition-colors mb-3 group"
            >
                <ArrowLeft class="size-4 transition-transform group-hover:-translate-x-1" />
                <span>Kembali ke Daftar Produk</span>
            </Link>

            <h1 class="text-[26px] sm:text-[30px] font-black text-night-ink tracking-tight leading-tight">
                Edit produk
            </h1>
            <p class="text-[14px] font-normal text-charcoal mt-1">
                Perbarui informasi produk <strong class="font-semibold">{{ product.name }}</strong> di Upcycle Store.
            </p>
        </div>

        <!-- 2-Column Form Layout -->
        <form @submit.prevent="submit" class="flex flex-col lg:flex-row items-start gap-6">
            <!-- Left Column: Main Fields -->
            <div
                class="flex-1 w-full bg-paper-white border-2 border-b-[4px] border-border-gray rounded-[22px] p-6 sm:p-7 shadow-xs space-y-6"
            >
                <!-- Nama Produk -->
                <div>
                    <label class="block text-[13px] font-bold text-night-ink mb-1.5">
                        Nama Produk
                        <span class="text-red-500">*</span>
                    </label>
                    <input
                        v-model="form.name"
                        type="text"
                        placeholder="Contoh: Tas Belanja Ramah Lingkungan dari Daur Ulang"
                        class="w-full h-[48px] px-3.5 bg-paper-white border-2 border-border-gray rounded-xl text-[15px] font-bold text-night-ink placeholder:font-normal placeholder:text-charcoal/60 focus:outline-none focus:border-eco-green focus:ring-4 focus:ring-eco-green/15 transition-all shadow-2xs"
                        :class="{ 'border-red-500': form.errors.name }"
                        autofocus
                    />
                    <p v-if="form.errors.name" class="text-[12px] font-bold text-red-600 mt-1">
                        {{ form.errors.name }}
                    </p>
                </div>

                <!-- Deskripsi Produk -->
                <div>
                    <label class="block text-[13px] font-bold text-night-ink mb-1.5">
                        Deskripsi Produk
                        <span class="text-charcoal font-normal text-[12px]">(Opsional)</span>
                    </label>
                    <textarea
                        v-model="form.description"
                        rows="5"
                        placeholder="Deskripsikan produk, bahan baku daur ulang yang digunakan, manfaat lingkungan, dan cara penggunaan..."
                        class="w-full px-3.5 py-3 bg-paper-white border-2 border-border-gray rounded-xl text-[14px] font-normal text-night-ink placeholder:text-charcoal/50 focus:outline-none focus:border-eco-green focus:ring-4 focus:ring-eco-green/15 transition-all resize-none leading-relaxed"
                        :class="{ 'border-red-500': form.errors.description }"
                    />
                    <p v-if="form.errors.description" class="text-[12px] font-bold text-red-600 mt-1">
                        {{ form.errors.description }}
                    </p>
                </div>

                <!-- Harga & Stok Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Harga Rupiah -->
                    <div>
                        <label class="block text-[13px] font-bold text-night-ink mb-1.5">
                            Harga
                            <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span
                                class="absolute left-3.5 top-1/2 -translate-y-1/2 text-[14px] font-bold text-charcoal pointer-events-none select-none"
                            >Rp</span>
                            <input
                                v-model="form.price_rupiah"
                                type="number"
                                min="1"
                                step="1"
                                placeholder="0"
                                class="w-full h-[44px] pl-10 pr-3.5 bg-paper-white border-2 border-border-gray rounded-xl text-[14px] font-semibold text-night-ink placeholder:text-charcoal/50 focus:outline-none focus:border-eco-green focus:ring-4 focus:ring-eco-green/15 transition-all"
                                :class="{ 'border-red-500': form.errors.price_rupiah }"
                            />
                        </div>
                        <p v-if="form.errors.price_rupiah" class="text-[12px] font-bold text-red-600 mt-1">
                            {{ form.errors.price_rupiah }}
                        </p>
                    </div>

                    <!-- Stok -->
                    <div>
                        <label class="block text-[13px] font-bold text-night-ink mb-1.5">
                            Stok
                            <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span
                                class="absolute right-3.5 top-1/2 -translate-y-1/2 text-[12px] font-semibold text-charcoal/60 pointer-events-none select-none"
                            >pcs</span>
                            <input
                                v-model="form.stock"
                                type="number"
                                min="0"
                                step="1"
                                placeholder="0"
                                class="w-full h-[44px] px-3.5 pr-12 bg-paper-white border-2 border-border-gray rounded-xl text-[14px] font-semibold text-night-ink placeholder:text-charcoal/50 focus:outline-none focus:border-eco-green focus:ring-4 focus:ring-eco-green/15 transition-all"
                                :class="{ 'border-red-500': form.errors.stock }"
                            />
                        </div>
                        <p v-if="form.errors.stock" class="text-[12px] font-bold text-red-600 mt-1">
                            {{ form.errors.stock }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Right Column: Foto, Action, Info -->
            <div class="w-full lg:w-[360px] space-y-5 shrink-0">
                <!-- Foto Produk Card -->
                <div
                    class="bg-paper-white border-2 border-b-[4px] border-border-gray rounded-[22px] p-5 shadow-xs"
                >
                    <h3 class="text-[16px] font-black text-night-ink mb-1">Foto Produk</h3>
                    <p class="text-[12px] font-medium text-charcoal mb-4">
                        Foto utama yang akan ditampilkan di halaman Upcycle Store.
                    </p>

                    <!-- Existing Photo -->
                    <div
                        v-if="product.photo_url && hasExistingPhoto && !thumbnailPreview"
                        class="relative rounded-2xl overflow-hidden border-2 border-border-gray bg-cloud-surface mb-3 group"
                    >
                        <img
                            :src="product.photo_url"
                            alt="Foto produk saat ini"
                            class="w-full h-[180px] object-cover"
                        />
                        <div
                            class="absolute inset-0 bg-night-ink/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2"
                        >
                            <button
                                type="button"
                                @click="triggerThumbnailSelect"
                                class="px-3 py-1.5 rounded-lg bg-paper-white text-night-ink text-[12px] font-extrabold shadow-sm hover:bg-cloud-surface transition-colors cursor-pointer"
                            >
                                Ganti
                            </button>
                            <button
                                type="button"
                                @click="removeExistingPhoto"
                                class="px-3 py-1.5 rounded-lg bg-red-600 text-white text-[12px] font-extrabold shadow-sm hover:bg-red-700 transition-colors cursor-pointer"
                            >
                                Hapus
                            </button>
                        </div>
                    </div>

                    <!-- New Photo Preview -->
                    <div
                        v-else-if="thumbnailPreview"
                        class="relative rounded-2xl overflow-hidden border-2 border-border-gray bg-cloud-surface mb-3 group"
                    >
                        <img
                            :src="thumbnailPreview"
                            alt="Preview foto produk baru"
                            class="w-full h-[180px] object-cover"
                        />
                        <div
                            class="absolute inset-0 bg-night-ink/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2"
                        >
                            <button
                                type="button"
                                @click="triggerThumbnailSelect"
                                class="px-3 py-1.5 rounded-lg bg-paper-white text-night-ink text-[12px] font-extrabold shadow-sm hover:bg-cloud-surface transition-colors cursor-pointer"
                            >
                                Ganti
                            </button>
                            <button
                                type="button"
                                @click="removeThumbnail"
                                class="px-3 py-1.5 rounded-lg bg-red-600 text-white text-[12px] font-extrabold shadow-sm hover:bg-red-700 transition-colors cursor-pointer"
                            >
                                Hapus
                            </button>
                        </div>
                    </div>

                    <!-- Dropzone (no photo) -->
                    <div
                        v-else
                        @click="triggerThumbnailSelect"
                        class="border-2 border-dashed rounded-2xl p-6 flex flex-col items-center justify-center text-center cursor-pointer transition-all mb-3 group"
                        :class="[
                            thumbnailError || form.errors.photo
                                ? 'border-red-400 bg-red-50/20'
                                : 'border-border-gray hover:border-eco-green hover:bg-sprout-tint/20'
                        ]"
                    >
                        <div
                            class="size-11 rounded-xl bg-cloud-surface border border-border-gray flex items-center justify-center text-charcoal group-hover:text-eco-green-dark group-hover:bg-sprout-tint/60 transition-colors mb-2.5"
                        >
                            <UploadCloud class="size-5" />
                        </div>
                        <p class="font-extrabold text-[13px] text-night-ink">
                            Pilih Foto Produk
                        </p>
                        <p class="text-[11px] font-medium text-charcoal mt-0.5">
                            PNG, JPG, atau WebP (Maks. 2MB)
                        </p>
                    </div>

                    <!-- Hidden File Input -->
                    <input
                        ref="thumbnailInputRef"
                        type="file"
                        accept="image/png,image/jpeg,image/webp"
                        class="hidden"
                        @change="handleThumbnailChange"
                    />

                    <p v-if="thumbnailError" class="text-[12px] font-bold text-red-600 flex items-center gap-1.5">
                        <AlertCircle class="size-4 shrink-0" />
                        <span>{{ thumbnailError }}</span>
                    </p>
                    <p v-if="form.errors.photo" class="text-[12px] font-bold text-red-600">
                        {{ form.errors.photo }}
                    </p>
                </div>

                <!-- Action Card -->
                <div
                    class="bg-paper-white border-2 border-b-[4px] border-border-gray rounded-[22px] p-5 shadow-xs space-y-3"
                >
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full h-[46px] rounded-xl border-2 border-b-[4px] border-eco-green-dark bg-eco-green text-white font-extrabold text-[14px] hover:brightness-105 active:translate-y-0.5 active:border-b-2 transition-all cursor-pointer shadow-xs disabled:opacity-60 disabled:cursor-not-allowed flex items-center justify-center gap-2"
                    >
                        <Check class="size-4.5" />
                        <span>{{ form.processing ? "Menyimpan..." : "Simpan Perubahan" }}</span>
                    </button>

                    <Link
                        href="/admin/products"
                        class="w-full h-[44px] rounded-xl border-2 border-b-[4px] border-border-gray bg-paper-white text-night-ink font-extrabold text-[14px] hover:bg-cloud-surface active:translate-y-0.5 active:border-b-2 transition-all cursor-pointer flex items-center justify-center"
                    >
                        Batal
                    </Link>
                </div>

                <!-- Info Card -->
                <div
                    class="bg-paper-white border-2 border-b-[4px] border-border-gray rounded-[22px] p-5 shadow-xs"
                >
                    <div class="flex items-center gap-2 mb-3">
                        <Info class="size-4.5 text-sky-500" />
                        <h4 class="font-extrabold text-[14px] text-night-ink">Info Produk</h4>
                    </div>
                    <ul class="text-[12px] font-medium text-charcoal space-y-2 leading-relaxed">
                        <li class="flex items-start gap-2">
                            <span class="size-1.5 rounded-full bg-eco-green mt-1.5 shrink-0" />
                            <span>Harga dalam Rupiah (IDR). Poin setara akan dihitung otomatis.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="size-1.5 rounded-full bg-eco-green mt-1.5 shrink-0" />
                            <span>Produk yang stoknya habis tetap tampil namun tidak bisa dibeli.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="size-1.5 rounded-full bg-eco-green mt-1.5 shrink-0" />
                            <span>Perubahan harga akan langsung memperbarui tampilan di Upcycle Store.</span>
                        </li>
                    </ul>
                </div>
            </div>
        </form>
    </DashboardLayout>
</template>
