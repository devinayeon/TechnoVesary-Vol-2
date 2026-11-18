<script setup lang="ts">
import { ref, watch, computed } from "vue";
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import DashboardLayout from "@/layouts/DashboardLayout.vue";
import UserAvatar from "@/components/Dashboard/UserAvatar.vue";
import StatusChip from "@/components/Dashboard/StatusChip.vue";
import UserDeleteModal from "@/components/Dashboard/UserDeleteModal.vue";
import AppDropdown from "@/components/Dashboard/AppDropdown.vue";
import PageHeader from "@/components/Dashboard/PageHeader.vue";
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
} from "lucide-vue-next";

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
const search = ref(props.filters.search || "");
const selectedRole = ref(props.filters.role || "");
const selectedStatus = ref(props.filters.status || "active");

let searchTimeout: any = null;
const applyFilters = () => {
    router.get(
        "/admin/users",
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
    { value: "", label: "Semua Role", icon: Users },
    ...props.roles.map((r) => ({
        value: r.value,
        label: r.label,
        icon:
            r.value === "admin" ? Shield : r.value === "driver" ? Truck : Users,
    })),
]);

const statusDropdownOptions = [
    { value: "active", label: "Aktif" },
    { value: "trashed", label: "Dinonaktifkan" },
    { value: "all", label: "Semua Status" },
];

const resetFilters = () => {
    search.value = "";
    selectedRole.value = "";
    selectedStatus.value = "active";
    router.get(
        "/admin/users",
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
    if (confirm("Aktifkan kembali akun pengguna ini?")) {
        router.post(
            `/admin/users/${id}/restore`,
            {},
            {
                preserveScroll: true,
            },
        );
    }
};

const getRoleTone = (role: string): "Green" | "Blue" | "Amber" | "Neutral" => {
    switch (role.toLowerCase()) {
        case "admin":
            return "Amber";
        case "driver":
            return "Blue";
        case "customer":
        default:
            return "Green";
    }
};

const getRoleLabel = (role: string): string => {
    switch (role.toLowerCase()) {
        case "admin":
            return "Admin";
        case "driver":
            return "Driver";
        case "customer":
        default:
            return "Customer";
    }
};
</script>

<template>
    <Head title="Manajemen Pengguna - Admin ReValue" />

    <DashboardLayout>
        <!-- Success Flash Alert -->
        <div
            v-if="showSuccessFlash && flashSuccess"
            class="mb-6 flex items-center justify-between gap-3 p-4 rounded-2xl bg-sprout-tint border-2 border-eco-green/40 text-night-ink font-bold text-[14px] shadow-2xs animate-in fade-in duration-200"
        >
            <div class="flex items-center gap-3 min-w-0">
                <CheckCircle2 class="size-5 text-eco-green-dark shrink-0" />
                <span class="truncate">{{ flashSuccess }}</span>
            </div>
            <button
                type="button"
                @click="showSuccessFlash = false"
                class="p-1 rounded-lg hover:bg-eco-green/20 text-charcoal hover:text-night-ink transition-colors cursor-pointer shrink-0"
                aria-label="Tutup notifikasi"
            >
                <X class="size-4" />
            </button>
        </div>

        <!-- Error Flash Alert -->
        <div
            v-if="showErrorFlash && flashError"
            class="mb-6 flex items-center justify-between gap-3 p-4 rounded-2xl bg-red-50 border-2 border-red-200 text-red-700 font-bold text-[14px] shadow-2xs animate-in fade-in duration-200"
        >
            <div class="flex items-center gap-3 min-w-0">
                <XCircle class="size-5 text-red-600 shrink-0" />
                <span class="truncate">{{ flashError }}</span>
            </div>
            <button
                type="button"
                @click="showErrorFlash = false"
                class="p-1 rounded-lg hover:bg-red-100 text-red-600 transition-colors cursor-pointer shrink-0"
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
                    class="inline-flex items-center justify-center gap-2 px-5 h-[46px] rounded-xl border-2 border-b-[4px] border-eco-green-dark bg-eco-green text-white font-extrabold text-[14px] hover:brightness-105 active:translate-y-0.5 active:border-b-2 transition-all cursor-pointer shadow-2xs shrink-0"
                >
                    <UserPlus class="size-5 stroke-[2.5]" />
                    <span>Tambah Pengguna</span>
                </Link>
            </template>
        </PageHeader>

        <!-- Summary Chip (Figma Node 482:1264) -->
        <div class="flex items-center gap-2 mb-6">
            <div
                class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-paper-white border border-border-gray text-[13px] font-bold text-night-ink shadow-2xs"
            >
                <Users class="size-4 text-eco-green-dark" />
                <span>Total Pengguna: {{ users.total }} akun</span>
            </div>
        </div>

        <!-- Toolbar: Search & Select Filter (Figma Node 482:1267) -->
        <div
            class="flex flex-col lg:flex-row items-stretch lg:items-center gap-3 mb-6"
        >
            <!-- Search Input -->
            <div class="relative w-full">
                <Search
                    class="absolute left-3.5 top-1/2 -translate-y-1/2 size-4.5 text-charcoal/70 pointer-events-none"
                />
                <input
                    v-model="search"
                    type="text"
                    placeholder="Cari nama, email, atau telepon..."
                    class="w-full h-[44px] pl-10 pr-10 bg-paper-white border-2 border-border-gray rounded-xl text-[14px] font-medium text-night-ink placeholder:text-charcoal/60 focus:outline-none focus:border-eco-green focus:ring-4 focus:ring-eco-green/15 transition-all shadow-2xs"
                />
                <button
                    v-if="search"
                    @click="search = ''"
                    type="button"
                    class="absolute right-3 top-1/2 -translate-y-1/2 p-1 rounded-md text-charcoal hover:bg-cloud-surface hover:text-night-ink transition-colors cursor-pointer"
                    aria-label="Bersihkan pencarian"
                >
                    <X class="size-3.5" />
                </button>
            </div>

            <!-- Filters Group -->
            <div
                class="flex flex-wrap sm:flex-nowrap items-center gap-2.5 w-full lg:w-auto"
            >
                <!-- Role Dropdown -->
                <AppDropdown
                    v-model="selectedRole"
                    :options="roleDropdownOptions"
                    placeholder="Semua Role"
                    class="flex-1 sm:flex-initial min-w-[140px] sm:w-[170px]"
                    menu-width="w-full sm:w-[190px]"
                    @change="onRoleChange"
                />

                <!-- Status Filter Dropdown -->
                <AppDropdown
                    v-model="selectedStatus"
                    :options="statusDropdownOptions"
                    placeholder="Semua Status"
                    class="flex-1 sm:flex-initial min-w-[150px] sm:w-[200px]"
                    menu-width="w-full sm:w-[210px]"
                    align="right"
                    @change="onStatusChange"
                />

                <!-- Reset Filter Button -->
                <button
                    v-if="search || selectedRole || selectedStatus !== 'active'"
                    type="button"
                    @click="resetFilters"
                    class="p-2.5 h-[44px] rounded-xl border-2 border-border-gray bg-paper-white text-charcoal hover:text-night-ink hover:bg-cloud-surface transition-colors cursor-pointer shrink-0"
                    title="Reset Filter"
                >
                    <RotateCcw class="size-4" />
                </button>
            </div>
        </div>

        <!-- Table / Cards Container (Figma Node 482:1278) -->
        <div
            class="bg-paper-white border-2 border-b-[4px] border-border-gray rounded-[20px] overflow-hidden shadow-xs"
        >
            <!-- Mobile User Cards View (< md) -->
            <div
                v-if="users.data.length > 0"
                class="block md:hidden divide-y divide-border-gray/70"
            >
                <div
                    v-for="u in users.data"
                    :key="`m-${u.id}`"
                    class="p-4 flex flex-col gap-3 hover:bg-cloud-surface/30 transition-colors"
                >
                    <!-- User Header: Avatar + Name + Email + Role Chip -->
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <UserAvatar
                                :photo="u.photo"
                                :role="u.role"
                                :name="u.name"
                                size="md"
                            />
                            <div class="min-w-0">
                                <h4
                                    class="font-extrabold text-[15px] text-night-ink leading-tight truncate"
                                >
                                    {{ u.name }}
                                </h4>
                                <p
                                    class="text-[12px] font-medium text-charcoal truncate mt-0.5"
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
                        class="flex items-center justify-between text-[12px] bg-cloud-surface/50 rounded-xl px-3 py-2 border border-border-gray/50"
                    >
                        <span class="text-charcoal font-semibold truncate">
                            {{ u.phone_number || "Tidak ada telepon" }}
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
                        class="flex items-center justify-end gap-2 pt-1 border-t border-border-gray/40"
                    >
                        <Link
                            :href="`/admin/users/${u.id}`"
                            class="px-3 py-1.5 text-[12px] font-extrabold text-night-ink bg-cloud-surface hover:bg-sprout-tint hover:text-eco-green-dark rounded-xl transition-colors"
                        >
                            Lihat
                        </Link>
                        <Link
                            :href="`/admin/users/${u.id}/edit`"
                            class="px-3 py-1.5 text-[12px] font-extrabold text-night-ink bg-cloud-surface hover:bg-sprout-tint hover:text-eco-green-dark rounded-xl transition-colors"
                        >
                            Ubah
                        </Link>
                        <!-- Hapus/Pulihkan — disembunyikan untuk akun diri sendiri -->
                        <template v-if="u.id !== currentUserId">
                            <button
                                v-if="!u.deleted_at"
                                type="button"
                                class="px-3 py-1.5 text-[12px] font-extrabold text-red-600 bg-red-50 hover:bg-red-100 rounded-xl transition-colors cursor-pointer"
                                @click="openDeleteModal(u)"
                            >
                                Hapus
                            </button>
                            <button
                                v-else
                                type="button"
                                class="px-3 py-1.5 text-[12px] font-extrabold text-eco-green-dark bg-sprout-tint hover:brightness-105 rounded-xl transition-colors cursor-pointer"
                                @click="restoreUser(u.id)"
                            >
                                Pulihkan
                            </button>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Desktop & Tablet Table (>= md) -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left border-collapse min-w-[760px]">
                    <!-- Table Header -->
                    <thead>
                        <tr
                            class="bg-cloud-surface border-b border-border-gray text-[12px] font-extrabold text-charcoal uppercase tracking-wider"
                        >
                            <th class="py-3.5 px-6">Nama Pengguna</th>
                            <th class="py-3.5 px-6">Email</th>
                            <th class="py-3.5 px-6">Peran</th>
                            <th class="py-3.5 px-6">Telepon</th>
                            <th class="py-3.5 px-6">Status</th>
                            <th class="py-3.5 px-6 text-right">Aksi</th>
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
                            class="hover:bg-cloud-surface/50 transition-colors"
                        >
                            <!-- Nama + Avatar -->
                            <td class="py-3.5 px-6">
                                <div class="flex items-center gap-3">
                                    <UserAvatar
                                        :photo="u.photo"
                                        :role="u.role"
                                        :name="u.name"
                                        size="md"
                                    />
                                    <div>
                                        <p
                                            class="font-bold text-[14px] text-night-ink leading-tight"
                                        >
                                            {{ u.name }}
                                        </p>
                                    </div>
                                </div>
                            </td>

                            <!-- Email -->
                            <td
                                class="py-3.5 px-6 text-[13px] font-medium text-night-ink"
                            >
                                {{ u.email }}
                            </td>

                            <!-- Peran (Role) -->
                            <td class="py-3.5 px-6">
                                <StatusChip
                                    :label="getRoleLabel(u.role)"
                                    :tone="getRoleTone(u.role)"
                                />
                            </td>

                            <!-- Telepon -->
                            <td
                                class="py-3.5 px-6 text-[13px] font-medium text-charcoal"
                            >
                                {{ u.phone_number || "—" }}
                            </td>

                            <!-- Status -->
                            <td class="py-3.5 px-6">
                                <StatusChip
                                    v-if="u.deleted_at"
                                    label="Nonaktif"
                                    tone="Neutral"
                                />
                                <StatusChip v-else label="Aktif" tone="Green" />
                            </td>

                            <!-- Actions -->
                            <td
                                class="py-3.5 px-6 text-right whitespace-nowrap"
                            >
                                <div
                                    class="inline-flex items-center gap-1.5 justify-end"
                                >
                                    <!-- Lihat -->
                                    <Link
                                        :href="`/admin/users/${u.id}`"
                                        class="px-2.5 py-1 text-[13px] font-extrabold text-night-ink hover:text-eco-green-dark hover:bg-cloud-surface rounded-lg transition-colors"
                                    >
                                        Lihat
                                    </Link>

                                    <!-- Ubah -->
                                    <Link
                                        :href="`/admin/users/${u.id}/edit`"
                                        class="px-2.5 py-1 text-[13px] font-extrabold text-night-ink hover:text-eco-green-dark hover:bg-cloud-surface rounded-lg transition-colors"
                                    >
                                        Ubah
                                    </Link>

                                    <!-- Hapus (jika aktif) -->
                                    <template v-if="u.id !== currentUserId">
                                        <button
                                            v-if="!u.deleted_at"
                                            type="button"
                                            class="px-2.5 py-1 text-[13px] font-extrabold text-red-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors cursor-pointer"
                                            @click="openDeleteModal(u)"
                                        >
                                            Hapus
                                        </button>

                                        <!-- Pulihkan (jika soft-deleted) -->
                                        <button
                                            v-else
                                            type="button"
                                            class="px-2.5 py-1 text-[13px] font-extrabold text-eco-green-dark hover:bg-sprout-tint/60 rounded-lg transition-colors cursor-pointer"
                                            @click="restoreUser(u.id)"
                                        >
                                            Pulihkan
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
                    class="size-24 rounded-full bg-cloud-surface flex items-center justify-center mb-4"
                >
                    <img
                        src="/assets/Maskot/Pose=Searching-Driver.png"
                        alt="Tidak ada hasil"
                        class="size-20 object-contain"
                    />
                </div>
                <h3 class="text-[20px] font-extrabold text-night-ink mb-1">
                    Tidak ada hasil yang cocok
                </h3>
                <p
                    class="text-[14px] font-normal text-charcoal max-w-[420px] mb-6 leading-relaxed"
                >
                    Coba kata kunci lain atau hapus filter untuk melihat seluruh
                    data pengguna.
                </p>
                <button
                    type="button"
                    class="px-6 h-[44px] bg-eco-green text-night-ink font-extrabold text-[14px] rounded-2xl border-2 border-b-[4px] border-eco-green-dark hover:brightness-105 active:translate-y-0.5 active:border-b-2 transition-all cursor-pointer"
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
                    class="size-24 rounded-full bg-cloud-surface flex items-center justify-center mb-4"
                >
                    <img
                        src="/assets/Maskot/Valu · In the neighborhood.png"
                        alt="Belum ada pengguna"
                        class="size-20 object-contain"
                    />
                </div>
                <h3 class="text-[20px] font-extrabold text-night-ink mb-1">
                    Belum ada pengguna
                </h3>
                <p
                    class="text-[14px] font-normal text-charcoal max-w-[420px] mb-6 leading-relaxed"
                >
                    Tambahkan pengguna pertama untuk mulai mengelola akses akun
                    di ReValue.
                </p>
                <Link
                    href="/admin/users/create"
                    class="px-6 h-[44px] bg-eco-green text-night-ink font-extrabold text-[14px] rounded-2xl border-2 border-b-[4px] border-eco-green-dark hover:brightness-105 active:translate-y-0.5 active:border-b-2 transition-all cursor-pointer inline-flex items-center justify-center"
                >
                    Tambah Pengguna
                </Link>
            </div>

            <!-- Pagination Bar (Figma Node 482:1472) -->
            <div
                v-if="users.total > 0"
                class="flex flex-col sm:flex-row items-center justify-between gap-4 px-4 sm:px-6 py-4 border-t border-border-gray bg-paper-white"
            >
                <p
                    class="text-[13px] font-normal text-charcoal text-center sm:text-left"
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
                    class="flex items-center gap-1 sm:gap-1.5 flex-wrap justify-center"
                >
                    <!-- Prev Button -->
                    <Link
                        v-if="users.prev_page_url"
                        :href="users.prev_page_url"
                        class="h-[36px] px-3 rounded-xl border border-border-gray text-[13px] font-extrabold text-night-ink hover:bg-cloud-surface transition-colors inline-flex items-center gap-1"
                    >
                        <ChevronLeft class="size-4" />
                        <span>Sebelumnya</span>
                    </Link>
                    <span
                        v-else
                        class="h-[36px] px-3 rounded-xl border border-border-gray/50 text-[13px] font-semibold text-charcoal/40 inline-flex items-center gap-1 opacity-50 cursor-not-allowed"
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
                                'size-9 rounded-xl flex items-center justify-center text-[13px] font-extrabold transition-all',
                                link.active
                                    ? 'bg-sprout-tint text-night-ink border-2 border-eco-green/40 shadow-2xs'
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
                        class="h-[36px] px-3 rounded-xl border border-border-gray text-[13px] font-extrabold text-night-ink hover:bg-cloud-surface transition-colors inline-flex items-center gap-1"
                    >
                        <span>Berikutnya</span>
                        <ChevronRight class="size-4" />
                    </Link>
                    <span
                        v-else
                        class="h-[36px] px-3 rounded-xl border border-border-gray/50 text-[13px] font-semibold text-charcoal/40 inline-flex items-center gap-1 opacity-50 cursor-not-allowed"
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
