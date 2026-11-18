<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import DashboardLayout from '@/layouts/DashboardLayout.vue';
import RichTextEditor from '@/components/Dashboard/RichTextEditor.vue';
import AppDropdown from '@/components/Dashboard/AppDropdown.vue';
import ArticleCategoryModal from '@/components/Dashboard/ArticleCategoryModal.vue';
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
} from 'lucide-vue-next';

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
    title: props.article.title || '',
    slug: props.article.slug || '',
    category_id: String(
        props.article.category_id || props.categories[0]?.id || '',
    ),
    content: props.article.content || '',
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

    if (!file.type.startsWith('image/')) {
        thumbnailError.value =
            'File harus berupa format gambar (JPG, PNG, atau WebP).';
        if (target) target.value = '';
        return;
    }

    if (file.size > 2 * 1024 * 1024) {
        thumbnailError.value =
            'Ukuran gambar thumbnail terlalu besar (maksimal 2MB).';
        if (target) target.value = '';
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
        thumbnailInputRef.value.value = '';
    }
};

const isCategoryModalOpen = ref(false);

const categoryDropdownOptions = computed(() =>
    props.categories.map((c) => ({
        value: String(c.id),
        label: c.name,
    })),
);

const submit = () => {
    form.transform((data) => ({
        ...data,
        _method: 'PUT',
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
                class="group mb-3 inline-flex items-center gap-2 text-[13px] font-bold text-charcoal transition-colors hover:text-night-ink"
            >
                <ArrowLeft
                    class="size-4 transition-transform group-hover:-translate-x-1"
                />
                <span>Kembali ke Daftar Artikel</span>
            </Link>

            <h1
                class="text-[26px] leading-tight font-black tracking-tight text-night-ink sm:text-[30px]"
            >
                Edit artikel edukasi
            </h1>
            <p class="mt-1 text-[14px] font-normal text-charcoal">
                Perbarui isi konten, sesuaikan video edukasi, atau ubah kategori
                artikel ini.
            </p>
        </div>

        <!-- 2-Column Form Layout -->
        <form
            @submit.prevent="submit"
            class="flex flex-col items-start gap-6 lg:flex-row"
        >
            <!-- Left Column: Main Editor & Metadata (Wide) -->
            <div
                class="w-full flex-1 space-y-6 rounded-[22px] border-2 border-b-[4px] border-border-gray bg-paper-white p-6 shadow-xs sm:p-7"
            >
                <!-- Judul Artikel -->
                <div>
                    <label
                        class="mb-1.5 block text-[13px] font-bold text-night-ink"
                    >
                        Judul Artikel
                        <span class="text-red-500">*</span>
                    </label>
                    <input
                        v-model="form.title"
                        type="text"
                        placeholder="Judul artikel edukasi..."
                        class="h-[48px] w-full rounded-xl border-2 border-border-gray bg-paper-white px-3.5 text-[15px] font-bold text-night-ink shadow-2xs transition-all placeholder:font-normal placeholder:text-charcoal/60 focus:border-eco-green focus:ring-4 focus:ring-eco-green/15 focus:outline-none"
                        :class="{ 'border-red-500': form.errors.title }"
                    />
                    <p
                        v-if="form.errors.title"
                        class="mt-1 text-[12px] font-bold text-red-600"
                    >
                        {{ form.errors.title }}
                    </p>
                </div>

                <!-- Slug & Kategori Grid -->
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <!-- Slug URL -->
                    <div>
                        <label
                            class="mb-1.5 block text-[13px] font-bold text-night-ink"
                        >
                            Slug URL
                            <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input
                                v-model="form.slug"
                                type="text"
                                placeholder="judul-artikel-anda"
                                class="h-[44px] w-full rounded-xl border-2 border-border-gray bg-paper-white px-3.5 text-[13px] font-medium text-night-ink transition-all placeholder:text-charcoal/60 focus:border-eco-green focus:ring-4 focus:ring-eco-green/15 focus:outline-none"
                                :class="{ 'border-red-500': form.errors.slug }"
                            />
                        </div>
                        <p
                            v-if="form.errors.slug"
                            class="mt-1 text-[12px] font-bold text-red-600"
                        >
                            {{ form.errors.slug }}
                        </p>
                    </div>

                    <!-- Kategori -->
                    <div>
                        <div class="mb-1.5 flex items-center justify-between">
                            <label
                                class="block text-[13px] font-bold text-night-ink"
                            >
                                Kategori Artikel
                                <span class="text-red-500">*</span>
                            </label>
                            <button
                                type="button"
                                @click="isCategoryModalOpen = true"
                                class="flex cursor-pointer items-center gap-1 text-[12px] font-extrabold text-eco-green-dark hover:underline"
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
                        <p
                            v-if="form.errors.category_id"
                            class="mt-1 text-[12px] font-bold text-red-600"
                        >
                            {{ form.errors.category_id }}
                        </p>
                    </div>
                </div>

                <!-- Konten Editor (RichTextEditor Tiptap) -->
                <div>
                    <div class="mb-2 flex items-center justify-between">
                        <label
                            class="block text-[13px] font-bold text-night-ink"
                        >
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

                    <p
                        v-if="form.errors.content"
                        class="mt-1 text-[12px] font-bold text-red-600"
                    >
                        {{ form.errors.content }}
                    </p>
                </div>
            </div>

            <!-- Right Column: Thumbnail, Info & Action Card (Side Panel) -->
            <div class="w-full shrink-0 space-y-5 lg:w-[360px]">
                <!-- Thumbnail Card -->
                <div
                    class="rounded-[22px] border-2 border-b-[4px] border-border-gray bg-paper-white p-5 shadow-xs"
                >
                    <h3 class="mb-1 text-[16px] font-black text-night-ink">
                        Foto Thumbnail
                    </h3>
                    <p class="mb-4 text-[12px] font-medium text-charcoal">
                        Gambar sampul utama yang akan ditampilkan pada kartu
                        artikel dan beranda.
                    </p>

                    <!-- Preview or Upload Dropzone -->
                    <div
                        v-if="thumbnailPreview"
                        class="group relative mb-3 overflow-hidden rounded-2xl border-2 border-border-gray bg-cloud-surface"
                    >
                        <img
                            :src="thumbnailPreview"
                            alt="Preview thumbnail"
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
                            Pilih Foto Thumbnail
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
                            form.processing
                                ? 'Menyimpan...'
                                : 'Simpan Perubahan'
                        }}</span>
                    </button>

                    <Link
                        href="/admin/articles"
                        class="flex h-[44px] w-full cursor-pointer items-center justify-center rounded-xl border-2 border-b-[4px] border-border-gray bg-paper-white text-[14px] font-extrabold text-night-ink transition-all hover:bg-cloud-surface active:translate-y-0.5 active:border-b-2"
                    >
                        Batal
                    </Link>
                </div>

                <!-- Meta Info Card -->
                <div
                    class="rounded-[22px] border-2 border-b-[4px] border-border-gray bg-paper-white p-5 shadow-xs"
                >
                    <h4 class="mb-2 text-[14px] font-extrabold text-night-ink">
                        Informasi Publikasi
                    </h4>
                    <div
                        class="space-y-1.5 text-[12px] font-medium text-charcoal"
                    >
                        <p>
                            Penulis:
                            <span class="font-bold text-night-ink">{{
                                article.admin?.user?.name || 'Admin ReValue'
                            }}</span>
                        </p>
                        <p>
                            Dibuat:
                            <span class="font-bold text-night-ink">{{
                                new Date(article.created_at).toLocaleDateString(
                                    'id-ID',
                                    {
                                        day: 'numeric',
                                        month: 'long',
                                        year: 'numeric',
                                    },
                                )
                            }}</span>
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
