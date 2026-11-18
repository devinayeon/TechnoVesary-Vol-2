<script setup lang="ts">
import { ref } from 'vue';
import type { User } from '@/types/auth';
import AppButton from '@/components/Landing/AppButton.vue';
import ProfileIdentityCard from './ProfileIdentityCard.vue';
import PersonalDetailsCard from './PersonalDetailsCard.vue';
import AccountContextCard from './AccountContextCard.vue';
import ProfileEditForm from './ProfileEditForm.vue';

const props = defineProps<{
    user: User;
    startInEditMode?: boolean;
}>();

const emit = defineEmits<{
    (e: 'goToAccountInfo'): void;
    (e: 'openPhotoModal'): void;
    (e: 'closedEdit'): void;
}>();

const isEditing = ref(props.startInEditMode ?? false);

const handleCancel = () => {
    isEditing.value = false;
    emit('closedEdit');
};

const handleSaved = () => {
    isEditing.value = false;
    emit('closedEdit');
};
</script>

<template>
    <div class="flex w-full flex-col gap-6">
        <!-- If in Edit Mode -->
        <ProfileEditForm
            v-if="isEditing"
            :user="user"
            @cancel="handleCancel"
            @saved="handleSaved"
        />

        <!-- Normal / Display Mode -->
        <template v-else>
            <!-- Page Heading Row -->
            <div
                class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center"
            >
                <div class="flex flex-col gap-1">
                    <h1
                        class="text-[28px] font-extrabold tracking-tight text-night-ink"
                    >
                        Profil saya
                    </h1>
                    <p class="text-[14px] text-charcoal">
                        Informasi pribadi untuk layanan ReValue.
                    </p>
                </div>

                <AppButton
                    variant="primary"
                    size="md"
                    class="self-start sm:self-auto"
                    @click="isEditing = true"
                >
                    Ubah profil
                </AppButton>
            </div>

            <!-- 2 Columns Grid -->
            <div class="grid grid-cols-1 items-start gap-6 lg:grid-cols-12">
                <!-- Left: Identity & Details -->
                <div class="flex flex-col gap-6 lg:col-span-8">
                    <ProfileIdentityCard
                        :user="user"
                        @open-photo-modal="emit('openPhotoModal')"
                    />
                    <PersonalDetailsCard :user="user" />
                </div>

                <!-- Right: Account Context -->
                <div class="lg:col-span-4">
                    <AccountContextCard
                        :user="user"
                        @go-to-account-info="emit('goToAccountInfo')"
                    />
                </div>
            </div>
        </template>
    </div>
</template>
