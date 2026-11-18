<script setup lang="ts">
import { watch } from "vue";
import { useForm } from "@inertiajs/vue3";
import { X, Sparkles, Coins, Info, ShieldCheck } from "lucide-vue-next";

export interface SubscriptionData {
    id: number;
    name: string;
    price: number;
    description: string | null;
}

const props = defineProps<{
    show: boolean;
    subscription: SubscriptionData | null;
}>();

const emit = defineEmits<{
    (e: "close"): void;
}>();

const form = useForm({
    price: 25000,
    description: "",
});

watch(
    () => [props.show, props.subscription],
    ([isOpen]) => {
        if (isOpen && props.subscription) {
            form.clearErrors();
            form.price = props.subscription.price || 25000;
            form.description = props.subscription.description || "";
        }
    },
    { immediate: true },
);

const handleClose = () => {
    if (form.processing) return;
    form.clearErrors();
    emit("close");
};

const submit = () => {
    if (!props.subscription) return;
    form.put(`/admin/subscriptions/${props.subscription.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            emit("close");
        },
    });
};

const formatNumber = (num: number): string => {
    return Number(num).toLocaleString("id-ID");
};
</script>

<template>
    <teleport to="body">
        <transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="show && subscription"
                class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-night-ink/50 backdrop-blur-xs overflow-y-auto"
                @click.self="handleClose"
            >
                <div
                    class="w-full max-w-[540px] bg-paper-white border-2 border-border-gray rounded-[24px] p-6 sm:p-8 shadow-2xl relative my-8 animate-in fade-in zoom-in-95 duration-150"
                >
                    <!-- Header -->
                    <div
                        class="flex items-start justify-between gap-4 mb-5 pb-4 border-b border-border-gray/70"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                class="size-11 rounded-2xl bg-sprout-tint text-eco-green-dark border-2 border-eco-green/30 flex items-center justify-center shrink-0 shadow-2xs"
                            >
                                <Sparkles class="size-6 text-eco-green-dark" />
                            </div>
                            <div>
                                <h3
                                    class="text-[20px] sm:text-[22px] font-extrabold text-night-ink leading-tight"
                                >
                                    Ubah Harga Subscription
                                </h3>
                                <p
                                    class="text-[13px] font-medium text-charcoal mt-0.5"
                                >
                                    Perbarui tarif langganan yang ditawarkan
                                    kepada customer.
                                </p>
                            </div>
                        </div>
                        <button
                            type="button"
                            class="p-2 rounded-xl text-charcoal hover:bg-cloud-surface hover:text-night-ink transition-colors cursor-pointer"
                            @click="handleClose"
                            :disabled="form.processing"
                            aria-label="Tutup Modal"
                        >
                            <X class="size-5" />
                        </button>
                    </div>

                    <!-- Current Plan Summary Box -->
                    <div
                        class="grid grid-cols-2 gap-3 p-3.5 mb-5 rounded-2xl bg-cloud-surface border border-border-gray"
                    >
                        <div>
                            <span
                                class="text-[11px] font-bold text-charcoal uppercase tracking-wider block"
                            >
                                Paket Langganan
                            </span>
                            <span
                                class="font-extrabold text-[14px] text-night-ink truncate block mt-0.5"
                            >
                                {{ subscription.name }}
                            </span>
                        </div>
                        <div>
                            <span
                                class="text-[11px] font-bold text-charcoal uppercase tracking-wider block"
                            >
                                Harga Saat Ini
                            </span>
                            <span
                                class="font-extrabold text-[14px] text-night-ink block mt-0.5"
                            >
                                Rp {{ formatNumber(subscription.price) }}
                                <span
                                    class="text-[11px] font-bold text-charcoal"
                                    >/ bln</span
                                >
                            </span>
                        </div>
                    </div>

                    <!-- Form Body -->
                    <form @submit.prevent="submit" class="space-y-4">
                        <!-- Input: Harga Baru -->
                        <div>
                            <label
                                class="block text-[14px] font-extrabold text-night-ink mb-1.5 flex items-center gap-2"
                            >
                                <Coins class="size-4 text-sunlight-gold" />
                                <span>Harga Baru Langganan (Rp)</span>
                                <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <div
                                    class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-charcoal font-black text-[14px]"
                                >
                                    Rp
                                </div>

                                <!-- 2. Perbaikan pada input: Ubah pl-11 menjadi pl-12 (atau pl-14) -->
                                <input
                                    v-model.number="form.price"
                                    type="number"
                                    min="1000"
                                    step="500"
                                    placeholder="Contoh: 30000"
                                    class="w-full h-[48px] pl-11 pr-24 rounded-xl border-2 font-extrabold text-[16px] text-night-ink transition-all outline-none"
                                    :class="[
                                        form.errors.price
                                            ? 'border-red-400 bg-red-50/30 focus:border-red-500 focus:ring-4 focus:ring-red-100'
                                            : 'border-border-gray bg-paper-white focus:border-eco-green focus:ring-4 focus:ring-eco-green/10',
                                    ]"
                                    required
                                />
                                <div
                                    class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-[13px] font-bold text-charcoal"
                                >
                                    / 30 hari
                                </div>
                            </div>
                            <p
                                v-if="form.errors.price"
                                class="mt-1.5 text-[12px] font-bold text-red-600 flex items-center gap-1"
                            >
                                <span>•</span> {{ form.errors.price }}
                            </p>
                        </div>

                        <!-- Input: Deskripsi / Catatan (Opsional) -->
                        <div>
                            <label
                                class="block text-[14px] font-extrabold text-night-ink mb-1.5"
                            >
                                Deskripsi Layanan
                            </label>
                            <textarea
                                v-model="form.description"
                                rows="2"
                                placeholder="Jelaskan benefit paket langganan..."
                                class="w-full p-3 rounded-xl border-2 font-medium text-[13px] text-night-ink placeholder:text-charcoal/50 transition-all outline-none resize-none"
                                :class="[
                                    form.errors.description
                                        ? 'border-red-400 bg-red-50/30 focus:border-red-500 focus:ring-4 focus:ring-red-100'
                                        : 'border-border-gray bg-paper-white focus:border-eco-green focus:ring-4 focus:ring-eco-green/10',
                                ]"
                            ></textarea>
                            <p
                                v-if="form.errors.description"
                                class="mt-1 text-[12px] font-bold text-red-600"
                            >
                                {{ form.errors.description }}
                            </p>
                        </div>

                        <!-- Context Notice Box (from Figma 485:4610) -->
                        <div
                            class="p-4 rounded-xl bg-sprout-tint/50 border border-eco-green/30 text-[12px] text-night-ink leading-relaxed space-y-1.5"
                        >
                            <div
                                class="flex items-center gap-2 font-black text-night-ink"
                            >
                                <ShieldCheck
                                    class="size-4 text-eco-green-dark shrink-0"
                                />
                                <span>Ketentuan Penerapan Harga Baru</span>
                            </div>
                            <p class="text-charcoal font-medium">
                                • Perubahan harga ini hanya berlaku untuk
                                transaksi baru.<br />
                                • Tagihan yang sudah dibuat tetap menggunakan
                                harga sebelumnya.<br />
                                • Masa aktif langganan customer yang sudah
                                berjalan tidak berubah.
                            </p>
                        </div>

                        <!-- Footer Actions -->
                        <div
                            class="flex items-center justify-end gap-3 pt-3 border-t border-border-gray/70"
                        >
                            <button
                                type="button"
                                class="px-5 h-[44px] rounded-xl border-2 border-b-[4px] border-border-gray bg-paper-white font-extrabold text-[14px] text-night-ink hover:bg-cloud-surface active:translate-y-0.5 active:border-b-2 transition-all cursor-pointer disabled:opacity-60 disabled:cursor-not-allowed"
                                @click="handleClose"
                                :disabled="form.processing"
                            >
                                Batal
                            </button>
                            <button
                                type="submit"
                                class="px-6 h-[44px] rounded-xl border-2 border-b-[4px] border-eco-green-dark bg-eco-green font-extrabold text-[14px] text-white hover:brightness-105 active:translate-y-0.5 active:border-b-2 transition-all cursor-pointer disabled:opacity-60 disabled:cursor-not-allowed flex items-center justify-center gap-2 shadow-2xs"
                                :disabled="form.processing"
                            >
                                <span v-if="form.processing">Menyimpan...</span>
                                <span v-else>Simpan Perubahan</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </transition>
    </teleport>
</template>
