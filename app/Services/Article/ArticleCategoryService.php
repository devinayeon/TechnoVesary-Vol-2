<?php

namespace App\Services\Article;

use App\Models\ArticleCategory;
use DomainException;
use Illuminate\Database\Eloquent\Collection;

class ArticleCategoryService
{
    /**
     * Dapatkan semua kategori artikel beserta jumlah artikel terkait.
     *
     * @return Collection<int, ArticleCategory>
     */
    public function getAllWithCounts(): Collection
    {
        return ArticleCategory::withCount('articles')
            ->orderBy('name')
            ->get();
    }

    /**
     * Simpan kategori artikel baru.
     *
     * @param array<string, mixed> $data
     */
    public function createCategory(array $data): ArticleCategory
    {
        return ArticleCategory::create([
            'name' => trim((string) $data['name']),
        ]);
    }

    /**
     * Perbarui nama kategori artikel.
     *
     * @param array<string, mixed> $data
     */
    public function updateCategory(ArticleCategory $category, array $data): ArticleCategory
    {
        $category->update([
            'name' => trim((string) $data['name']),
        ]);

        return $category;
    }

    /**
     * Hapus kategori artikel dengan safety guard jika masih memuat artikel.
     *
     * @throws DomainException
     */
    public function deleteCategory(ArticleCategory $category): bool
    {
        $articlesCount = $category->articles()->count();

        if ($articlesCount > 0) {
            throw new DomainException(
                "Kategori '{$category->name}' tidak dapat dihapus karena masih memuat {$articlesCount} artikel. Silakan pindahkan atau sesuaikan kategori artikel terlebih dahulu."
            );
        }

        return (bool) $category->delete();
    }
}
