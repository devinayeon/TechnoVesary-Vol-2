<?php

namespace App\Services\Waste;

use App\Models\WasteCategory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class WasteCategoryService
{
    /**
     * Get paginated waste categories with optional search, status filtering, and sorting.
     *
     * @param  array<string, mixed>  $filters
     */
    public function getPaginatedCategories(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = WasteCategory::query();

        // Status Filter: active (default), trashed, all
        $status = $filters['status'] ?? 'active';
        if ($status === 'trashed') {
            $query->onlyTrashed();
        } elseif ($status === 'all') {
            $query->withTrashed();
        }

        // Search Filter (name or description)
        if (! empty($filters['search'])) {
            $search = '%'.trim((string) $filters['search']).'%';
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', $search)
                    ->orWhere('description', 'like', $search);
            });
        }

        // Sorting
        $allowedSorts = ['name', 'points_per_kg', 'created_at', 'updated_at'];
        $sortBy = in_array($filters['sort_by'] ?? '', $allowedSorts, true) ? $filters['sort_by'] : 'created_at';
        $sortDirection = strtolower($filters['sort_direction'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

        return $query->orderBy($sortBy, $sortDirection)->paginate($perPage)->withQueryString();
    }

    /**
     * Create a new waste category.
     *
     * @param  array<string, mixed>  $data
     */
    public function createCategory(array $data): WasteCategory
    {
        return WasteCategory::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'points_per_kg' => (int) $data['points_per_kg'],
        ]);
    }

    /**
     * Update an existing waste category.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateCategory(WasteCategory $category, array $data): WasteCategory
    {
        $category->update([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'points_per_kg' => (int) $data['points_per_kg'],
        ]);

        return $category->fresh();
    }

    /**
     * Soft delete a waste category.
     */
    public function deleteCategory(WasteCategory $category): bool
    {
        return (bool) $category->delete();
    }

    /**
     * Restore a soft-deleted waste category.
     */
    public function restoreCategory(int|string $id): WasteCategory
    {
        $category = WasteCategory::onlyTrashed()->findOrFail($id);
        $category->restore();

        return $category;
    }
}
