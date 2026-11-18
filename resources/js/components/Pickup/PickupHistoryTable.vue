<script setup lang="ts">
import { Eye, Navigation, XCircle } from 'lucide-vue-next';
import AppTable from '@/components/Dashboard/AppTable.vue';
import AppPagination from '@/components/Dashboard/AppPagination.vue';
import UserAvatar from '@/components/Dashboard/UserAvatar.vue';
import PickupStatusChip from './PickupStatusChip.vue';
import type { PickupSummary, PaginatedPickups } from '@/types/pickup';
import {
    formatPickupDate,
    pickupCategoryNames,
    pickupIsCancelable,
} from '@/lib/pickup';

withDefaults(
    defineProps<{
        pickups: PaginatedPickups;
        showCustomer?: boolean;
        showDriver?: boolean;
        allowCancel?: boolean;
        allowClaim?: boolean;
        claimDisabled?: boolean;
        hasFilter?: boolean;
        emptyTitle?: string;
        emptyDescription?: string;
    }>(),
    {
        showCustomer: false,
        showDriver: true,
        allowCancel: false,
        allowClaim: false,
        claimDisabled: false,
        hasFilter: false,
        emptyTitle: 'Belum ada penjemputan',
        emptyDescription:
            'Pengajuan penjemputan sampah Anda akan tampil di sini setelah dikirim.',
    },
);

const emit = defineEmits<{
    (e: 'view', pickup: PickupSummary): void;
    (e: 'cancel', pickup: PickupSummary): void;
    (e: 'claim', pickup: PickupSummary): void;
    (e: 'reset-filter'): void;
}>();

function canCancel(pickup: PickupSummary): boolean {
    return pickupIsCancelable(pickup.status);
}

function canClaim(pickup: PickupSummary): boolean {
    return pickup.status === 'pending' && !pickup.driver_id;
}
</script>

<template>
    <AppTable
        :is-empty="pickups.data.length === 0"
        :has-filter="hasFilter"
        :empty-title="emptyTitle"
        :empty-description="emptyDescription"
        empty-filter-title="Tidak ada penjemputan yang cocok"
        empty-filter-description="Coba ubah kata kunci pencarian atau sesuaikan filter status."
        empty-action-label=""
        min-width="min-w-[900px]"
        @reset-filter="emit('reset-filter')"
    >
        <!-- Mobile Cards -->
        <template #mobile>
            <div
                v-for="pickup in pickups.data"
                :key="`m-${pickup.id}`"
                class="flex flex-col gap-3 p-4 transition-colors hover:bg-cloud-surface/30"
            >
                <div class="flex items-center justify-between gap-2">
                    <div class="flex items-center gap-2">
                        <PickupStatusChip :status="pickup.status" />
                        <span class="text-[12px] font-bold text-charcoal/70">
                            #{{ pickup.id }}
                        </span>
                    </div>
                    <span class="text-[12px] font-bold text-charcoal/70">
                        {{ formatPickupDate(pickup.created_at) }}
                    </span>
                </div>

                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0 flex-1">
                        <p
                            class="line-clamp-1 text-[13px] font-bold text-night-ink"
                        >
                            {{ pickup.address }}
                        </p>
                        <p class="mt-0.5 text-[12px] font-medium text-charcoal">
                            {{ pickupCategoryNames(pickup) }}
                        </p>
                        <p
                            v-if="showCustomer && pickup.customer?.user"
                            class="mt-1 text-[12px] font-semibold text-charcoal"
                        >
                            Customer: {{ pickup.customer.user.name }}
                        </p>
                        <p
                            v-if="showDriver"
                            class="mt-1 text-[12px] font-semibold text-charcoal"
                        >
                            Driver:
                            {{
                                pickup.driver?.user?.name ?? 'Belum ditugaskan'
                            }}
                        </p>
                    </div>

                    <div class="flex shrink-0 items-center gap-1.5">
                        <button
                            v-if="
                                allowClaim && canClaim(pickup) && !claimDisabled
                            "
                            type="button"
                            class="rounded-lg border-2 border-b-[3px] border-eco-green-dark bg-eco-green p-2 text-night-ink transition-all hover:brightness-105 active:translate-y-0.5 active:border-b-2"
                            title="Ambil Tugas"
                            @click="emit('claim', pickup)"
                        >
                            <Navigation class="size-4" />
                        </button>
                        <button
                            v-if="allowCancel && canCancel(pickup)"
                            type="button"
                            class="rounded-lg border-2 border-b-[3px] border-border-gray p-2 text-red-500 transition-all hover:bg-red-50 active:translate-y-0.5 active:border-b-2"
                            title="Batalkan Pengajuan"
                            @click="emit('cancel', pickup)"
                        >
                            <XCircle class="size-4" />
                        </button>
                        <button
                            type="button"
                            class="rounded-lg border-2 border-b-[3px] border-border-gray p-2 text-charcoal transition-all hover:bg-cloud-surface hover:text-night-ink active:translate-y-0.5 active:border-b-2"
                            title="Lihat Detail"
                            @click="emit('view', pickup)"
                        >
                            <Eye class="size-4" />
                        </button>
                    </div>
                </div>
            </div>
        </template>

        <!-- Desktop Header -->
        <template #header>
            <th class="w-16 px-4 py-3.5 text-left">ID</th>
            <th v-if="showCustomer" class="px-4 py-3.5 text-left">Customer</th>
            <th v-if="showDriver" class="px-4 py-3.5 text-left">Driver</th>
            <th class="px-4 py-3.5 text-left">Jenis Sampah</th>
            <th class="w-44 px-4 py-3.5 text-left">Status</th>
            <th class="w-36 px-4 py-3.5 text-left">Tanggal</th>
            <th class="w-32 px-4 py-3.5 text-center">Aksi</th>
        </template>

        <!-- Desktop Body -->
        <template #body>
            <tr
                v-for="pickup in pickups.data"
                :key="pickup.id"
                class="transition-colors hover:bg-cloud-surface/50"
            >
                <td class="px-4 py-4">
                    <span class="text-[13px] font-bold text-charcoal/60">
                        #{{ pickup.id }}
                    </span>
                </td>

                <td v-if="showCustomer" class="px-4 py-4">
                    <div
                        v-if="pickup.customer?.user"
                        class="flex items-center gap-2.5"
                    >
                        <UserAvatar
                            :photo="pickup.customer.user.photo"
                            :role="pickup.customer.user.role"
                            :name="pickup.customer.user.name"
                            size="sm"
                        />
                        <div class="min-w-0">
                            <p
                                class="max-w-[180px] truncate text-[14px] font-bold text-night-ink"
                            >
                                {{ pickup.customer.user.name }}
                            </p>
                            <p
                                class="max-w-[180px] truncate text-[12px] text-charcoal/70"
                            >
                                {{ pickup.customer.user.email }}
                            </p>
                        </div>
                    </div>
                    <span v-else class="text-[13px] text-charcoal/50 italic">
                        -
                    </span>
                </td>

                <td v-if="showDriver" class="px-4 py-4">
                    <div
                        v-if="pickup.driver?.user"
                        class="flex items-center gap-2.5"
                    >
                        <UserAvatar
                            :photo="pickup.driver.user.photo"
                            :role="pickup.driver.user.role"
                            :name="pickup.driver.user.name"
                            size="sm"
                        />
                        <div class="min-w-0">
                            <p
                                class="max-w-[160px] truncate text-[14px] font-bold text-night-ink"
                            >
                                {{ pickup.driver.user.name }}
                            </p>
                            <p
                                class="max-w-[160px] truncate text-[12px] text-charcoal/70"
                            >
                                {{ pickup.driver.user.email }}
                            </p>
                        </div>
                    </div>
                    <span v-else class="text-[13px] text-charcoal/50 italic">
                        Belum ditugaskan
                    </span>
                </td>

                <td class="px-4 py-4">
                    <p
                        class="max-w-[220px] truncate text-[13px] font-semibold text-night-ink"
                        :title="pickupCategoryNames(pickup)"
                    >
                        {{ pickupCategoryNames(pickup) }}
                    </p>
                    <p
                        class="max-w-[220px] truncate text-[12px] text-charcoal/70"
                        :title="pickup.address"
                    >
                        {{ pickup.address }}
                    </p>
                </td>

                <td class="px-4 py-4">
                    <PickupStatusChip :status="pickup.status" />
                </td>

                <td class="px-4 py-4">
                    <span class="text-[13px] font-semibold text-charcoal">
                        {{ formatPickupDate(pickup.created_at) }}
                    </span>
                </td>

                <td class="px-4 py-4">
                    <div class="flex items-center justify-center gap-1">
                        <button
                            v-if="
                                allowClaim && canClaim(pickup) && !claimDisabled
                            "
                            type="button"
                            class="rounded-lg border-2 border-b-[3px] border-eco-green-dark bg-eco-green p-2 text-night-ink transition-all hover:brightness-105 active:translate-y-0.5 active:border-b-2"
                            title="Ambil Tugas"
                            @click="emit('claim', pickup)"
                        >
                            <Navigation class="size-4" />
                        </button>
                        <button
                            v-if="allowCancel && canCancel(pickup)"
                            type="button"
                            class="rounded-lg p-2 text-charcoal transition-colors hover:bg-red-50 hover:text-red-600"
                            title="Batalkan Pengajuan"
                            @click="emit('cancel', pickup)"
                        >
                            <XCircle class="size-4" />
                        </button>
                        <button
                            type="button"
                            class="rounded-lg border-2 border-b-[3px] border-border-gray p-2 text-charcoal transition-all hover:bg-cloud-surface hover:text-night-ink active:translate-y-0.5 active:border-b-2"
                            title="Lihat Detail"
                            @click="emit('view', pickup)"
                        >
                            <Eye class="size-4" />
                        </button>
                    </div>
                </td>
            </tr>
        </template>

        <!-- Pagination -->
        <template #pagination>
            <AppPagination :pagination="pickups" item-label="penjemputan" />
        </template>
    </AppTable>
</template>
