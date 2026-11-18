<script setup lang="ts">
import { ref, computed } from "vue";
import { useForm, router, usePage } from "@inertiajs/vue3";
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
} from "lucide-vue-next";
import StatusChip from "@/components/Dashboard/StatusChip.vue";

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
    (e: "close"): void;
}>();

const page = usePage();

// Form Add Category
const addForm = useForm({
    name: "",
});

const isSubmittingAdd = ref(false);
const handleAddCategory = () => {
    if (!addForm.name.trim()) return;
    isSubmittingAdd.value = true;
    addForm.post("/admin/article-categories", {
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
    name: "",
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
                class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-night-ink/50 backdrop-blur-xs overflow-y-auto"
                @click.self="emit('close')"
            >
                <div
                    class="w-full max-w-[560px] bg-paper-white border-2 border-border-gray rounded-[24px] p-6 sm:p-7 shadow-2xl relative my-8 animate-in fade-in zoom-in-95 duration-150"
                >
                    <!-- Header -->
                    <div
                        class="flex items-start justify-between gap-4 pb-4 mb-4 border-b border-border-gray/70"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                class="size-10 rounded-xl bg-sprout-tint text-eco-green-dark flex items-center justify-center shrink-0 border border-eco-green/30"
                            >
                                <FolderKanban class="size-5" />
                            </div>
                            <div>
                                <h3
                                    class="text-[20px] font-black text-night-ink leading-tight"
                                >
                                    Kelola Kategori Artikel
                                </h3>
                                <p class="text-[13px] font-medium text-charcoal mt-0.5">
                                    Tambah, ubah nama, atau hapus kategori edukasi lingkungan.
                                </p>
                            </div>
                        </div>

                        <button
                            type="button"
                            @click="emit('close')"
                            class="p-1.5 rounded-lg text-charcoal hover:bg-cloud-surface hover:text-night-ink transition-colors cursor-pointer"
                        >
                            <X class="size-5" />
                        </button>
                    </div>

                    <!-- Flash Message Alert -->
                    <div
                        v-if="flashSuccess"
                        class="mb-4 flex items-center gap-2.5 p-3 rounded-xl bg-sprout-tint border border-eco-green/40 text-night-ink font-bold text-[13px]"
                    >
                        <CheckCircle2 class="size-4.5 text-eco-green-dark shrink-0" />
                        <span class="truncate">{{ flashSuccess }}</span>
                    </div>

                    <div
                        v-if="flashError"
                        class="mb-4 flex items-center gap-2.5 p-3 rounded-xl bg-red-50 border border-red-300 text-red-800 font-bold text-[13px]"
                    >
                        <XCircle class="size-4.5 text-red-600 shrink-0" />
                        <span class="truncate">{{ flashError }}</span>
                    </div>

                    <!-- Add Category Form -->
                    <form
                        @submit.prevent="handleAddCategory"
                        class="mb-5 p-4 rounded-2xl bg-cloud-surface/60 border border-border-gray/70 space-y-2.5"
                    >
                        <label class="block text-[13px] font-extrabold text-night-ink">
                            Tambah Kategori Baru
                        </label>
                        <div class="flex items-center gap-2">
                            <input
                                v-model="addForm.name"
                                type="text"
                                placeholder="Contoh: Daur Ulang Tekstil"
                                class="flex-1 h-[42px] px-3.5 bg-paper-white border-2 border-border-gray rounded-xl text-[14px] font-medium text-night-ink placeholder:text-charcoal/60 focus:outline-none focus:border-eco-green focus:ring-4 focus:ring-eco-green/15 transition-all shadow-2xs"
                                :class="{ 'border-red-500': addForm.errors.name }"
                            />
                            <button
                                type="submit"
                                :disabled="isSubmittingAdd || !addForm.name.trim()"
                                class="px-4 h-[42px] rounded-xl border-2 border-b-[4px] border-eco-green-dark bg-eco-green text-white font-extrabold text-[13px] hover:brightness-105 active:translate-y-0.5 active:border-b-2 transition-all cursor-pointer shadow-xs disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-1.5 shrink-0"
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
                        class="mb-4 p-4 rounded-xl bg-red-50 border-2 border-red-200 text-night-ink space-y-2 animate-in fade-in duration-150"
                    >
                        <div class="flex items-start gap-2.5">
                            <AlertCircle class="size-5 text-red-600 shrink-0 mt-0.5" />
                            <div>
                                <p class="text-[13px] font-extrabold text-red-900">
                                    Hapus kategori "{{ categoryPendingDelete.name }}"?
                                </p>
                                <p class="text-[12px] font-medium text-red-700 mt-0.5">
                                    Kategori ini tidak memiliki artikel dan aman untuk dihapus secara permanen.
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center justify-end gap-2 pt-1">
                            <button
                                type="button"
                                @click="cancelDeletePrompt"
                                class="px-3 py-1.5 rounded-lg border border-border-gray bg-paper-white text-[12px] font-extrabold text-night-ink hover:bg-cloud-surface transition-colors cursor-pointer"
                            >
                                Batal
                            </button>
                            <button
                                type="button"
                                @click="confirmDelete(categoryPendingDelete)"
                                :disabled="deletingCategoryId === categoryPendingDelete.id"
                                class="px-3.5 py-1.5 rounded-lg bg-red-600 text-white text-[12px] font-extrabold hover:bg-red-700 transition-colors cursor-pointer disabled:opacity-50"
                            >
                                {{ deletingCategoryId === categoryPendingDelete.id ? "Menghapus..." : "Ya, Hapus" }}
                            </button>
                        </div>
                    </div>

                    <!-- Categories List -->
                    <div class="space-y-2 max-h-[360px] overflow-y-auto pr-1">
                        <div
                            v-if="categories.length === 0"
                            class="text-center py-8 text-charcoal text-[13px] font-medium"
                        >
                            Belum ada kategori artikel yang dibuat.
                        </div>

                        <div
                            v-for="cat in categories"
                            :key="cat.id"
                            class="p-3 rounded-xl border border-border-gray/80 bg-paper-white hover:bg-cloud-surface/40 transition-colors flex items-center justify-between gap-3"
                        >
                            <!-- Normal Mode or Edit Mode -->
                            <template v-if="editingCategoryId !== cat.id">
                                <div class="min-w-0 flex items-center gap-2.5">
                                    <span
                                        class="font-extrabold text-[14px] text-night-ink truncate"
                                    >
                                        {{ cat.name }}
                                    </span>
                                    <StatusChip
                                        :label="`${cat.articles_count ?? 0} artikel`"
                                        :tone="(cat.articles_count ?? 0) > 0 ? 'Green' : 'Neutral'"
                                    />
                                </div>

                                <div class="flex items-center gap-1 shrink-0">
                                    <button
                                        type="button"
                                        @click="startEdit(cat)"
                                        class="p-1.5 rounded-lg text-charcoal hover:text-night-ink hover:bg-cloud-surface transition-colors cursor-pointer"
                                        title="Ubah Nama Kategori"
                                    >
                                        <Edit3 class="size-4" />
                                    </button>

                                    <!-- Delete Button: Disabled if articles_count > 0 -->
                                    <button
                                        type="button"
                                        @click="promptDelete(cat)"
                                        :disabled="(cat.articles_count ?? 0) > 0"
                                        :title="
                                            (cat.articles_count ?? 0) > 0
                                                ? `Tidak dapat dihapus karena masih memuat ${cat.articles_count} artikel`
                                                : 'Hapus Kategori'
                                        "
                                        :class="[
                                            'p-1.5 rounded-lg transition-colors',
                                            (cat.articles_count ?? 0) > 0
                                                ? 'text-charcoal/30 cursor-not-allowed'
                                                : 'text-charcoal hover:text-red-600 hover:bg-red-50 cursor-pointer',
                                        ]"
                                    >
                                        <Trash2 class="size-4" />
                                    </button>
                                </div>
                            </template>

                            <!-- Inline Edit Mode -->
                            <template v-else>
                                <div class="flex-1 flex items-center gap-2 min-w-0">
                                    <input
                                        v-model="editForm.name"
                                        type="text"
                                        class="flex-1 h-[36px] px-3 bg-paper-white border-2 border-eco-green rounded-lg text-[13px] font-bold text-night-ink focus:outline-none shadow-2xs"
                                        @keydown.enter.prevent="saveEdit(cat)"
                                        @keydown.esc="cancelEdit"
                                        autofocus
                                    />
                                    <button
                                        type="button"
                                        @click="saveEdit(cat)"
                                        :disabled="editForm.processing || !editForm.name.trim()"
                                        class="p-1.5 rounded-lg bg-eco-green text-white hover:brightness-105 transition-colors cursor-pointer disabled:opacity-50"
                                        title="Simpan"
                                    >
                                        <Check class="size-4" />
                                    </button>
                                    <button
                                        type="button"
                                        @click="cancelEdit"
                                        class="p-1.5 rounded-lg text-charcoal hover:bg-cloud-surface transition-colors cursor-pointer"
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
                        class="mt-5 pt-3.5 border-t border-border-gray/70 flex items-center justify-between text-[12px] text-charcoal"
                    >
                        <span class="flex items-center gap-1.5">
                            <Info class="size-3.5 text-charcoal/70" />
                            Kategori dengan artikel aktif terlindungi dari penghapusan.
                        </span>
                        <button
                            type="button"
                            @click="emit('close')"
                            class="px-4 h-[38px] rounded-xl border-2 border-b-[4px] border-border-gray bg-paper-white font-extrabold text-[13px] text-night-ink hover:bg-cloud-surface active:translate-y-0.5 active:border-b-2 transition-all cursor-pointer"
                        >
                            Selesai
                        </button>
                    </div>
                </div>
            </div>
        </transition>
    </teleport>
</template>
