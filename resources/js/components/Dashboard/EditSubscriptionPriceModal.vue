<script setup lang="ts">
import { watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { X, Sparkles, Coins, Info, ShieldCheck } from 'lucide-vue-next';

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
    (e: 'close'): void;
}>();

const form = useForm({
    price: 25000,
    description: '',
});

watch(
    () => [props.show, props.subscription],
    ([isOpen]) => {
        if (isOpen && props.subscription) {
            form.clearErrors();
            form.price = props.subscription.price || 25000;
            form.description = props.subscription.description || '';
        }
    },
    { immediate: true },
);

const handleClose = () => {
    if (form.processing) return;
    form.clearErrors();
    emit('close');
};

const submit = () => {
    if (!props.subscription) return;
    form.put(`/admin/subscriptions/${props.subscription.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            emit('close');
        },
    });
};

const formatNumber = (num: number): string => {
    return Number(num).toLocaleString('id-ID');
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
                class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-night-ink/50 p-4 backdrop-blur-xs"
                @click.self="handleClose"
            >
                <div
                    class="animate-in fade-in zoom-in-95 relative my-8 w-full max-w-[540px] rounded-[24px] border-2 border-border-gray bg-paper-white p-6 shadow-2xl duration-150 sm:p-8"
                >
                    <!-- Header -->
                    <div
                        class="mb-5 flex items-start justify-between gap-4 border-b border-border-gray/70 pb-4"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                class="flex size-11 shrink-0 items-center justify-center rounded-2xl border-2 border-eco-green/30 bg-sprout-tint text-eco-green-dark shadow-2xs"
                            >
                                <Sparkles class="size-6 text-eco-green-dark" />
                            </div>
                            <div>
                                <h3
                                    class="text-[20px] leading-tight font-extrabold text-night-ink sm:text-[22px]"
                                >
                                    Ubah Harga Subscription
                                </h3>
                                <p
                                    class="mt-0.5 text-[13px] font-medium text-charcoal"
                                >
                                    Perbarui tarif langganan yang ditawarkan
                                    kepada customer.
                                </p>
                            </div>
                        </div>
                        <button
                            type="button"
                            class="cursor-pointer rounded-xl p-2 text-charcoal transition-colors hover:bg-cloud-surface hover:text-night-ink"
                            @click="handleClose"
                            :disabled="form.processing"
                            aria-label="Tutup Modal"
                        >
                            <X class="size-5" />
                        </button>
                    </div>

                    <!-- Current Plan Summary Box -->
                    <div
                        class="mb-5 grid grid-cols-2 gap-3 rounded-2xl border border-border-gray bg-cloud-surface p-3.5"
                    >
                        <div>
                            <span
                                class="block text-[11px] font-bold tracking-wider text-charcoal uppercase"
                            >
                                Paket Langganan
                            </span>
                            <span
                                class="mt-0.5 block truncate text-[14px] font-extrabold text-night-ink"
                            >
                                {{ subscription.name }}
                            </span>
                        </div>
                        <div>
                            <span
                                class="block text-[11px] font-bold tracking-wider text-charcoal uppercase"
                            >
                                Harga Saat Ini
                            </span>
                            <span
                                class="mt-0.5 block text-[14px] font-extrabold text-night-ink"
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
                                class="mb-1.5 block flex items-center gap-2 text-[14px] font-extrabold text-night-ink"
                            >
                                <Coins class="size-4 text-sunlight-gold" />
                                <span>Harga Baru Langganan (Rp)</span>
                                <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <div
                                    class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-[14px] font-black text-charcoal"
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
                                    class="h-[48px] w-full rounded-xl border-2 pr-24 pl-11 text-[16px] font-extrabold text-night-ink transition-all outline-none"
                                    :class="[
                                        form.errors.price
                                            ? 'border-red-400 bg-red-50/30 focus:border-red-500 focus:ring-4 focus:ring-red-100'
                                            : 'border-border-gray bg-paper-white focus:border-eco-green focus:ring-4 focus:ring-eco-green/10',
                                    ]"
                                    required
                                />
                                <div
                                    class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4 text-[13px] font-bold text-charcoal"
                                >
                                    / 30 hari
                                </div>
                            </div>
                            <p
                                v-if="form.errors.price"
                                class="mt-1.5 flex items-center gap-1 text-[12px] font-bold text-red-600"
                            >
                                <span>•</span> {{ form.errors.price }}
                            </p>
                        </div>

                        <!-- Input: Deskripsi / Catatan (Opsional) -->
                        <div>
                            <label
                                class="mb-1.5 block text-[14px] font-extrabold text-night-ink"
                            >
                                Deskripsi Layanan
                            </label>
                            <textarea
                                v-model="form.description"
                                rows="2"
                                placeholder="Jelaskan benefit paket langganan..."
                                class="w-full resize-none rounded-xl border-2 p-3 text-[13px] font-medium text-night-ink transition-all outline-none placeholder:text-charcoal/50"
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
                            class="space-y-1.5 rounded-xl border border-eco-green/30 bg-sprout-tint/50 p-4 text-[12px] leading-relaxed text-night-ink"
                        >
                            <div
                                class="flex items-center gap-2 font-black text-night-ink"
                            >
                                <ShieldCheck
                                    class="size-4 shrink-0 text-eco-green-dark"
                                />
                                <span>Ketentuan Penerapan Harga Baru</span>
                            </div>
                            <p class="font-medium text-charcoal">
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
                            class="flex items-center justify-end gap-3 border-t border-border-gray/70 pt-3"
                        >
                            <button
                                type="button"
                                class="h-[44px] cursor-pointer rounded-xl border-2 border-b-[4px] border-border-gray bg-paper-white px-5 text-[14px] font-extrabold text-night-ink transition-all hover:bg-cloud-surface active:translate-y-0.5 active:border-b-2 disabled:cursor-not-allowed disabled:opacity-60"
                                @click="handleClose"
                                :disabled="form.processing"
                            >
                                Batal
                            </button>
                            <button
                                type="submit"
                                class="flex h-[44px] cursor-pointer items-center justify-center gap-2 rounded-xl border-2 border-b-[4px] border-eco-green-dark bg-eco-green px-6 text-[14px] font-extrabold text-white shadow-2xs transition-all hover:brightness-105 active:translate-y-0.5 active:border-b-2 disabled:cursor-not-allowed disabled:opacity-60"
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
