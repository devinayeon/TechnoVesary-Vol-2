<script setup lang="ts">
import { computed } from "vue";
import type { User } from "@/types/auth";
import DetailField from "./DetailField.vue";
import StatusChip from "@/components/Dashboard/StatusChip.vue";

const props = defineProps<{
    user: User;
}>();

const isEmailVerified = computed(() => !!props.user.email_verified_at);
const formattedPhone = computed(() => {
    return props.user.phone_number || (props.user as any).nomor_telepon || "-";
});
</script>

<template>
    <div
        class="bg-paper-white border border-border-gray rounded-[20px] p-6 flex flex-col gap-5 shadow-xs"
    >
        <h3 class="text-[21px] font-extrabold text-night-ink leading-snug">
            Informasi pribadi
        </h3>

        <!-- Fields Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <DetailField label="Nama lengkap" :value="user.name" />
            <DetailField label="Nomor telepon" :value="formattedPhone" />
        </div>

        <DetailField label="Alamat email" :value="user.email" />

        <!-- Email Verification Status -->
        <div class="pt-2 flex items-center gap-3">
            <StatusChip
                :label="isEmailVerified ? 'Email terverifikasi' : 'Belum diverifikasi'"
                :tone="isEmailVerified ? 'Green' : 'Amber'"
            />
        </div>
    </div>
</template>
