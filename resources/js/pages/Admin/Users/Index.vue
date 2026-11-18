<script setup lang="ts">
import { ref, watch, computed } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import DashboardLayout from '@/layouts/DashboardLayout.vue';
import UserAvatar from '@/components/Dashboard/UserAvatar.vue';
import StatusChip from '@/components/Dashboard/StatusChip.vue';
import UserDeleteModal from '@/components/Dashboard/UserDeleteModal.vue';
import AppDropdown from '@/components/Dashboard/AppDropdown.vue';
import PageHeader from '@/components/Dashboard/PageHeader.vue';
import {
    Search,
    UserPlus,
    RotateCcw,
    CheckCircle2,
    XCircle,
    ChevronLeft,
    ChevronRight,
    Users,
    Shield,
    Truck,
    X,
    Eye,
    Edit3,
    Trash2,
} from 'lucide-vue-next';

interface UserItem {
    id: number;
    name: string;
    email: string;
    role: string;
    phone_number: string | null;
    photo: string | null;
    deleted_at: string | null;
    email_verified_at: string | null;
}

interface PaginationLinks {
    url: string | null;
    label: string;
    active: boolean;
}

interface PaginatedUsers {
    data: UserItem[];
    current_page: number;
    last_page: number;
    from: number | null;
    to: number | null;
    total: number;
    links: PaginationLinks[];
    prev_page_url: string | null;
    next_page_url: string | null;
}

const props = defineProps<{
    users: PaginatedUsers;
    filters: {
        search?: string;
        role?: string;
        status?: string;
    };
    roles: Array<{ value: string; label: string }>;
}>();

const page = usePage();

// Current logged-in admin's user ID
const currentUserId = computed(() => page.props.auth?.user?.id);

// Flash alerts visibility state
const showSuccessFlash = ref(true);
const showErrorFlash = ref(true);

const flashSuccess = computed(() => page.props.flash?.success);
const flashError = computed(
    () => page.props.flash?.error || (page.props.errors as any)?.error,
);

watch(
    () => page.props.flash,
    () => {
        showSuccessFlash.value = true;
        showErrorFlash.value = true;
    },
    { deep: true },
);

// Search & Filter local state
const search = ref(props.filters.search || '');
const selectedRole = ref(props.filters.role || '');
const selectedStatus = ref(props.filters.status || 'active');

let searchTimeout: any = null;
const applyFilters = () => {
    router.get(
        '/admin/users',
        {
            search: search.value || undefined,
            role: selectedRole.value || undefined,
            status: selectedStatus.value || undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
};

watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        applyFilters();
    }, 350);
});

const onRoleChange = () => {
    applyFilters();
};

const onStatusChange = () => {
    applyFilters();
};

const roleDropdownOptions = computed(() => [
    { value: '', label: 'Semua Role', icon: Users },
    ...props.roles.map((r) => ({
        value: r.value,
        label: r.label,
        icon:
            r.value === 'admin' ? Shield : r.value === 'driver' ? Truck : Users,
    })),
]);

const statusDropdownOptions = [
    { value: 'active', label: 'Aktif' },
    { value: 'trashed', label: 'Dinonaktifkan' },
    { value: 'all', label: 'Semua Status' },
];

const resetFilters = () => {
    search.value = '';
    selectedRole.value = '';
    selectedStatus.value = 'active';
    router.get(
        '/admin/users',
        {},
        {
            preserveState: true,
            preserveScroll: true,
        },
    );
};

// Delete Modal State
const selectedUserForDelete = ref<{
    id: number;
    name: string;
    email: string;
} | null>(null);
const isDeleteModalOpen = ref(false);

const openDeleteModal = (u: UserItem) => {
    selectedUserForDelete.value = {
        id: u.id,
        name: u.name,
        email: u.email,
    };
    isDeleteModalOpen.value = true;
};

const closeDeleteModal = () => {
    isDeleteModalOpen.value = false;
    selectedUserForDelete.value = null;
};

// Restore user
const restoreUser = (id: number) => {
    if (confirm('Aktifkan kembali akun pengguna ini?')) {
        router.post(
            `/admin/users/${id}/restore`,
            {},
            {
                preserveScroll: true,
            },
        );
    }
};

const getRoleTone = (role: string): 'Green' | 'Blue' | 'Amber' | 'Neutral' => {
    switch (role.toLowerCase()) {
        case 'admin':
            return 'Amber';
        case 'driver':
            return 'Blue';
        case 'customer':
        default:
            return 'Green';
    }
};

const getRoleLabel = (role: string): string => {
    switch (role.toLowerCase()) {
        case 'admin':
            return 'Admin';
        case 'driver':
            return 'Driver';
        case 'customer':
        default:
            return 'Customer';
    }
};
</script>

<template>
    <Head title="Manajemen Pengguna - Admin ReValue" />

    <DashboardLayout>
        <!-- Success Flash Alert -->
        <div
            v-if="showSuccessFlash && flashSuccess"
            class="animate-in fade-in mb-6 flex items-center justify-between gap-3 rounded-2xl border-2 border-eco-green/40 bg-sprout-tint p-4 text-[14px] font-bold text-night-ink shadow-2xs duration-200"
        >
            <div class="flex min-w-0 items-center gap-3">
                <CheckCircle2 class="size-5 shrink-0 text-eco-green-dark" />
                <span class="truncate">{{ flashSuccess }}</span>
            </div>
            <button
                type="button"
                @click="showSuccessFlash = false"
                class="shrink-0 cursor-pointer rounded-lg p-1 text-charcoal transition-colors hover:bg-eco-green/20 hover:text-night-ink"
                aria-label="Tutup notifikasi"
            >
                <X class="size-4" />
            </button>
        </div>

        <!-- Error Flash Alert -->
        <div
            v-if="showErrorFlash && flashError"
            class="animate-in fade-in mb-6 flex items-center justify-between gap-3 rounded-2xl border-2 border-red-200 bg-red-50 p-4 text-[14px] font-bold text-red-700 shadow-2xs duration-200"
        >
            <div class="flex min-w-0 items-center gap-3">
                <XCircle class="size-5 shrink-0 text-red-600" />
                <span class="truncate">{{ flashError }}</span>
            </div>
            <button
                type="button"
                @click="showErrorFlash = false"
                class="shrink-0 cursor-pointer rounded-lg p-1 text-red-600 transition-colors hover:bg-red-100"
                aria-label="Tutup notifikasi"
            >
                <X class="size-4" />
            </button>
        </div>

        <!-- Page Heading -->
        <PageHeader
            title="Pengguna"
            subtitle="Kelola akun dan akses Customer, Driver, serta Admin."
            badge="Master Data"
        >
            <template #action>
                <Link
                    href="/admin/users/create"
                    class="inline-flex h-[46px] shrink-0 cursor-pointer items-center justify-center gap-2 rounded-xl border-2 border-b-[4px] border-eco-green-dark bg-eco-green px-5 text-[14px] font-extrabold text-white shadow-2xs transition-all hover:brightness-105 active:translate-y-0.5 active:border-b-2"
                >
                    <UserPlus class="size-5 stroke-[2.5]" />
                    <span>Tambah Pengguna</span>
                </Link>
            </template>
        </PageHeader>

        <!-- Summary Chip (Figma Node 482:1264) -->
        <div class="mb-6 flex items-center gap-2">
            <div
                class="inline-flex items-center gap-2 rounded-xl border border-border-gray bg-paper-white px-3 py-1.5 text-[13px] font-bold text-night-ink shadow-2xs"
            >
                <Users class="size-4 text-eco-green-dark" />
                <span>Total Pengguna: {{ users.total }} akun</span>
            </div>
        </div>

        <!-- Toolbar: Search & Select Filter (Figma Node 482:1267) -->
        <div
            class="mb-6 flex flex-col items-stretch gap-3 lg:flex-row lg:items-center"
        >
            <!-- Search Input -->
            <div class="relative w-full">
                <Search
                    class="pointer-events-none absolute top-1/2 left-3.5 size-4.5 -translate-y-1/2 text-charcoal/70"
                />
                <input
                    v-model="search"
                    type="text"
                    placeholder="Cari nama, email, atau telepon..."
                    class="h-[44px] w-full rounded-xl border-2 border-border-gray bg-paper-white pr-10 pl-10 text-[14px] font-medium text-night-ink shadow-2xs transition-all placeholder:text-charcoal/60 focus:border-eco-green focus:ring-4 focus:ring-eco-green/15 focus:outline-none"
                />
                <button
                    v-if="search"
                    @click="search = ''"
                    type="button"
                    class="absolute top-1/2 right-3 -translate-y-1/2 cursor-pointer rounded-md p-1 text-charcoal transition-colors hover:bg-cloud-surface hover:text-night-ink"
                    aria-label="Bersihkan pencarian"
                >
                    <X class="size-3.5" />
                </button>
            </div>

            <!-- Filters Group -->
            <div
                class="flex w-full flex-wrap items-center gap-2.5 sm:flex-nowrap lg:w-auto"
            >
                <!-- Role Dropdown -->
                <AppDropdown
                    v-model="selectedRole"
                    :options="roleDropdownOptions"
                    placeholder="Semua Role"
                    class="min-w-[140px] flex-1 sm:w-[170px] sm:flex-initial"
                    menu-width="w-full sm:w-[190px]"
                    @change="onRoleChange"
                />

                <!-- Status Filter Dropdown -->
                <AppDropdown
                    v-model="selectedStatus"
                    :options="statusDropdownOptions"
                    placeholder="Semua Status"
                    class="min-w-[150px] flex-1 sm:w-[200px] sm:flex-initial"
                    menu-width="w-full sm:w-[210px]"
                    align="right"
                    @change="onStatusChange"
                />

                <!-- Reset Filter Button -->
                <button
                    v-if="search || selectedRole || selectedStatus !== 'active'"
                    type="button"
                    @click="resetFilters"
                    class="h-[44px] shrink-0 cursor-pointer rounded-xl border-2 border-border-gray bg-paper-white p-2.5 text-charcoal transition-colors hover:bg-cloud-surface hover:text-night-ink"
                    title="Reset Filter"
                >
                    <RotateCcw class="size-4" />
                </button>
            </div>
        </div>

        <!-- Table / Cards Container (Figma Node 482:1278) -->
        <div
            class="overflow-hidden rounded-[20px] border-2 border-b-[4px] border-border-gray bg-paper-white shadow-xs"
        >
            <!-- Mobile User Cards View (< md) -->
            <div
                v-if="users.data.length > 0"
                class="block divide-y divide-border-gray/70 md:hidden"
            >
                <div
                    v-for="u in users.data"
                    :key="`m-${u.id}`"
                    class="flex flex-col gap-3 p-4 transition-colors hover:bg-cloud-surface/30"
                >
                    <!-- User Header: Avatar + Name + Email + Role Chip -->
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex min-w-0 items-center gap-3">
                            <UserAvatar
                                :photo="u.photo"
                                :role="u.role"
                                :name="u.name"
                                size="md"
                            />
                            <div class="min-w-0">
                                <h4
                                    class="truncate text-[15px] leading-tight font-extrabold text-night-ink"
                                >
                                    {{ u.name }}
                                </h4>
                                <p
                                    class="mt-0.5 truncate text-[12px] font-medium text-charcoal"
                                >
                                    {{ u.email }}
                                </p>
                            </div>
                        </div>
                        <StatusChip
                            :label="getRoleLabel(u.role)"
                            :tone="getRoleTone(u.role)"
                        />
                    </div>

                    <!-- User Meta: Phone & Status -->
                    <div
                        class="flex items-center justify-between rounded-xl border border-border-gray/50 bg-cloud-surface/50 px-3 py-2 text-[12px]"
                    >
                        <span class="truncate font-semibold text-charcoal">
                            {{ u.phone_number || 'Tidak ada telepon' }}
                        </span>
                        <StatusChip
                            v-if="u.deleted_at"
                            label="Nonaktif"
                            tone="Neutral"
                        />
                        <StatusChip v-else label="Aktif" tone="Green" />
                    </div>

                    <!-- Action Buttons -->
                    <div
                        class="flex items-center justify-end gap-2 border-t border-border-gray/40 pt-1"
                    >
                        <Link
                            :href="`/admin/users/${u.id}`"
                            class="rounded-xl bg-cloud-surface px-3 py-1.5 text-[12px] font-extrabold text-night-ink transition-colors hover:bg-sprout-tint hover:text-eco-green-dark"
                        >
                            Lihat
                        </Link>
                        <Link
                            :href="`/admin/users/${u.id}/edit`"
                            class="rounded-xl bg-cloud-surface px-3 py-1.5 text-[12px] font-extrabold text-night-ink transition-colors hover:bg-sprout-tint hover:text-eco-green-dark"
                        >
                            Ubah
                        </Link>
                        <!-- Hapus/Pulihkan — disembunyikan untuk akun diri sendiri -->
                        <template v-if="u.id !== currentUserId">
                            <button
                                v-if="!u.deleted_at"
                                type="button"
                                class="cursor-pointer rounded-xl bg-red-50 px-3 py-1.5 text-[12px] font-extrabold text-red-600 transition-colors hover:bg-red-100"
                                @click="openDeleteModal(u)"
                            >
                                Hapus
                            </button>
                            <button
                                v-else
                                type="button"
                                class="cursor-pointer rounded-xl bg-sprout-tint px-3 py-1.5 text-[12px] font-extrabold text-eco-green-dark transition-colors hover:brightness-105"
                                @click="restoreUser(u.id)"
                            >
                                Pulihkan
                            </button>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Desktop & Tablet Table (>= md) -->
            <div class="hidden overflow-x-auto md:block">
                <table class="w-full min-w-[760px] border-collapse text-left">
                    <!-- Table Header -->
                    <thead>
                        <tr
                            class="border-b border-border-gray bg-cloud-surface text-[12px] font-extrabold tracking-wider text-charcoal uppercase"
                        >
                            <th class="px-6 py-3.5">Nama Pengguna</th>
                            <th class="px-6 py-3.5">Email</th>
                            <th class="px-6 py-3.5">Peran</th>
                            <th class="px-6 py-3.5">Telepon</th>
                            <th class="px-6 py-3.5">Status</th>
                            <th class="px-6 py-3.5 text-right">Aksi</th>
                        </tr>
                    </thead>

                    <!-- Table Body -->
                    <tbody
                        v-if="users.data.length > 0"
                        class="divide-y divide-border-gray/70"
                    >
                        <tr
                            v-for="u in users.data"
                            :key="u.id"
                            class="transition-colors hover:bg-cloud-surface/50"
                        >
                            <!-- Nama + Avatar -->
                            <td class="px-6 py-3.5">
                                <div class="flex items-center gap-3">
                                    <UserAvatar
                                        :photo="u.photo"
                                        :role="u.role"
                                        :name="u.name"
                                        size="md"
                                    />
                                    <div>
                                        <p
                                            class="text-[14px] leading-tight font-bold text-night-ink"
                                        >
                                            {{ u.name }}
                                        </p>
                                    </div>
                                </div>
                            </td>

                            <!-- Email -->
                            <td
                                class="px-6 py-3.5 text-[13px] font-medium text-night-ink"
                            >
                                {{ u.email }}
                            </td>

                            <!-- Peran (Role) -->
                            <td class="px-6 py-3.5">
                                <StatusChip
                                    :label="getRoleLabel(u.role)"
                                    :tone="getRoleTone(u.role)"
                                />
                            </td>

                            <!-- Telepon -->
                            <td
                                class="px-6 py-3.5 text-[13px] font-medium text-charcoal"
                            >
                                {{ u.phone_number || '—' }}
                            </td>

                            <!-- Status -->
                            <td class="px-6 py-3.5">
                                <StatusChip
                                    v-if="u.deleted_at"
                                    label="Nonaktif"
                                    tone="Neutral"
                                />
                                <StatusChip v-else label="Aktif" tone="Green" />
                            </td>

                            <!-- Actions -->
                            <td
                                class="px-6 py-3.5 text-right whitespace-nowrap"
                            >
                                <div
                                    class="inline-flex items-center justify-end gap-1"
                                >
                                    <!-- Lihat -->
                                    <Link
                                        :href="`/admin/users/${u.id}`"
                                        class="cursor-pointer rounded-lg p-2 text-charcoal transition-colors hover:bg-cloud-surface hover:text-night-ink"
                                        title="Lihat Detail Pengguna"
                                    >
                                        <Eye class="size-4" />
                                    </Link>

                                    <!-- Ubah -->
                                    <Link
                                        :href="`/admin/users/${u.id}/edit`"
                                        class="cursor-pointer rounded-lg p-2 text-charcoal transition-colors hover:bg-sprout-tint/60 hover:text-eco-green-dark"
                                        title="Ubah Pengguna"
                                    >
                                        <Edit3 class="size-4" />
                                    </Link>

                                    <!-- Hapus/Pulihkan (disembunyikan untuk akun diri sendiri) -->
                                    <template v-if="u.id !== currentUserId">
                                        <button
                                            v-if="!u.deleted_at"
                                            type="button"
                                            class="cursor-pointer rounded-lg p-2 text-charcoal transition-colors hover:bg-red-50 hover:text-red-600"
                                            @click="openDeleteModal(u)"
                                            title="Nonaktifkan Pengguna"
                                        >
                                            <Trash2 class="size-4" />
                                        </button>

                                        <button
                                            v-else
                                            type="button"
                                            class="cursor-pointer rounded-lg p-2 text-eco-green-dark transition-colors hover:bg-sprout-tint"
                                            @click="restoreUser(u.id)"
                                            title="Pulihkan Pengguna"
                                        >
                                            <RotateCcw class="size-4" />
                                        </button>
                                    </template>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Empty State: No Results (Figma Node 493:14534) -->
            <div
                v-if="
                    users.data.length === 0 &&
                    (search || selectedRole || selectedStatus !== 'active')
                "
                class="flex flex-col items-center justify-center p-12 text-center"
            >
                <div
                    class="mb-4 flex size-24 items-center justify-center rounded-full bg-cloud-surface"
                >
                    <img
                        src="/assets/Maskot/Pose=Searching-Driver.png"
                        alt="Tidak ada hasil"
                        class="size-20 object-contain"
                    />
                </div>
                <h3 class="mb-1 text-[20px] font-extrabold text-night-ink">
                    Tidak ada hasil yang cocok
                </h3>
                <p
                    class="mb-6 max-w-[420px] text-[14px] leading-relaxed font-normal text-charcoal"
                >
                    Coba kata kunci lain atau hapus filter untuk melihat seluruh
                    data pengguna.
                </p>
                <button
                    type="button"
                    class="h-[44px] cursor-pointer rounded-2xl border-2 border-b-[4px] border-eco-green-dark bg-eco-green px-6 text-[14px] font-extrabold text-night-ink transition-all hover:brightness-105 active:translate-y-0.5 active:border-b-2"
                    @click="resetFilters"
                >
                    Reset Filter
                </button>
            </div>

            <!-- Empty State: Belum Ada Pengguna (Figma Node 493:13806) -->
            <div
                v-else-if="users.data.length === 0"
                class="flex flex-col items-center justify-center p-12 text-center"
            >
                <div
                    class="mb-4 flex size-24 items-center justify-center rounded-full bg-cloud-surface"
                >
                    <img
                        src="/assets/Maskot/Valu · In the neighborhood.png"
                        alt="Belum ada pengguna"
                        class="size-20 object-contain"
                    />
                </div>
                <h3 class="mb-1 text-[20px] font-extrabold text-night-ink">
                    Belum ada pengguna
                </h3>
                <p
                    class="mb-6 max-w-[420px] text-[14px] leading-relaxed font-normal text-charcoal"
                >
                    Tambahkan pengguna pertama untuk mulai mengelola akses akun
                    di ReValue.
                </p>
                <Link
                    href="/admin/users/create"
                    class="inline-flex h-[44px] cursor-pointer items-center justify-center rounded-2xl border-2 border-b-[4px] border-eco-green-dark bg-eco-green px-6 text-[14px] font-extrabold text-night-ink transition-all hover:brightness-105 active:translate-y-0.5 active:border-b-2"
                >
                    Tambah Pengguna
                </Link>
            </div>

            <!-- Pagination Bar (Figma Node 482:1472) -->
            <div
                v-if="users.total > 0"
                class="flex flex-col items-center justify-between gap-4 border-t border-border-gray bg-paper-white px-4 py-4 sm:flex-row sm:px-6"
            >
                <p
                    class="text-center text-[13px] font-normal text-charcoal sm:text-left"
                >
                    Menampilkan
                    <span class="font-bold text-night-ink"
                        >{{ users.from || 0 }}–{{ users.to || 0 }}</span
                    >
                    dari
                    <span class="font-bold text-night-ink">{{
                        users.total
                    }}</span>
                    data
                </p>

                <div
                    class="flex flex-wrap items-center justify-center gap-1 sm:gap-1.5"
                >
                    <!-- Prev Button -->
                    <Link
                        v-if="users.prev_page_url"
                        :href="users.prev_page_url"
                        class="inline-flex h-[36px] items-center gap-1 rounded-xl border border-border-gray px-3 text-[13px] font-extrabold text-night-ink transition-colors hover:bg-cloud-surface"
                    >
                        <ChevronLeft class="size-4" />
                        <span>Sebelumnya</span>
                    </Link>
                    <span
                        v-else
                        class="inline-flex h-[36px] cursor-not-allowed items-center gap-1 rounded-xl border border-border-gray/50 px-3 text-[13px] font-semibold text-charcoal/40 opacity-50"
                    >
                        <ChevronLeft class="size-4" />
                        <span>Sebelumnya</span>
                    </span>

                    <!-- Page Numbers -->
                    <template v-for="(link, i) in users.links" :key="i">
                        <Link
                            v-if="
                                link.url &&
                                !link.label.includes('Previous') &&
                                !link.label.includes('Next')
                            "
                            :href="link.url"
                            :class="[
                                'flex size-9 items-center justify-center rounded-xl text-[13px] font-extrabold transition-all',
                                link.active
                                    ? 'border-2 border-eco-green/40 bg-sprout-tint text-night-ink shadow-2xs'
                                    : 'border border-border-gray text-charcoal hover:bg-cloud-surface hover:text-night-ink',
                            ]"
                        >
                            {{ link.label }}
                        </Link>
                    </template>

                    <!-- Next Button -->
                    <Link
                        v-if="users.next_page_url"
                        :href="users.next_page_url"
                        class="inline-flex h-[36px] items-center gap-1 rounded-xl border border-border-gray px-3 text-[13px] font-extrabold text-night-ink transition-colors hover:bg-cloud-surface"
                    >
                        <span>Berikutnya</span>
                        <ChevronRight class="size-4" />
                    </Link>
                    <span
                        v-else
                        class="inline-flex h-[36px] cursor-not-allowed items-center gap-1 rounded-xl border border-border-gray/50 px-3 text-[13px] font-semibold text-charcoal/40 opacity-50"
                    >
                        <span>Berikutnya</span>
                        <ChevronRight class="size-4" />
                    </span>
                </div>
            </div>
        </div>

        <!-- Delete Modal Component (Figma Node 491:4973) -->
        <UserDeleteModal
            :show="isDeleteModalOpen"
            :user="selectedUserForDelete"
            @close="closeDeleteModal"
        />
    </DashboardLayout>
</template>
