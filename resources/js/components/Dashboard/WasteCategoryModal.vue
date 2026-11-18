<script setup lang="ts">
import { watch, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { X, LucideRecycle } from 'lucide-vue-next';

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
    (e: 'close'): void;
}>();

const isEdit = computed(() => !!props.category && !!props.category.id);

const form = useForm({
    name: '',
    points_per_kg: 1000,
    description: '',
});

// Sync form values whenever modal opens or category changes
watch(
    () => [props.show, props.category],
    ([isOpen]) => {
        if (isOpen) {
            form.clearErrors();
            if (props.category) {
                form.name = props.category.name || '';
                form.points_per_kg = props.category.points_per_kg || 1000;
                form.description = props.category.description || '';
            } else {
                form.name = '';
                form.points_per_kg = 1000;
                form.description = '';
            }
        }
    },
    { immediate: true },
);

const handleClose = () => {
    if (form.processing) return;
    form.clearErrors();
    emit('close');
};

const submit = () => {
    if (isEdit.value && props.category?.id) {
        form.put(`/admin/waste-categories/${props.category.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                form.reset();
                emit('close');
            },
        });
    } else {
        form.post('/admin/waste-categories', {
            preserveScroll: true,
            onSuccess: () => {
                form.reset();
                emit('close');
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
                class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-night-ink/50 p-4 backdrop-blur-xs"
                @click.self="handleClose"
            >
                <div
                    class="animate-in fade-in zoom-in-95 relative my-8 w-full max-w-[560px] rounded-[24px] border-2 border-border-gray bg-paper-white p-6 shadow-2xl duration-150 sm:p-8"
                >
                    <!-- Header -->
                    <div
                        class="mb-5 flex items-start justify-between gap-4 border-b border-border-gray/70 pb-4"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                class="flex size-11 shrink-0 items-center justify-center rounded-2xl border-2 border-eco-green/30 bg-sprout-tint text-eco-green-dark shadow-2xs"
                            >
                                <LucideRecycle
                                    class="size-6 text-eco-green-dark"
                                />
                            </div>
                            <div>
                                <h3
                                    class="text-[20px] leading-tight font-extrabold text-night-ink sm:text-[22px]"
                                >
                                    {{
                                        isEdit
                                            ? 'Edit Kategori Sampah'
                                            : 'Tambah Kategori Sampah'
                                    }}
                                </h3>
                                <p
                                    class="mt-0.5 text-[13px] font-medium text-charcoal"
                                >
                                    {{
                                        isEdit
                                            ? 'Perbarui informasi dan nilai poin per kg.'
                                            : 'Tambahkan jenis sampah baru dan tetapkan nilai poinnya.'
                                    }}
                                </p>
                            </div>
                        </div>
                        <button
                            type="button"
                            class="cursor-pointer rounded-xl p-2 text-charcoal transition-colors hover:bg-cloud-surface hover:text-night-ink"
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
                                class="mb-1.5 block flex items-center gap-2 text-[14px] font-extrabold text-night-ink"
                            >
                                <span>Nama Kategori Sampah</span>
                                <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="form.name"
                                type="text"
                                placeholder="Contoh: Plastik PET, Kertas & Kardus, Logam"
                                class="h-[48px] w-full rounded-xl border-2 px-4 text-[14px] font-bold text-night-ink transition-all outline-none placeholder:font-normal placeholder:text-charcoal/50"
                                :class="[
                                    form.errors.name
                                        ? 'border-red-400 bg-red-50/30 focus:border-red-500 focus:ring-4 focus:ring-red-100'
                                        : 'border-border-gray bg-paper-white focus:border-eco-green focus:ring-4 focus:ring-eco-green/10',
                                ]"
                                required
                            />
                            <p
                                v-if="form.errors.name"
                                class="mt-1.5 flex items-center gap-1 text-[12px] font-bold text-red-600"
                            >
                                <span>•</span> {{ form.errors.name }}
                            </p>
                        </div>

                        <!-- Field: Poin per Kg -->
                        <div>
                            <label
                                class="mb-1.5 block flex items-center gap-2 text-[14px] font-extrabold text-night-ink"
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
                                    class="h-[48px] w-full rounded-xl border-2 pr-18 pl-4 text-[15px] font-extrabold text-night-ink transition-all outline-none"
                                    :class="[
                                        form.errors.points_per_kg
                                            ? 'border-red-400 bg-red-50/30 focus:border-red-500 focus:ring-4 focus:ring-red-100'
                                            : 'border-border-gray bg-paper-white focus:border-eco-green focus:ring-4 focus:ring-eco-green/10',
                                    ]"
                                    required
                                />
                                <div
                                    class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4 text-[13px] font-extrabold text-charcoal"
                                >
                                    Poin / kg
                                </div>
                            </div>
                            <p
                                class="mt-1 text-[12px] font-medium text-charcoal"
                            >
                                Customer akan memperoleh poin ini untuk setiap 1
                                kg sampah yang disetorkan.
                            </p>
                            <p
                                v-if="form.errors.points_per_kg"
                                class="mt-1 flex items-center gap-1 text-[12px] font-bold text-red-600"
                            >
                                <span>•</span> {{ form.errors.points_per_kg }}
                            </p>
                        </div>

                        <!-- Field: Deskripsi -->
                        <div>
                            <label
                                class="mb-1.5 block flex items-center gap-2 text-[14px] font-extrabold text-night-ink"
                            >
                                <span>Deskripsi & Kriteria Sampah</span>
                                <span
                                    class="text-[12px] font-medium text-charcoal/60"
                                    >(Opsional)</span
                                >
                            </label>
                            <textarea
                                v-model="form.description"
                                rows="3"
                                placeholder="Jelaskan jenis barang atau kondisi yang dapat diterima (misal: botol bersih tanpa tutup, kardus kering, dsb)..."
                                class="w-full resize-none rounded-xl border-2 p-3.5 text-[14px] font-medium text-night-ink transition-all outline-none placeholder:text-charcoal/50"
                                :class="[
                                    form.errors.description
                                        ? 'border-red-400 bg-red-50/30 focus:border-red-500 focus:ring-4 focus:ring-red-100'
                                        : 'border-border-gray bg-paper-white focus:border-eco-green focus:ring-4 focus:ring-eco-green/10',
                                ]"
                            ></textarea>
                            <p
                                v-if="form.errors.description"
                                class="mt-1 flex items-center gap-1 text-[12px] font-bold text-red-600"
                            >
                                <span>•</span> {{ form.errors.description }}
                            </p>
                        </div>

                        <!-- Footer Actions -->
                        <div
                            class="flex items-center justify-end gap-3 border-t border-border-gray/70 pt-4"
                        >
                            <button
                                type="button"
                                class="h-[46px] cursor-pointer rounded-xl border-2 border-b-[4px] border-border-gray bg-paper-white px-5 text-[14px] font-extrabold text-night-ink transition-all hover:bg-cloud-surface active:translate-y-0.5 active:border-b-2 disabled:cursor-not-allowed disabled:opacity-60"
                                @click="handleClose"
                                :disabled="form.processing"
                            >
                                Batal
                            </button>
                            <button
                                type="submit"
                                class="flex h-[46px] cursor-pointer items-center justify-center gap-2 rounded-xl border-2 border-b-[4px] border-eco-green-dark bg-eco-green px-6 text-[14px] font-extrabold text-white shadow-2xs transition-all hover:brightness-105 active:translate-y-0.5 active:border-b-2 disabled:cursor-not-allowed disabled:opacity-60"
                                :disabled="form.processing"
                            >
                                <span v-if="form.processing">Menyimpan...</span>
                                <span v-else>{{
                                    isEdit
                                        ? 'Perbarui Kategori'
                                        : 'Simpan Kategori'
                                }}</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </transition>
    </teleport>
</template>
