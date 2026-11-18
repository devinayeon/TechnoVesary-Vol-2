<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import DashboardLayout from '@/layouts/DashboardLayout.vue';
import StatusChip from '@/components/Dashboard/StatusChip.vue';
import {
    ArrowLeft,
    Save,
    Info,
    Shield,
    Truck,
    Users,
    Lock,
} from 'lucide-vue-next';

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
    password: '',
    password_confirmation: '',
    role: props.user.role,
    phone_number: props.user.phone_number || '',
    address: props.user.address || '',
    latitude: props.user.latitude,
    longitude: props.user.longitude,
    // Driver attribute
    is_available: props.user.driver?.is_available ?? true,
    // Customer attributes
    points: props.user.customer?.points ?? 0,
    is_member: props.user.customer?.is_member ?? false,
    membership_expires_at: props.user.customer?.membership_expires_at || '',
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
                Ubah data pengguna
            </h1>
            <p class="mt-1 text-[14px] font-normal text-charcoal">
                Perbarui identitas, kontak, dan pengaturan akun untuk
                <span class="font-bold text-night-ink">{{ user.name }}</span
                >.
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
                            <div
                                class="mb-1.5 flex items-center justify-between"
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
                                    class="inline-flex items-center gap-1.5 rounded-lg border border-amber-200 bg-amber-50 px-2.5 py-1 text-[11px] font-bold text-amber-700"
                                >
                                    <Lock class="size-3 shrink-0" />
                                    <span
                                        >Tidak dapat mengubah role sendiri</span
                                    >
                                </div>
                            </div>
                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                                <label
                                    v-for="r in roles"
                                    :key="r.value"
                                    :class="[
                                        'flex items-center gap-3 rounded-xl border-2 p-3.5 transition-all',
                                        isSelf
                                            ? 'cursor-not-allowed opacity-60 select-none'
                                            : 'cursor-pointer',
                                        form.role === r.value
                                            ? 'border-eco-green bg-sprout-tint/60 font-extrabold text-night-ink shadow-2xs'
                                            : isSelf
                                              ? 'border-border-gray bg-cloud-surface font-semibold text-charcoal'
                                              : 'border-border-gray bg-paper-white font-semibold text-charcoal hover:bg-cloud-surface',
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
                                class="mt-1 text-[12px] font-semibold text-red-500"
                            >
                                {{ form.errors.role }}
                            </p>
                        </div>
                    </div>

                    <!-- Section 2: Reset Kata Sandi (Opsional) -->
                    <div>
                        <div
                            class="mb-4 flex items-center justify-between border-b border-border-gray/70 pb-2"
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

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <!-- Kata Sandi Baru -->
                            <div>
                                <label
                                    class="mb-1.5 block text-[13px] font-bold text-night-ink"
                                >
                                    Kata Sandi Baru
                                </label>
                                <input
                                    v-model="form.password"
                                    type="password"
                                    placeholder="Masukkan sandi baru"
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
                                    Konfirmasi Kata Sandi Baru
                                </label>
                                <input
                                    v-model="form.password_confirmation"
                                    type="password"
                                    placeholder="Ulangi sandi baru"
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
                                    Saldo Poin
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
                                    id="is_member_edit"
                                    v-model="form.is_member"
                                    type="checkbox"
                                    class="size-5 cursor-pointer rounded border-border-gray text-eco-green focus:ring-eco-green"
                                />
                                <label
                                    for="is_member_edit"
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
                                id="is_available_edit"
                                v-model="form.is_available"
                                type="checkbox"
                                class="size-5 cursor-pointer rounded border-border-gray text-eco-green focus:ring-eco-green"
                            />
                            <label
                                for="is_available_edit"
                                class="cursor-pointer text-[14px] font-bold text-night-ink select-none"
                            >
                                Armada Siap Menerima Tugas Penjemputan
                            </label>
                        </div>
                    </div>

                    <!-- Actions Buttons (Figma Node 485:3128) -->
                    <div
                        class="flex items-center justify-end gap-3 border-t border-border-gray/70 pt-4"
                    >
                        <Link
                            :href="`/admin/users/${user.id}`"
                            class="inline-flex h-[44px] cursor-pointer items-center justify-center rounded-xl border-2 border-b-[4px] border-border-gray bg-paper-white px-5 text-[14px] font-extrabold text-night-ink transition-all hover:bg-cloud-surface active:translate-y-0.5 active:border-b-2"
                        >
                            Batal
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="inline-flex h-[44px] cursor-pointer items-center justify-center gap-2 rounded-2xl border-2 border-b-[4px] border-eco-green-dark bg-eco-green px-6 text-[14px] font-extrabold text-night-ink shadow-xs transition-all hover:brightness-105 active:translate-y-0.5 active:border-b-2 disabled:cursor-not-allowed disabled:opacity-60"
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
                class="flex w-full shrink-0 flex-col gap-4 rounded-[20px] border-2 border-b-[4px] border-border-gray bg-paper-white p-6 shadow-xs lg:w-[368px]"
            >
                <StatusChip label="Panduan Ubah" tone="Blue" />

                <h3
                    class="text-[18px] leading-snug font-extrabold text-night-ink"
                >
                    Perubahan Data
                </h3>

                <p class="text-[13px] leading-relaxed text-charcoal">
                    Perubahan data role akan menyesuaikan akses modul pengguna
                    secara otomatis. Pastikan alamat email tetap unik dan nomor
                    telepon aktif.
                </p>

                <div
                    class="flex items-start gap-2 rounded-xl border-t border-border-gray/60 bg-cloud-surface/70 p-3 pt-4"
                >
                    <Info class="mt-0.5 size-4 shrink-0 text-charcoal/70" />
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
