<script setup lang="ts">
import { computed } from "vue";

const props = withDefaults(
    defineProps<{
        photo?: string | null;
        role?: string | null;
        name?: string | null;
        size?: "sm" | "md" | "lg" | "xl" | "2xl";
    }>(),
    {
        photo: null,
        role: "customer",
        name: "User",
        size: "md",
    }
);

const getRolePlaceholder = (role?: string | null) => {
    const r = (role || "").toLowerCase();
    if (r === "admin") {
        return "/assets/placeholder/Admin/65.png";
    }
    if (r === "driver") {
        return "/assets/placeholder/Driver/65.png";
    }
    return "/assets/placeholder/Customer/65.png";
};

const avatarSrc = computed(() => {
    const p = props.photo || (props as any).foto;
    if (p && typeof p === "string" && p.trim() !== "") {
        if (p.startsWith("http://") || p.startsWith("https://")) {
            return p;
        }
        if (p.startsWith("/")) {
            return p;
        }
        const clean = p.startsWith("storage/") ? p.slice(8) : p;
        return `/storage/${clean}`;
    }
    return getRolePlaceholder(props.role);
});

const onImageError = (event: Event) => {
    const target = event.target as HTMLImageElement;
    target.src = getRolePlaceholder(props.role);
};

const sizeClasses = computed(() => {
    switch (props.size) {
        case "sm":
            return "size-7 text-[10px]";
        case "lg":
            return "size-11 text-[14px]";
        case "xl":
            return "size-16 text-[22px]";
        case "2xl":
            return "size-[88px] text-[28px]";
        case "md":
        default:
            return "size-9 text-[12px]";
    }
});
</script>

<template>
    <div
        :class="[
            'rounded-full overflow-hidden bg-sprout-tint border-2 border-eco-green/40 flex items-center justify-center shrink-0 select-none shadow-2xs',
            sizeClasses,
        ]"
    >
        <img
            :src="avatarSrc"
            :alt="name ?? 'Avatar'"
            class="size-full object-cover"
            @error="onImageError"
        />
    </div>
</template>
