<script setup lang="ts">
import { ref, computed } from "vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import DashboardLayout from "@/layouts/DashboardLayout.vue";
import RichTextEditor from "@/components/Dashboard/RichTextEditor.vue";
import AppDropdown from "@/components/Dashboard/AppDropdown.vue";
import ArticleCategoryModal from "@/components/Dashboard/ArticleCategoryModal.vue";
import {
    ArrowLeft,
    UploadCloud,
    X,
    Sparkles,
    Check,
    Image as ImageIcon,
    BookOpen,
    AlertCircle,
    Plus,
} from "lucide-vue-next";

export interface ArticleDetailData {
    id: number;
    title: string;
    slug: string;
    category_id: number;
    photo: string | null;
    photo_url?: string | null;
    content: string;
    created_at: string;
    category?: { id: number; name: string } | null;
    admin?: {
        id: number;
        user?: {
            name: string;
        } | null;
    } | null;
}

const props = defineProps<{
    article: ArticleDetailData;
    categories: Array<{ id: number; name: string }>;
}>();

const form = useForm({
    title: props.article.title || "",
    slug: props.article.slug || "",
    category_id: String(props.article.category_id || props.categories[0]?.id || ""),
    content: props.article.content || "",
    photo: null as File | null,
    remove_photo: false,
});

const thumbnailPreview = ref<string | null>(props.article.photo_url || null);
const thumbnailError = ref<string | null>(null);
const thumbnailInputRef = ref<HTMLInputElement | null>(null);

// Thumbnail selection
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
        thumbnailError.value = "Ukuran gambar thumbnail terlalu besar (maksimal 2MB).";
        if (target) target.value = "";
        return;
    }

    thumbnailError.value = null;
    form.photo = file;
    form.remove_photo = false;
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

const isCategoryModalOpen = ref(false);

const categoryDropdownOptions = computed(() =>
    props.categories.map((c) => ({
        value: String(c.id),
        label: c.name,
    }))
);

const submit = () => {
    form.transform((data) => ({
        ...data,
        _method: "PUT",
    })).post(`/admin/articles/${props.article.id}`, {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head :title="`Edit: ${article.title} - Admin ReValue`" />

    <DashboardLayout>
        <!-- Back Link & Page Heading -->
        <div class="mb-6">
            <Link
                href="/admin/articles"
                class="inline-flex items-center gap-2 text-[13px] font-bold text-charcoal hover:text-night-ink transition-colors mb-3 group"
            >
                <ArrowLeft
                    class="size-4 transition-transform group-hover:-translate-x-1"
                />
                <span>Kembali ke Daftar Artikel</span>
            </Link>

            <h1
                class="text-[26px] sm:text-[30px] font-black text-night-ink tracking-tight leading-tight"
            >
                Edit artikel edukasi
            </h1>
            <p class="text-[14px] font-normal text-charcoal mt-1">
                Perbarui isi konten, sesuaikan video edukasi, atau ubah kategori artikel ini.
            </p>
        </div>

        <!-- 2-Column Form Layout -->
        <form @submit.prevent="submit" class="flex flex-col lg:flex-row items-start gap-6">
            <!-- Left Column: Main Editor & Metadata (Wide) -->
            <div
                class="flex-1 w-full bg-paper-white border-2 border-b-[4px] border-border-gray rounded-[22px] p-6 sm:p-7 shadow-xs space-y-6"
            >
                <!-- Judul Artikel -->
                <div>
                    <label class="block text-[13px] font-bold text-night-ink mb-1.5">
                        Judul Artikel
                        <span class="text-red-500">*</span>
                    </label>
                    <input
                        v-model="form.title"
                        type="text"
                        placeholder="Judul artikel edukasi..."
                        class="w-full h-[48px] px-3.5 bg-paper-white border-2 border-border-gray rounded-xl text-[15px] font-bold text-night-ink placeholder:font-normal placeholder:text-charcoal/60 focus:outline-none focus:border-eco-green focus:ring-4 focus:ring-eco-green/15 transition-all shadow-2xs"
                        :class="{ 'border-red-500': form.errors.title }"
                    />
                    <p v-if="form.errors.title" class="text-[12px] font-bold text-red-600 mt-1">
                        {{ form.errors.title }}
                    </p>
                </div>

                <!-- Slug & Kategori Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Slug URL -->
                    <div>
                        <label class="block text-[13px] font-bold text-night-ink mb-1.5">
                            Slug URL
                            <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input
                                v-model="form.slug"
                                type="text"
                                placeholder="judul-artikel-anda"
                                class="w-full h-[44px] px-3.5 bg-paper-white border-2 border-border-gray rounded-xl text-[13px] font-medium text-night-ink placeholder:text-charcoal/60 focus:outline-none focus:border-eco-green focus:ring-4 focus:ring-eco-green/15 transition-all"
                                :class="{ 'border-red-500': form.errors.slug }"
                            />
                        </div>
                        <p v-if="form.errors.slug" class="text-[12px] font-bold text-red-600 mt-1">
                            {{ form.errors.slug }}
                        </p>
                    </div>

                    <!-- Kategori -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-[13px] font-bold text-night-ink">
                                Kategori Artikel
                                <span class="text-red-500">*</span>
                            </label>
                            <button
                                type="button"
                                @click="isCategoryModalOpen = true"
                                class="text-[12px] font-extrabold text-eco-green-dark hover:underline flex items-center gap-1 cursor-pointer"
                            >
                                <Plus class="size-3.5" />
                                <span>Kelola Kategori</span>
                            </button>
                        </div>
                        <AppDropdown
                            v-model="form.category_id"
                            :options="categoryDropdownOptions"
                            placeholder="Pilih Kategori"
                            class="w-full"
                            menu-width="w-full"
                        />
                        <p v-if="form.errors.category_id" class="text-[12px] font-bold text-red-600 mt-1">
                            {{ form.errors.category_id }}
                        </p>
                    </div>
                </div>

                <!-- Konten Editor (RichTextEditor Tiptap) -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-[13px] font-bold text-night-ink">
                            Isi Konten Artikel
                            <span class="text-red-500">*</span>
                        </label>
                        <span class="text-[12px] font-medium text-charcoal">
                            Mendukung Video YouTube & Foto
                        </span>
                    </div>

                    <RichTextEditor
                        v-model="form.content"
                        placeholder="Tuliskan panduan, tips, atau inspirasi ramah lingkungan di sini..."
                    />

                    <p v-if="form.errors.content" class="text-[12px] font-bold text-red-600 mt-1">
                        {{ form.errors.content }}
                    </p>
                </div>
            </div>

            <!-- Right Column: Thumbnail, Info & Action Card (Side Panel) -->
            <div class="w-full lg:w-[360px] space-y-5 shrink-0">
                <!-- Thumbnail Card -->
                <div
                    class="bg-paper-white border-2 border-b-[4px] border-border-gray rounded-[22px] p-5 shadow-xs"
                >
                    <h3 class="text-[16px] font-black text-night-ink mb-1">
                        Foto Thumbnail
                    </h3>
                    <p class="text-[12px] font-medium text-charcoal mb-4">
                        Gambar sampul utama yang akan ditampilkan pada kartu artikel dan beranda.
                    </p>

                    <!-- Preview or Upload Dropzone -->
                    <div
                        v-if="thumbnailPreview"
                        class="relative rounded-2xl overflow-hidden border-2 border-border-gray bg-cloud-surface mb-3 group"
                    >
                        <img
                            :src="thumbnailPreview"
                            alt="Preview thumbnail"
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
                            Pilih Foto Thumbnail
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
                        href="/admin/articles"
                        class="w-full h-[44px] rounded-xl border-2 border-b-[4px] border-border-gray bg-paper-white text-night-ink font-extrabold text-[14px] hover:bg-cloud-surface active:translate-y-0.5 active:border-b-2 transition-all cursor-pointer flex items-center justify-center"
                    >
                        Batal
                    </Link>
                </div>

                <!-- Meta Info Card -->
                <div
                    class="bg-paper-white border-2 border-b-[4px] border-border-gray rounded-[22px] p-5 shadow-xs"
                >
                    <h4 class="font-extrabold text-[14px] text-night-ink mb-2">
                        Informasi Publikasi
                    </h4>
                    <div class="text-[12px] font-medium text-charcoal space-y-1.5">
                        <p>
                            Penulis: <span class="font-bold text-night-ink">{{ article.admin?.user?.name || "Admin ReValue" }}</span>
                        </p>
                        <p>
                            Dibuat: <span class="font-bold text-night-ink">{{ new Date(article.created_at).toLocaleDateString("id-ID", { day: "numeric", month: "long", year: "numeric" }) }}</span>
                        </p>
                    </div>
                </div>
            </div>
        </form>

        <ArticleCategoryModal
            :show="isCategoryModalOpen"
            :categories="categories"
            @close="isCategoryModalOpen = false"
        />
    </DashboardLayout>
</template>
