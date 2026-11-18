<script setup lang="ts">
import { computed } from 'vue';
import type { User } from '@/types/auth';
import AppButton from '@/components/Landing/AppButton.vue';
import UserAvatar from '@/components/Dashboard/UserAvatar.vue';

const props = defineProps<{
    user: User;
}>();

const emit = defineEmits<{
    (e: 'openPhotoModal'): void;
}>();

const initials = computed(() => {
    if (!props.user?.name) return 'U';
    return props.user.name
        .split(' ')
        .slice(0, 2)
        .map((w) => w[0]?.toUpperCase())
        .join('');
});

const userPhoto = computed(() => {
    return props.user.photo || (props.user as any).foto || null;
});
</script>

<template>
    <div
        class="flex flex-col gap-5 rounded-[20px] border border-border-gray bg-paper-white p-6 shadow-xs"
    >
        <!-- Identity Header Row -->
        <div class="flex items-center gap-6">
            <!-- 88px Avatar circle -->
            <div
                v-if="!userPhoto"
                class="flex size-[88px] shrink-0 items-center justify-center rounded-full bg-sprout-tint text-[28px] font-extrabold text-night-ink shadow-xs select-none"
            >
                {{ initials }}
            </div>
            <div
                v-else
                class="size-[88px] shrink-0 overflow-hidden rounded-full border border-border-gray"
            >
                <UserAvatar
                    :photo="userPhoto"
                    :role="user.role"
                    :name="user.name"
                    size="2xl"
                    class="size-full object-cover"
                />
            </div>

            <!-- Name and Email -->
            <div class="flex min-w-0 flex-col">
                <h2
                    class="truncate text-[24px] font-extrabold tracking-tight text-night-ink sm:text-[26px]"
                >
                    {{ user.name }}
                </h2>
                <p class="truncate text-[14px] font-normal text-charcoal">
                    {{ user.email }}
                </p>
            </div>
        </div>

        <!-- Action & File Notice -->
        <div
            class="flex flex-col items-start gap-3 sm:flex-row sm:items-center"
        >
            <AppButton
                variant="outline"
                size="md"
                class="w-full sm:w-auto"
                @click="emit('openPhotoModal')"
            >
                Ubah foto profil
            </AppButton>
        </div>

        <p class="text-[12px] font-normal text-charcoal">
            Foto opsional • JPG, PNG, atau WebP maksimal 2 MB.
        </p>
    </div>
</template>
