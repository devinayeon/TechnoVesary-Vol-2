<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import DashboardLayout from '@/layouts/DashboardLayout.vue';
import StatusChip from '@/components/Dashboard/StatusChip.vue';
import {
    ArrowLeft,
    UserPlus,
    Info,
    Shield,
    Truck,
    Users,
} from 'lucide-vue-next';

defineProps<{
    roles: Array<{ value: string; label: string }>;
}>();

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: 'customer',
    phone_number: '',
    address: '',
    latitude: null as number | null,
    longitude: null as number | null,
    // Driver attribute
    is_available: true,
    // Customer attributes
    points: 0,
    is_member: false,
    membership_expires_at: '',
});

const submit = () => {
    form.post('/admin/users', {
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
                class="group mb-3 inline-flex items-center gap-2 text-[13px] font-bold text-charcoal transition-colors hover:text-night-ink"
            >
                <ArrowLeft
                    class="size-4 transition-transform group-hover:-translate-x-1"
                />
                <span>Kembali ke Daftar Pengguna</span>
            </Link>

            <h1
                class="text-[26px] leading-tight font-extrabold tracking-tight text-night-ink sm:text-[30px]"
            >
                Tambah pengguna baru
            </h1>
            <p class="mt-1 text-[14px] font-normal text-charcoal">
                Lengkapi formulir di bawah ini untuk mendaftarkan pengguna baru
                ke ekosistem ReValue.
            </p>
        </div>

        <!-- 2-Column Layout (Form Card + Context Panel) -->
        <div class="flex flex-col items-start gap-6 lg:flex-row">
            <!-- Form Card (Left Column) -->
            <div
                class="w-full flex-1 rounded-[20px] border-2 border-b-[4px] border-border-gray bg-paper-white p-6 shadow-xs lg:p-8"
            >
                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Section 1: Data Akun Utama -->
                    <div>
                        <h2
                            class="mb-4 border-b border-border-gray/70 pb-2 text-[16px] font-extrabold text-night-ink"
                        >
                            Informasi Akun
                        </h2>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <!-- Nama Lengkap -->
                            <div>
                                <label
                                    class="mb-1.5 block text-[13px] font-bold text-night-ink"
                                >
                                    Nama Lengkap
                                    <span class="text-red-500">*</span>
                                </label>
                                <input
                                    v-model="form.name"
                                    type="text"
                                    placeholder="Contoh: Siti Aminah"
                                    class="h-[48px] w-full rounded-xl border-2 border-border-gray bg-paper-white px-3.5 text-[14px] font-medium text-night-ink transition-all focus:border-eco-green focus:ring-4 focus:ring-eco-green/15 focus:outline-none"
                                    :class="{
                                        'border-red-500': form.errors.name,
                                    }"
                                />
                                <p
                                    v-if="form.errors.name"
                                    class="mt-1 text-[12px] font-semibold text-red-500"
                                >
                                    {{ form.errors.name }}
                                </p>
                            </div>

                            <!-- Alamat Email -->
                            <div>
                                <label
                                    class="mb-1.5 block text-[13px] font-bold text-night-ink"
                                >
                                    Alamat Email
                                    <span class="text-red-500">*</span>
                                </label>
                                <input
                                    v-model="form.email"
                                    type="email"
                                    placeholder="siti@contoh.test"
                                    class="h-[48px] w-full rounded-xl border-2 border-border-gray bg-paper-white px-3.5 text-[14px] font-medium text-night-ink transition-all focus:border-eco-green focus:ring-4 focus:ring-eco-green/15 focus:outline-none"
                                    :class="{
                                        'border-red-500': form.errors.email,
                                    }"
                                />
                                <p
                                    v-if="form.errors.email"
                                    class="mt-1 text-[12px] font-semibold text-red-500"
                                >
                                    {{ form.errors.email }}
                                </p>
                            </div>
                        </div>

                        <!-- Peran / Role Selection -->
                        <div class="mt-4">
                            <label
                                class="mb-1.5 block text-[13px] font-bold text-night-ink"
                            >
                                Peran Akun (Role)
                                <span class="text-red-500">*</span>
                            </label>
                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                                <label
                                    v-for="r in roles"
                                    :key="r.value"
                                    :class="[
                                        'flex cursor-pointer items-center gap-3 rounded-xl border-2 p-3.5 transition-all',
                                        form.role === r.value
                                            ? 'border-eco-green bg-sprout-tint/60 font-extrabold text-night-ink shadow-2xs'
                                            : 'border-border-gray bg-paper-white font-semibold text-charcoal hover:bg-cloud-surface',
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
                                class="mt-1 text-[12px] font-semibold text-red-500"
                            >
                                {{ form.errors.role }}
                            </p>
                        </div>
                    </div>

                    <!-- Section 2: Kata Sandi -->
                    <div>
                        <h2
                            class="mb-4 border-b border-border-gray/70 pb-2 text-[16px] font-extrabold text-night-ink"
                        >
                            Keamanan Akun
                        </h2>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <!-- Kata Sandi -->
                            <div>
                                <label
                                    class="mb-1.5 block text-[13px] font-bold text-night-ink"
                                >
                                    Kata Sandi
                                    <span class="text-red-500">*</span>
                                </label>
                                <input
                                    v-model="form.password"
                                    type="password"
                                    placeholder="Minimal 8 karakter"
                                    class="h-[48px] w-full rounded-xl border-2 border-border-gray bg-paper-white px-3.5 text-[14px] font-medium text-night-ink transition-all focus:border-eco-green focus:ring-4 focus:ring-eco-green/15 focus:outline-none"
                                    :class="{
                                        'border-red-500': form.errors.password,
                                    }"
                                />
                                <p
                                    v-if="form.errors.password"
                                    class="mt-1 text-[12px] font-semibold text-red-500"
                                >
                                    {{ form.errors.password }}
                                </p>
                            </div>

                            <!-- Konfirmasi Kata Sandi -->
                            <div>
                                <label
                                    class="mb-1.5 block text-[13px] font-bold text-night-ink"
                                >
                                    Konfirmasi Kata Sandi
                                    <span class="text-red-500">*</span>
                                </label>
                                <input
                                    v-model="form.password_confirmation"
                                    type="password"
                                    placeholder="Ketik ulang kata sandi"
                                    class="h-[48px] w-full rounded-xl border-2 border-border-gray bg-paper-white px-3.5 text-[14px] font-medium text-night-ink transition-all focus:border-eco-green focus:ring-4 focus:ring-eco-green/15 focus:outline-none"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Section 3: Kontak & Alamat -->
                    <div>
                        <h2
                            class="mb-4 border-b border-border-gray/70 pb-2 text-[16px] font-extrabold text-night-ink"
                        >
                            Kontak & Lokasi
                        </h2>

                        <div class="space-y-4">
                            <!-- Nomor Telepon -->
                            <div>
                                <label
                                    class="mb-1.5 block text-[13px] font-bold text-night-ink"
                                >
                                    Nomor Telepon
                                </label>
                                <input
                                    v-model="form.phone_number"
                                    type="text"
                                    placeholder="Contoh: 081234567890"
                                    class="h-[48px] w-full rounded-xl border-2 border-border-gray bg-paper-white px-3.5 text-[14px] font-medium text-night-ink transition-all focus:border-eco-green focus:ring-4 focus:ring-eco-green/15 focus:outline-none"
                                    :class="{
                                        'border-red-500':
                                            form.errors.phone_number,
                                    }"
                                />
                                <p
                                    v-if="form.errors.phone_number"
                                    class="mt-1 text-[12px] font-semibold text-red-500"
                                >
                                    {{ form.errors.phone_number }}
                                </p>
                            </div>

                            <!-- Alamat Lengkap -->
                            <div>
                                <label
                                    class="mb-1.5 block text-[13px] font-bold text-night-ink"
                                >
                                    Alamat Lengkap
                                </label>
                                <textarea
                                    v-model="form.address"
                                    rows="3"
                                    placeholder="Nama jalan, nomor rumah, RT/RW, kelurahan, kecamatan..."
                                    class="w-full resize-none rounded-xl border-2 border-border-gray bg-paper-white p-3.5 text-[14px] font-medium text-night-ink transition-all focus:border-eco-green focus:ring-4 focus:ring-eco-green/15 focus:outline-none"
                                    :class="{
                                        'border-red-500': form.errors.address,
                                    }"
                                ></textarea>
                                <p
                                    v-if="form.errors.address"
                                    class="mt-1 text-[12px] font-semibold text-red-500"
                                >
                                    {{ form.errors.address }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Section 4: Atribut Spesifik Role -->
                    <div v-if="form.role === 'customer'">
                        <h2
                            class="mb-4 border-b border-border-gray/70 pb-2 text-[16px] font-extrabold text-night-ink"
                        >
                            Pengaturan Customer
                        </h2>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label
                                    class="mb-1.5 block text-[13px] font-bold text-night-ink"
                                >
                                    Poin Awal
                                </label>
                                <input
                                    v-model="form.points"
                                    type="number"
                                    min="0"
                                    class="h-[48px] w-full rounded-xl border-2 border-border-gray bg-paper-white px-3.5 text-[14px] font-medium text-night-ink transition-all focus:border-eco-green focus:ring-4 focus:ring-eco-green/15 focus:outline-none"
                                />
                            </div>
                            <div class="flex items-center gap-3 pt-7">
                                <input
                                    id="is_member"
                                    v-model="form.is_member"
                                    type="checkbox"
                                    class="size-5 cursor-pointer rounded border-border-gray text-eco-green focus:ring-eco-green"
                                />
                                <label
                                    for="is_member"
                                    class="cursor-pointer text-[14px] font-bold text-night-ink select-none"
                                >
                                    Status Langganan ReValue+ Aktif
                                </label>
                            </div>
                        </div>
                    </div>

                    <div v-else-if="form.role === 'driver'">
                        <h2
                            class="mb-4 border-b border-border-gray/70 pb-2 text-[16px] font-extrabold text-night-ink"
                        >
                            Pengaturan Driver
                        </h2>
                        <div class="flex items-center gap-3">
                            <input
                                id="is_available"
                                v-model="form.is_available"
                                type="checkbox"
                                class="size-5 cursor-pointer rounded border-border-gray text-eco-green focus:ring-eco-green"
                            />
                            <label
                                for="is_available"
                                class="cursor-pointer text-[14px] font-bold text-night-ink select-none"
                            >
                                Armada Siap Menerima Tugas Penjemputan
                            </label>
                        </div>
                    </div>

                    <!-- Actions Buttons (Figma Node 482:1480) -->
                    <div
                        class="flex items-center justify-end gap-3 border-t border-border-gray/70 pt-4"
                    >
                        <Link
                            href="/admin/users"
                            class="inline-flex h-[44px] cursor-pointer items-center justify-center rounded-xl border-2 border-b-[4px] border-border-gray bg-paper-white px-5 text-[14px] font-extrabold text-night-ink transition-all hover:bg-cloud-surface active:translate-y-0.5 active:border-b-2"
                        >
                            Batal
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="inline-flex h-[44px] cursor-pointer items-center justify-center gap-2 rounded-2xl border-2 border-b-[4px] border-eco-green-dark bg-eco-green px-6 text-[14px] font-extrabold text-night-ink shadow-xs transition-all hover:brightness-105 active:translate-y-0.5 active:border-b-2 disabled:cursor-not-allowed disabled:opacity-60"
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
                class="flex w-full shrink-0 flex-col gap-4 rounded-[20px] border-2 border-b-[4px] border-border-gray bg-paper-white p-6 shadow-xs lg:w-[368px]"
            >
                <StatusChip label="Panduan Singkat" tone="Blue" />

                <h3
                    class="text-[18px] leading-snug font-extrabold text-night-ink"
                >
                    Akses sesuai role
                </h3>

                <div
                    class="space-y-3.5 text-[13px] leading-relaxed text-charcoal"
                >
                    <div class="flex items-start gap-2.5">
                        <div
                            class="mt-1.5 size-2 shrink-0 rounded-full bg-eco-green"
                        ></div>
                        <p>
                            <strong class="text-night-ink">Customer:</strong>
                            Mendapat akses ke aplikasi customer untuk
                            menjadwalkan jemput sampah, mengumpulkan poin, dan
                            melihat saldo.
                        </p>
                    </div>

                    <div class="flex items-start gap-2.5">
                        <div
                            class="mt-1.5 size-2 shrink-0 rounded-full bg-sky-blue"
                        ></div>
                        <p>
                            <strong class="text-night-ink">Driver:</strong>
                            Bertugas menerima antrean jemput, melihat rute
                            logistik, dan mencatat timbangan setoran customer.
                        </p>
                    </div>

                    <div class="flex items-start gap-2.5">
                        <div
                            class="mt-1.5 size-2 shrink-0 rounded-full bg-sunlight-gold"
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
                    class="mt-2 flex items-start gap-2 rounded-xl border-t border-border-gray/60 bg-cloud-surface/70 p-3 pt-4"
                >
                    <Info class="mt-0.5 size-4 shrink-0 text-charcoal/70" />
                    <p class="text-[12px] font-medium text-charcoal">
                        Akun yang dibuat oleh admin langsung aktif dan
                        terverifikasi secara otomatis.
                    </p>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
