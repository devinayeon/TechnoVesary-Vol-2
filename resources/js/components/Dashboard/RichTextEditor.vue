<script setup lang="ts">
import { ref, watch, onBeforeUnmount } from "vue";
import { useEditor, EditorContent } from "@tiptap/vue-3";
import StarterKit from "@tiptap/starter-kit";
import Youtube from "@tiptap/extension-youtube";
import Image from "@tiptap/extension-image";
import Link from "@tiptap/extension-link";
import Placeholder from "@tiptap/extension-placeholder";
import {
    Bold,
    Italic,
    Strikethrough,
    Heading1,
    Heading2,
    Heading3,
    List,
    ListOrdered,
    Quote,
    Minus,
    Youtube as YoutubeIcon,
    Image as ImageIcon,
    Link as LinkIcon,
    Unlink,
    Undo2,
    Redo2,
    UploadCloud,
    X,
    Check,
    AlertCircle,
} from "lucide-vue-next";

const props = withDefaults(
    defineProps<{
        modelValue: string;
        placeholder?: string;
        editable?: boolean;
    }>(),
    {
        modelValue: "",
        placeholder: "Mulai tulis artikel edukasi ramah lingkungan di sini...",
        editable: true,
    }
);

const emit = defineEmits<{
    (e: "update:modelValue", value: string): void;
}>();

// Modals State
const showYoutubeModal = ref(false);
const youtubeUrl = ref("");
const youtubeError = ref("");

const showImageUrlModal = ref(false);
const imageUrl = ref("");
const isUploadingImage = ref(false);
const imageUploadError = ref("");

const showLinkModal = ref(false);
const linkUrl = ref("");

const fileInputRef = ref<HTMLInputElement | null>(null);

const editor = useEditor({
    content: props.modelValue,
    editable: props.editable,
    extensions: [
        StarterKit.configure({
            heading: {
                levels: [1, 2, 3],
            },
        }),
        Youtube.configure({
            inline: false,
            nocookie: true,
            allowFullscreen: true,
            HTMLAttributes: {
                class: "w-full aspect-video rounded-xl border-2 border-border-gray shadow-xs my-4",
            },
        }),
        Image.configure({
            inline: true,
            allowBase64: false,
            HTMLAttributes: {
                class: "max-w-full rounded-xl border border-border-gray my-4 shadow-2xs object-cover",
            },
        }),
        Link.configure({
            openOnClick: false,
            HTMLAttributes: {
                class: "text-eco-green-dark underline font-bold hover:text-eco-green cursor-pointer",
            },
        }),
        Placeholder.configure({
            placeholder: props.placeholder,
        }),
    ],
    onUpdate: () => {
        if (!editor.value) return;
        const html = editor.value.getHTML();
        // Emit empty string if editor only contains empty paragraph
        emit("update:modelValue", html === "<p></p>" ? "" : html);
    },
});

watch(
    () => props.modelValue,
    (newValue) => {
        if (!editor.value) return;
        const isSame = editor.value.getHTML() === newValue;
        if (!isSame) {
            editor.value.commands.setContent(newValue, { emitUpdate: false });
        }
    }
);

watch(
    () => props.editable,
    (newVal) => {
        if (editor.value) {
            editor.value.setEditable(newVal);
        }
    }
);

onBeforeUnmount(() => {
    editor.value?.destroy();
});

// YouTube Action
const openYoutubeModal = () => {
    youtubeUrl.value = "";
    youtubeError.value = "";
    showYoutubeModal.value = true;
};

const insertYoutube = () => {
    if (!youtubeUrl.value.trim()) {
        youtubeError.value = "Masukkan tautan video YouTube";
        return;
    }

    try {
        editor.value
            ?.chain()
            .focus()
            .setYoutubeVideo({
                src: youtubeUrl.value.trim(),
            })
            .run();
        showYoutubeModal.value = false;
        youtubeUrl.value = "";
    } catch (e: any) {
        youtubeError.value = "Format tautan YouTube tidak valid";
    }
};

// Image Upload / Insert Action
const triggerImageUpload = () => {
    fileInputRef.value?.click();
};

const handleImageFileChange = async (event: Event) => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];
    if (!file) return;

    if (!file.type.startsWith("image/")) {
        imageUploadError.value = "File harus berupa format gambar (JPG, PNG, atau WebP).";
        if (target) target.value = "";
        return;
    }

    if (file.size > 3 * 1024 * 1024) {
        imageUploadError.value = "Ukuran gambar terlalu besar (maksimal 3MB).";
        if (target) target.value = "";
        return;
    }

    isUploadingImage.value = true;
    imageUploadError.value = "";

    try {
        const formData = new FormData();
        formData.append("image", file);

        // Fetch CSRF token
        const csrfToken =
            (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)
                ?.content || "";

        const response = await fetch("/admin/articles/upload-image", {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": csrfToken,
                Accept: "application/json",
            },
            body: formData,
        });

        if (!response.ok) {
            const errData = await response.json();
            throw new Error(errData.message || "Gagal mengunggah gambar");
        }

        const data = await response.json();
        if (data.url) {
            editor.value
                ?.chain()
                .focus()
                .setImage({ src: data.url, alt: file.name })
                .run();
        }
    } catch (err: any) {
        imageUploadError.value = err.message || "Terjadi kesalahan saat mengunggah gambar ke server";
    } finally {
        isUploadingImage.value = false;
        if (target) target.value = "";
    }
};

const insertImageUrl = () => {
    if (!imageUrl.value.trim()) return;
    editor.value
        ?.chain()
        .focus()
        .setImage({ src: imageUrl.value.trim() })
        .run();
    showImageUrlModal.value = false;
    imageUrl.value = "";
};

// Link Action
const openLinkModal = () => {
    const previousUrl = editor.value?.getAttributes("link").href || "";
    linkUrl.value = previousUrl;
    showLinkModal.value = true;
};

const setLink = () => {
    if (!linkUrl.value.trim()) {
        editor.value?.chain().focus().unsetLink().run();
    } else {
        editor.value
            ?.chain()
            .focus()
            .setLink({ href: linkUrl.value.trim() })
            .run();
    }
    showLinkModal.value = false;
    linkUrl.value = "";
};

const removeLink = () => {
    editor.value?.chain().focus().unsetLink().run();
};
</script>

<template>
    <div
        class="rich-text-editor border-2 border-border-gray rounded-2xl bg-paper-white overflow-hidden shadow-2xs focus-within:border-eco-green focus-within:ring-4 focus-within:ring-eco-green/15 transition-all"
    >
        <!-- Toolbar -->
        <div
            v-if="editor && editable"
            class="p-2 sm:p-2.5 bg-cloud-surface/70 border-b-2 border-border-gray flex flex-wrap items-center gap-1 select-none"
        >
            <!-- Headings -->
            <button
                type="button"
                @click="editor.chain().focus().toggleHeading({ level: 1 }).run()"
                :class="[
                    'size-8 sm:size-9 rounded-lg flex items-center justify-center font-black text-[13px] border transition-all cursor-pointer',
                    editor.isActive('heading', { level: 1 })
                        ? 'bg-sprout-tint text-eco-green-dark border-eco-green/40 shadow-2xs'
                        : 'border-transparent text-charcoal hover:bg-paper-white hover:text-night-ink hover:border-border-gray/60',
                ]"
                title="Judul Utama (H1)"
            >
                <Heading1 class="size-4" />
            </button>

            <button
                type="button"
                @click="editor.chain().focus().toggleHeading({ level: 2 }).run()"
                :class="[
                    'size-8 sm:size-9 rounded-lg flex items-center justify-center font-black text-[13px] border transition-all cursor-pointer',
                    editor.isActive('heading', { level: 2 })
                        ? 'bg-sprout-tint text-eco-green-dark border-eco-green/40 shadow-2xs'
                        : 'border-transparent text-charcoal hover:bg-paper-white hover:text-night-ink hover:border-border-gray/60',
                ]"
                title="Subjudul (H2)"
            >
                <Heading2 class="size-4" />
            </button>

            <button
                type="button"
                @click="editor.chain().focus().toggleHeading({ level: 3 }).run()"
                :class="[
                    'size-8 sm:size-9 rounded-lg flex items-center justify-center font-black text-[13px] border transition-all cursor-pointer',
                    editor.isActive('heading', { level: 3 })
                        ? 'bg-sprout-tint text-eco-green-dark border-eco-green/40 shadow-2xs'
                        : 'border-transparent text-charcoal hover:bg-paper-white hover:text-night-ink hover:border-border-gray/60',
                ]"
                title="Poin Subjudul (H3)"
            >
                <Heading3 class="size-4" />
            </button>

            <div class="w-px h-5 bg-border-gray mx-1" />

            <!-- Text Formatting -->
            <button
                type="button"
                @click="editor.chain().focus().toggleBold().run()"
                :class="[
                    'size-8 sm:size-9 rounded-lg flex items-center justify-center font-black text-[13px] border transition-all cursor-pointer',
                    editor.isActive('bold')
                        ? 'bg-sprout-tint text-eco-green-dark border-eco-green/40 shadow-2xs'
                        : 'border-transparent text-charcoal hover:bg-paper-white hover:text-night-ink hover:border-border-gray/60',
                ]"
                title="Tebal (Ctrl+B)"
            >
                <Bold class="size-4" />
            </button>

            <button
                type="button"
                @click="editor.chain().focus().toggleItalic().run()"
                :class="[
                    'size-8 sm:size-9 rounded-lg flex items-center justify-center font-black text-[13px] border transition-all cursor-pointer',
                    editor.isActive('italic')
                        ? 'bg-sprout-tint text-eco-green-dark border-eco-green/40 shadow-2xs'
                        : 'border-transparent text-charcoal hover:bg-paper-white hover:text-night-ink hover:border-border-gray/60',
                ]"
                title="Miring (Ctrl+I)"
            >
                <Italic class="size-4" />
            </button>

            <button
                type="button"
                @click="editor.chain().focus().toggleStrike().run()"
                :class="[
                    'size-8 sm:size-9 rounded-lg flex items-center justify-center font-black text-[13px] border transition-all cursor-pointer',
                    editor.isActive('strike')
                        ? 'bg-sprout-tint text-eco-green-dark border-eco-green/40 shadow-2xs'
                        : 'border-transparent text-charcoal hover:bg-paper-white hover:text-night-ink hover:border-border-gray/60',
                ]"
                title="Coret"
            >
                <Strikethrough class="size-4" />
            </button>

            <div class="w-px h-5 bg-border-gray mx-1" />

            <!-- Lists -->
            <button
                type="button"
                @click="editor.chain().focus().toggleBulletList().run()"
                :class="[
                    'size-8 sm:size-9 rounded-lg flex items-center justify-center font-black text-[13px] border transition-all cursor-pointer',
                    editor.isActive('bulletList')
                        ? 'bg-sprout-tint text-eco-green-dark border-eco-green/40 shadow-2xs'
                        : 'border-transparent text-charcoal hover:bg-paper-white hover:text-night-ink hover:border-border-gray/60',
                ]"
                title="Daftar Poin"
            >
                <List class="size-4" />
            </button>

            <button
                type="button"
                @click="editor.chain().focus().toggleOrderedList().run()"
                :class="[
                    'size-8 sm:size-9 rounded-lg flex items-center justify-center font-black text-[13px] border transition-all cursor-pointer',
                    editor.isActive('orderedList')
                        ? 'bg-sprout-tint text-eco-green-dark border-eco-green/40 shadow-2xs'
                        : 'border-transparent text-charcoal hover:bg-paper-white hover:text-night-ink hover:border-border-gray/60',
                ]"
                title="Daftar Nomor"
            >
                <ListOrdered class="size-4" />
            </button>

            <button
                type="button"
                @click="editor.chain().focus().toggleBlockquote().run()"
                :class="[
                    'size-8 sm:size-9 rounded-lg flex items-center justify-center font-black text-[13px] border transition-all cursor-pointer',
                    editor.isActive('blockquote')
                        ? 'bg-sprout-tint text-eco-green-dark border-eco-green/40 shadow-2xs'
                        : 'border-transparent text-charcoal hover:bg-paper-white hover:text-night-ink hover:border-border-gray/60',
                ]"
                title="Kutipan / Catatan"
            >
                <Quote class="size-4" />
            </button>

            <button
                type="button"
                @click="editor.chain().focus().setHorizontalRule().run()"
                class="size-8 sm:size-9 rounded-lg flex items-center justify-center font-black text-[13px] border border-transparent text-charcoal hover:bg-paper-white hover:text-night-ink hover:border-border-gray/60 transition-all cursor-pointer"
                title="Garis Pemisah"
            >
                <Minus class="size-4" />
            </button>

            <div class="w-px h-5 bg-border-gray mx-1" />

            <!-- Media: YouTube & Image Upload -->
            <button
                type="button"
                @click="openYoutubeModal"
                class="px-2.5 h-8 sm:h-9 rounded-lg flex items-center gap-1.5 font-bold text-[12px] border border-border-gray/70 bg-paper-white text-night-ink hover:border-red-400 hover:text-red-600 transition-all cursor-pointer shadow-2xs"
                title="Sisipkan Video YouTube"
            >
                <YoutubeIcon class="size-4 text-red-600" />
                <span class="hidden sm:inline">YouTube</span>
            </button>

            <button
                type="button"
                @click="triggerImageUpload"
                :disabled="isUploadingImage"
                class="px-2.5 h-8 sm:h-9 rounded-lg flex items-center gap-1.5 font-bold text-[12px] border border-border-gray/70 bg-paper-white text-night-ink hover:border-eco-green hover:text-eco-green-dark transition-all cursor-pointer shadow-2xs disabled:opacity-60"
                title="Unggah Gambar dari Perangkat"
            >
                <UploadCloud class="size-4 text-eco-green-dark" />
                <span class="hidden sm:inline">
                    {{ isUploadingImage ? "Mengunggah..." : "Unggah Foto" }}
                </span>
            </button>

            <!-- Hidden File Input for Image Upload -->
            <input
                ref="fileInputRef"
                type="file"
                accept="image/png,image/jpeg,image/webp,image/gif"
                class="hidden"
                @change="handleImageFileChange"
            />

            <!-- Link Controls -->
            <button
                type="button"
                @click="openLinkModal"
                :class="[
                    'size-8 sm:size-9 rounded-lg flex items-center justify-center font-black text-[13px] border transition-all cursor-pointer',
                    editor.isActive('link')
                        ? 'bg-sprout-tint text-eco-green-dark border-eco-green/40 shadow-2xs'
                        : 'border-transparent text-charcoal hover:bg-paper-white hover:text-night-ink hover:border-border-gray/60',
                ]"
                title="Tautan / Link"
            >
                <LinkIcon class="size-4" />
            </button>

            <button
                v-if="editor.isActive('link')"
                type="button"
                @click="removeLink"
                class="size-8 sm:size-9 rounded-lg flex items-center justify-center font-black text-[13px] border border-transparent text-red-600 hover:bg-red-50 transition-all cursor-pointer"
                title="Hapus Tautan"
            >
                <Unlink class="size-4" />
            </button>

            <div class="w-px h-5 bg-border-gray mx-1 ml-auto" />

            <!-- Undo / Redo -->
            <button
                type="button"
                @click="editor.chain().focus().undo().run()"
                :disabled="!editor.can().undo()"
                class="size-8 sm:size-9 rounded-lg flex items-center justify-center border border-transparent text-charcoal hover:bg-paper-white hover:text-night-ink hover:border-border-gray/60 transition-all cursor-pointer disabled:opacity-30 disabled:cursor-not-allowed"
                title="Undo (Ctrl+Z)"
            >
                <Undo2 class="size-4" />
            </button>

            <button
                type="button"
                @click="editor.chain().focus().redo().run()"
                :disabled="!editor.can().redo()"
                class="size-8 sm:size-9 rounded-lg flex items-center justify-center border border-transparent text-charcoal hover:bg-paper-white hover:text-night-ink hover:border-border-gray/60 transition-all cursor-pointer disabled:opacity-30 disabled:cursor-not-allowed"
                title="Redo (Ctrl+Y)"
            >
                <Redo2 class="size-4" />
            </button>
        </div>

        <!-- Image Upload Error Flash Banner -->
        <div
            v-if="imageUploadError"
            class="m-3 p-3 rounded-xl bg-red-50 border-2 border-red-300 text-red-800 text-[13px] font-bold flex items-center justify-between gap-3 shadow-2xs animate-in fade-in duration-150"
        >
            <div class="flex items-center gap-2.5 min-w-0">
                <AlertCircle class="size-4.5 text-red-600 shrink-0" />
                <span>{{ imageUploadError }}</span>
            </div>
            <button
                type="button"
                @click="imageUploadError = ''"
                class="p-1 rounded-md hover:bg-red-100 text-red-700 transition-colors cursor-pointer shrink-0"
                aria-label="Tutup error"
            >
                <X class="size-3.5" />
            </button>
        </div>

        <!-- Editor Content Area -->
        <EditorContent
            :editor="editor"
            class="tiptap-content-wrapper p-4 sm:p-6 min-h-[320px] max-h-[700px] overflow-y-auto focus:outline-none"
        />

        <!-- YouTube Embed Modal -->
        <teleport to="body">
            <div
                v-if="showYoutubeModal"
                class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-night-ink/50 backdrop-blur-xs select-none"
                @click.self="showYoutubeModal = false"
            >
                <div
                    class="w-full max-w-[460px] bg-paper-white border-2 border-border-gray rounded-[22px] p-6 shadow-2xl animate-in fade-in zoom-in-95 duration-150"
                >
                    <div class="flex items-center justify-between gap-3 mb-4">
                        <div class="flex items-center gap-2.5">
                            <div
                                class="size-9 rounded-xl bg-red-50 text-red-600 flex items-center justify-center shrink-0"
                            >
                                <YoutubeIcon class="size-5" />
                            </div>
                            <h3 class="font-extrabold text-[18px] text-night-ink">
                                Sisipkan Video YouTube
                            </h3>
                        </div>
                        <button
                            type="button"
                            @click="showYoutubeModal = false"
                            class="p-1 rounded-lg text-charcoal hover:bg-cloud-surface hover:text-night-ink transition-colors cursor-pointer"
                        >
                            <X class="size-4" />
                        </button>
                    </div>

                    <p class="text-[13px] font-medium text-charcoal mb-4">
                        Tempel URL tautan video YouTube (contoh: https://www.youtube.com/watch?v=... atau https://youtu.be/...).
                    </p>

                    <div class="space-y-2 mb-5">
                        <input
                            v-model="youtubeUrl"
                            type="url"
                            placeholder="https://www.youtube.com/watch?v=..."
                            @keydown.enter.prevent="insertYoutube"
                            class="w-full h-[46px] px-3.5 bg-paper-white border-2 border-border-gray rounded-xl text-[14px] font-medium text-night-ink focus:outline-none focus:border-eco-green focus:ring-4 focus:ring-eco-green/15 transition-all"
                            :class="{ 'border-red-500': youtubeError }"
                        />
                        <p v-if="youtubeError" class="text-[12px] font-bold text-red-600">
                            {{ youtubeError }}
                        </p>
                    </div>

                    <div class="flex items-center justify-end gap-2.5">
                        <button
                            type="button"
                            @click="showYoutubeModal = false"
                            class="px-4 h-[42px] rounded-xl border-2 border-b-[4px] border-border-gray bg-paper-white font-extrabold text-[13px] text-night-ink hover:bg-cloud-surface active:translate-y-0.5 active:border-b-2 transition-all cursor-pointer"
                        >
                            Batal
                        </button>
                        <button
                            type="button"
                            @click="insertYoutube"
                            class="px-5 h-[42px] rounded-xl border-2 border-b-[4px] border-eco-green-dark bg-eco-green font-extrabold text-[13px] text-white hover:brightness-105 active:translate-y-0.5 active:border-b-2 transition-all cursor-pointer flex items-center gap-1.5"
                        >
                            <Check class="size-4" />
                            <span>Sisipkan Video</span>
                        </button>
                    </div>
                </div>
            </div>
        </teleport>

        <!-- Hyperlink Modal -->
        <teleport to="body">
            <div
                v-if="showLinkModal"
                class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-night-ink/50 backdrop-blur-xs select-none"
                @click.self="showLinkModal = false"
            >
                <div
                    class="w-full max-w-[440px] bg-paper-white border-2 border-border-gray rounded-[22px] p-6 shadow-2xl animate-in fade-in zoom-in-95 duration-150"
                >
                    <div class="flex items-center justify-between gap-3 mb-4">
                        <div class="flex items-center gap-2.5">
                            <div
                                class="size-9 rounded-xl bg-sprout-tint text-eco-green-dark flex items-center justify-center shrink-0"
                            >
                                <LinkIcon class="size-5" />
                            </div>
                            <h3 class="font-extrabold text-[18px] text-night-ink">
                                Pasang Tautan
                            </h3>
                        </div>
                        <button
                            type="button"
                            @click="showLinkModal = false"
                            class="p-1 rounded-lg text-charcoal hover:bg-cloud-surface hover:text-night-ink transition-colors cursor-pointer"
                        >
                            <X class="size-4" />
                        </button>
                    </div>

                    <div class="space-y-2 mb-5">
                        <input
                            v-model="linkUrl"
                            type="url"
                            placeholder="https://example.com"
                            @keydown.enter.prevent="setLink"
                            class="w-full h-[46px] px-3.5 bg-paper-white border-2 border-border-gray rounded-xl text-[14px] font-medium text-night-ink focus:outline-none focus:border-eco-green focus:ring-4 focus:ring-eco-green/15 transition-all"
                        />
                    </div>

                    <div class="flex items-center justify-end gap-2.5">
                        <button
                            type="button"
                            @click="showLinkModal = false"
                            class="px-4 h-[42px] rounded-xl border-2 border-b-[4px] border-border-gray bg-paper-white font-extrabold text-[13px] text-night-ink hover:bg-cloud-surface active:translate-y-0.5 active:border-b-2 transition-all cursor-pointer"
                        >
                            Batal
                        </button>
                        <button
                            type="button"
                            @click="setLink"
                            class="px-5 h-[42px] rounded-xl border-2 border-b-[4px] border-eco-green-dark bg-eco-green font-extrabold text-[13px] text-white hover:brightness-105 active:translate-y-0.5 active:border-b-2 transition-all cursor-pointer flex items-center gap-1.5"
                        >
                            <Check class="size-4" />
                            <span>Simpan Tautan</span>
                        </button>
                    </div>
                </div>
            </div>
        </teleport>
    </div>
</template>

<style>
/* Tiptap Editor Typography */
.tiptap-content-wrapper .tiptap {
    outline: none;
    font-size: 15px;
    line-height: 1.7;
    color: var(--color-night-ink, #1f2937);
}

.tiptap-content-wrapper .tiptap p.is-editor-empty:first-child::before {
    content: attr(data-placeholder);
    float: left;
    color: var(--color-charcoal, #6b7280);
    opacity: 0.55;
    pointer-events: none;
    height: 0;
}

.tiptap-content-wrapper .tiptap h1 {
    font-size: 26px;
    font-weight: 900;
    line-height: 1.25;
    margin-top: 1.2em;
    margin-bottom: 0.5em;
    color: var(--color-night-ink, #111827);
    letter-spacing: -0.02em;
}

.tiptap-content-wrapper .tiptap h2 {
    font-size: 21px;
    font-weight: 800;
    line-height: 1.3;
    margin-top: 1.1em;
    margin-bottom: 0.4em;
    color: var(--color-night-ink, #111827);
    letter-spacing: -0.01em;
}

.tiptap-content-wrapper .tiptap h3 {
    font-size: 17px;
    font-weight: 800;
    line-height: 1.4;
    margin-top: 0.9em;
    margin-bottom: 0.3em;
    color: var(--color-night-ink, #111827);
}

.tiptap-content-wrapper .tiptap ul {
    list-style-type: disc;
    padding-left: 1.5em;
    margin: 0.75em 0;
}

.tiptap-content-wrapper .tiptap ol {
    list-style-type: decimal;
    padding-left: 1.5em;
    margin: 0.75em 0;
}

.tiptap-content-wrapper .tiptap li {
    margin: 0.25em 0;
}

.tiptap-content-wrapper .tiptap blockquote {
    border-left: 4px solid var(--color-eco-green, #10b981);
    background-color: var(--color-cloud-surface, #f9fafb);
    padding: 0.75em 1.2em;
    margin: 1em 0;
    border-radius: 0 12px 12px 0;
    font-style: italic;
    color: var(--color-charcoal, #4b5563);
}

.tiptap-content-wrapper .tiptap hr {
    border: none;
    border-top: 2px dashed var(--color-border-gray, #e5e7eb);
    margin: 1.5em 0;
}

.tiptap-content-wrapper .tiptap img {
    max-width: 100%;
    height: auto;
    border-radius: 14px;
    border: 2px solid var(--color-border-gray, #e5e7eb);
    margin: 1em 0;
}

.tiptap-content-wrapper .tiptap iframe {
    width: 100%;
    aspect-ratio: 16 / 9;
    border-radius: 14px;
    border: 2px solid var(--color-border-gray, #e5e7eb);
    margin: 1em 0;
}
</style>
