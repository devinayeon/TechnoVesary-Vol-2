<script setup lang="ts">
import { ref, computed } from "vue";
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import DashboardLayout from "@/layouts/DashboardLayout.vue";
import UserAvatar from "@/components/Dashboard/UserAvatar.vue";
import StatusChip from "@/components/Dashboard/StatusChip.vue";
import UserDeleteModal from "@/components/Dashboard/UserDeleteModal.vue";
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
} from "lucide-vue-next";

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
    if (confirm("Aktifkan kembali akun pengguna ini?")) {
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
        case "admin":
            return "Administrator";
        case "driver":
            return "Driver Armada";
        case "customer":
        default:
            return "Customer";
    }
};

const roleLabel = computed(() => getRoleLabel(props.user.role));

const roleTone = computed(() => {
    switch (props.user.role?.toLowerCase()) {
        case "admin":
            return "Amber" as const;
        case "driver":
            return "Blue" as const;
        case "customer":
        default:
            return "Green" as const;
    }
});

const formattedDate = (d?: string | null) => {
    if (!d) return "—";
    try {
        return new Date(d).toLocaleDateString("id-ID", {
            day: "numeric",
            month: "long",
            year: "numeric",
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
            class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6"
        >
            <div>
                <Link
                    href="/admin/users"
                    class="inline-flex items-center gap-2 text-[13px] font-bold text-charcoal hover:text-night-ink transition-colors mb-2 group"
                >
                    <ArrowLeft
                        class="size-4 transition-transform group-hover:-translate-x-1"
                    />
                    <span>Kembali ke Pengguna</span>
                </Link>

                <h1
                    class="text-[26px] sm:text-[30px] font-extrabold text-night-ink tracking-tight leading-tight"
                >
                    {{ user.name }}
                </h1>
                <p class="text-[14px] font-normal text-charcoal mt-0.5">
                    Detail profil dan status layanan akun pengguna.
                </p>
            </div>

            <!-- Detail Actions (Figma Node 485:3080) -->
            <div class="flex items-center gap-2.5">
                <!-- Ubah Button -->
                <Link
                    :href="`/admin/users/${user.id}/edit`"
                    class="px-5 h-[44px] rounded-xl border-2 border-b-[4px] border-eco-green-dark bg-eco-green font-extrabold text-[14px] text-night-ink hover:brightness-105 active:translate-y-0.5 active:border-b-2 transition-all cursor-pointer inline-flex items-center justify-center gap-2"
                >
                    <Edit3 class="size-4" />
                    <span>Ubah</span>
                </Link>

                <!-- Self-lock badge (menggantikan tombol Hapus saat melihat profil sendiri) -->
                <div
                    v-if="isSelf"
                    class="px-4 h-[44px] rounded-xl border-2 border-amber-200 bg-amber-50 font-bold text-[13px] text-amber-700 inline-flex items-center gap-2 select-none"
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
                        class="px-5 h-[44px] rounded-xl border-2 border-b-[4px] border-red-200 bg-red-50 font-extrabold text-[14px] text-red-600 hover:bg-red-100 active:translate-y-0.5 active:border-b-2 transition-all cursor-pointer inline-flex items-center justify-center gap-2"
                        @click="openDeleteModal"
                    >
                        <Trash2 class="size-4" />
                        <span>Hapus</span>
                    </button>
                    <button
                        v-else
                        type="button"
                        class="px-5 h-[44px] rounded-xl border-2 border-b-[4px] border-eco-green/40 bg-sprout-tint font-extrabold text-[14px] text-night-ink hover:brightness-105 active:translate-y-0.5 active:border-b-2 transition-all cursor-pointer inline-flex items-center justify-center gap-2"
                        @click="restoreUser"
                    >
                        <RotateCcw class="size-4 text-eco-green-dark" />
                        <span>Pulihkan Akun</span>
                    </button>
                </template>
            </div>
        </div>

        <!-- 2-Column Detail Layout (Figma Node 485:3087) -->
        <div class="flex flex-col lg:flex-row items-start gap-6">
            <!-- Account Profile Card (Left Column - Figma Node 485:3088) -->
            <div
                class="flex-1 w-full bg-paper-white border-2 border-b-[4px] border-border-gray rounded-[20px] p-6 lg:p-8 shadow-xs flex flex-col gap-6"
            >
                <!-- Profile Heading (Avatar + Name + Role Chip) -->
                <div
                    class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-border-gray/70"
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
                                class="text-[22px] font-extrabold text-night-ink leading-tight"
                            >
                                {{ user.name }}
                            </h2>
                            <p
                                class="text-[14px] font-medium text-charcoal mt-0.5"
                            >
                                {{ user.email }}
                            </p>
                            <p
                                class="text-[12px] font-normal text-charcoal/70 mt-1"
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
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <!-- Nama Lengkap -->
                    <div
                        class="bg-cloud-surface/50 border border-border-gray/60 rounded-xl p-4"
                    >
                        <span
                            class="text-[12px] font-semibold text-charcoal block mb-1"
                        >
                            Nama Lengkap
                        </span>
                        <span class="text-[15px] font-extrabold text-night-ink">
                            {{ user.name }}
                        </span>
                    </div>

                    <!-- Role -->
                    <div
                        class="bg-cloud-surface/50 border border-border-gray/60 rounded-xl p-4"
                    >
                        <span
                            class="text-[12px] font-semibold text-charcoal block mb-1"
                        >
                            Peran Pengguna (Role)
                        </span>
                        <span class="text-[15px] font-extrabold text-night-ink">
                            {{ roleLabel }}
                        </span>
                    </div>

                    <!-- Email -->
                    <div
                        class="bg-cloud-surface/50 border border-border-gray/60 rounded-xl p-4"
                    >
                        <div
                            class="flex items-center gap-1.5 mb-1 text-charcoal"
                        >
                            <Mail class="size-3.5" />
                            <span class="text-[12px] font-semibold"
                                >Alamat Email</span
                            >
                        </div>
                        <span
                            class="text-[15px] font-extrabold text-night-ink break-all"
                        >
                            {{ user.email }}
                        </span>
                    </div>

                    <!-- Nomor Telepon -->
                    <div
                        class="bg-cloud-surface/50 border border-border-gray/60 rounded-xl p-4"
                    >
                        <div
                            class="flex items-center gap-1.5 mb-1 text-charcoal"
                        >
                            <Phone class="size-3.5" />
                            <span class="text-[12px] font-semibold"
                                >Nomor Telepon</span
                            >
                        </div>
                        <span class="text-[15px] font-extrabold text-night-ink">
                            {{ user.phone_number || "—" }}
                        </span>
                    </div>
                </div>

                <!-- Alamat -->
                <div
                    class="bg-cloud-surface/50 border border-border-gray/60 rounded-xl p-4"
                >
                    <div class="flex items-center gap-1.5 mb-1 text-charcoal">
                        <MapPin class="size-3.5" />
                        <span class="text-[12px] font-semibold"
                            >Alamat Domisili</span
                        >
                    </div>
                    <span
                        class="text-[14px] font-medium text-night-ink leading-relaxed"
                    >
                        {{ user.address || "Belum ada data alamat domisili." }}
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
                class="w-full lg:w-[368px] bg-paper-white border-2 border-b-[4px] border-border-gray rounded-[20px] p-6 shadow-xs shrink-0 flex flex-col gap-5"
            >
                <div
                    class="flex items-center justify-between pb-3 border-b border-border-gray/70"
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
                        class="bg-cloud-surface/70 rounded-xl p-3.5 border border-border-gray/60"
                    >
                        <div
                            class="flex items-center gap-1.5 text-charcoal text-[12px] font-semibold mb-1"
                        >
                            <Sparkles class="size-3.5 text-sunlight-gold" />
                            <span>Saldo Poin Eco-Reward</span>
                        </div>
                        <p class="text-[22px] font-extrabold text-night-ink">
                            {{
                                (user.customer?.points ?? 0).toLocaleString(
                                    "id-ID",
                                )
                            }}
                            Poin
                        </p>
                    </div>

                    <!-- Subscription Status -->
                    <div
                        class="bg-cloud-surface/70 rounded-xl p-3.5 border border-border-gray/60"
                    >
                        <div
                            class="flex items-center gap-1.5 text-charcoal text-[12px] font-semibold mb-1"
                        >
                            <Shield class="size-3.5 text-eco-green-dark" />
                            <span>Langganan ReValue+</span>
                        </div>
                        <p class="text-[15px] font-extrabold text-night-ink">
                            {{
                                user.customer?.is_member
                                    ? "Aktif"
                                    : "Belum Berlangganan"
                            }}
                        </p>
                        <p
                            v-if="user.customer?.membership_expires_at"
                            class="text-[12px] font-medium text-charcoal mt-1"
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
                        class="text-[12px] font-medium text-charcoal/80 leading-relaxed bg-cloud-surface/40 p-3 rounded-xl"
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
                        class="bg-cloud-surface/70 rounded-xl p-3.5 border border-border-gray/60"
                    >
                        <div
                            class="flex items-center gap-1.5 text-charcoal text-[12px] font-semibold mb-1"
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
                                    ? "Siap Menerima Tugas"
                                    : "Sedang Istirahat / Nonaktif"
                            }}
                        </p>
                    </div>

                    <p
                        class="text-[12px] font-medium text-charcoal/80 leading-relaxed bg-cloud-surface/40 p-3 rounded-xl"
                    >
                        Status armada menentukan apakah driver dapat menerima
                        tugas penjemputan sampah di wilayah terdekat.
                    </p>
                </div>

                <!-- Admin Details -->
                <div v-else class="space-y-4">
                    <div
                        class="bg-cloud-surface/70 rounded-xl p-3.5 border border-border-gray/60"
                    >
                        <div
                            class="flex items-center gap-1.5 text-charcoal text-[12px] font-semibold mb-1"
                        >
                            <Shield class="size-3.5 text-sunlight-gold" />
                            <span>Tingkat Akses</span>
                        </div>
                        <p class="text-[16px] font-extrabold text-night-ink">
                            Super Administrator
                        </p>
                    </div>

                    <p
                        class="text-[12px] font-medium text-charcoal/80 leading-relaxed bg-cloud-surface/40 p-3 rounded-xl"
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
