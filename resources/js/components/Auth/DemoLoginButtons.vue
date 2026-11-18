<script setup lang="ts">
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { Shield, Truck, UserCheck, Loader2 } from 'lucide-vue-next';

type DemoRole = 'admin' | 'driver' | 'customer';

const activeRole = ref<DemoRole | null>(null);

const demoAccounts: Array<{
    role: DemoRole;
    label: string;
    description: string;
    icon: typeof Shield;
    badgeTone: string;
    hoverBorder: string;
    hoverBg: string;
    iconColor: string;
}> = [
    {
        role: 'admin',
        label: 'Admin',
        description: 'Kelola Sistem',
        icon: Shield,
        badgeTone: 'border-border-gray text-night-ink',
        hoverBorder: 'hover:border-night-ink/40',
        hoverBg: 'hover:bg-cloud-surface',
        iconColor: 'text-night-ink',
    },
    {
        role: 'driver',
        label: 'Driver',
        description: 'Antar Jemput',
        icon: Truck,
        badgeTone: 'border-sky-blue/30 text-sky-blue',
        hoverBorder: 'hover:border-sky-blue',
        hoverBg: 'hover:bg-sky-tint/40',
        iconColor: 'text-sky-blue',
    },
    {
        role: 'customer',
        label: 'Customer',
        description: 'Pilah Sampah',
        icon: UserCheck,
        badgeTone: 'border-eco-green/40 text-eco-green-dark',
        hoverBorder: 'hover:border-eco-green',
        hoverBg: 'hover:bg-sprout-tint/40',
        iconColor: 'text-eco-green-dark',
    },
];

const loginAs = (role: DemoRole) => {
    if (activeRole.value !== null) {
        return;
    }

    activeRole.value = role;

    router.post(
        '/demo-login',
        { role },
        {
            onFinish: () => {
                activeRole.value = null;
            },
        },
    );
};
</script>

<template>
    <div class="w-full">
        <!-- Tactile Divider -->
        <div class="relative my-6 flex items-center justify-center">
            <div class="w-full border-t-2 border-border-gray/70"></div>
            <span
                class="absolute bg-paper-white px-3 text-[11px] font-extrabold tracking-wider text-charcoal/80 uppercase"
            >
                Atau coba cepat (Demo)
            </span>
        </div>

        <!-- 3 Quick Demo Buttons Grid -->
        <div class="grid grid-cols-3 gap-2.5 sm:gap-3">
            <button
                v-for="account in demoAccounts"
                :key="account.role"
                type="button"
                :disabled="activeRole !== null"
                @click="loginAs(account.role)"
                class="group relative flex cursor-pointer flex-col items-center justify-center rounded-2xl border-2 border-b-[4px] border-border-gray bg-paper-white p-3 text-center transition-all active:translate-y-0.5 active:border-b-2 active:shadow-none disabled:cursor-not-allowed disabled:opacity-50"
                :class="[account.hoverBorder, account.hoverBg]"
            >
                <!-- Loading Spinner per role -->
                <div
                    v-if="activeRole === account.role"
                    class="flex flex-col items-center justify-center py-1"
                >
                    <Loader2 class="size-5 animate-spin text-night-ink" />
                    <span class="mt-1 text-[11px] font-extrabold text-night-ink"
                        >Masuk...</span
                    >
                </div>

                <!-- Normal Button Content -->
                <div
                    v-else
                    class="flex flex-col items-center justify-center gap-1"
                >
                    <div
                        class="flex size-9 items-center justify-center rounded-xl bg-cloud-surface transition-transform group-hover:scale-110"
                    >
                        <component
                            :is="account.icon"
                            class="size-5"
                            :class="account.iconColor"
                        />
                    </div>
                    <span
                        class="text-[13px] leading-tight font-extrabold text-night-ink"
                    >
                        {{ account.label }}
                    </span>
                    <span
                        class="text-[10px] leading-none font-semibold text-charcoal/80"
                    >
                        {{ account.description }}
                    </span>
                </div>
            </button>
        </div>

        <p class="mt-2.5 text-center text-[11px] font-medium text-charcoal/70">
            Klik salah satu role untuk langsung masuk dengan akun seeder
            evaluasi.
        </p>
    </div>
</template>
