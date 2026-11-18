<?php

namespace App\Services\Article;

use App\Models\Article;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ArticleManagementService
{
    /**
     * Dapatkan daftar artikel terpaginasi dengan filter pencarian, kategori, dan status soft-delete.
     *
     * @param  array<string, mixed>  $filters
     */
    public function getPaginatedArticles(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = Article::query()
            ->with([
                'category:id,name',
                'admin.user:id,name,email,photo',
            ]);

        // Filter Status: active (default), trashed, all
        $status = $filters['status'] ?? 'active';
        if ($status === 'trashed') {
            $query->onlyTrashed();
        } elseif ($status === 'all') {
            $query->withTrashed();
        }

        // Filter Kategori
        if (! empty($filters['category_id']) && $filters['category_id'] !== 'all') {
            $query->where('category_id', $filters['category_id']);
        }

        // Filter Pencarian (Judul atau Isi)
        if (! empty($filters['search'])) {
            $search = '%'.trim((string) $filters['search']).'%';
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', $search)
                    ->orWhere('content', 'like', $search);
            });
        }

        // Sorting
        $allowedSorts = ['title', 'created_at', 'updated_at'];
        $sortBy = in_array($filters['sort_by'] ?? '', $allowedSorts, true) ? $filters['sort_by'] : 'created_at';
        $sortDirection = strtolower($filters['sort_direction'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

        return $query->orderBy($sortBy, $sortDirection)->paginate($perPage)->withQueryString();
    }

    /**
     * Dapatkan daftar artikel yang dipublikasikan untuk halaman customer/publik.
     * Hanya artikel aktif (tidak soft-deleted) dengan pencarian, filter kategori, dan sorting.
     *
     * @param  array<string, mixed>  $filters
     */
    public function getPublishedArticles(array $filters = [], int $perPage = 9): LengthAwarePaginator
    {
        $query = Article::query()
            ->with([
                'category:id,name',
                'admin.user:id,name,photo,role',
            ]);

        // Filter Kategori
        if (! empty($filters['category_id']) && $filters['category_id'] !== 'all') {
            $query->where('category_id', $filters['category_id']);
        }

        // Filter Pencarian (Judul atau Isi)
        if (! empty($filters['search'])) {
            $search = '%'.trim((string) $filters['search']).'%';
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', $search)
                    ->orWhere('content', 'like', $search);
            });
        }

        // Sorting
        $sort = (string) ($filters['sort'] ?? 'latest');
        match ($sort) {
            'oldest' => $query->oldest(),
            'title' => $query->orderBy('title'),
            default => $query->latest(),
        };

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Ambil satu artikel aktif berdasarkan slug untuk halaman detail.
     */
    public function findPublishedBySlug(string $slug): Article
    {
        return Article::query()
            ->with([
                'category:id,name',
                'admin.user:id,name,photo,role',
            ])
            ->where('slug', $slug)
            ->firstOrFail();
    }

    /**
     * Artikel terkait dari kategori yang sama (exclude artikel aktif).
     *
     * @return Collection<int, Article>
     */
    public function getRelatedArticles(Article $article, int $limit = 3): Collection
    {
        return Article::query()
            ->with(['category:id,name'])
            ->where('category_id', $article->category_id)
            ->whereKeyNot($article->getKey())
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Simpan artikel baru.
     *
     * @param  array<string, mixed>  $data
     */
    public function createArticle(array $data, int $adminId, ?UploadedFile $photo = null): Article
    {
        $slug = ! empty($data['slug']) ? Str::slug($data['slug']) : Str::slug($data['title']);
        $uniqueSlug = $this->generateUniqueSlug($slug);

        $photoPath = null;
        if ($photo) {
            $photoPath = $photo->store('articles/thumbnails', 'public');
        }

        return Article::create([
            'admin_id' => $adminId,
            'category_id' => (int) $data['category_id'],
            'title' => $data['title'],
            'slug' => $uniqueSlug,
            'photo' => $photoPath,
            'content' => $data['content'],
        ]);
    }

    /**
     * Perbarui data artikel yang ada.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateArticle(Article $article, array $data, ?UploadedFile $photo = null, bool $removePhoto = false): Article
    {
        $slug = ! empty($data['slug']) ? Str::slug($data['slug']) : Str::slug($data['title']);
        $uniqueSlug = $this->generateUniqueSlug($slug, $article->id);

        $photoPath = $article->photo;

        if ($removePhoto || $photo) {
            if ($photoPath && Storage::disk('public')->exists($photoPath)) {
                Storage::disk('public')->delete($photoPath);
            }
            $photoPath = null;
        }

        if ($photo) {
            $photoPath = $photo->store('articles/thumbnails', 'public');
        }

        $article->update([
            'category_id' => (int) $data['category_id'],
            'title' => $data['title'],
            'slug' => $uniqueSlug,
            'photo' => $photoPath,
            'content' => $data['content'],
        ]);

        return $article->fresh(['category', 'admin.user']);
    }

    /**
     * Nonaktifkan artikel secara aman (soft delete).
     */
    public function deleteArticle(Article $article): bool
    {
        return (bool) $article->delete();
    }

    /**
     * Pulihkan kembali artikel yang di-soft-delete.
     */
    public function restoreArticle(int $id): bool
    {
        $article = Article::onlyTrashed()->findOrFail($id);

        return (bool) $article->restore();
    }

    /**
     * Unggah gambar konten dari Rich Text Editor (Tiptap).
     */
    public function uploadContentImage(UploadedFile $file): string
    {
        $path = $file->store('articles/content', 'public');

        return Storage::disk('public')->url($path);
    }

    /**
     * Pastikan slug unik di tabel articles.
     */
    protected function generateUniqueSlug(string $slug, ?int $ignoreId = null): string
    {
        $originalSlug = $slug;
        $counter = 1;

        while (
            Article::withTrashed()
                ->where('slug', $slug)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = "{$originalSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }
}
