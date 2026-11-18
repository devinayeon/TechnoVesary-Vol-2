<script setup lang="ts">
import { computed, nextTick, ref } from "vue";
import { useForm } from "@inertiajs/vue3";
import { Trash2, X, AlertTriangle } from "lucide-vue-next";
import type { User } from "@/types/auth";
import DetailField from "./DetailField.vue";
import AppButton from "@/components/Landing/AppButton.vue";
import UserAvatar from "@/components/Dashboard/UserAvatar.vue";

const props = defineProps<{
    user: User;
}>();

const userPhoto = computed(() => {
    return props.user.photo || (props.user as any).foto || null;
});

const confirmingDeletion = ref(false);
const passwordInput = ref<HTMLInputElement | null>(null);

const deleteForm = useForm({
    password: "",
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
    deleteForm.delete("/profile", {
        preserveScroll: true,
        onSuccess: () => closeDeleteModal(),
        onError: () => passwordInput.value?.focus(),
        onFinish: () => deleteForm.reset(),
    });
};

const initials = computed(() => {
    if (!props.user?.name) return "U";
    return props.user.name
        .split(" ")
        .slice(0, 2)
        .map((w) => w[0]?.toUpperCase())
        .join("");
});

const role = computed(() => (props.user?.role as string) || "customer");

const roleLabel = computed(() => {
    if (role.value === "admin") return "Administrator";
    if (role.value === "driver") return "Driver";
    return "Customer";
});

const formatDate = (raw?: string | null, fallback = "25 November 2026") => {
    if (!raw) return fallback;
    try {
        return new Date(raw).toLocaleDateString("id-ID", {
            day: "numeric",
            month: "long",
            year: "numeric",
        });
    } catch {
        return fallback;
    }
};

const joinedDate = computed(() => formatDate(props.user.created_at, "12 Oktober 2026"));

const pointsFormatted = computed(() => {
    const pts = props.user.customer?.points ?? (props.user.customer as any)?.poin ?? 60000;
    return `${Number(pts).toLocaleString("id-ID")} poin`;
});

const membershipExpiry = computed(() => {
    const raw = props.user.customer?.membership_expires_at || (props.user.customer as any)?.expired_member_date;
    return formatDate(raw, "25 November 2026");
});
</script>

<template>
    <div class="flex flex-col gap-6 w-full">
        <!-- Heading -->
        <div class="flex flex-col gap-1">
            <h1 class="text-[28px] font-extrabold text-night-ink tracking-tight">
                Informasi akun
            </h1>
            <p class="text-[14px] text-charcoal">
                Ringkasan akses dan layanan yang terhubung dengan akunmu.
            </p>
        </div>

        <!-- 2 Columns Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            <!-- Left: Account Identity Records -->
            <div
                class="lg:col-span-7 bg-paper-white border border-border-gray rounded-[20px] p-6 sm:p-8 flex flex-col gap-5 shadow-xs"
            >
                <!-- Mini Profile Identity -->
                <div class="flex items-center gap-4 pb-4 border-b border-border-gray/50">
                    <div
                        v-if="!userPhoto"
                        class="size-14 rounded-full bg-sprout-tint text-night-ink flex items-center justify-center font-extrabold text-xl select-none shrink-0"
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
                    <div class="flex flex-col min-w-0">
                        <span class="text-lg font-extrabold text-night-ink truncate">
                            {{ user.name }}
                        </span>
                        <span class="text-xs text-charcoal truncate font-normal">
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
                <div class="p-4 rounded-xl bg-sky-tint/50 border border-sky-blue/20 flex flex-col gap-1">
                    <span class="text-[13px] font-bold text-night-ink">
                        Role dikelola ReValue
                    </span>
                    <span class="text-[12px] text-charcoal leading-relaxed">
                        Hubungi pengelola jika akses akunmu perlu ditinjau.
                    </span>
                </div>

                <!-- Delete Account Option -->
                <div class="pt-2 border-t border-border-gray/50 flex items-center justify-between">
                    <span class="text-xs text-charcoal">Tindakan Akun</span>
                    <button
                        type="button"
                        @click="openDeleteModal"
                        class="text-xs font-bold text-rose-500 hover:text-rose-600 inline-flex items-center gap-1.5 transition cursor-pointer"
                    >
                        <Trash2 class="size-3.5" />
                        <span>Hapus Akun</span>
                    </button>
                </div>
            </div>

            <!-- Right: Role Specific Data -->
            <div
                class="lg:col-span-5 bg-paper-white border border-border-gray rounded-[20px] p-6 sm:p-8 flex flex-col gap-5 shadow-xs"
            >
                <!-- Customer Role Specific -->
                <template v-if="role === 'customer'">
                    <h3 class="text-[21px] font-extrabold text-night-ink leading-snug">
                        Layanan Customer
                    </h3>

                    <div class="flex flex-col divide-y divide-border-gray/50">
                        <DetailField label="Saldo poin" :value="pointsFormatted" />
                        <DetailField
                            label="Status keanggotaan"
                            :value="(user.customer?.is_member ?? true) ? 'Aktif' : 'Nonaktif'"
                        />
                        <DetailField label="Masa aktif" :value="membershipExpiry" />
                    </div>

                    <p class="text-[12px] text-charcoal leading-relaxed pt-2 border-t border-border-gray/50">
                        Saldo berasal dari transaksi poin. Status dan masa aktif berasal dari langganan yang telah dibayar.
                    </p>
                </template>

                <!-- Driver Role Specific -->
                <template v-else-if="role === 'driver'">
                    <h3 class="text-[21px] font-extrabold text-night-ink leading-snug">
                        Status Driver
                    </h3>

                    <div class="flex flex-col divide-y divide-border-gray/50">
                        <DetailField
                            label="Kesiapan tugas"
                            :value="(user.driver?.is_available ?? true) ? 'Siap bertugas' : 'Istirahat'"
                        />
                        <DetailField label="Area Operasional" value="Jember & Sekitarnya" />
                    </div>

                    <p class="text-[12px] text-charcoal leading-relaxed pt-2 border-t border-border-gray/50">
                        Kesiapan menerima jemputan diatur di dashboard Driver. Profil ini tidak memuat saldo poin Customer.
                    </p>
                </template>

                <!-- Admin Role Specific -->
                <template v-else>
                    <h3 class="text-[21px] font-extrabold text-night-ink leading-snug">
                        Akses Administrator
                    </h3>

                    <div class="flex flex-col divide-y divide-border-gray/50">
                        <DetailField label="Hak akses" value="Penuh (Super Admin)" />
                        <DetailField label="Modul Terhubung" value="Manajemen Pengguna & Jenis Sampah" />
                    </div>

                    <p class="text-[12px] text-charcoal leading-relaxed pt-2 border-t border-border-gray/50">
                        Akun ini memiliki hak akses penuh ke data operasional ReValue.
                    </p>
                </template>
            </div>
        </div>

        <!-- Confirm Delete Account Modal -->
        <div
            v-if="confirmingDeletion"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-night-ink/40 backdrop-blur-xs animate-in fade-in duration-200"
        >
            <div
                class="bg-paper-white border border-border-gray rounded-[24px] max-w-md w-full p-6 sm:p-8 flex flex-col gap-5 shadow-xl relative"
            >
                <button
                    type="button"
                    @click="closeDeleteModal"
                    class="absolute top-5 right-5 text-charcoal hover:text-night-ink p-1 rounded-lg transition"
                >
                    <X class="size-5" />
                </button>

                <div class="flex items-center gap-3 text-rose-600">
                    <AlertTriangle class="size-6 shrink-0" />
                    <h3 class="text-[18px] font-extrabold text-night-ink">
                        Konfirmasi Hapus Akun
                    </h3>
                </div>

                <p class="text-[13px] text-charcoal leading-relaxed">
                    Setelah akun Anda dihapus, semua data profil, riwayat setoran, dan poin akan dinonaktifkan. Masukkan kata sandi untuk melanjutkan.
                </p>

                <form @submit.prevent="executeDeleteAccount" class="flex flex-col gap-4">
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
                            class="h-[48px] px-4 rounded-xl border border-border-gray bg-paper-white text-[15px] text-night-ink focus:outline-none focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20 transition"
                            :class="{ 'border-rose-500': deleteForm.errors.password }"
                        />
                        <span
                            v-if="deleteForm.errors.password"
                            class="text-xs text-rose-500 font-semibold"
                        >
                            {{ deleteForm.errors.password }}
                        </span>
                    </div>

                    <div class="pt-2 flex items-center justify-end gap-3 border-t border-border-gray/70">
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
                            class="px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-sm font-extrabold shadow-sm transition disabled:opacity-50 cursor-pointer"
                        >
                            {{ deleteForm.processing ? "Menghapus..." : "Hapus Akun" }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
