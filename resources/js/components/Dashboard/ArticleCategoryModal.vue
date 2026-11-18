<script setup lang="ts">
import { ref, computed } from 'vue';
import { useForm, router, usePage } from '@inertiajs/vue3';
import {
    X,
    Plus,
    FolderKanban,
    Edit3,
    Trash2,
    Check,
    AlertCircle,
    Info,
    CheckCircle2,
    XCircle,
} from 'lucide-vue-next';
import StatusChip from '@/components/Dashboard/StatusChip.vue';

export interface ArticleCategoryItem {
    id: number;
    name: string;
    articles_count?: number;
}

const props = defineProps<{
    show: boolean;
    categories: ArticleCategoryItem[];
}>();

const emit = defineEmits<{
    (e: 'close'): void;
}>();

const page = usePage();

// Form Add Category
const addForm = useForm({
    name: '',
});

const isSubmittingAdd = ref(false);
const handleAddCategory = () => {
    if (!addForm.name.trim()) return;
    isSubmittingAdd.value = true;
    addForm.post('/admin/article-categories', {
        preserveScroll: true,
        onSuccess: () => {
            addForm.reset();
            isSubmittingAdd.value = false;
        },
        onError: () => {
            isSubmittingAdd.value = false;
        },
    });
};

// Inline Edit State
const editingCategoryId = ref<number | null>(null);
const editForm = useForm({
    name: '',
});

const startEdit = (cat: ArticleCategoryItem) => {
    editingCategoryId.value = cat.id;
    editForm.name = cat.name;
    editForm.clearErrors();
};

const cancelEdit = () => {
    editingCategoryId.value = null;
    editForm.reset();
    editForm.clearErrors();
};

const saveEdit = (cat: ArticleCategoryItem) => {
    if (!editForm.name.trim()) return;
    editForm.put(`/admin/article-categories/${cat.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            editingCategoryId.value = null;
            editForm.reset();
        },
    });
};

// Delete Category
const deletingCategoryId = ref<number | null>(null);
const categoryPendingDelete = ref<ArticleCategoryItem | null>(null);

const promptDelete = (cat: ArticleCategoryItem) => {
    if ((cat.articles_count ?? 0) > 0) return;
    categoryPendingDelete.value = cat;
};

const cancelDeletePrompt = () => {
    categoryPendingDelete.value = null;
};

const confirmDelete = (cat: ArticleCategoryItem) => {
    deletingCategoryId.value = cat.id;
    router.delete(`/admin/article-categories/${cat.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            deletingCategoryId.value = null;
            categoryPendingDelete.value = null;
        },
        onError: () => {
            deletingCategoryId.value = null;
        },
    });
};

const flashSuccess = computed(() => page.props.flash?.success);
const flashError = computed(() => page.props.flash?.error);
</script>

<template>
    <teleport to="body">
        <transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="show"
                class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-night-ink/50 p-4 backdrop-blur-xs"
                @click.self="emit('close')"
            >
                <div
                    class="animate-in fade-in zoom-in-95 relative my-8 w-full max-w-[560px] rounded-[24px] border-2 border-border-gray bg-paper-white p-6 shadow-2xl duration-150 sm:p-7"
                >
                    <!-- Header -->
                    <div
                        class="mb-4 flex items-start justify-between gap-4 border-b border-border-gray/70 pb-4"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                class="flex size-10 shrink-0 items-center justify-center rounded-xl border border-eco-green/30 bg-sprout-tint text-eco-green-dark"
                            >
                                <FolderKanban class="size-5" />
                            </div>
                            <div>
                                <h3
                                    class="text-[20px] leading-tight font-black text-night-ink"
                                >
                                    Kelola Kategori Artikel
                                </h3>
                                <p
                                    class="mt-0.5 text-[13px] font-medium text-charcoal"
                                >
                                    Tambah, ubah nama, atau hapus kategori
                                    edukasi lingkungan.
                                </p>
                            </div>
                        </div>

                        <button
                            type="button"
                            @click="emit('close')"
                            class="cursor-pointer rounded-lg p-1.5 text-charcoal transition-colors hover:bg-cloud-surface hover:text-night-ink"
                        >
                            <X class="size-5" />
                        </button>
                    </div>

                    <!-- Flash Message Alert -->
                    <div
                        v-if="flashSuccess"
                        class="mb-4 flex items-center gap-2.5 rounded-xl border border-eco-green/40 bg-sprout-tint p-3 text-[13px] font-bold text-night-ink"
                    >
                        <CheckCircle2
                            class="size-4.5 shrink-0 text-eco-green-dark"
                        />
                        <span class="truncate">{{ flashSuccess }}</span>
                    </div>

                    <div
                        v-if="flashError"
                        class="mb-4 flex items-center gap-2.5 rounded-xl border border-red-300 bg-red-50 p-3 text-[13px] font-bold text-red-800"
                    >
                        <XCircle class="size-4.5 shrink-0 text-red-600" />
                        <span class="truncate">{{ flashError }}</span>
                    </div>

                    <!-- Add Category Form -->
                    <form
                        @submit.prevent="handleAddCategory"
                        class="mb-5 space-y-2.5 rounded-2xl border border-border-gray/70 bg-cloud-surface/60 p-4"
                    >
                        <label
                            class="block text-[13px] font-extrabold text-night-ink"
                        >
                            Tambah Kategori Baru
                        </label>
                        <div class="flex items-center gap-2">
                            <input
                                v-model="addForm.name"
                                type="text"
                                placeholder="Contoh: Daur Ulang Tekstil"
                                class="h-[42px] flex-1 rounded-xl border-2 border-border-gray bg-paper-white px-3.5 text-[14px] font-medium text-night-ink shadow-2xs transition-all placeholder:text-charcoal/60 focus:border-eco-green focus:ring-4 focus:ring-eco-green/15 focus:outline-none"
                                :class="{
                                    'border-red-500': addForm.errors.name,
                                }"
                            />
                            <button
                                type="submit"
                                :disabled="
                                    isSubmittingAdd || !addForm.name.trim()
                                "
                                class="flex h-[42px] shrink-0 cursor-pointer items-center gap-1.5 rounded-xl border-2 border-b-[4px] border-eco-green-dark bg-eco-green px-4 text-[13px] font-extrabold text-white shadow-xs transition-all hover:brightness-105 active:translate-y-0.5 active:border-b-2 disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                <Plus class="size-4" />
                                <span>Tambah</span>
                            </button>
                        </div>
                        <p
                            v-if="addForm.errors.name"
                            class="text-[12px] font-bold text-red-600"
                        >
                            {{ addForm.errors.name }}
                        </p>
                    </form>

                    <!-- Category Confirmation Delete Modal Box -->
                    <div
                        v-if="categoryPendingDelete"
                        class="animate-in fade-in mb-4 space-y-2 rounded-xl border-2 border-red-200 bg-red-50 p-4 text-night-ink duration-150"
                    >
                        <div class="flex items-start gap-2.5">
                            <AlertCircle
                                class="mt-0.5 size-5 shrink-0 text-red-600"
                            />
                            <div>
                                <p
                                    class="text-[13px] font-extrabold text-red-900"
                                >
                                    Hapus kategori "{{
                                        categoryPendingDelete.name
                                    }}"?
                                </p>
                                <p
                                    class="mt-0.5 text-[12px] font-medium text-red-700"
                                >
                                    Kategori ini tidak memiliki artikel dan aman
                                    untuk dihapus secara permanen.
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center justify-end gap-2 pt-1">
                            <button
                                type="button"
                                @click="cancelDeletePrompt"
                                class="cursor-pointer rounded-lg border border-border-gray bg-paper-white px-3 py-1.5 text-[12px] font-extrabold text-night-ink transition-colors hover:bg-cloud-surface"
                            >
                                Batal
                            </button>
                            <button
                                type="button"
                                @click="confirmDelete(categoryPendingDelete)"
                                :disabled="
                                    deletingCategoryId ===
                                    categoryPendingDelete.id
                                "
                                class="cursor-pointer rounded-lg bg-red-600 px-3.5 py-1.5 text-[12px] font-extrabold text-white transition-colors hover:bg-red-700 disabled:opacity-50"
                            >
                                {{
                                    deletingCategoryId ===
                                    categoryPendingDelete.id
                                        ? 'Menghapus...'
                                        : 'Ya, Hapus'
                                }}
                            </button>
                        </div>
                    </div>

                    <!-- Categories List -->
                    <div class="max-h-[360px] space-y-2 overflow-y-auto pr-1">
                        <div
                            v-if="categories.length === 0"
                            class="py-8 text-center text-[13px] font-medium text-charcoal"
                        >
                            Belum ada kategori artikel yang dibuat.
                        </div>

                        <div
                            v-for="cat in categories"
                            :key="cat.id"
                            class="flex items-center justify-between gap-3 rounded-xl border border-border-gray/80 bg-paper-white p-3 transition-colors hover:bg-cloud-surface/40"
                        >
                            <!-- Normal Mode or Edit Mode -->
                            <template v-if="editingCategoryId !== cat.id">
                                <div class="flex min-w-0 items-center gap-2.5">
                                    <span
                                        class="truncate text-[14px] font-extrabold text-night-ink"
                                    >
                                        {{ cat.name }}
                                    </span>
                                    <StatusChip
                                        :label="`${cat.articles_count ?? 0} artikel`"
                                        :tone="
                                            (cat.articles_count ?? 0) > 0
                                                ? 'Green'
                                                : 'Neutral'
                                        "
                                    />
                                </div>

                                <div class="flex shrink-0 items-center gap-1">
                                    <button
                                        type="button"
                                        @click="startEdit(cat)"
                                        class="cursor-pointer rounded-lg p-1.5 text-charcoal transition-colors hover:bg-cloud-surface hover:text-night-ink"
                                        title="Ubah Nama Kategori"
                                    >
                                        <Edit3 class="size-4" />
                                    </button>

                                    <!-- Delete Button: Disabled if articles_count > 0 -->
                                    <button
                                        type="button"
                                        @click="promptDelete(cat)"
                                        :disabled="
                                            (cat.articles_count ?? 0) > 0
                                        "
                                        :title="
                                            (cat.articles_count ?? 0) > 0
                                                ? `Tidak dapat dihapus karena masih memuat ${cat.articles_count} artikel`
                                                : 'Hapus Kategori'
                                        "
                                        :class="[
                                            'rounded-lg p-1.5 transition-colors',
                                            (cat.articles_count ?? 0) > 0
                                                ? 'cursor-not-allowed text-charcoal/30'
                                                : 'cursor-pointer text-charcoal hover:bg-red-50 hover:text-red-600',
                                        ]"
                                    >
                                        <Trash2 class="size-4" />
                                    </button>
                                </div>
                            </template>

                            <!-- Inline Edit Mode -->
                            <template v-else>
                                <div
                                    class="flex min-w-0 flex-1 items-center gap-2"
                                >
                                    <input
                                        v-model="editForm.name"
                                        type="text"
                                        class="h-[36px] flex-1 rounded-lg border-2 border-eco-green bg-paper-white px-3 text-[13px] font-bold text-night-ink shadow-2xs focus:outline-none"
                                        @keydown.enter.prevent="saveEdit(cat)"
                                        @keydown.esc="cancelEdit"
                                        autofocus
                                    />
                                    <button
                                        type="button"
                                        @click="saveEdit(cat)"
                                        :disabled="
                                            editForm.processing ||
                                            !editForm.name.trim()
                                        "
                                        class="cursor-pointer rounded-lg bg-eco-green p-1.5 text-white transition-colors hover:brightness-105 disabled:opacity-50"
                                        title="Simpan"
                                    >
                                        <Check class="size-4" />
                                    </button>
                                    <button
                                        type="button"
                                        @click="cancelEdit"
                                        class="cursor-pointer rounded-lg p-1.5 text-charcoal transition-colors hover:bg-cloud-surface"
                                        title="Batal"
                                    >
                                        <X class="size-4" />
                                    </button>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Footer Note -->
                    <div
                        class="mt-5 flex items-center justify-between border-t border-border-gray/70 pt-3.5 text-[12px] text-charcoal"
                    >
                        <span class="flex items-center gap-1.5">
                            <Info class="size-3.5 text-charcoal/70" />
                            Kategori dengan artikel aktif terlindungi dari
                            penghapusan.
                        </span>
                        <button
                            type="button"
                            @click="emit('close')"
                            class="h-[38px] cursor-pointer rounded-xl border-2 border-b-[4px] border-border-gray bg-paper-white px-4 text-[13px] font-extrabold text-night-ink transition-all hover:bg-cloud-surface active:translate-y-0.5 active:border-b-2"
                        >
                            Selesai
                        </button>
                    </div>
                </div>
            </div>
        </transition>
    </teleport>
</template>
