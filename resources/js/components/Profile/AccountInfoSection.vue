<script setup lang="ts">
import { computed, nextTick, ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { Trash2, X, AlertTriangle } from 'lucide-vue-next';
import type { User } from '@/types/auth';
import DetailField from './DetailField.vue';
import AppButton from '@/components/Landing/AppButton.vue';
import UserAvatar from '@/components/Dashboard/UserAvatar.vue';

const props = defineProps<{
    user: User;
}>();

const userPhoto = computed(() => {
    return props.user.photo || (props.user as any).foto || null;
});

const confirmingDeletion = ref(false);
const passwordInput = ref<HTMLInputElement | null>(null);

const deleteForm = useForm({
    password: '',
});

const openDeleteModal = () => {
    confirmingDeletion.value = true;
    nextTick(() => passwordInput.value?.focus());
};

const closeDeleteModal = () => {
    confirmingDeletion.value = false;
    deleteForm.clearErrors();
    deleteForm.reset();
};

const executeDeleteAccount = () => {
    deleteForm.delete('/profile', {
        preserveScroll: true,
        onSuccess: () => closeDeleteModal(),
        onError: () => passwordInput.value?.focus(),
        onFinish: () => deleteForm.reset(),
    });
};

const initials = computed(() => {
    if (!props.user?.name) return 'U';
    return props.user.name
        .split(' ')
        .slice(0, 2)
        .map((w) => w[0]?.toUpperCase())
        .join('');
});

const role = computed(() => (props.user?.role as string) || 'customer');

const roleLabel = computed(() => {
    if (role.value === 'admin') return 'Administrator';
    if (role.value === 'driver') return 'Driver';
    return 'Customer';
});

const formatDate = (raw?: string | null, fallback = '25 November 2026') => {
    if (!raw) return fallback;
    try {
        return new Date(raw).toLocaleDateString('id-ID', {
            day: 'numeric',
            month: 'long',
            year: 'numeric',
        });
    } catch {
        return fallback;
    }
};

const joinedDate = computed(() =>
    formatDate(props.user.created_at, '12 Oktober 2026'),
);

const pointsFormatted = computed(() => {
    const pts =
        props.user.customer?.points ??
        (props.user.customer as any)?.poin ??
        60000;
    return `${Number(pts).toLocaleString('id-ID')} poin`;
});

const membershipExpiry = computed(() => {
    const raw =
        props.user.customer?.membership_expires_at ||
        (props.user.customer as any)?.expired_member_date;
    return formatDate(raw, '25 November 2026');
});
</script>

<template>
    <div class="flex w-full flex-col gap-6">
        <!-- Heading -->
        <div class="flex flex-col gap-1">
            <h1
                class="text-[28px] font-extrabold tracking-tight text-night-ink"
            >
                Informasi akun
            </h1>
            <p class="text-[14px] text-charcoal">
                Ringkasan akses dan layanan yang terhubung dengan akunmu.
            </p>
        </div>

        <!-- 2 Columns Grid -->
        <div class="grid grid-cols-1 items-start gap-6 lg:grid-cols-12">
            <!-- Left: Account Identity Records -->
            <div
                class="flex flex-col gap-5 rounded-[20px] border border-border-gray bg-paper-white p-6 shadow-xs sm:p-8 lg:col-span-7"
            >
                <!-- Mini Profile Identity -->
                <div
                    class="flex items-center gap-4 border-b border-border-gray/50 pb-4"
                >
                    <div
                        v-if="!userPhoto"
                        class="flex size-14 shrink-0 items-center justify-center rounded-full bg-sprout-tint text-xl font-extrabold text-night-ink select-none"
                    >
                        {{ initials }}
                    </div>
                    <UserAvatar
                        v-else
                        :photo="userPhoto"
                        :role="user.role"
                        :name="user.name"
                        size="lg"
                    />
                    <div class="flex min-w-0 flex-col">
                        <span
                            class="truncate text-lg font-extrabold text-night-ink"
                        >
                            {{ user.name }}
                        </span>
                        <span
                            class="truncate text-xs font-normal text-charcoal"
                        >
                            {{ user.email }}
                        </span>
                    </div>
                </div>

                <!-- Fields -->
                <div class="flex flex-col divide-y divide-border-gray/50">
                    <DetailField label="ID akun" :value="`#${user.id}`" />
                    <DetailField label="Role akun" :value="roleLabel" />
                    <DetailField label="Bergabung sejak" :value="joinedDate" />
                </div>

                <!-- Callout Banner -->
                <div
                    class="flex flex-col gap-1 rounded-xl border border-sky-blue/20 bg-sky-tint/50 p-4"
                >
                    <span class="text-[13px] font-bold text-night-ink">
                        Role dikelola ReValue
                    </span>
                    <span class="text-[12px] leading-relaxed text-charcoal">
                        Hubungi pengelola jika akses akunmu perlu ditinjau.
                    </span>
                </div>

                <!-- Delete Account Option -->
                <div
                    class="flex items-center justify-between border-t border-border-gray/50 pt-2"
                >
                    <span class="text-xs text-charcoal">Tindakan Akun</span>
                    <button
                        type="button"
                        @click="openDeleteModal"
                        class="inline-flex cursor-pointer items-center gap-1.5 text-xs font-bold text-rose-500 transition hover:text-rose-600"
                    >
                        <Trash2 class="size-3.5" />
                        <span>Hapus Akun</span>
                    </button>
                </div>
            </div>

            <!-- Right: Role Specific Data -->
            <div
                class="flex flex-col gap-5 rounded-[20px] border border-border-gray bg-paper-white p-6 shadow-xs sm:p-8 lg:col-span-5"
            >
                <!-- Customer Role Specific -->
                <template v-if="role === 'customer'">
                    <h3
                        class="text-[21px] leading-snug font-extrabold text-night-ink"
                    >
                        Layanan Customer
                    </h3>

                    <div class="flex flex-col divide-y divide-border-gray/50">
                        <DetailField
                            label="Saldo poin"
                            :value="pointsFormatted"
                        />
                        <DetailField
                            label="Status keanggotaan"
                            :value="
                                (user.customer?.is_member ?? true)
                                    ? 'Aktif'
                                    : 'Nonaktif'
                            "
                        />
                        <DetailField
                            label="Masa aktif"
                            :value="membershipExpiry"
                        />
                    </div>

                    <p
                        class="border-t border-border-gray/50 pt-2 text-[12px] leading-relaxed text-charcoal"
                    >
                        Saldo berasal dari transaksi poin. Status dan masa aktif
                        berasal dari langganan yang telah dibayar.
                    </p>
                </template>

                <!-- Driver Role Specific -->
                <template v-else-if="role === 'driver'">
                    <h3
                        class="text-[21px] leading-snug font-extrabold text-night-ink"
                    >
                        Status Driver
                    </h3>

                    <div class="flex flex-col divide-y divide-border-gray/50">
                        <DetailField
                            label="Kesiapan tugas"
                            :value="
                                (user.driver?.is_available ?? true)
                                    ? 'Siap bertugas'
                                    : 'Istirahat'
                            "
                        />
                        <DetailField
                            label="Area Operasional"
                            value="Jember & Sekitarnya"
                        />
                    </div>

                    <p
                        class="border-t border-border-gray/50 pt-2 text-[12px] leading-relaxed text-charcoal"
                    >
                        Kesiapan menerima jemputan diatur di dashboard Driver.
                        Profil ini tidak memuat saldo poin Customer.
                    </p>
                </template>

                <!-- Admin Role Specific -->
                <template v-else>
                    <h3
                        class="text-[21px] leading-snug font-extrabold text-night-ink"
                    >
                        Akses Administrator
                    </h3>

                    <div class="flex flex-col divide-y divide-border-gray/50">
                        <DetailField
                            label="Hak akses"
                            value="Penuh (Super Admin)"
                        />
                        <DetailField
                            label="Modul Terhubung"
                            value="Manajemen Pengguna & Jenis Sampah"
                        />
                    </div>

                    <p
                        class="border-t border-border-gray/50 pt-2 text-[12px] leading-relaxed text-charcoal"
                    >
                        Akun ini memiliki hak akses penuh ke data operasional
                        ReValue.
                    </p>
                </template>
            </div>
        </div>

        <!-- Confirm Delete Account Modal -->
        <div
            v-if="confirmingDeletion"
            class="animate-in fade-in fixed inset-0 z-50 flex items-center justify-center bg-night-ink/40 p-4 backdrop-blur-xs duration-200"
        >
            <div
                class="relative flex w-full max-w-md flex-col gap-5 rounded-[24px] border border-border-gray bg-paper-white p-6 shadow-xl sm:p-8"
            >
                <button
                    type="button"
                    @click="closeDeleteModal"
                    class="absolute top-5 right-5 rounded-lg p-1 text-charcoal transition hover:text-night-ink"
                >
                    <X class="size-5" />
                </button>

                <div class="flex items-center gap-3 text-rose-600">
                    <AlertTriangle class="size-6 shrink-0" />
                    <h3 class="text-[18px] font-extrabold text-night-ink">
                        Konfirmasi Hapus Akun
                    </h3>
                </div>

                <p class="text-[13px] leading-relaxed text-charcoal">
                    Setelah akun Anda dihapus, semua data profil, riwayat
                    setoran, dan poin akan dinonaktifkan. Masukkan kata sandi
                    untuk melanjutkan.
                </p>

                <form
                    @submit.prevent="executeDeleteAccount"
                    class="flex flex-col gap-4"
                >
                    <div class="flex flex-col gap-1.5">
                        <label class="text-[13px] font-bold text-night-ink">
                            Kata Sandi Anda *
                        </label>
                        <input
                            ref="passwordInput"
                            v-model="deleteForm.password"
                            type="password"
                            required
                            placeholder="Kata sandi akun"
                            class="h-[48px] rounded-xl border border-border-gray bg-paper-white px-4 text-[15px] text-night-ink transition focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20 focus:outline-none"
                            :class="{
                                'border-rose-500': deleteForm.errors.password,
                            }"
                        />
                        <span
                            v-if="deleteForm.errors.password"
                            class="text-xs font-semibold text-rose-500"
                        >
                            {{ deleteForm.errors.password }}
                        </span>
                    </div>

                    <div
                        class="flex items-center justify-end gap-3 border-t border-border-gray/70 pt-2"
                    >
                        <AppButton
                            variant="outline"
                            size="md"
                            type="button"
                            @click="closeDeleteModal"
                        >
                            Batal
                        </AppButton>
                        <button
                            type="submit"
                            :disabled="deleteForm.processing"
                            class="cursor-pointer rounded-xl bg-rose-600 px-5 py-2.5 text-sm font-extrabold text-white shadow-sm transition hover:bg-rose-700 disabled:opacity-50"
                        >
                            {{
                                deleteForm.processing
                                    ? 'Menghapus...'
                                    : 'Hapus Akun'
                            }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
