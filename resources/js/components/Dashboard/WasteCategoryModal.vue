<script setup lang="ts">
import { watch, computed } from "vue";
import { useForm } from "@inertiajs/vue3";
import { X, LucideRecycle } from "lucide-vue-next";

export interface WasteCategoryData {
    id?: number;
    name: string;
    description: string | null;
    points_per_kg: number;
    deleted_at?: string | null;
}

const props = defineProps<{
    show: boolean;
    category?: WasteCategoryData | null;
}>();

const emit = defineEmits<{
    (e: "close"): void;
}>();

const isEdit = computed(() => !!props.category && !!props.category.id);

const form = useForm({
    name: "",
    points_per_kg: 1000,
    description: "",
});

// Sync form values whenever modal opens or category changes
watch(
    () => [props.show, props.category],
    ([isOpen]) => {
        if (isOpen) {
            form.clearErrors();
            if (props.category) {
                form.name = props.category.name || "";
                form.points_per_kg = props.category.points_per_kg || 1000;
                form.description = props.category.description || "";
            } else {
                form.name = "";
                form.points_per_kg = 1000;
                form.description = "";
            }
        }
    },
    { immediate: true },
);

const handleClose = () => {
    if (form.processing) return;
    form.clearErrors();
    emit("close");
};

const submit = () => {
    if (isEdit.value && props.category?.id) {
        form.put(`/admin/waste-categories/${props.category.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                form.reset();
                emit("close");
            },
        });
    } else {
        form.post("/admin/waste-categories", {
            preserveScroll: true,
            onSuccess: () => {
                form.reset();
                emit("close");
            },
        });
    }
};
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
                @click.self="handleClose"
            >
                <div
                    class="w-full max-w-[560px] bg-paper-white border-2 border-border-gray rounded-[24px] p-6 sm:p-8 shadow-2xl relative my-8 animate-in fade-in zoom-in-95 duration-150"
                >
                    <!-- Header -->
                    <div
                        class="flex items-start justify-between gap-4 mb-5 pb-4 border-b border-border-gray/70"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                class="size-11 rounded-2xl bg-sprout-tint text-eco-green-dark border-2 border-eco-green/30 flex items-center justify-center shrink-0 shadow-2xs"
                            >
                                <LucideRecycle
                                    class="size-6 text-eco-green-dark"
                                />
                            </div>
                            <div>
                                <h3
                                    class="text-[20px] sm:text-[22px] font-extrabold text-night-ink leading-tight"
                                >
                                    {{
                                        isEdit
                                            ? "Edit Kategori Sampah"
                                            : "Tambah Kategori Sampah"
                                    }}
                                </h3>
                                <p
                                    class="text-[13px] font-medium text-charcoal mt-0.5"
                                >
                                    {{
                                        isEdit
                                            ? "Perbarui informasi dan nilai poin per kg."
                                            : "Tambahkan jenis sampah baru dan tetapkan nilai poinnya."
                                    }}
                                </p>
                            </div>
                        </div>
                        <button
                            type="button"
                            class="p-2 rounded-xl text-charcoal hover:bg-cloud-surface hover:text-night-ink transition-colors cursor-pointer"
                            @click="handleClose"
                            :disabled="form.processing"
                            aria-label="Tutup Modal"
                        >
                            <X class="size-5" />
                        </button>
                    </div>

                    <!-- Form Body -->
                    <form @submit.prevent="submit" class="space-y-5">
                        <!-- Field: Nama Kategori -->
                        <div>
                            <label
                                class="block text-[14px] font-extrabold text-night-ink mb-1.5 flex items-center gap-2"
                            >
                                <span>Nama Kategori Sampah</span>
                                <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="form.name"
                                type="text"
                                placeholder="Contoh: Plastik PET, Kertas & Kardus, Logam"
                                class="w-full h-[48px] px-4 rounded-xl border-2 font-bold text-[14px] text-night-ink placeholder:text-charcoal/50 placeholder:font-normal transition-all outline-none"
                                :class="[
                                    form.errors.name
                                        ? 'border-red-400 bg-red-50/30 focus:border-red-500 focus:ring-4 focus:ring-red-100'
                                        : 'border-border-gray bg-paper-white focus:border-eco-green focus:ring-4 focus:ring-eco-green/10',
                                ]"
                                required
                            />
                            <p
                                v-if="form.errors.name"
                                class="mt-1.5 text-[12px] font-bold text-red-600 flex items-center gap-1"
                            >
                                <span>•</span> {{ form.errors.name }}
                            </p>
                        </div>

                        <!-- Field: Poin per Kg -->
                        <div>
                            <label
                                class="block text-[14px] font-extrabold text-night-ink mb-1.5 flex items-center gap-2"
                            >
                                <span>Nilai Poin per Kilogram (Kg)</span>
                                <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input
                                    v-model.number="form.points_per_kg"
                                    type="number"
                                    min="0"
                                    step="50"
                                    placeholder="Contoh: 3500"
                                    class="w-full h-[48px] pl-4 pr-18 rounded-xl border-2 font-extrabold text-[15px] text-night-ink transition-all outline-none"
                                    :class="[
                                        form.errors.points_per_kg
                                            ? 'border-red-400 bg-red-50/30 focus:border-red-500 focus:ring-4 focus:ring-red-100'
                                            : 'border-border-gray bg-paper-white focus:border-eco-green focus:ring-4 focus:ring-eco-green/10',
                                    ]"
                                    required
                                />
                                <div
                                    class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-[13px] font-extrabold text-charcoal"
                                >
                                    Poin / kg
                                </div>
                            </div>
                            <p
                                class="mt-1 text-[12px] font-medium text-charcoal"
                            >
                                Nasabah akan memperoleh poin ini untuk setiap 1
                                kg sampah yang disetorkan.
                            </p>
                            <p
                                v-if="form.errors.points_per_kg"
                                class="mt-1 text-[12px] font-bold text-red-600 flex items-center gap-1"
                            >
                                <span>•</span> {{ form.errors.points_per_kg }}
                            </p>
                        </div>

                        <!-- Field: Deskripsi -->
                        <div>
                            <label
                                class="block text-[14px] font-extrabold text-night-ink mb-1.5 flex items-center gap-2"
                            >
                                <span>Deskripsi & Kriteria Sampah</span>
                                <span
                                    class="text-charcoal/60 font-medium text-[12px]"
                                    >(Opsional)</span
                                >
                            </label>
                            <textarea
                                v-model="form.description"
                                rows="3"
                                placeholder="Jelaskan jenis barang atau kondisi yang dapat diterima (misal: botol bersih tanpa tutup, kardus kering, dsb)..."
                                class="w-full p-3.5 rounded-xl border-2 font-medium text-[14px] text-night-ink placeholder:text-charcoal/50 transition-all outline-none resize-none"
                                :class="[
                                    form.errors.description
                                        ? 'border-red-400 bg-red-50/30 focus:border-red-500 focus:ring-4 focus:ring-red-100'
                                        : 'border-border-gray bg-paper-white focus:border-eco-green focus:ring-4 focus:ring-eco-green/10',
                                ]"
                            ></textarea>
                            <p
                                v-if="form.errors.description"
                                class="mt-1 text-[12px] font-bold text-red-600 flex items-center gap-1"
                            >
                                <span>•</span> {{ form.errors.description }}
                            </p>
                        </div>

                        <!-- Footer Actions -->
                        <div
                            class="flex items-center justify-end gap-3 pt-4 border-t border-border-gray/70"
                        >
                            <button
                                type="button"
                                class="px-5 h-[46px] rounded-xl border-2 border-b-[4px] border-border-gray bg-paper-white font-extrabold text-[14px] text-night-ink hover:bg-cloud-surface active:translate-y-0.5 active:border-b-2 transition-all cursor-pointer disabled:opacity-60 disabled:cursor-not-allowed"
                                @click="handleClose"
                                :disabled="form.processing"
                            >
                                Batal
                            </button>
                            <button
                                type="submit"
                                class="px-6 h-[46px] rounded-xl border-2 border-b-[4px] border-eco-green-dark bg-eco-green font-extrabold text-[14px] text-white hover:brightness-105 active:translate-y-0.5 active:border-b-2 transition-all cursor-pointer disabled:opacity-60 disabled:cursor-not-allowed flex items-center justify-center gap-2 shadow-2xs"
                                :disabled="form.processing"
                            >
                                <span v-if="form.processing">Menyimpan...</span>
                                <span v-else>{{
                                    isEdit
                                        ? "Perbarui Kategori"
                                        : "Simpan Kategori"
                                }}</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </transition>
    </teleport>
</template>
