<script setup lang="ts">
import { computed, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import type { User } from '@/types/auth';
import DetailField from './DetailField.vue';

const props = defineProps<{
    user: User;
}>();

const emit = defineEmits<{
    (e: 'goToAccountInfo'): void;
}>();

const isToggling = ref(false);
const role = computed(() => (props.user?.role as string) || 'customer');

const roleBadgeLabel = computed(() => {
    if (role.value === 'admin') return 'Admin';
    if (role.value === 'driver') return 'Driver';
    return 'Customer';
});

const formattedExpiry = computed(() => {
    const rawDate =
        props.user.customer?.membership_expires_at ||
        (props.user.customer as any)?.expired_member_date;
    if (!rawDate) return '25 November 2026';
    try {
        const d = new Date(rawDate);
        return d.toLocaleDateString('id-ID', {
            day: 'numeric',
            month: 'long',
            year: 'numeric',
        });
    } catch {
        return '25 November 2026';
    }
});

const pointsFormatted = computed(() => {
    const pts =
        props.user.customer?.points ?? (props.user.customer as any)?.poin;
    const value = pts !== undefined && pts !== null ? pts : 60000;
    return `${Number(value).toLocaleString('id-ID')} poin`;
});

const isReValuePlusActive = computed(() => {
    return props.user.customer?.is_member ?? true;
});

const isDriverAvailable = computed(() => {
    return props.user.driver?.is_available ?? true;
});

const driverStatus = computed(() => {
    return isDriverAvailable.value ? 'Siap bertugas' : 'Sedang istirahat';
});

const toggleDriverStatus = () => {
    isToggling.value = true;
    router.patch(
        '/profile',
        {
            is_available: !isDriverAvailable.value,
        },
        {
            preserveScroll: true,
            onFinish: () => {
                isToggling.value = false;
            },
        },
    );
};
</script>

<template>
    <div
        class="flex flex-col gap-5 rounded-[20px] border border-border-gray bg-paper-white p-6 shadow-xs"
    >
        <!-- Role Badge -->
        <div class="flex items-center">
            <span
                class="inline-flex items-center rounded-[8px] px-2.5 py-1 text-[12px] font-bold tracking-wide"
                :class="[
                    role === 'admin'
                        ? 'border border-border-gray bg-cloud-surface text-night-ink'
                        : 'border border-sky-blue/20 bg-sky-tint text-night-ink',
                ]"
            >
                {{ roleBadgeLabel }}
            </span>
        </div>

        <!-- Role Slogan -->
        <h3 class="text-[22px] leading-snug font-extrabold text-night-ink">
            <template v-if="role === 'customer'">
                Setiap setoran, lebih bernilai.
            </template>
            <template v-else-if="role === 'driver'">
                Jemput dengan data yang tepat.
            </template>
            <template v-else> Kelola akun operasionalmu. </template>
        </h3>

        <!-- Role Details -->
        <div class="flex flex-col divide-y divide-border-gray/50">
            <!-- Customer Fields -->
            <template v-if="role === 'customer'">
                <DetailField label="Saldo tersedia" :value="pointsFormatted" />
                <DetailField
                    label="Status ReValue+"
                    :value="isReValuePlusActive ? 'Aktif' : 'Nonaktif'"
                />
                <DetailField label="Aktif sampai" :value="formattedExpiry" />
            </template>

            <!-- Driver Fields -->
            <template v-else-if="role === 'driver'">
                <DetailField
                    label="Ketersediaan Driver"
                    :value="driverStatus"
                />
                <div class="flex items-center justify-between py-2">
                    <span class="text-[12px] font-medium text-charcoal"
                        >Status Operasional Driver</span
                    >
                    <button
                        type="button"
                        :disabled="isToggling"
                        @click="toggleDriverStatus"
                        class="cursor-pointer rounded-lg border px-2.5 py-1 text-xs font-bold transition select-none"
                        :class="
                            isDriverAvailable
                                ? 'border-eco-green/40 bg-sprout-tint text-night-ink hover:brightness-95'
                                : 'border-border-gray bg-cloud-surface text-charcoal hover:bg-border-gray/30'
                        "
                    >
                        {{
                            isToggling
                                ? 'Menyimpan...'
                                : isDriverAvailable
                                  ? 'Set Istirahat'
                                  : 'Set Siap Bertugas'
                        }}
                    </button>
                </div>
                <p
                    class="py-1 text-[12px] leading-relaxed font-normal text-charcoal"
                >
                    Status kesiapan diubah dari dashboard Driver atau profil.
                </p>
            </template>

            <!-- Admin Fields -->
            <template v-else>
                <DetailField label="Akses akun" value="Administrator" />
                <p
                    class="py-2 text-[12px] leading-relaxed font-normal text-charcoal"
                >
                    Role tidak dapat diubah melalui profil pribadi.
                </p>
            </template>
        </div>

        <!-- Action Link to Account Info -->
        <button
            type="button"
            @click="emit('goToAccountInfo')"
            class="mt-1 cursor-pointer text-left text-[13px] font-extrabold text-night-ink transition select-none hover:text-eco-green"
        >
            Lihat informasi akun
        </button>
    </div>
</template>
