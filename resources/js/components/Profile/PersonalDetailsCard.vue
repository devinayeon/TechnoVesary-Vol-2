<script setup lang="ts">
import { computed } from 'vue';
import type { User } from '@/types/auth';
import DetailField from './DetailField.vue';
import StatusChip from '@/components/Dashboard/StatusChip.vue';

const props = defineProps<{
    user: User;
}>();

const isEmailVerified = computed(() => !!props.user.email_verified_at);
const formattedPhone = computed(() => {
    return props.user.phone_number || (props.user as any).nomor_telepon || '-';
});
</script>

<template>
    <div
        class="flex flex-col gap-5 rounded-[20px] border border-border-gray bg-paper-white p-6 shadow-xs"
    >
        <h3 class="text-[21px] leading-snug font-extrabold text-night-ink">
            Informasi pribadi
        </h3>

        <!-- Fields Grid -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <DetailField label="Nama lengkap" :value="user.name" />
            <DetailField label="Nomor telepon" :value="formattedPhone" />
        </div>

        <DetailField label="Alamat email" :value="user.email" />

        <!-- Email Verification Status -->
        <div class="flex items-center gap-3 pt-2">
            <StatusChip
                :label="
                    isEmailVerified
                        ? 'Email terverifikasi'
                        : 'Belum diverifikasi'
                "
                :tone="isEmailVerified ? 'Green' : 'Amber'"
            />
        </div>
    </div>
</template>
