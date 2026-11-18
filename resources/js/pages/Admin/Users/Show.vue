<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import DashboardLayout from '@/layouts/DashboardLayout.vue';
import UserAvatar from '@/components/Dashboard/UserAvatar.vue';
import StatusChip from '@/components/Dashboard/StatusChip.vue';
import UserDeleteModal from '@/components/Dashboard/UserDeleteModal.vue';
import {
    ArrowLeft,
    Edit3,
    Trash2,
    RotateCcw,
    Mail,
    Phone,
    MapPin,
    Shield,
    Sparkles,
    Calendar,
    Truck,
} from 'lucide-vue-next';

const props = defineProps<{
    user: {
        id: number;
        name: string;
        email: string;
        role: string;
        phone_number: string | null;
        photo: string | null;
        address: string | null;
        latitude: number | null;
        longitude: number | null;
        email_verified_at: string | null;
        deleted_at: string | null;
        created_at: string;
        admin?: any;
        driver?: {
            is_available: boolean;
        };
        customer?: {
            points: number;
            is_member: boolean;
            membership_expires_at: string | null;
        };
    };
}>();

const page = usePage();
const isSelf = computed(() => page.props.auth?.user?.id === props.user.id);

const isDeleteModalOpen = ref(false);

const openDeleteModal = () => {
    isDeleteModalOpen.value = true;
};

const closeDeleteModal = () => {
    isDeleteModalOpen.value = false;
};

const restoreUser = () => {
    if (confirm('Aktifkan kembali akun pengguna ini?')) {
        router.post(
            `/admin/users/${props.user.id}/restore`,
            {},
            {
                preserveScroll: true,
            },
        );
    }
};

const getRoleLabel = (role?: string): string => {
    switch (role?.toLowerCase()) {
        case 'admin':
            return 'Administrator';
        case 'driver':
            return 'Driver Armada';
        case 'customer':
        default:
            return 'Customer';
    }
};

const roleLabel = computed(() => getRoleLabel(props.user.role));

const roleTone = computed(() => {
    switch (props.user.role?.toLowerCase()) {
        case 'admin':
            return 'Amber' as const;
        case 'driver':
            return 'Blue' as const;
        case 'customer':
        default:
            return 'Green' as const;
    }
});

const formattedDate = (d?: string | null) => {
    if (!d) return '—';
    try {
        return new Date(d).toLocaleDateString('id-ID', {
            day: 'numeric',
            month: 'long',
            year: 'numeric',
        });
    } catch {
        return d;
    }
};
</script>

<template>
    <Head :title="`Detail ${user.name} - Admin ReValue`" />

    <DashboardLayout>
        <!-- Page Heading & Actions (Figma Node 485:3076) -->
        <div
            class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <div>
                <Link
                    href="/admin/users"
                    class="group mb-2 inline-flex items-center gap-2 text-[13px] font-bold text-charcoal transition-colors hover:text-night-ink"
                >
                    <ArrowLeft
                        class="size-4 transition-transform group-hover:-translate-x-1"
                    />
                    <span>Kembali ke Pengguna</span>
                </Link>

                <h1
                    class="text-[26px] leading-tight font-extrabold tracking-tight text-night-ink sm:text-[30px]"
                >
                    {{ user.name }}
                </h1>
                <p class="mt-0.5 text-[14px] font-normal text-charcoal">
                    Detail profil dan status layanan akun pengguna.
                </p>
            </div>

            <!-- Detail Actions (Figma Node 485:3080) -->
            <div class="flex items-center gap-2.5">
                <!-- Ubah Button -->
                <Link
                    :href="`/admin/users/${user.id}/edit`"
                    class="inline-flex h-[44px] cursor-pointer items-center justify-center gap-2 rounded-xl border-2 border-b-[4px] border-eco-green-dark bg-eco-green px-5 text-[14px] font-extrabold text-night-ink transition-all hover:brightness-105 active:translate-y-0.5 active:border-b-2"
                >
                    <Edit3 class="size-4" />
                    <span>Ubah</span>
                </Link>

                <!-- Self-lock badge (menggantikan tombol Hapus saat melihat profil sendiri) -->
                <div
                    v-if="isSelf"
                    class="inline-flex h-[44px] items-center gap-2 rounded-xl border-2 border-amber-200 bg-amber-50 px-4 text-[13px] font-bold text-amber-700 select-none"
                    title="Admin tidak dapat menghapus akun miliknya sendiri"
                >
                    <Shield class="size-4 shrink-0" />
                    <span>Akun Anda sendiri</span>
                </div>

                <!-- Hapus / Pulihkan Button (hanya tampil jika bukan profil sendiri) -->
                <template v-else>
                    <button
                        v-if="!user.deleted_at"
                        type="button"
                        class="inline-flex h-[44px] cursor-pointer items-center justify-center gap-2 rounded-xl border-2 border-b-[4px] border-red-200 bg-red-50 px-5 text-[14px] font-extrabold text-red-600 transition-all hover:bg-red-100 active:translate-y-0.5 active:border-b-2"
                        @click="openDeleteModal"
                    >
                        <Trash2 class="size-4" />
                        <span>Hapus</span>
                    </button>
                    <button
                        v-else
                        type="button"
                        class="inline-flex h-[44px] cursor-pointer items-center justify-center gap-2 rounded-xl border-2 border-b-[4px] border-eco-green/40 bg-sprout-tint px-5 text-[14px] font-extrabold text-night-ink transition-all hover:brightness-105 active:translate-y-0.5 active:border-b-2"
                        @click="restoreUser"
                    >
                        <RotateCcw class="size-4 text-eco-green-dark" />
                        <span>Pulihkan Akun</span>
                    </button>
                </template>
            </div>
        </div>

        <!-- 2-Column Detail Layout (Figma Node 485:3087) -->
        <div class="flex flex-col items-start gap-6 lg:flex-row">
            <!-- Account Profile Card (Left Column - Figma Node 485:3088) -->
            <div
                class="flex w-full flex-1 flex-col gap-6 rounded-[20px] border-2 border-b-[4px] border-border-gray bg-paper-white p-6 shadow-xs lg:p-8"
            >
                <!-- Profile Heading (Avatar + Name + Role Chip) -->
                <div
                    class="flex flex-col justify-between gap-4 border-b border-border-gray/70 pb-6 sm:flex-row sm:items-center"
                >
                    <div class="flex items-center gap-4">
                        <UserAvatar
                            :photo="user.photo"
                            :role="user.role"
                            :name="user.name"
                            size="xl"
                        />
                        <div>
                            <h2
                                class="text-[22px] leading-tight font-extrabold text-night-ink"
                            >
                                {{ user.name }}
                            </h2>
                            <p
                                class="mt-0.5 text-[14px] font-medium text-charcoal"
                            >
                                {{ user.email }}
                            </p>
                            <p
                                class="mt-1 text-[12px] font-normal text-charcoal/70"
                            >
                                Bergabung sejak
                                {{ formattedDate(user.created_at) }}
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <StatusChip :label="roleLabel" :tone="roleTone" />
                        <StatusChip
                            v-if="user.deleted_at"
                            label="Nonaktif"
                            tone="Neutral"
                        />
                        <StatusChip v-else label="Aktif" tone="Green" />
                    </div>
                </div>

                <!-- Identity & Contact Fields Grid -->
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <!-- Nama Lengkap -->
                    <div
                        class="rounded-xl border border-border-gray/60 bg-cloud-surface/50 p-4"
                    >
                        <span
                            class="mb-1 block text-[12px] font-semibold text-charcoal"
                        >
                            Nama Lengkap
                        </span>
                        <span class="text-[15px] font-extrabold text-night-ink">
                            {{ user.name }}
                        </span>
                    </div>

                    <!-- Role -->
                    <div
                        class="rounded-xl border border-border-gray/60 bg-cloud-surface/50 p-4"
                    >
                        <span
                            class="mb-1 block text-[12px] font-semibold text-charcoal"
                        >
                            Peran Pengguna (Role)
                        </span>
                        <span class="text-[15px] font-extrabold text-night-ink">
                            {{ roleLabel }}
                        </span>
                    </div>

                    <!-- Email -->
                    <div
                        class="rounded-xl border border-border-gray/60 bg-cloud-surface/50 p-4"
                    >
                        <div
                            class="mb-1 flex items-center gap-1.5 text-charcoal"
                        >
                            <Mail class="size-3.5" />
                            <span class="text-[12px] font-semibold"
                                >Alamat Email</span
                            >
                        </div>
                        <span
                            class="text-[15px] font-extrabold break-all text-night-ink"
                        >
                            {{ user.email }}
                        </span>
                    </div>

                    <!-- Nomor Telepon -->
                    <div
                        class="rounded-xl border border-border-gray/60 bg-cloud-surface/50 p-4"
                    >
                        <div
                            class="mb-1 flex items-center gap-1.5 text-charcoal"
                        >
                            <Phone class="size-3.5" />
                            <span class="text-[12px] font-semibold"
                                >Nomor Telepon</span
                            >
                        </div>
                        <span class="text-[15px] font-extrabold text-night-ink">
                            {{ user.phone_number || '—' }}
                        </span>
                    </div>
                </div>

                <!-- Alamat -->
                <div
                    class="rounded-xl border border-border-gray/60 bg-cloud-surface/50 p-4"
                >
                    <div class="mb-1 flex items-center gap-1.5 text-charcoal">
                        <MapPin class="size-3.5" />
                        <span class="text-[12px] font-semibold"
                            >Alamat Domisili</span
                        >
                    </div>
                    <span
                        class="text-[14px] leading-relaxed font-medium text-night-ink"
                    >
                        {{ user.address || 'Belum ada data alamat domisili.' }}
                    </span>
                </div>

                <!-- Verification Status Chip -->
                <div class="flex items-center gap-2 pt-2">
                    <StatusChip
                        v-if="user.email_verified_at"
                        label="Email Terverifikasi"
                        tone="Blue"
                    />
                    <StatusChip
                        v-else
                        label="Email Belum Terverifikasi"
                        tone="Amber"
                    />
                </div>
            </div>

            <!-- Service Status Card (Right Column - Figma Node 485:3116) -->
            <div
                class="flex w-full shrink-0 flex-col gap-5 rounded-[20px] border-2 border-b-[4px] border-border-gray bg-paper-white p-6 shadow-xs lg:w-[368px]"
            >
                <div
                    class="flex items-center justify-between border-b border-border-gray/70 pb-3"
                >
                    <h3 class="text-[18px] font-extrabold text-night-ink">
                        Status Layanan
                    </h3>
                    <StatusChip :label="roleLabel" :tone="roleTone" />
                </div>

                <!-- Customer Details -->
                <div
                    v-if="user.role?.toLowerCase() === 'customer'"
                    class="space-y-4"
                >
                    <!-- Saldo Poin -->
                    <div
                        class="rounded-xl border border-border-gray/60 bg-cloud-surface/70 p-3.5"
                    >
                        <div
                            class="mb-1 flex items-center gap-1.5 text-[12px] font-semibold text-charcoal"
                        >
                            <Sparkles class="size-3.5 text-sunlight-gold" />
                            <span>Saldo Poin Eco-Reward</span>
                        </div>
                        <p class="text-[22px] font-extrabold text-night-ink">
                            {{
                                (user.customer?.points ?? 0).toLocaleString(
                                    'id-ID',
                                )
                            }}
                            Poin
                        </p>
                    </div>

                    <!-- Subscription Status -->
                    <div
                        class="rounded-xl border border-border-gray/60 bg-cloud-surface/70 p-3.5"
                    >
                        <div
                            class="mb-1 flex items-center gap-1.5 text-[12px] font-semibold text-charcoal"
                        >
                            <Shield class="size-3.5 text-eco-green-dark" />
                            <span>Langganan ReValue+</span>
                        </div>
                        <p class="text-[15px] font-extrabold text-night-ink">
                            {{
                                user.customer?.is_member
                                    ? 'Aktif'
                                    : 'Belum Berlangganan'
                            }}
                        </p>
                        <p
                            v-if="user.customer?.membership_expires_at"
                            class="mt-1 text-[12px] font-medium text-charcoal"
                        >
                            Berlaku s/d
                            {{
                                formattedDate(
                                    user.customer.membership_expires_at,
                                )
                            }}
                        </p>
                    </div>

                    <p
                        class="rounded-xl bg-cloud-surface/40 p-3 text-[12px] leading-relaxed font-medium text-charcoal/80"
                    >
                        Saldo dan langganan ditampilkan untuk audit data.
                        Perubahan saldo dilakukan otomatis saat penjemputan
                        sampah.
                    </p>
                </div>

                <!-- Driver Details -->
                <div
                    v-else-if="user.role?.toLowerCase() === 'driver'"
                    class="space-y-4"
                >
                    <div
                        class="rounded-xl border border-border-gray/60 bg-cloud-surface/70 p-3.5"
                    >
                        <div
                            class="mb-1 flex items-center gap-1.5 text-[12px] font-semibold text-charcoal"
                        >
                            <Truck class="size-3.5 text-sky-blue" />
                            <span>Ketersediaan Armada</span>
                        </div>
                        <p
                            class="text-[16px] font-extrabold"
                            :class="
                                user.driver?.is_available
                                    ? 'text-eco-green-dark'
                                    : 'text-charcoal'
                            "
                        >
                            {{
                                user.driver?.is_available
                                    ? 'Siap Menerima Tugas'
                                    : 'Sedang Istirahat / Nonaktif'
                            }}
                        </p>
                    </div>

                    <p
                        class="rounded-xl bg-cloud-surface/40 p-3 text-[12px] leading-relaxed font-medium text-charcoal/80"
                    >
                        Status armada menentukan apakah driver dapat menerima
                        tugas penjemputan sampah di wilayah terdekat.
                    </p>
                </div>

                <!-- Admin Details -->
                <div v-else class="space-y-4">
                    <div
                        class="rounded-xl border border-border-gray/60 bg-cloud-surface/70 p-3.5"
                    >
                        <div
                            class="mb-1 flex items-center gap-1.5 text-[12px] font-semibold text-charcoal"
                        >
                            <Shield class="size-3.5 text-sunlight-gold" />
                            <span>Tingkat Akses</span>
                        </div>
                        <p class="text-[16px] font-extrabold text-night-ink">
                            Super Administrator
                        </p>
                    </div>

                    <p
                        class="rounded-xl bg-cloud-surface/40 p-3 text-[12px] leading-relaxed font-medium text-charcoal/80"
                    >
                        Administrator memiliki kendali penuh atas tata kelola
                        sistem, manajemen pengguna, penjemputan, dan transaksi.
                    </p>
                </div>
            </div>
        </div>

        <!-- Delete Modal -->
        <UserDeleteModal
            :show="isDeleteModalOpen"
            :user="{ id: user.id, name: user.name, email: user.email }"
            @close="closeDeleteModal"
        />
    </DashboardLayout>
</template>
