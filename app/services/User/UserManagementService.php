<?php

namespace App\Services\User;

use App\Enums\UserRole;
use App\Models\Admin;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use InvalidArgumentException;

class UserManagementService
{
    /**
     * Mengambil daftar pengguna terpaginasi dengan pencarian dan filter
     *
     * @param array<string, mixed> $filters
     */
    public function getPaginatedUsers(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = User::query()->with(['admin', 'driver', 'customer']);

        // Filter status: active, trashed (soft-deleted), all
        $status = $filters['status'] ?? 'active';
        if ($status === 'trashed') {
            $query->onlyTrashed();
        } elseif ($status === 'all') {
            $query->withTrashed();
        }

        // Pencarian berdasarkan nama, email, atau nomor telepon
        if (! empty($filters['search'])) {
            $keyword = trim((string) $filters['search']);
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                    ->orWhere('email', 'like', "%{$keyword}%")
                    ->orWhere('phone_number', 'like', "%{$keyword}%");
            });
        }

        // Filter berdasarkan role
        if (! empty($filters['role'])) {
            $query->where('role', $filters['role']);
        }

        return $query->latest('id')->paginate($perPage)->withQueryString();
    }

    /**
     * Membuat pengguna baru beserta record profil tabel spesifiknya secara otomatis
     *
     * @param array<string, mixed> $data
     */
    public function createUser(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $role = $data['role'] instanceof UserRole
                ? $data['role']
                : UserRole::from((string) $data['role']);

            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => $role,
                'phone_number' => $data['phone_number'] ?? null,
                'address' => $data['address'] ?? null,
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
                'email_verified_at' => now(), // Akun yang dibuat oleh admin langsung aktif terverifikasi
            ]);

            // Otomatis membuat record profil spesifik di tabel pasangannya
            match ($role) {
                UserRole::ADMIN => Admin::create([
                    'user_id' => $user->id,
                ]),
                UserRole::DRIVER => Driver::create([
                    'user_id' => $user->id,
                    'is_available' => $data['is_available'] ?? true,
                ]),
                UserRole::CUSTOMER => Customer::create([
                    'user_id' => $user->id,
                    'points' => $data['points'] ?? 0,
                    'is_member' => $data['is_member'] ?? false,
                    'membership_expires_at' => $data['membership_expires_at'] ?? null,
                ]),
            };

            return $user->load(['admin', 'driver', 'customer']);
        });
    }

    /**
     * Memperbarui data pengguna, opsional reset password, dan sinkronisasi profil role
     *
     * @param array<string, mixed> $data
     */
    public function updateUser(User $user, array $data, ?int $currentUserId = null): User
    {
        return DB::transaction(function () use ($user, $data, $currentUserId) {
            $role = $data['role'] instanceof UserRole
                ? $data['role']
                : UserRole::from((string) $data['role']);

            // Guard: admin tidak dapat mengubah role akun miliknya sendiri
            if ($currentUserId !== null && $user->id === $currentUserId && $user->role !== $role) {
                throw new InvalidArgumentException('Anda tidak dapat mengubah role akun Anda sendiri.');
            }

            $updatePayload = [
                'name' => $data['name'],
                'email' => $data['email'],
                'role' => $role,
                'phone_number' => $data['phone_number'] ?? null,
                'address' => $data['address'] ?? null,
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
            ];

            // Jika admin mengisi kata sandi baru, hash dan perbarui
            if (! empty($data['password'])) {
                $updatePayload['password'] = Hash::make($data['password']);
            }

            $user->update($updatePayload);

            // Sinkronisasi profil tabel spesifik sesuai role pengguna
            match ($role) {
                UserRole::ADMIN => Admin::firstOrCreate(['user_id' => $user->id]),
                UserRole::DRIVER => tap(
                    Driver::firstOrCreate(['user_id' => $user->id]),
                    function (Driver $driver) use ($data) {
                        if (array_key_exists('is_available', $data)) {
                            $driver->update(['is_available' => (bool) $data['is_available']]);
                        }
                    }
                ),
                UserRole::CUSTOMER => tap(
                    Customer::firstOrCreate(['user_id' => $user->id]),
                    function (Customer $customer) use ($data) {
                        $customerPayload = [];
                        if (array_key_exists('points', $data)) {
                            $customerPayload['points'] = (int) $data['points'];
                        }
                        if (array_key_exists('is_member', $data)) {
                            $customerPayload['is_member'] = (bool) $data['is_member'];
                        }
                        if (array_key_exists('membership_expires_at', $data)) {
                            $customerPayload['membership_expires_at'] = $data['membership_expires_at'];
                        }
                        if (! empty($customerPayload)) {
                            $customer->update($customerPayload);
                        }
                    }
                ),
            };

            return $user->fresh(['admin', 'driver', 'customer']);
        });
    }

    /**
     * Menghapus pengguna secara Soft Delete (dengan safety guard)
     */
    public function deleteUser(User $user, int $currentUserId): bool
    {
        if ($user->id === $currentUserId) {
            throw new InvalidArgumentException('Anda tidak dapat menghapus akun Anda sendiri.');
        }

        return (bool) $user->delete();
    }

    /**
     * Mengaktifkan kembali pengguna yang berada di status soft-delete
     */
    public function restoreUser(int $id): User
    {
        $user = User::onlyTrashed()->findOrFail($id);
        $user->restore();

        return $user;
    }
}
