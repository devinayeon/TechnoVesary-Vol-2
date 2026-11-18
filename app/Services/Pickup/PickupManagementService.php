<?php

namespace App\Services\Pickup;

use App\Enums\PickupStatus;
use App\Enums\PointType;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\PointHistory;
use App\Models\WasteCategory;
use App\Models\WasteDeposit;
use App\Models\WasteDepositDetail;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PickupManagementService
{
    /**
     * Daftar penjemputan milik satu customer (riwayat).
     *
     * @param  array<string, mixed>  $filters
     */
    public function getCustomerPickups(Customer $customer, array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return $this->baseQuery()
            ->forCustomer($customer)
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($filters['search'] ?? null, function ($q, $search) {
                $search = trim((string) $search);

                $q->where(function ($inner) use ($search) {
                    $inner->where('address', 'like', "%{$search}%")
                        ->orWhere('notes', 'like', "%{$search}%");

                    if (ctype_digit($search)) {
                        $inner->orWhere('id', (int) $search);
                    }
                });
            })
            ->latest('created_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Detail penjemputan milik customer tertentu (anti-IDOR).
     */
    public function findCustomerPickup(Customer $customer, int $id): WasteDeposit
    {
        return $this->detailQuery()
            ->forCustomer($customer)
            ->findOrFail($id);
    }

    /**
     * Buat pengajuan penjemputan baru (status pending, belum ditimbang).
     *
     * @param  array<string, mixed>  $data
     */
    public function createPickup(Customer $customer, array $data, UploadedFile $photo): WasteDeposit
    {
        return DB::transaction(function () use ($customer, $data, $photo) {
            $photoPath = $photo->store('pickups/requests', 'public');

            $pickup = WasteDeposit::create([
                'customer_id' => $customer->id,
                'status' => PickupStatus::PENDING,
                'address' => $data['address'],
                'notes' => $data['notes'] ?? null,
                'latitude' => $data['latitude'],
                'longitude' => $data['longitude'],
                'photo' => $photoPath,
            ]);

            $categories = collect($data['categories'])
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values();

            $wasteCategories = WasteCategory::query()
                ->whereIn('id', $categories)
                ->get()
                ->keyBy('id');

            if ($wasteCategories->count() !== $categories->count()) {
                throw ValidationException::withMessages([
                    'categories' => 'Sebagian kategori sampah tidak tersedia. Silakan pilih ulang.',
                ]);
            }

            foreach ($categories as $categoryId) {
                $category = $wasteCategories->get($categoryId);

                WasteDepositDetail::create([
                    'waste_deposit_id' => $pickup->id,
                    'waste_category_id' => $category->id,
                    'points_per_kg' => (int) $category->points_per_kg,
                    'weight' => null,
                    'subtotal_points' => null,
                ]);
            }

            return $pickup->load('details.wasteCategory');
        });
    }

    /**
     * Batalkan pengajuan selama masih menunggu driver.
     */
    public function cancelPickup(Customer $customer, WasteDeposit $pickup): WasteDeposit
    {
        if ($pickup->status !== PickupStatus::PENDING) {
            throw ValidationException::withMessages([
                'pickup' => 'Penjemputan ini sudah diproses driver dan tidak dapat dibatalkan.',
            ]);
        }

        $pickup->update(['status' => PickupStatus::CANCELED]);

        return $pickup->fresh(['details.wasteCategory', 'driver.user']);
    }

    /**
     * Daftar tugas aktif driver: tugas yang sedang dikerjakan olehnya
     * atau antrean pending yang masih tersedia untuk diambil.
     *
     * @param  array<string, mixed>  $filters
     */
    public function getDriverTasks(Driver $driver, array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $activeStatuses = [
            PickupStatus::ON_THE_WAY->value,
            PickupStatus::PICKED_UP->value,
        ];
        $allowedStatuses = array_merge([PickupStatus::PENDING->value], $activeStatuses);

        return $this->baseQuery()
            ->where(function ($query) use ($driver, $activeStatuses) {
                // Tugas yang sedang dikerjakan driver ini
                $query->where(function ($mine) use ($driver, $activeStatuses) {
                    $mine->where('driver_id', $driver->id)
                        ->whereIn('status', $activeStatuses);
                })
                    // Antrean yang belum diambil driver mana pun
                    ->orWhere(function ($available) {
                        $available->whereNull('driver_id')
                            ->where('status', PickupStatus::PENDING->value);
                    });
            })
            ->when($filters['status'] ?? null, function ($q, $status) use ($allowedStatuses) {
                if (in_array($status, $allowedStatuses, true)) {
                    $q->where('status', $status);
                }
            })
            ->when($filters['search'] ?? null, function ($q, $search) {
                $search = trim((string) $search);

                $q->where(function ($inner) use ($search) {
                    $inner->where('address', 'like', "%{$search}%")
                        ->orWhereHas('customer.user', fn ($user) => $user->where('name', 'like', "%{$search}%"));
                });
            })
            // Tugas milik sendiri tampil lebih dulu dibanding antrean tersedia.
            ->orderByRaw('CASE WHEN driver_id = ? THEN 0 ELSE 1 END ASC', [$driver->id])
            ->latest('created_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Riwayat penjemputan yang sudah diselesaikan / dibatalkan driver ini.
     *
     * @param  array<string, mixed>  $filters
     */
    public function getDriverHistory(Driver $driver, array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $historyStatuses = [
            PickupStatus::COMPLETED->value,
            PickupStatus::CANCELED->value,
        ];

        return $this->baseQuery()
            ->where('driver_id', $driver->id)
            ->whereIn('status', $historyStatuses)
            ->when($filters['status'] ?? null, function ($q, $status) use ($historyStatuses) {
                if (in_array($status, $historyStatuses, true)) {
                    $q->where('status', $status);
                }
            })
            ->when($filters['search'] ?? null, function ($q, $search) {
                $search = trim((string) $search);

                $q->where(function ($inner) use ($search) {
                    $inner->where('address', 'like', "%{$search}%")
                        ->orWhereHas('customer.user', fn ($user) => $user->where('name', 'like', "%{$search}%"));
                });
            })
            ->latest('updated_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Detail tugas driver (boleh melihat antrean pending atau tugasnya sendiri).
     */
    public function findDriverTask(Driver $driver, int $id): WasteDeposit
    {
        return $this->detailQuery()
            ->where(function ($query) use ($driver) {
                $query->where('driver_id', $driver->id)
                    ->orWhere(function ($pending) {
                        $pending->whereNull('driver_id')
                            ->where('status', PickupStatus::PENDING->value);
                    });
            })
            ->findOrFail($id);
    }

    /**
     * Ambil tugas aktif driver (driver hanya boleh menangani satu tugas sekaligus).
     */
    public function getActivePickup(Driver $driver, ?int $exceptId = null): ?WasteDeposit
    {
        return WasteDeposit::query()
            ->with(['customer.user:id,name,email,photo,role', 'details.wasteCategory:id,name'])
            ->where('driver_id', $driver->id)
            ->whereIn('status', [
                PickupStatus::ON_THE_WAY->value,
                PickupStatus::PICKED_UP->value,
            ])
            ->when($exceptId, fn ($query) => $query->whereKeyNot($exceptId))
            ->latest('updated_at')
            ->first();
    }

    /**
     * Driver mengambil tugas penjemputan dan mulai menuju lokasi.
     */
    public function claimPickup(Driver $driver, WasteDeposit $pickup): WasteDeposit
    {
        if ($pickup->driver_id !== null && $pickup->driver_id !== $driver->id) {
            throw ValidationException::withMessages([
                'pickup' => 'Penjemputan ini sudah ditugaskan ke driver lain.',
            ]);
        }

        if ($pickup->status !== PickupStatus::PENDING) {
            throw ValidationException::withMessages([
                'pickup' => 'Penjemputan ini tidak dapat diambil karena statusnya sudah berubah.',
            ]);
        }

        // Satu driver hanya boleh menjalankan satu penjemputan dalam satu waktu.
        $activePickup = $this->getActivePickup($driver, $pickup->id);

        if ($activePickup) {
            throw ValidationException::withMessages([
                'pickup' => "Anda masih memiliki penjemputan aktif (#{$activePickup->id}). Selesaikan tugas tersebut sebelum mengambil tugas baru.",
            ]);
        }

        $pickup->update([
            'driver_id' => $driver->id,
            'status' => PickupStatus::ON_THE_WAY,
        ]);

        return $pickup->fresh(['details.wasteCategory', 'customer.user', 'driver.user']);
    }

    /**
     * Driver menyatakan sudah tiba di lokasi dan sampah siap ditimbang.
     */
    public function markPickedUp(Driver $driver, WasteDeposit $pickup): WasteDeposit
    {
        $this->ensureOwnedBy($driver, $pickup);

        if ($pickup->status !== PickupStatus::ON_THE_WAY) {
            throw ValidationException::withMessages([
                'pickup' => 'Status penjemputan tidak dapat diubah menjadi ditimbang.',
            ]);
        }

        $pickup->update(['status' => PickupStatus::PICKED_UP]);

        return $pickup->fresh(['details.wasteCategory', 'customer.user', 'driver.user']);
    }

    /**
     * Selesaikan penjemputan: simpan berat, bukti foto, dan terbitkan poin customer.
     *
     * @param  array<int, array{id:int, weight:float|string}>  $weights
     */
    public function completePickup(
        Driver $driver,
        WasteDeposit $pickup,
        array $weights,
        UploadedFile $proofPhoto
    ): WasteDeposit {
        $this->ensureOwnedBy($driver, $pickup);

        if ($pickup->status === PickupStatus::COMPLETED) {
            return $pickup->fresh(['details.wasteCategory', 'customer.user', 'driver.user']);
        }

        if ($pickup->status !== PickupStatus::PICKED_UP) {
            throw ValidationException::withMessages([
                'pickup' => 'Penjemputan harus berstatus ditimbang sebelum diselesaikan.',
            ]);
        }

        return DB::transaction(function () use ($pickup, $weights, $proofPhoto) {
            $weightMap = collect($weights)
                ->mapWithKeys(fn ($row) => [(int) $row['id'] => (float) $row['weight']]);

            $pickup->load('details');
            $totalPoints = 0;

            foreach ($pickup->details as $detail) {
                if (! $weightMap->has($detail->id)) {
                    continue;
                }

                $weight = round((float) $weightMap->get($detail->id), 2);
                $subtotal = (int) round($weight * (int) $detail->points_per_kg);

                $detail->update([
                    'weight' => $weight,
                    'subtotal_points' => $subtotal,
                ]);

                $totalPoints += $subtotal;
            }

            $proofPath = $proofPhoto->store('pickups/proofs', 'public');

            $pickup->update([
                'status' => PickupStatus::COMPLETED,
                'pickup_proof_photo' => $proofPath,
                'pickup_at' => now(),
            ]);

            $customer = $pickup->customer;

            if ($customer && $totalPoints > 0) {
                $customer->increment('points', $totalPoints);

                PointHistory::create([
                    'customer_id' => $customer->id,
                    'type' => PointType::IN,
                    'amount' => $totalPoints,
                    'source' => 'Penjemputan #'.$pickup->id,
                ]);
            }

            return $pickup->fresh(['details.wasteCategory', 'customer.user', 'driver.user']);
        });
    }

    /**
     * Perbarui koordinat terakhir driver.
     */
    public function updateDriverLocation(Driver $driver, float $latitude, float $longitude): Driver
    {
        $driver->update([
            'current_latitude' => $latitude,
            'current_longitude' => $longitude,
            'location_updated_at' => now(),
        ]);

        return $driver->fresh();
    }

    /**
     * Query dasar riwayat penjemputan dengan relasi yang dibutuhkan UI.
     */
    protected function baseQuery(): Builder
    {
        return WasteDeposit::query()->with([
            'customer.user:id,name,email,photo,role',
            'driver.user:id,name,email,photo,role',
            'details.wasteCategory:id,name,points_per_kg',
        ]);
    }

    /**
     * Query detail lengkap termasuk lokasi driver untuk peta rute.
     */
    protected function detailQuery(): Builder
    {
        return WasteDeposit::query()->with([
            'customer.user:id,name,email,photo,phone_number,role',
            'driver.user:id,name,email,photo,phone_number,role',
            'details.wasteCategory:id,name,points_per_kg',
        ]);
    }

    /**
     * Pastikan penjemputan memang ditugaskan ke driver ini.
     */
    protected function ensureOwnedBy(Driver $driver, WasteDeposit $pickup): void
    {
        if ($pickup->driver_id !== $driver->id) {
            throw ValidationException::withMessages([
                'pickup' => 'Penjemputan ini bukan tugas Anda.',
            ]);
        }
    }
}
