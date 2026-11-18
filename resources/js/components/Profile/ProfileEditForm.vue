<script setup lang="ts">
import { computed, watch } from "vue";
import { useForm } from "@inertiajs/vue3";
import type { User } from "@/types/auth";
import AppButton from "@/components/Landing/AppButton.vue";
import DetailField from "./DetailField.vue";

const props = defineProps<{
    user: User;
}>();

const emit = defineEmits<{
    (e: "cancel"): void;
    (e: "saved"): void;
}>();

const getInitialPhone = (u: User) => {
    return u.phone_number || (u as any).nomor_telepon || "";
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
    { deep: true }
);

const roleLabel = computed(() => {
    const role = (props.user?.role as string) || "customer";
    if (role === "admin") return "Administrator";
    if (role === "driver") return "Driver";
    return "Customer";
});

const submit = () => {
    form.patch("/profile", {
        preserveScroll: true,
        onSuccess: () => {
            emit("saved");
        },
    });
};
</script>

<template>
    <div class="flex flex-col gap-6 w-full">
        <!-- Page Heading Row -->
        <div class="flex items-center justify-between">
            <div class="flex flex-col gap-1">
                <h1 class="text-[28px] font-extrabold text-night-ink tracking-tight">
                    Ubah profil
                </h1>
                <p class="text-[14px] text-charcoal">
                    Perbarui nama dan kontak akunmu. Alamat dapat diatur di bagian Alamat utama.
                </p>
            </div>
        </div>

        <!-- 2 Columns Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            <!-- Left: Form -->
            <form
                @submit.prevent="submit"
                class="lg:col-span-8 bg-paper-white border border-border-gray rounded-[20px] p-6 sm:p-8 flex flex-col gap-6 shadow-xs"
            >
                <h3 class="text-[22px] font-extrabold text-night-ink leading-snug">
                    Informasi pribadi
                </h3>

                <!-- Name & Phone Row -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
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
                            class="h-[52px] px-4 rounded-xl border border-border-gray bg-paper-white text-[16px] text-night-ink placeholder:text-charcoal/50 focus:outline-none focus:border-eco-green focus:ring-2 focus:ring-eco-green/20 transition"
                            :class="{ 'border-rose-500': form.errors.name }"
                        />
                        <span class="text-[12px] text-charcoal font-semibold">
                            Maksimal 255 karakter.
                        </span>
                        <span v-if="form.errors.name" class="text-xs text-rose-500 font-semibold">
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
                            class="h-[52px] px-4 rounded-xl border border-border-gray bg-paper-white text-[16px] text-night-ink placeholder:text-charcoal/50 focus:outline-none focus:border-eco-green focus:ring-2 focus:ring-eco-green/20 transition"
                            :class="{ 'border-rose-500': form.errors.phone_number }"
                        />
                        <span class="text-[12px] text-charcoal font-semibold">
                            Opsional • maksimal 20 karakter.
                        </span>
                        <span v-if="form.errors.phone_number" class="text-xs text-rose-500 font-semibold">
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
                        class="h-[52px] px-4 rounded-xl border border-border-gray bg-cloud-surface text-[16px] text-night-ink focus:outline-none focus:border-eco-green focus:ring-2 focus:ring-eco-green/20 transition"
                        :class="{ 'border-rose-500': form.errors.email }"
                    />
                    <span class="text-[12px] text-charcoal font-semibold">
                        Perubahan email akan memerlukan verifikasi ulang.
                    </span>
                    <span v-if="form.errors.email" class="text-xs text-rose-500 font-semibold">
                        {{ form.errors.email }}
                    </span>
                </div>

                <!-- Action Buttons -->
                <div class="pt-4 flex flex-col-reverse sm:flex-row items-center justify-end gap-3 border-t border-border-gray/70">
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
                        {{ form.processing ? "Menyimpan..." : "Simpan perubahan" }}
                    </AppButton>
                </div>
            </form>

            <!-- Right: Guidance Card -->
            <div
                class="lg:col-span-4 bg-paper-white border border-border-gray rounded-[20px] p-6 flex flex-col gap-5 shadow-xs"
            >
                <h3 class="text-[21px] font-extrabold text-night-ink leading-snug">
                    Data yang tetap milikmu
                </h3>
                <p class="text-[13px] text-charcoal leading-relaxed">
                    Perbarui kontak agar informasi layanan sampai ke orang yang tepat. Role dan status layanan dikelola oleh ReValue.
                </p>

                <div class="flex flex-col divide-y divide-border-gray/50 pt-1">
                    <DetailField label="Pemilik akun" :value="user.name" />
                    <DetailField label="Role akun" :value="roleLabel" />
                </div>
            </div>
        </div>
    </div>
</template>
