<script setup lang="ts">
import { ref } from "vue";
import { useForm } from "@inertiajs/vue3";
import { X, Upload, Trash2 } from "lucide-vue-next";
import type { User } from "@/types/auth";
import AppButton from "@/components/Landing/AppButton.vue";

const props = defineProps<{
    show: boolean;
    user: User;
}>();

const emit = defineEmits<{
    (e: "close"): void;
}>();

const fileInput = ref<HTMLInputElement | null>(null);
const previewUrl = ref<string | null>(null);

const form = useForm({
    name: props.user.name,
    email: props.user.email,
    photo: null as File | null,
    remove_photo: false,
    _method: "PATCH",
});

const handleFileSelect = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        const file = target.files[0];
        form.photo = file;
        form.remove_photo = false;
        if (previewUrl.value) {
            URL.revokeObjectURL(previewUrl.value);
        }
        previewUrl.value = URL.createObjectURL(file);
    }
};

const handleRemovePhoto = () => {
    form.photo = null;
    form.remove_photo = true;
    if (previewUrl.value) {
        URL.revokeObjectURL(previewUrl.value);
        previewUrl.value = null;
    }
    submit();
};

const submit = () => {
    form.name = props.user.name;
    form.email = props.user.email;
    form.post("/profile", {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            emit("close");
            if (previewUrl.value) {
                URL.revokeObjectURL(previewUrl.value);
                previewUrl.value = null;
            }
            form.reset();
        },
    });
};
</script>

<template>
    <div
        v-if="show"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-night-ink/40 backdrop-blur-xs animate-in fade-in duration-200"
    >
        <div
            class="bg-paper-white border border-border-gray rounded-[24px] max-w-md w-full p-6 sm:p-8 flex flex-col gap-6 shadow-xl relative"
        >
            <!-- Close Button -->
            <button
                type="button"
                @click="emit('close')"
                class="absolute top-5 right-5 text-charcoal hover:text-night-ink p-1 rounded-lg transition"
            >
                <X class="size-5" />
            </button>

            <!-- Header -->
            <div class="flex flex-col gap-1">
                <h3 class="text-[20px] font-extrabold text-night-ink">
                    Ubah Foto Profil
                </h3>
                <p class="text-[13px] text-charcoal">
                    Format file JPG, PNG, atau WebP maksimal 2 MB.
                </p>
            </div>

            <!-- Upload Area & Preview -->
            <div
                class="border-2 border-dashed border-border-gray hover:border-eco-green rounded-2xl p-6 flex flex-col items-center justify-center gap-3 bg-cloud-surface transition cursor-pointer"
                @click="fileInput?.click()"
            >
                <input
                    ref="fileInput"
                    type="file"
                    accept="image/png, image/jpeg, image/webp"
                    class="hidden"
                    @change="handleFileSelect"
                />

                <div
                    v-if="previewUrl || user.photo"
                    class="size-24 rounded-full overflow-hidden border-2 border-eco-green shadow-xs"
                >
                    <img
                        :src="previewUrl || (user.photo?.startsWith('/') ? user.photo : `/storage/${user.photo}`)"
                        alt="Preview"
                        class="size-full object-cover"
                    />
                </div>
                <div
                    v-else
                    class="size-16 rounded-full bg-sprout-tint text-night-ink flex items-center justify-center"
                >
                    <Upload class="size-7 text-night-ink" />
                </div>

                <div class="flex flex-col text-center">
                    <span class="text-sm font-bold text-night-ink">
                        Klik untuk unggah foto baru
                    </span>
                    <span class="text-xs text-charcoal">
                        Pilih foto yang jelas dan formal
                    </span>
                </div>
            </div>

            <p v-if="form.errors.photo" class="text-xs text-rose-500 font-bold text-center">
                {{ form.errors.photo }}
            </p>

            <!-- Actions -->
            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-2">
                <button
                    v-if="user.photo"
                    type="button"
                    @click="handleRemovePhoto"
                    class="inline-flex items-center gap-1.5 text-xs font-bold text-rose-500 hover:text-rose-600 transition"
                >
                    <Trash2 class="size-4" />
                    <span>Hapus Foto</span>
                </button>
                <div v-else></div>

                <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
                    <AppButton
                        variant="outline"
                        size="md"
                        type="button"
                        @click="emit('close')"
                    >
                        Batal
                    </AppButton>
                    <AppButton
                        variant="primary"
                        size="md"
                        type="button"
                        :disabled="form.processing || !form.photo"
                        @click="submit"
                    >
                        {{ form.processing ? "Menyimpan..." : "Simpan Foto" }}
                    </AppButton>
                </div>
            </div>
        </div>
    </div>
</template>
