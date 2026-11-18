<script setup lang="ts">
import { computed } from "vue";
import { X, MapPin, Package, Truck, User, Scale } from "lucide-vue-next";
import StatusChip from "@/components/Dashboard/StatusChip.vue";
import UserAvatar from "@/components/Dashboard/UserAvatar.vue";

export interface WasteDepositDetailItem {
    id: number;
    waste_deposit_id: number;
    waste_category_id: number;
    points_per_kg: number;
    weight: number;
    subtotal_points: number;
    waste_category?: {
        id: number;
        name: string;
    } | null;
}

export interface PickupDetailData {
    id: number;
    status: string;
    address: string;
    notes: string | null;
    latitude: string | null;
    longitude: string | null;
    photo: string | null;
    pickup_proof_photo: string | null;
    pickup_at: string | null;
    created_at: string;
    updated_at: string;
    customer?: {
        id: number;
        user?: {
            id: number;
            name: string;
            email: string;
            photo: string | null;
            role: string;
        } | null;
    } | null;
    driver?: {
        id: number;
        user?: {
            id: number;
            name: string;
            email: string;
            photo: string | null;
            role: string;
        } | null;
    } | null;
    details?: WasteDepositDetailItem[];
}

const props = defineProps<{
    show: boolean;
    pickup: PickupDetailData | null;
}>();

const emit = defineEmits<{
    (e: "close"): void;
}>();

const statusTone = computed(() => {
    if (!props.pickup) return "Neutral";
    const map: Record<string, "Green" | "Amber" | "Blue" | "Neutral"> = {
        pending: "Amber",
        on_the_way: "Blue",
        picked_up: "Amber",
        completed: "Green",
        canceled: "Neutral",
    };
    return map[props.pickup.status] ?? "Neutral";
});

const statusLabel = computed(() => {
    if (!props.pickup) return "";
    const map: Record<string, string> = {
        pending: "Menunggu Penugasan Driver",
        on_the_way: "Driver Menuju Lokasi",
        picked_up: "Sampah Diambil / Ditimbang",
        completed: "Selesai & Poin Diterbitkan",
        canceled: "Penjemputan Dibatalkan",
    };
    return map[props.pickup.status] ?? props.pickup.status;
});

const totalPoints = computed(() => {
    if (!props.pickup?.details) return 0;
    return props.pickup.details.reduce((sum, d) => sum + (d.subtotal_points ?? 0), 0);
});

const formatDate = (dateStr: string | null | undefined) => {
    if (!dateStr) return "-";
    return new Date(dateStr).toLocaleDateString("id-ID", {
        day: "numeric",
        month: "long",
        year: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    });
};

const formatNumber = (n: number) =>
    n.toLocaleString("id-ID");
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="show && pickup"
                class="fixed inset-0 z-50 flex items-center justify-center p-4"
                @click.self="emit('close')"
            >
                <!-- Backdrop -->
                <div class="absolute inset-0 bg-night-ink/40 backdrop-blur-sm" @click="emit('close')" />

                <!-- Modal Card -->
                <div
                    class="relative z-10 w-full max-w-2xl max-h-[90vh] overflow-y-auto bg-cloud-surface rounded-[24px] border-2 border-b-[6px] border-border-gray shadow-2xl"
                >
                    <!-- Header -->
                    <div class="flex items-start justify-between gap-4 p-6 border-b border-border-gray/50">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1">
                                <Truck class="size-5 text-eco-green shrink-0" />
                                <h2 class="text-lg font-black text-night-ink truncate">
                                    Penjemputan #{{ pickup.id }}
                                </h2>
                            </div>
                            <div class="flex flex-wrap items-center gap-2">
                                <StatusChip :tone="statusTone" :label="statusLabel" />
                                <span class="text-sm text-muted-text">{{ formatDate(pickup.created_at) }}</span>
                            </div>
                        </div>
                        <button
                            @click="emit('close')"
                            class="shrink-0 p-2 rounded-xl border-2 border-b-[3px] border-border-gray text-muted-text hover:text-night-ink hover:bg-cloud-hover active:translate-y-0.5 active:border-b-2 transition-all"
                        >
                            <X class="size-4" />
                        </button>
                    </div>

                    <!-- Body -->
                    <div class="p-6 space-y-6">
                        <!-- Nasabah -->
                        <div>
                            <h3 class="text-xs font-bold text-muted-text uppercase tracking-wider mb-3 flex items-center gap-1.5">
                                <User class="size-3.5" /> Nasabah
                            </h3>
                            <div v-if="pickup.customer?.user" class="flex items-center gap-3 p-3 rounded-xl bg-cloud-hover border border-border-gray/50">
                                <UserAvatar :user="pickup.customer.user" size="sm" />
                                <div>
                                    <p class="font-bold text-night-ink text-sm">{{ pickup.customer.user.name }}</p>
                                    <p class="text-xs text-muted-text">{{ pickup.customer.user.email }}</p>
                                </div>
                            </div>
                            <p v-else class="text-sm text-muted-text italic">Data nasabah tidak tersedia</p>
                        </div>

                        <!-- Driver -->
                        <div>
                            <h3 class="text-xs font-bold text-muted-text uppercase tracking-wider mb-3 flex items-center gap-1.5">
                                <Truck class="size-3.5" /> Driver
                            </h3>
                            <div v-if="pickup.driver?.user" class="flex items-center gap-3 p-3 rounded-xl bg-cloud-hover border border-border-gray/50">
                                <UserAvatar :user="pickup.driver.user" size="sm" />
                                <div>
                                    <p class="font-bold text-night-ink text-sm">{{ pickup.driver.user.name }}</p>
                                    <p class="text-xs text-muted-text">{{ pickup.driver.user.email }}</p>
                                </div>
                            </div>
                            <p v-else class="text-sm text-muted-text italic px-3 py-2 rounded-xl bg-cloud-hover border border-border-gray/50">
                                Belum ditugaskan driver
                            </p>
                        </div>

                        <!-- Alamat & Catatan -->
                        <div>
                            <h3 class="text-xs font-bold text-muted-text uppercase tracking-wider mb-3 flex items-center gap-1.5">
                                <MapPin class="size-3.5" /> Alamat Penjemputan
                            </h3>
                            <p class="text-sm text-night-ink bg-cloud-hover border border-border-gray/50 rounded-xl px-4 py-3 leading-relaxed">
                                {{ pickup.address || "-" }}
                            </p>
                            <div v-if="pickup.notes" class="mt-2 text-sm text-muted-text bg-cloud-hover border border-border-gray/50 rounded-xl px-4 py-3">
                                <span class="font-semibold">Catatan:</span> {{ pickup.notes }}
                            </div>
                        </div>

                        <!-- Waktu Jemput -->
                        <div v-if="pickup.pickup_at">
                            <h3 class="text-xs font-bold text-muted-text uppercase tracking-wider mb-2">Waktu Penjemputan</h3>
                            <p class="text-sm text-night-ink font-semibold">{{ formatDate(pickup.pickup_at) }}</p>
                        </div>

                        <!-- Detail Sampah -->
                        <div v-if="pickup.details && pickup.details.length > 0">
                            <h3 class="text-xs font-bold text-muted-text uppercase tracking-wider mb-3 flex items-center gap-1.5">
                                <Scale class="size-3.5" /> Detail Sampah
                            </h3>
                            <div class="border border-border-gray/60 rounded-xl overflow-hidden">
                                <table class="w-full text-sm">
                                    <thead class="bg-cloud-hover border-b border-border-gray/60">
                                        <tr>
                                            <th class="text-left px-4 py-2.5 text-xs font-bold text-muted-text">Kategori</th>
                                            <th class="text-right px-4 py-2.5 text-xs font-bold text-muted-text">Poin/kg</th>
                                            <th class="text-right px-4 py-2.5 text-xs font-bold text-muted-text">Berat (kg)</th>
                                            <th class="text-right px-4 py-2.5 text-xs font-bold text-muted-text">Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-border-gray/40">
                                        <tr v-for="detail in pickup.details" :key="detail.id">
                                            <td class="px-4 py-3 font-medium text-night-ink">{{ detail.waste_category?.name ?? "-" }}</td>
                                            <td class="px-4 py-3 text-right text-muted-text">{{ formatNumber(detail.points_per_kg) }}</td>
                                            <td class="px-4 py-3 text-right text-muted-text">{{ detail.weight }}</td>
                                            <td class="px-4 py-3 text-right font-bold text-eco-green">{{ formatNumber(detail.subtotal_points) }} poin</td>
                                        </tr>
                                    </tbody>
                                    <tfoot class="bg-cloud-hover border-t-2 border-border-gray/60">
                                        <tr>
                                            <td colspan="3" class="px-4 py-3 text-sm font-black text-night-ink text-right">Total Poin</td>
                                            <td class="px-4 py-3 text-right font-black text-eco-green">{{ formatNumber(totalPoints) }} poin</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                        <div v-else>
                            <h3 class="text-xs font-bold text-muted-text uppercase tracking-wider mb-2 flex items-center gap-1.5">
                                <Scale class="size-3.5" /> Detail Sampah
                            </h3>
                            <p class="text-sm text-muted-text italic">Belum ada detail sampah tercatat.</p>
                        </div>

                        <!-- Foto -->
                        <div v-if="pickup.photo || pickup.pickup_proof_photo">
                            <h3 class="text-xs font-bold text-muted-text uppercase tracking-wider mb-3 flex items-center gap-1.5">
                                <Package class="size-3.5" /> Foto
                            </h3>
                            <div class="grid grid-cols-2 gap-3">
                                <div v-if="pickup.photo">
                                    <p class="text-xs text-muted-text mb-1 font-semibold">Foto Sampah</p>
                                    <img
                                        :src="pickup.photo"
                                        alt="Foto Sampah"
                                        class="w-full aspect-square object-cover rounded-xl border-2 border-border-gray"
                                    />
                                </div>
                                <div v-if="pickup.pickup_proof_photo">
                                    <p class="text-xs text-muted-text mb-1 font-semibold">Bukti Timbang</p>
                                    <img
                                        :src="pickup.pickup_proof_photo"
                                        alt="Bukti Timbang"
                                        class="w-full aspect-square object-cover rounded-xl border-2 border-border-gray"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="p-6 border-t border-border-gray/50 flex justify-end">
                        <button
                            @click="emit('close')"
                            class="px-6 py-2.5 text-sm font-bold text-night-ink bg-cloud-surface border-2 border-b-[4px] border-border-gray rounded-xl hover:bg-cloud-hover active:translate-y-0.5 active:border-b-2 transition-all"
                        >
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
