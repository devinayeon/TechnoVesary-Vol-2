<script setup lang="ts">
import { computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import type { User } from '@/types/auth';
import AppButton from '@/components/Landing/AppButton.vue';
import DetailField from './DetailField.vue';

const props = defineProps<{
    user: User;
}>();

const emit = defineEmits<{
    (e: 'cancel'): void;
    (e: 'saved'): void;
}>();

const getInitialPhone = (u: User) => {
    return u.phone_number || (u as any).nomor_telepon || '';
};

const form = useForm({
    name: props.user.name,
    email: props.user.email,
    phone_number: getInitialPhone(props.user),
});

watch(
    () => props.user,
    (newUser) => {
        if (newUser) {
            form.name = newUser.name;
            form.email = newUser.email;
            form.phone_number = getInitialPhone(newUser);
        }
    },
    { deep: true },
);

const roleLabel = computed(() => {
    const role = (props.user?.role as string) || 'customer';
    if (role === 'admin') return 'Administrator';
    if (role === 'driver') return 'Driver';
    return 'Customer';
});

const submit = () => {
    form.patch('/profile', {
        preserveScroll: true,
        onSuccess: () => {
            emit('saved');
        },
    });
};
</script>

<template>
    <div class="flex w-full flex-col gap-6">
        <!-- Page Heading Row -->
        <div class="flex items-center justify-between">
            <div class="flex flex-col gap-1">
                <h1
                    class="text-[28px] font-extrabold tracking-tight text-night-ink"
                >
                    Ubah profil
                </h1>
                <p class="text-[14px] text-charcoal">
                    Perbarui nama dan kontak akunmu. Alamat dapat diatur di
                    bagian Alamat utama.
                </p>
            </div>
        </div>

        <!-- 2 Columns Grid -->
        <div class="grid grid-cols-1 items-start gap-6 lg:grid-cols-12">
            <!-- Left: Form -->
            <form
                @submit.prevent="submit"
                class="flex flex-col gap-6 rounded-[20px] border border-border-gray bg-paper-white p-6 shadow-xs sm:p-8 lg:col-span-8"
            >
                <h3
                    class="text-[22px] leading-snug font-extrabold text-night-ink"
                >
                    Informasi pribadi
                </h3>

                <!-- Name & Phone Row -->
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <!-- Nama Lengkap -->
                    <div class="flex flex-col gap-1.5">
                        <label
                            for="edit-name"
                            class="text-[14px] font-bold text-night-ink"
                        >
                            Nama lengkap *
                        </label>
                        <input
                            id="edit-name"
                            v-model="form.name"
                            type="text"
                            required
                            maxlength="255"
                            class="h-[52px] rounded-xl border border-border-gray bg-paper-white px-4 text-[16px] text-night-ink transition placeholder:text-charcoal/50 focus:border-eco-green focus:ring-2 focus:ring-eco-green/20 focus:outline-none"
                            :class="{ 'border-rose-500': form.errors.name }"
                        />
                        <span class="text-[12px] font-semibold text-charcoal">
                            Maksimal 255 karakter.
                        </span>
                        <span
                            v-if="form.errors.name"
                            class="text-xs font-semibold text-rose-500"
                        >
                            {{ form.errors.name }}
                        </span>
                    </div>

                    <!-- Nomor Telepon -->
                    <div class="flex flex-col gap-1.5">
                        <label
                            for="edit-phone"
                            class="text-[14px] font-bold text-night-ink"
                        >
                            Nomor telepon
                        </label>
                        <input
                            id="edit-phone"
                            v-model="form.phone_number"
                            type="tel"
                            maxlength="20"
                            placeholder="0812 3456 7810"
                            class="h-[52px] rounded-xl border border-border-gray bg-paper-white px-4 text-[16px] text-night-ink transition placeholder:text-charcoal/50 focus:border-eco-green focus:ring-2 focus:ring-eco-green/20 focus:outline-none"
                            :class="{
                                'border-rose-500': form.errors.phone_number,
                            }"
                        />
                        <span class="text-[12px] font-semibold text-charcoal">
                            Opsional • maksimal 20 karakter.
                        </span>
                        <span
                            v-if="form.errors.phone_number"
                            class="text-xs font-semibold text-rose-500"
                        >
                            {{ form.errors.phone_number }}
                        </span>
                    </div>
                </div>

                <!-- Alamat Email -->
                <div class="flex flex-col gap-1.5">
                    <label
                        for="edit-email"
                        class="text-[14px] font-bold text-night-ink"
                    >
                        Alamat email *
                    </label>
                    <input
                        id="edit-email"
                        v-model="form.email"
                        type="email"
                        required
                        class="h-[52px] rounded-xl border border-border-gray bg-cloud-surface px-4 text-[16px] text-night-ink transition focus:border-eco-green focus:ring-2 focus:ring-eco-green/20 focus:outline-none"
                        :class="{ 'border-rose-500': form.errors.email }"
                    />
                    <span class="text-[12px] font-semibold text-charcoal">
                        Perubahan email akan memerlukan verifikasi ulang.
                    </span>
                    <span
                        v-if="form.errors.email"
                        class="text-xs font-semibold text-rose-500"
                    >
                        {{ form.errors.email }}
                    </span>
                </div>

                <!-- Action Buttons -->
                <div
                    class="flex flex-col-reverse items-center justify-end gap-3 border-t border-border-gray/70 pt-4 sm:flex-row"
                >
                    <AppButton
                        variant="outline"
                        size="md"
                        type="button"
                        class="w-full sm:w-auto"
                        @click="emit('cancel')"
                    >
                        Batal
                    </AppButton>
                    <AppButton
                        variant="primary"
                        size="md"
                        type="submit"
                        :disabled="form.processing"
                        class="w-full sm:w-auto"
                    >
                        {{
                            form.processing
                                ? 'Menyimpan...'
                                : 'Simpan perubahan'
                        }}
                    </AppButton>
                </div>
            </form>

            <!-- Right: Guidance Card -->
            <div
                class="flex flex-col gap-5 rounded-[20px] border border-border-gray bg-paper-white p-6 shadow-xs lg:col-span-4"
            >
                <h3
                    class="text-[21px] leading-snug font-extrabold text-night-ink"
                >
                    Data yang tetap milikmu
                </h3>
                <p class="text-[13px] leading-relaxed text-charcoal">
                    Perbarui kontak agar informasi layanan sampai ke orang yang
                    tepat. Role dan status layanan dikelola oleh ReValue.
                </p>

                <div class="flex flex-col divide-y divide-border-gray/50 pt-1">
                    <DetailField label="Pemilik akun" :value="user.name" />
                    <DetailField label="Role akun" :value="roleLabel" />
                </div>
            </div>
        </div>
    </div>
</template>
