<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import DashboardLayout from '@/layouts/DashboardLayout.vue';
import {
    ArrowLeft,
    UploadCloud,
    X,
    Info,
    AlertCircle,
    Check,
    ShoppingBag,
    Package,
} from 'lucide-vue-next';

const form = useForm({
    name: '',
    description: '',
    price_rupiah: '' as string | number,
    stock: 0 as string | number,
    photo: null as File | null,
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

    if (!file.type.startsWith('image/')) {
        thumbnailError.value =
            'File harus berupa format gambar (JPG, PNG, atau WebP).';
        if (target) target.value = '';
        return;
    }

    if (file.size > 2 * 1024 * 1024) {
        thumbnailError.value = 'Ukuran gambar terlalu besar (maksimal 2MB).';
        if (target) target.value = '';
        return;
    }

    thumbnailError.value = null;
    form.photo = file;
    thumbnailPreview.value = URL.createObjectURL(file);
};

const removeThumbnail = () => {
    form.photo = null;
    thumbnailPreview.value = null;
    thumbnailError.value = null;
    if (thumbnailInputRef.value) {
        thumbnailInputRef.value.value = '';
    }
};

const submit = () => {
    form.post('/admin/products', {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Tambah Produk Baru - Admin ReValue" />

    <DashboardLayout>
        <!-- Back Link & Page Heading -->
        <div class="mb-6">
            <Link
                href="/admin/products"
                class="group mb-3 inline-flex items-center gap-2 text-[13px] font-bold text-charcoal transition-colors hover:text-night-ink"
            >
                <ArrowLeft
                    class="size-4 transition-transform group-hover:-translate-x-1"
                />
                <span>Kembali ke Daftar Produk</span>
            </Link>

            <h1
                class="text-[26px] leading-tight font-black tracking-tight text-night-ink sm:text-[30px]"
            >
                Tambah produk baru
            </h1>
            <p class="mt-1 text-[14px] font-normal text-charcoal">
                Tambahkan produk daur ulang dan ramah lingkungan ke Upcycle
                Store ReValue.
            </p>
        </div>

        <!-- 2-Column Form Layout -->
        <form
            @submit.prevent="submit"
            class="flex flex-col items-start gap-6 lg:flex-row"
        >
            <!-- Left Column: Main Fields -->
            <div
                class="w-full flex-1 space-y-6 rounded-[22px] border-2 border-b-[4px] border-border-gray bg-paper-white p-6 shadow-xs sm:p-7"
            >
                <!-- Nama Produk -->
                <div>
                    <label
                        class="mb-1.5 block text-[13px] font-bold text-night-ink"
                    >
                        Nama Produk
                        <span class="text-red-500">*</span>
                    </label>
                    <input
                        v-model="form.name"
                        type="text"
                        placeholder="Contoh: Tas Belanja Ramah Lingkungan dari Daur Ulang"
                        class="h-[48px] w-full rounded-xl border-2 border-border-gray bg-paper-white px-3.5 text-[15px] font-bold text-night-ink shadow-2xs transition-all placeholder:font-normal placeholder:text-charcoal/60 focus:border-eco-green focus:ring-4 focus:ring-eco-green/15 focus:outline-none"
                        :class="{ 'border-red-500': form.errors.name }"
                        autofocus
                    />
                    <p
                        v-if="form.errors.name"
                        class="mt-1 text-[12px] font-bold text-red-600"
                    >
                        {{ form.errors.name }}
                    </p>
                </div>

                <!-- Deskripsi Produk -->
                <div>
                    <label
                        class="mb-1.5 block text-[13px] font-bold text-night-ink"
                    >
                        Deskripsi Produk
                        <span class="text-[12px] font-normal text-charcoal"
                            >(Opsional)</span
                        >
                    </label>
                    <textarea
                        v-model="form.description"
                        rows="5"
                        placeholder="Deskripsikan produk, bahan baku daur ulang yang digunakan, manfaat lingkungan, dan cara penggunaan..."
                        class="w-full resize-none rounded-xl border-2 border-border-gray bg-paper-white px-3.5 py-3 text-[14px] leading-relaxed font-normal text-night-ink transition-all placeholder:text-charcoal/50 focus:border-eco-green focus:ring-4 focus:ring-eco-green/15 focus:outline-none"
                        :class="{ 'border-red-500': form.errors.description }"
                    />
                    <p
                        v-if="form.errors.description"
                        class="mt-1 text-[12px] font-bold text-red-600"
                    >
                        {{ form.errors.description }}
                    </p>
                </div>

                <!-- Harga & Stok Grid -->
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <!-- Harga Rupiah -->
                    <div>
                        <label
                            class="mb-1.5 block text-[13px] font-bold text-night-ink"
                        >
                            Harga
                            <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span
                                class="pointer-events-none absolute top-1/2 left-3.5 -translate-y-1/2 text-[14px] font-bold text-charcoal select-none"
                                >Rp</span
                            >
                            <input
                                v-model="form.price_rupiah"
                                type="number"
                                min="1"
                                step="1"
                                placeholder="0"
                                class="h-[44px] w-full rounded-xl border-2 border-border-gray bg-paper-white pr-3.5 pl-10 text-[14px] font-semibold text-night-ink transition-all placeholder:text-charcoal/50 focus:border-eco-green focus:ring-4 focus:ring-eco-green/15 focus:outline-none"
                                :class="{
                                    'border-red-500': form.errors.price_rupiah,
                                }"
                            />
                        </div>
                        <p
                            v-if="form.errors.price_rupiah"
                            class="mt-1 text-[12px] font-bold text-red-600"
                        >
                            {{ form.errors.price_rupiah }}
                        </p>
                    </div>

                    <!-- Stok -->
                    <div>
                        <label
                            class="mb-1.5 block text-[13px] font-bold text-night-ink"
                        >
                            Stok
                            <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span
                                class="pointer-events-none absolute top-1/2 right-3.5 -translate-y-1/2 text-[12px] font-semibold text-charcoal/60 select-none"
                                >pcs</span
                            >
                            <input
                                v-model="form.stock"
                                type="number"
                                min="0"
                                step="1"
                                placeholder="0"
                                class="h-[44px] w-full rounded-xl border-2 border-border-gray bg-paper-white px-3.5 pr-12 text-[14px] font-semibold text-night-ink transition-all placeholder:text-charcoal/50 focus:border-eco-green focus:ring-4 focus:ring-eco-green/15 focus:outline-none"
                                :class="{ 'border-red-500': form.errors.stock }"
                            />
                        </div>
                        <p
                            v-if="form.errors.stock"
                            class="mt-1 text-[12px] font-bold text-red-600"
                        >
                            {{ form.errors.stock }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Right Column: Foto, Action, Info -->
            <div class="w-full shrink-0 space-y-5 lg:w-[360px]">
                <!-- Foto Produk Card -->
                <div
                    class="rounded-[22px] border-2 border-b-[4px] border-border-gray bg-paper-white p-5 shadow-xs"
                >
                    <h3 class="mb-1 text-[16px] font-black text-night-ink">
                        Foto Produk
                    </h3>
                    <p class="mb-4 text-[12px] font-medium text-charcoal">
                        Foto utama yang akan ditampilkan di halaman Upcycle
                        Store.
                    </p>

                    <!-- Preview or Dropzone -->
                    <div
                        v-if="thumbnailPreview"
                        class="group relative mb-3 overflow-hidden rounded-2xl border-2 border-border-gray bg-cloud-surface"
                    >
                        <img
                            :src="thumbnailPreview"
                            alt="Preview foto produk"
                            class="h-[180px] w-full object-cover"
                        />
                        <div
                            class="absolute inset-0 flex items-center justify-center gap-2 bg-night-ink/40 opacity-0 transition-opacity group-hover:opacity-100"
                        >
                            <button
                                type="button"
                                @click="triggerThumbnailSelect"
                                class="cursor-pointer rounded-lg bg-paper-white px-3 py-1.5 text-[12px] font-extrabold text-night-ink shadow-sm transition-colors hover:bg-cloud-surface"
                            >
                                Ganti
                            </button>
                            <button
                                type="button"
                                @click="removeThumbnail"
                                class="cursor-pointer rounded-lg bg-red-600 px-3 py-1.5 text-[12px] font-extrabold text-white shadow-sm transition-colors hover:bg-red-700"
                            >
                                Hapus
                            </button>
                        </div>
                    </div>

                    <div
                        v-else
                        @click="triggerThumbnailSelect"
                        class="group mb-3 flex cursor-pointer flex-col items-center justify-center rounded-2xl border-2 border-dashed p-6 text-center transition-all"
                        :class="[
                            thumbnailError || form.errors.photo
                                ? 'border-red-400 bg-red-50/20'
                                : 'border-border-gray hover:border-eco-green hover:bg-sprout-tint/20',
                        ]"
                    >
                        <div
                            class="mb-2.5 flex size-11 items-center justify-center rounded-xl border border-border-gray bg-cloud-surface text-charcoal transition-colors group-hover:bg-sprout-tint/60 group-hover:text-eco-green-dark"
                        >
                            <UploadCloud class="size-5" />
                        </div>
                        <p class="text-[13px] font-extrabold text-night-ink">
                            Pilih Foto Produk
                        </p>
                        <p class="mt-0.5 text-[11px] font-medium text-charcoal">
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

                    <p
                        v-if="thumbnailError"
                        class="flex items-center gap-1.5 text-[12px] font-bold text-red-600"
                    >
                        <AlertCircle class="size-4 shrink-0" />
                        <span>{{ thumbnailError }}</span>
                    </p>
                    <p
                        v-if="form.errors.photo"
                        class="text-[12px] font-bold text-red-600"
                    >
                        {{ form.errors.photo }}
                    </p>
                </div>

                <!-- Action Card -->
                <div
                    class="space-y-3 rounded-[22px] border-2 border-b-[4px] border-border-gray bg-paper-white p-5 shadow-xs"
                >
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="flex h-[46px] w-full cursor-pointer items-center justify-center gap-2 rounded-xl border-2 border-b-[4px] border-eco-green-dark bg-eco-green text-[14px] font-extrabold text-white shadow-xs transition-all hover:brightness-105 active:translate-y-0.5 active:border-b-2 disabled:cursor-not-allowed disabled:opacity-60"
                    >
                        <Check class="size-4.5" />
                        <span>{{
                            form.processing ? 'Menyimpan...' : 'Simpan Produk'
                        }}</span>
                    </button>

                    <Link
                        href="/admin/products"
                        class="flex h-[44px] w-full cursor-pointer items-center justify-center rounded-xl border-2 border-b-[4px] border-border-gray bg-paper-white text-[14px] font-extrabold text-night-ink transition-all hover:bg-cloud-surface active:translate-y-0.5 active:border-b-2"
                    >
                        Batal
                    </Link>
                </div>

                <!-- Info Card -->
                <div
                    class="rounded-[22px] border-2 border-b-[4px] border-border-gray bg-paper-white p-5 shadow-xs"
                >
                    <div class="mb-3 flex items-center gap-2">
                        <Info class="size-4.5 text-sky-500" />
                        <h4 class="text-[14px] font-extrabold text-night-ink">
                            Info Produk
                        </h4>
                    </div>
                    <ul
                        class="space-y-2 text-[12px] leading-relaxed font-medium text-charcoal"
                    >
                        <li class="flex items-start gap-2">
                            <span
                                class="mt-1.5 size-1.5 shrink-0 rounded-full bg-eco-green"
                            />
                            <span
                                >Harga dalam Rupiah (IDR). Poin setara akan
                                dihitung otomatis.</span
                            >
                        </li>
                        <li class="flex items-start gap-2">
                            <span
                                class="mt-1.5 size-1.5 shrink-0 rounded-full bg-eco-green"
                            />
                            <span
                                >Produk yang stoknya habis tetap tampil namun
                                tidak bisa dibeli.</span
                            >
                        </li>
                        <li class="flex items-start gap-2">
                            <span
                                class="mt-1.5 size-1.5 shrink-0 rounded-full bg-eco-green"
                            />
                            <span
                                >Foto produk akan ditampilkan di halaman Upcycle
                                Store dan landing page.</span
                            >
                        </li>
                    </ul>
                </div>
            </div>
        </form>
    </DashboardLayout>
</template>
