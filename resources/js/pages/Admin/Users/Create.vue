<script setup lang="ts">
import { Head, Link, useForm } from "@inertiajs/vue3";
import DashboardLayout from "@/layouts/DashboardLayout.vue";
import StatusChip from "@/components/Dashboard/StatusChip.vue";
import {
    ArrowLeft,
    UserPlus,
    Info,
    Shield,
    Truck,
    Users,
} from "lucide-vue-next";

defineProps<{
    roles: Array<{ value: string; label: string }>;
}>();

const form = useForm({
    name: "",
    email: "",
    password: "",
    password_confirmation: "",
    role: "customer",
    phone_number: "",
    address: "",
    latitude: null as number | null,
    longitude: null as number | null,
    // Driver attribute
    is_available: true,
    // Customer attributes
    points: 0,
    is_member: false,
    membership_expires_at: "",
});

const submit = () => {
    form.post("/admin/users", {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Tambah Pengguna Baru - Admin ReValue" />

    <DashboardLayout>
        <!-- Back Link & Page Heading (Figma Node 482:1480) -->
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
                Tambah pengguna baru
            </h1>
            <p class="text-[14px] font-normal text-charcoal mt-1">
                Lengkapi formulir di bawah ini untuk mendaftarkan pengguna baru
                ke ekosistem ReValue.
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
                            <label
                                class="block text-[13px] font-bold text-night-ink mb-1.5"
                            >
                                Peran Akun (Role)
                                <span class="text-red-500">*</span>
                            </label>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <label
                                    v-for="r in roles"
                                    :key="r.value"
                                    :class="[
                                        'flex items-center gap-3 p-3.5 rounded-xl border-2 cursor-pointer transition-all',
                                        form.role === r.value
                                            ? 'bg-sprout-tint/60 border-eco-green text-night-ink shadow-2xs font-extrabold'
                                            : 'bg-paper-white border-border-gray text-charcoal hover:bg-cloud-surface font-semibold',
                                    ]"
                                >
                                    <input
                                        type="radio"
                                        v-model="form.role"
                                        :value="r.value"
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

                    <!-- Section 2: Kata Sandi -->
                    <div>
                        <h2
                            class="text-[16px] font-extrabold text-night-ink mb-4 pb-2 border-b border-border-gray/70"
                        >
                            Keamanan Akun
                        </h2>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Kata Sandi -->
                            <div>
                                <label
                                    class="block text-[13px] font-bold text-night-ink mb-1.5"
                                >
                                    Kata Sandi
                                    <span class="text-red-500">*</span>
                                </label>
                                <input
                                    v-model="form.password"
                                    type="password"
                                    placeholder="Minimal 8 karakter"
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
                                    Konfirmasi Kata Sandi
                                    <span class="text-red-500">*</span>
                                </label>
                                <input
                                    v-model="form.password_confirmation"
                                    type="password"
                                    placeholder="Ketik ulang kata sandi"
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
                                    Poin Awal
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
                                    id="is_member"
                                    v-model="form.is_member"
                                    type="checkbox"
                                    class="size-5 rounded text-eco-green focus:ring-eco-green border-border-gray cursor-pointer"
                                />
                                <label
                                    for="is_member"
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
                                id="is_available"
                                v-model="form.is_available"
                                type="checkbox"
                                class="size-5 rounded text-eco-green focus:ring-eco-green border-border-gray cursor-pointer"
                            />
                            <label
                                for="is_available"
                                class="text-[14px] font-bold text-night-ink cursor-pointer select-none"
                            >
                                Armada Siap Menerima Tugas Penjemputan
                            </label>
                        </div>
                    </div>

                    <!-- Actions Buttons (Figma Node 482:1480) -->
                    <div
                        class="flex items-center justify-end gap-3 pt-4 border-t border-border-gray/70"
                    >
                        <Link
                            href="/admin/users"
                            class="px-5 h-[44px] rounded-xl border-2 border-b-[4px] border-border-gray bg-paper-white font-extrabold text-[14px] text-night-ink hover:bg-cloud-surface active:translate-y-0.5 active:border-b-2 transition-all cursor-pointer inline-flex items-center justify-center"
                        >
                            Batal
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-6 h-[44px] bg-eco-green text-night-ink font-extrabold text-[14px] rounded-2xl border-2 border-b-[4px] border-eco-green-dark hover:brightness-105 active:translate-y-0.5 active:border-b-2 transition-all cursor-pointer disabled:opacity-60 disabled:cursor-not-allowed inline-flex items-center justify-center gap-2 shadow-xs"
                        >
                            <UserPlus class="size-4" />
                            <span v-if="form.processing">Menyimpan...</span>
                            <span v-else>Tambah Pengguna</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Context Panel (Right Column - Figma Node 482:1628) -->
            <div
                class="w-full lg:w-[368px] bg-paper-white border-2 border-b-[4px] border-border-gray rounded-[20px] p-6 shadow-xs shrink-0 flex flex-col gap-4"
            >
                <StatusChip label="Panduan Singkat" tone="Blue" />

                <h3
                    class="text-[18px] font-extrabold text-night-ink leading-snug"
                >
                    Akses sesuai role
                </h3>

                <div
                    class="space-y-3.5 text-[13px] leading-relaxed text-charcoal"
                >
                    <div class="flex items-start gap-2.5">
                        <div
                            class="size-2 rounded-full bg-eco-green shrink-0 mt-1.5"
                        ></div>
                        <p>
                            <strong class="text-night-ink">Customer:</strong>
                            Mendapat akses ke aplikasi nasabah untuk
                            menjadwalkan jemput sampah, mengumpulkan poin, dan
                            melihat saldo.
                        </p>
                    </div>

                    <div class="flex items-start gap-2.5">
                        <div
                            class="size-2 rounded-full bg-sky-blue shrink-0 mt-1.5"
                        ></div>
                        <p>
                            <strong class="text-night-ink">Driver:</strong>
                            Bertugas menerima antrean jemput, melihat rute
                            logistik, dan mencatat timbangan setoran nasabah.
                        </p>
                    </div>

                    <div class="flex items-start gap-2.5">
                        <div
                            class="size-2 rounded-full bg-sunlight-gold shrink-0 mt-1.5"
                        ></div>
                        <p>
                            <strong class="text-night-ink">Admin:</strong>
                            Memiliki wewenang penuh atas tata kelola sistem,
                            manajemen pengguna, kategori sampah, dan riwayat
                            operasional.
                        </p>
                    </div>
                </div>

                <div
                    class="mt-2 pt-4 border-t border-border-gray/60 flex items-start gap-2 bg-cloud-surface/70 rounded-xl p-3"
                >
                    <Info class="size-4 text-charcoal/70 shrink-0 mt-0.5" />
                    <p class="text-[12px] font-medium text-charcoal">
                        Akun yang dibuat oleh admin langsung aktif dan
                        terverifikasi secara otomatis.
                    </p>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
