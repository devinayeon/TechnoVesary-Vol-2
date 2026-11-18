<script setup lang="ts">
import { computed } from "vue";
import { Head, Link, useForm, usePage } from "@inertiajs/vue3";
import DashboardLayout from "@/layouts/DashboardLayout.vue";
import StatusChip from "@/components/Dashboard/StatusChip.vue";
import {
    ArrowLeft,
    Save,
    Info,
    Shield,
    Truck,
    Users,
    Lock,
} from "lucide-vue-next";

const props = defineProps<{
    user: {
        id: number;
        name: string;
        email: string;
        role: string;
        phone_number: string | null;
        address: string | null;
        latitude: number | null;
        longitude: number | null;
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
    roles: Array<{ value: string; label: string }>;
}>();

const page = usePage();
const isSelf = computed(() => page.props.auth?.user?.id === props.user.id);

const form = useForm({
    name: props.user.name,
    email: props.user.email,
    password: "",
    password_confirmation: "",
    role: props.user.role,
    phone_number: props.user.phone_number || "",
    address: props.user.address || "",
    latitude: props.user.latitude,
    longitude: props.user.longitude,
    // Driver attribute
    is_available: props.user.driver?.is_available ?? true,
    // Customer attributes
    points: props.user.customer?.points ?? 0,
    is_member: props.user.customer?.is_member ?? false,
    membership_expires_at: props.user.customer?.membership_expires_at || "",
});

const submit = () => {
    form.put(`/admin/users/${props.user.id}`, {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head :title="`Ubah ${user.name} - Admin ReValue`" />

    <DashboardLayout>
        <!-- Back Link & Page Heading (Figma Node 485:3128 / 491:4861) -->
        <div class="mb-6">
            <Link
                href="/admin/users"
                class="inline-flex items-center gap-2 text-[13px] font-bold text-charcoal hover:text-night-ink transition-colors mb-3 group"
            >
                <ArrowLeft
                    class="size-4 transition-transform group-hover:-translate-x-1"
                />
                <span>Kembali ke Daftar Pengguna</span>
            </Link>

            <h1
                class="text-[26px] sm:text-[30px] font-extrabold text-night-ink tracking-tight leading-tight"
            >
                Ubah data pengguna
            </h1>
            <p class="text-[14px] font-normal text-charcoal mt-1">
                Perbarui identitas, kontak, dan pengaturan akun untuk
                <span class="font-bold text-night-ink">{{ user.name }}</span
                >.
            </p>
        </div>

        <!-- 2-Column Layout (Form Card + Context Panel) -->
        <div class="flex flex-col lg:flex-row items-start gap-6">
            <!-- Form Card (Left Column) -->
            <div
                class="flex-1 w-full bg-paper-white border-2 border-b-[4px] border-border-gray rounded-[20px] p-6 lg:p-8 shadow-xs"
            >
                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Section 1: Data Akun Utama -->
                    <div>
                        <h2
                            class="text-[16px] font-extrabold text-night-ink mb-4 pb-2 border-b border-border-gray/70"
                        >
                            Informasi Akun
                        </h2>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Nama Lengkap -->
                            <div>
                                <label
                                    class="block text-[13px] font-bold text-night-ink mb-1.5"
                                >
                                    Nama Lengkap
                                    <span class="text-red-500">*</span>
                                </label>
                                <input
                                    v-model="form.name"
                                    type="text"
                                    placeholder="Contoh: Siti Aminah"
                                    class="w-full h-[48px] px-3.5 bg-paper-white border-2 border-border-gray rounded-xl text-[14px] font-medium text-night-ink focus:outline-none focus:border-eco-green focus:ring-4 focus:ring-eco-green/15 transition-all"
                                    :class="{
                                        'border-red-500': form.errors.name,
                                    }"
                                />
                                <p
                                    v-if="form.errors.name"
                                    class="text-[12px] font-semibold text-red-500 mt-1"
                                >
                                    {{ form.errors.name }}
                                </p>
                            </div>

                            <!-- Alamat Email -->
                            <div>
                                <label
                                    class="block text-[13px] font-bold text-night-ink mb-1.5"
                                >
                                    Alamat Email
                                    <span class="text-red-500">*</span>
                                </label>
                                <input
                                    v-model="form.email"
                                    type="email"
                                    placeholder="siti@contoh.test"
                                    class="w-full h-[48px] px-3.5 bg-paper-white border-2 border-border-gray rounded-xl text-[14px] font-medium text-night-ink focus:outline-none focus:border-eco-green focus:ring-4 focus:ring-eco-green/15 transition-all"
                                    :class="{
                                        'border-red-500': form.errors.email,
                                    }"
                                />
                                <p
                                    v-if="form.errors.email"
                                    class="text-[12px] font-semibold text-red-500 mt-1"
                                >
                                    {{ form.errors.email }}
                                </p>
                            </div>
                        </div>

                        <!-- Peran / Role Selection -->
                        <div class="mt-4">
                            <div
                                class="flex items-center justify-between mb-1.5"
                            >
                                <label
                                    class="block text-[13px] font-bold text-night-ink"
                                >
                                    Peran Akun (Role)
                                    <span class="text-red-500">*</span>
                                </label>
                                <!-- Self-lock notice -->
                                <div
                                    v-if="isSelf"
                                    class="inline-flex items-center gap-1.5 text-[11px] font-bold text-amber-700 bg-amber-50 border border-amber-200 px-2.5 py-1 rounded-lg"
                                >
                                    <Lock class="size-3 shrink-0" />
                                    <span
                                        >Tidak dapat mengubah role sendiri</span
                                    >
                                </div>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <label
                                    v-for="r in roles"
                                    :key="r.value"
                                    :class="[
                                        'flex items-center gap-3 p-3.5 rounded-xl border-2 transition-all',
                                        isSelf
                                            ? 'cursor-not-allowed opacity-60 select-none'
                                            : 'cursor-pointer',
                                        form.role === r.value
                                            ? 'bg-sprout-tint/60 border-eco-green text-night-ink shadow-2xs font-extrabold'
                                            : isSelf
                                              ? 'bg-cloud-surface border-border-gray text-charcoal font-semibold'
                                              : 'bg-paper-white border-border-gray text-charcoal hover:bg-cloud-surface font-semibold',
                                    ]"
                                >
                                    <input
                                        type="radio"
                                        v-model="form.role"
                                        :value="r.value"
                                        :disabled="isSelf"
                                        class="sr-only"
                                    />
                                    <component
                                        :is="
                                            r.value === 'admin'
                                                ? Shield
                                                : r.value === 'driver'
                                                  ? Truck
                                                  : Users
                                        "
                                        class="size-5 shrink-0"
                                        :class="
                                            form.role === r.value
                                                ? 'text-eco-green-dark'
                                                : 'text-charcoal/60'
                                        "
                                    />
                                    <span class="text-[13px]">{{
                                        r.label
                                    }}</span>
                                </label>
                            </div>
                            <p
                                v-if="form.errors.role"
                                class="text-[12px] font-semibold text-red-500 mt-1"
                            >
                                {{ form.errors.role }}
                            </p>
                        </div>
                    </div>

                    <!-- Section 2: Reset Kata Sandi (Opsional) -->
                    <div>
                        <div
                            class="flex items-center justify-between pb-2 border-b border-border-gray/70 mb-4"
                        >
                            <h2
                                class="text-[16px] font-extrabold text-night-ink"
                            >
                                Reset Kata Sandi
                            </h2>
                            <span class="text-[12px] font-medium text-charcoal">
                                (Kosongkan jika tidak ingin mengubah sandi)
                            </span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Kata Sandi Baru -->
                            <div>
                                <label
                                    class="block text-[13px] font-bold text-night-ink mb-1.5"
                                >
                                    Kata Sandi Baru
                                </label>
                                <input
                                    v-model="form.password"
                                    type="password"
                                    placeholder="Masukkan sandi baru"
                                    class="w-full h-[48px] px-3.5 bg-paper-white border-2 border-border-gray rounded-xl text-[14px] font-medium text-night-ink focus:outline-none focus:border-eco-green focus:ring-4 focus:ring-eco-green/15 transition-all"
                                    :class="{
                                        'border-red-500': form.errors.password,
                                    }"
                                />
                                <p
                                    v-if="form.errors.password"
                                    class="text-[12px] font-semibold text-red-500 mt-1"
                                >
                                    {{ form.errors.password }}
                                </p>
                            </div>

                            <!-- Konfirmasi Kata Sandi -->
                            <div>
                                <label
                                    class="block text-[13px] font-bold text-night-ink mb-1.5"
                                >
                                    Konfirmasi Kata Sandi Baru
                                </label>
                                <input
                                    v-model="form.password_confirmation"
                                    type="password"
                                    placeholder="Ulangi sandi baru"
                                    class="w-full h-[48px] px-3.5 bg-paper-white border-2 border-border-gray rounded-xl text-[14px] font-medium text-night-ink focus:outline-none focus:border-eco-green focus:ring-4 focus:ring-eco-green/15 transition-all"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Section 3: Kontak & Alamat -->
                    <div>
                        <h2
                            class="text-[16px] font-extrabold text-night-ink mb-4 pb-2 border-b border-border-gray/70"
                        >
                            Kontak & Lokasi
                        </h2>

                        <div class="space-y-4">
                            <!-- Nomor Telepon -->
                            <div>
                                <label
                                    class="block text-[13px] font-bold text-night-ink mb-1.5"
                                >
                                    Nomor Telepon
                                </label>
                                <input
                                    v-model="form.phone_number"
                                    type="text"
                                    placeholder="Contoh: 081234567890"
                                    class="w-full h-[48px] px-3.5 bg-paper-white border-2 border-border-gray rounded-xl text-[14px] font-medium text-night-ink focus:outline-none focus:border-eco-green focus:ring-4 focus:ring-eco-green/15 transition-all"
                                    :class="{
                                        'border-red-500':
                                            form.errors.phone_number,
                                    }"
                                />
                                <p
                                    v-if="form.errors.phone_number"
                                    class="text-[12px] font-semibold text-red-500 mt-1"
                                >
                                    {{ form.errors.phone_number }}
                                </p>
                            </div>

                            <!-- Alamat Lengkap -->
                            <div>
                                <label
                                    class="block text-[13px] font-bold text-night-ink mb-1.5"
                                >
                                    Alamat Lengkap
                                </label>
                                <textarea
                                    v-model="form.address"
                                    rows="3"
                                    placeholder="Nama jalan, nomor rumah, RT/RW, kelurahan, kecamatan..."
                                    class="w-full p-3.5 bg-paper-white border-2 border-border-gray rounded-xl text-[14px] font-medium text-night-ink focus:outline-none focus:border-eco-green focus:ring-4 focus:ring-eco-green/15 transition-all resize-none"
                                    :class="{
                                        'border-red-500': form.errors.address,
                                    }"
                                ></textarea>
                                <p
                                    v-if="form.errors.address"
                                    class="text-[12px] font-semibold text-red-500 mt-1"
                                >
                                    {{ form.errors.address }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Section 4: Atribut Spesifik Role -->
                    <div v-if="form.role === 'customer'">
                        <h2
                            class="text-[16px] font-extrabold text-night-ink mb-4 pb-2 border-b border-border-gray/70"
                        >
                            Pengaturan Nasabah
                        </h2>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label
                                    class="block text-[13px] font-bold text-night-ink mb-1.5"
                                >
                                    Saldo Poin
                                </label>
                                <input
                                    v-model="form.points"
                                    type="number"
                                    min="0"
                                    class="w-full h-[48px] px-3.5 bg-paper-white border-2 border-border-gray rounded-xl text-[14px] font-medium text-night-ink focus:outline-none focus:border-eco-green focus:ring-4 focus:ring-eco-green/15 transition-all"
                                />
                            </div>
                            <div class="flex items-center gap-3 pt-7">
                                <input
                                    id="is_member_edit"
                                    v-model="form.is_member"
                                    type="checkbox"
                                    class="size-5 rounded text-eco-green focus:ring-eco-green border-border-gray cursor-pointer"
                                />
                                <label
                                    for="is_member_edit"
                                    class="text-[14px] font-bold text-night-ink cursor-pointer select-none"
                                >
                                    Status Langganan ReValue+ Aktif
                                </label>
                            </div>
                        </div>
                    </div>

                    <div v-else-if="form.role === 'driver'">
                        <h2
                            class="text-[16px] font-extrabold text-night-ink mb-4 pb-2 border-b border-border-gray/70"
                        >
                            Pengaturan Driver
                        </h2>
                        <div class="flex items-center gap-3">
                            <input
                                id="is_available_edit"
                                v-model="form.is_available"
                                type="checkbox"
                                class="size-5 rounded text-eco-green focus:ring-eco-green border-border-gray cursor-pointer"
                            />
                            <label
                                for="is_available_edit"
                                class="text-[14px] font-bold text-night-ink cursor-pointer select-none"
                            >
                                Armada Siap Menerima Tugas Penjemputan
                            </label>
                        </div>
                    </div>

                    <!-- Actions Buttons (Figma Node 485:3128) -->
                    <div
                        class="flex items-center justify-end gap-3 pt-4 border-t border-border-gray/70"
                    >
                        <Link
                            :href="`/admin/users/${user.id}`"
                            class="px-5 h-[44px] rounded-xl border-2 border-b-[4px] border-border-gray bg-paper-white font-extrabold text-[14px] text-night-ink hover:bg-cloud-surface active:translate-y-0.5 active:border-b-2 transition-all cursor-pointer inline-flex items-center justify-center"
                        >
                            Batal
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-6 h-[44px] bg-eco-green text-night-ink font-extrabold text-[14px] rounded-2xl border-2 border-b-[4px] border-eco-green-dark hover:brightness-105 active:translate-y-0.5 active:border-b-2 transition-all cursor-pointer disabled:opacity-60 disabled:cursor-not-allowed inline-flex items-center justify-center gap-2 shadow-xs"
                        >
                            <Save class="size-4" />
                            <span v-if="form.processing">Menyimpan...</span>
                            <span v-else>Simpan Perubahan</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Context Panel (Right Column) -->
            <div
                class="w-full lg:w-[368px] bg-paper-white border-2 border-b-[4px] border-border-gray rounded-[20px] p-6 shadow-xs shrink-0 flex flex-col gap-4"
            >
                <StatusChip label="Panduan Ubah" tone="Blue" />

                <h3
                    class="text-[18px] font-extrabold text-night-ink leading-snug"
                >
                    Perubahan Data
                </h3>

                <p class="text-[13px] text-charcoal leading-relaxed">
                    Perubahan data role akan menyesuaikan akses modul pengguna
                    secara otomatis. Pastikan alamat email tetap unik dan nomor
                    telepon aktif.
                </p>

                <div
                    class="pt-4 border-t border-border-gray/60 flex items-start gap-2 bg-cloud-surface/70 rounded-xl p-3"
                >
                    <Info class="size-4 text-charcoal/70 shrink-0 mt-0.5" />
                    <p class="text-[12px] font-medium text-charcoal">
                        Jika pengguna lupa kata sandi, admin dapat mengisi kolom
                        <strong>Kata Sandi Baru</strong> untuk mereset kata
                        sandi akun secara langsung.
                    </p>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
