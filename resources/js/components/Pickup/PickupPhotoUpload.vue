<script setup lang="ts">
import { onUnmounted, ref, watch } from 'vue';
import { ImagePlus, X } from 'lucide-vue-next';

const props = withDefaults(
    defineProps<{
        modelValue: File | null;
        label?: string;
        hint?: string;
        error?: string | null;
        disabled?: boolean;
    }>(),
    {
        label: 'Foto Bukti Sampah',
        hint: 'Format JPG, PNG, atau WEBP. Maksimal 4 MB.',
        error: null,
        disabled: false,
    },
);

const emit = defineEmits<{
    (e: 'update:modelValue', value: File | null): void;
}>();

const inputRef = ref<HTMLInputElement | null>(null);
const previewUrl = ref<string | null>(null);
const isDragging = ref(false);

function revokePreview() {
    if (previewUrl.value) {
        URL.revokeObjectURL(previewUrl.value);
        previewUrl.value = null;
    }
}

function acceptFile(file?: File | null) {
    if (!file || props.disabled) return;

    revokePreview();
    previewUrl.value = URL.createObjectURL(file);
    emit('update:modelValue', file);
}

function onFileChange(event: Event) {
    const target = event.target as HTMLInputElement;
    acceptFile(target.files?.[0]);
}

function onDrop(event: DragEvent) {
    isDragging.value = false;
    acceptFile(event.dataTransfer?.files?.[0]);
}

function clear() {
    revokePreview();
    emit('update:modelValue', null);

    if (inputRef.value) {
        inputRef.value.value = '';
    }
}

watch(
    () => props.modelValue,
    (value) => {
        if (!value) revokePreview();
    },
);

onUnmounted(revokePreview);
</script>

<template>
    <div class="flex flex-col gap-2">
        <label class="text-[14px] font-bold text-night-ink">
            {{ label }} <span class="text-red-500">*</span>
        </label>

        <div
            v-if="!previewUrl"
            class="flex cursor-pointer flex-col items-center justify-center gap-2 rounded-2xl border-2 border-dashed p-6 text-center transition-colors"
            :class="
                isDragging
                    ? 'border-eco-green bg-sprout-tint'
                    : error
                      ? 'border-red-300 bg-red-50/40'
                      : 'border-border-gray bg-cloud-surface/60 hover:border-eco-green hover:bg-sprout-tint/40'
            "
            role="button"
            tabindex="0"
            @click="inputRef?.click()"
            @keydown.enter.prevent="inputRef?.click()"
            @keydown.space.prevent="inputRef?.click()"
            @dragover.prevent="isDragging = true"
            @dragleave.prevent="isDragging = false"
            @drop.prevent="onDrop"
        >
            <div
                class="flex size-12 items-center justify-center rounded-2xl border-2 border-border-gray bg-paper-white"
            >
                <ImagePlus class="size-6 text-eco-green-dark" />
            </div>
            <p class="text-[13px] font-bold text-night-ink">
                Klik atau tarik foto ke sini
            </p>
            <p class="text-[12px] font-medium text-charcoal">{{ hint }}</p>
        </div>

        <div
            v-else
            class="relative overflow-hidden rounded-2xl border-2 border-border-gray bg-cloud-surface"
        >
            <img
                :src="previewUrl"
                alt="Pratinjau foto"
                class="max-h-[280px] w-full object-cover"
            />
            <button
                type="button"
                class="absolute top-2 right-2 flex size-8 cursor-pointer items-center justify-center rounded-xl border-2 border-border-gray bg-paper-white/95 text-charcoal shadow-xs transition-colors hover:text-red-600"
                title="Hapus foto"
                @click="clear"
            >
                <X class="size-4" />
            </button>
        </div>

        <input
            ref="inputRef"
            type="file"
            accept="image/jpeg,image/jpg,image/png,image/webp"
            class="hidden"
            :disabled="disabled"
            @change="onFileChange"
        />

        <span v-if="error" class="text-[12px] font-semibold text-red-500">
            {{ error }}
        </span>
    </div>
</template>
