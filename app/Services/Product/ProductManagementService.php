<?php

namespace App\Services\Product;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ProductManagementService
{
    /**
     * Dapatkan daftar produk terpaginasi dengan filter pencarian dan status soft-delete.
     *
     * @param  array<string, mixed>  $filters
     */
    public function getPaginatedProducts(array $filters = [], int $perPage = 12): LengthAwarePaginator
    {
        $query = Product::query()->with(['admin.user:id,name,email,photo']);

        // Filter Status: active (default), trashed, all
        $status = $filters['status'] ?? 'active';
        if ($status === 'trashed') {
            $query->onlyTrashed();
        } elseif ($status === 'all') {
            $query->withTrashed();
        }

        // Filter Pencarian (Nama atau Deskripsi)
        if (! empty($filters['search'])) {
            $search = '%'.trim((string) $filters['search']).'%';
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', $search)
                    ->orWhere('description', 'like', $search);
            });
        }

        // Sorting
        $allowedSorts = ['name', 'price_rupiah', 'stock', 'created_at', 'updated_at'];
        $sortBy = in_array($filters['sort_by'] ?? '', $allowedSorts, true) ? $filters['sort_by'] : 'created_at';
        $sortDirection = strtolower($filters['sort_direction'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

        return $query->orderBy($sortBy, $sortDirection)->paginate($perPage)->withQueryString();
    }

    /**
     * Simpan produk baru.
     *
     * @param  array<string, mixed>  $data
     */
    public function createProduct(array $data, int $adminId, ?UploadedFile $photo = null): Product
    {
        $photoPath = null;
        if ($photo) {
            $photoPath = $photo->store('products/photos', 'public');
        }

        $priceRupiah = (int) $data['price_rupiah'];

        return Product::create([
            'admin_id' => $adminId,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'price_rupiah' => $priceRupiah,
            'price_points' => $priceRupiah, // Auto-sync: price_points == price_rupiah (Shopee-style)
            'stock' => (int) ($data['stock'] ?? 0),
            'photo' => $photoPath,
        ]);
    }

    /**
     * Perbarui data produk yang ada.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateProduct(Product $product, array $data, ?UploadedFile $photo = null, bool $removePhoto = false): Product
    {
        $photoPath = $product->photo;

        if ($removePhoto || $photo) {
            if ($photoPath && Storage::disk('public')->exists($photoPath)) {
                Storage::disk('public')->delete($photoPath);
            }
            $photoPath = null;
        }

        if ($photo) {
            $photoPath = $photo->store('products/photos', 'public');
        }

        $priceRupiah = (int) $data['price_rupiah'];

        $product->update([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'price_rupiah' => $priceRupiah,
            'price_points' => $priceRupiah, // Auto-sync: price_points == price_rupiah (Shopee-style)
            'stock' => (int) ($data['stock'] ?? 0),
            'photo' => $photoPath,
        ]);

        return $product->fresh(['admin.user']);
    }

    /**
     * Nonaktifkan produk secara aman (soft delete).
     */
    public function deleteProduct(Product $product): bool
    {
        return (bool) $product->delete();
    }

    /**
     * Pulihkan kembali produk yang di-soft-delete.
     */
    public function restoreProduct(int $id): bool
    {
        $product = Product::onlyTrashed()->findOrFail($id);

        return (bool) $product->restore();
    }
}
