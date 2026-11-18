<script setup lang="ts">
import { computed, ref } from "vue";
import { Head, usePage } from "@inertiajs/vue3";
import type { User } from "@/types/auth";
import ProfileSidebar from "@/components/Profile/ProfileSidebar.vue";
import ProfileHeader from "@/components/Profile/ProfileHeader.vue";
import ProfileMainView from "@/components/Profile/ProfileMainView.vue";
import AddressSection from "@/components/Profile/AddressSection.vue";
import SecuritySection from "@/components/Profile/SecuritySection.vue";
import AccountInfoSection from "@/components/Profile/AccountInfoSection.vue";
import AvatarUploadModal from "@/components/Profile/AvatarUploadModal.vue";

const props = defineProps<{
    mustVerifyEmail?: boolean;
    status?: string;
    userData?: User;
}>();

const page = usePage();
const currentUser = computed(() => {
    return (props.userData || (page.props.auth as any)?.user) as User;
});

type TabType = "profile" | "address" | "security" | "account";

// Parse initial tab from URL query params (e.g. /profile?tab=address)
const urlParams = new URLSearchParams(typeof window !== "undefined" ? window.location.search : "");
const initialTab = (urlParams.get("tab") as TabType) || "profile";
const activeTab = ref<TabType>(
    ["profile", "address", "security", "account"].includes(initialTab) ? initialTab : "profile"
);

const showPhotoModal = ref(false);
const editProfileDirectly = ref(false);

const tabTitles: Record<TabType, string> = {
    profile: "Profil saya",
    address: "Alamat utama",
    security: "Keamanan akun",
    account: "Informasi akun",
};

const currentTabTitle = computed(() => tabTitles[activeTab.value]);

const handleGoToEditEmail = () => {
    editProfileDirectly.value = true;
    selectTab("profile");
};

const selectTab = (tab: TabType) => {
    activeTab.value = tab;
    if (tab !== "profile") {
        editProfileDirectly.value = false;
    }
    if (typeof window !== "undefined" && window.history) {
        const url = new URL(window.location.href);
        if (tab === "profile") {
            url.searchParams.delete("tab");
        } else {
            url.searchParams.set("tab", tab);
        }
        window.history.replaceState({}, "", url.toString());
    }
};
</script>

<template>
    <Head :title="`${currentTabTitle} - Pengaturan Akun ReValue`" />

    <div class="min-h-screen bg-cloud-surface text-night-ink font-sans flex flex-col lg:flex-row antialiased">
        <!-- 1. Shared Account Sidebar -->
        <ProfileSidebar
            :active-tab="activeTab"
            @select-tab="selectTab"
        />

        <!-- 2. Main Workspace -->
        <div class="flex-1 flex flex-col min-w-0">
            <!-- Top Shared Header -->
            <ProfileHeader
                :user="currentUser"
                :current-tab-title="currentTabTitle"
            />

            <!-- Content Container -->
            <main class="flex-1 p-6 lg:p-8 max-w-7xl w-full mx-auto">
                <!-- Status Flash Alert -->
                <div
                    v-if="status === 'profile-updated'"
                    class="mb-6 p-4 rounded-xl bg-sprout-tint/80 border border-eco-green/40 text-night-ink font-bold text-sm shadow-xs animate-in fade-in duration-300"
                >
                    Profil berhasil diperbarui.
                </div>

                <!-- Tab 1: Profil Saya -->
                <ProfileMainView
                    v-if="activeTab === 'profile'"
                    :user="currentUser"
                    :start-in-edit-mode="editProfileDirectly"
                    @go-to-account-info="selectTab('account')"
                    @open-photo-modal="showPhotoModal = true"
                    @closed-edit="editProfileDirectly = false"
                />

                <!-- Tab 2: Alamat Utama -->
                <AddressSection
                    v-else-if="activeTab === 'address'"
                    :user="currentUser"
                />

                <!-- Tab 3: Keamanan Akun -->
                <SecuritySection
                    v-else-if="activeTab === 'security'"
                    :user="currentUser"
                    :must-verify-email="mustVerifyEmail"
                    :status="status"
                    @go-to-edit-email="handleGoToEditEmail"
                />

                <!-- Tab 4: Informasi Akun -->
                <AccountInfoSection
                    v-else-if="activeTab === 'account'"
                    :user="currentUser"
                />
            </main>
        </div>

        <!-- Avatar Upload Modal -->
        <AvatarUploadModal
            :show="showPhotoModal"
            :user="currentUser"
            @close="showPhotoModal = false"
        />
    </div>
</template>
