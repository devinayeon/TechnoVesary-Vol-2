<script setup lang="ts">
import { computed } from "vue";
import type { User } from "@/types/auth";
import AppButton from "@/components/Landing/AppButton.vue";
import UserAvatar from "@/components/Dashboard/UserAvatar.vue";

const props = defineProps<{
    user: User;
}>();

const emit = defineEmits<{
    (e: "openPhotoModal"): void;
}>();

const initials = computed(() => {
    if (!props.user?.name) return "U";
    return props.user.name
        .split(" ")
        .slice(0, 2)
        .map((w) => w[0]?.toUpperCase())
        .join("");
});

const userPhoto = computed(() => {
    return props.user.photo || (props.user as any).foto || null;
});
</script>

<template>
    <div
        class="bg-paper-white border border-border-gray rounded-[20px] p-6 flex flex-col gap-5 shadow-xs"
    >
        <!-- Identity Header Row -->
        <div class="flex items-center gap-6">
            <!-- 88px Avatar circle -->
            <div
                v-if="!userPhoto"
                class="size-[88px] rounded-full bg-sprout-tint text-night-ink flex items-center justify-center font-extrabold text-[28px] select-none shrink-0 shadow-xs"
            >
                {{ initials }}
            </div>
            <div v-else class="size-[88px] rounded-full overflow-hidden shrink-0 border border-border-gray">
                <UserAvatar
                    :photo="userPhoto"
                    :role="user.role"
                    :name="user.name"
                    size="2xl"
                    class="size-full object-cover"
                />
            </div>

            <!-- Name and Email -->
            <div class="flex flex-col min-w-0">
                <h2 class="text-[24px] sm:text-[26px] font-extrabold text-night-ink truncate tracking-tight">
                    {{ user.name }}
                </h2>
                <p class="text-[14px] text-charcoal truncate font-normal">
                    {{ user.email }}
                </p>
            </div>
        </div>

        <!-- Action & File Notice -->
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
            <AppButton
                variant="outline"
                size="md"
                class="w-full sm:w-auto"
                @click="emit('openPhotoModal')"
            >
                Ubah foto profil
            </AppButton>
        </div>

        <p class="text-[12px] text-charcoal font-normal">
            Foto opsional • JPG, PNG, atau WebP maksimal 2 MB.
        </p>
    </div>
</template>
