<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import type { User } from '@/types/auth';
import ProfileSidebar from '@/components/Profile/ProfileSidebar.vue';
import ProfileHeader from '@/components/Profile/ProfileHeader.vue';
import ProfileMainView from '@/components/Profile/ProfileMainView.vue';
import AddressSection from '@/components/Profile/AddressSection.vue';
import SecuritySection from '@/components/Profile/SecuritySection.vue';
import AccountInfoSection from '@/components/Profile/AccountInfoSection.vue';
import AvatarUploadModal from '@/components/Profile/AvatarUploadModal.vue';
import AchievementSection, {
    type CustomerAchievementsData,
} from '@/components/Profile/AchievementSection.vue';

const props = defineProps<{
    mustVerifyEmail?: boolean;
    status?: string;
    userData?: User;
    basemapApiKey?: string;
    achievements?: CustomerAchievementsData | null;
}>();

const page = usePage();
const currentUser = computed(() => {
    return (props.userData || (page.props.auth as any)?.user) as User;
});

type TabType = 'profile' | 'achievements' | 'address' | 'security' | 'account';

// Parse initial tab from URL query params (e.g. /profile?tab=address)
const urlParams = new URLSearchParams(
    typeof window !== 'undefined' ? window.location.search : '',
);
const initialTab = (urlParams.get('tab') as TabType) || 'profile';
const activeTab = ref<TabType>(
    ['profile', 'achievements', 'address', 'security', 'account'].includes(
        initialTab,
    )
        ? initialTab
        : 'profile',
);

const showPhotoModal = ref(false);
const editProfileDirectly = ref(false);

const tabTitles: Record<TabType, string> = {
    profile: 'Profil saya',
    achievements: 'Pencapaian saya',
    address: 'Alamat utama',
    security: 'Keamanan akun',
    account: 'Informasi akun',
};

const currentTabTitle = computed(() => tabTitles[activeTab.value]);

const handleGoToEditEmail = () => {
    editProfileDirectly.value = true;
    selectTab('profile');
};

const selectTab = (tab: TabType) => {
    activeTab.value = tab;
    if (tab !== 'profile') {
        editProfileDirectly.value = false;
    }
    if (typeof window !== 'undefined' && window.history) {
        const url = new URL(window.location.href);
        if (tab === 'profile') {
            url.searchParams.delete('tab');
        } else {
            url.searchParams.set('tab', tab);
        }
        window.history.replaceState({}, '', url.toString());
    }
};
</script>

<template>
    <Head :title="`${currentTabTitle} - Pengaturan Akun ReValue`" />

    <div
        class="flex min-h-screen flex-col bg-cloud-surface font-sans text-night-ink antialiased lg:flex-row"
    >
        <!-- 1. Shared Account Sidebar -->
        <ProfileSidebar :active-tab="activeTab" @select-tab="selectTab" />

        <!-- 2. Main Workspace -->
        <div class="flex min-w-0 flex-1 flex-col">
            <!-- Top Shared Header -->
            <ProfileHeader
                :user="currentUser"
                :current-tab-title="currentTabTitle"
            />

            <!-- Content Container -->
            <main class="mx-auto w-full max-w-7xl flex-1 p-6 lg:p-8">
                <!-- Status Flash Alert -->
                <div
                    v-if="status === 'profile-updated'"
                    class="animate-in fade-in mb-6 rounded-xl border border-eco-green/40 bg-sprout-tint/80 p-4 text-sm font-bold text-night-ink shadow-xs duration-300"
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

                <!-- Tab: Pencapaian (Achievements) -->
                <AchievementSection
                    v-else-if="activeTab === 'achievements'"
                    :achievements="achievements"
                />

                <!-- Tab 2: Alamat Utama -->
                <AddressSection
                    v-else-if="activeTab === 'address'"
                    :user="currentUser"
                    :basemap-api-key="basemapApiKey"
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
