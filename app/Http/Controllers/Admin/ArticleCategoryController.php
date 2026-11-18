<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreArticleCategoryRequest;
use App\Http\Requests\Admin\UpdateArticleCategoryRequest;
use App\Models\ArticleCategory;
use App\Services\Article\ArticleCategoryService;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ArticleCategoryController extends Controller
{
    public function __construct(
        protected ArticleCategoryService $categoryService
    ) {}

    /**
     * Dapatkan daftar kategori artikel terformat (JSON untuk modal/fetch).
     */
    public function index(Request $request): JsonResponse
    {
        $categories = $this->categoryService->getAllWithCounts();

        return response()->json([
            'categories' => $categories,
        ]);
    }

    /**
     * Simpan kategori artikel baru.
     */
    public function store(StoreArticleCategoryRequest $request): RedirectResponse
    {
        $category = $this->categoryService->createCategory($request->validated());

        return redirect()
            ->back()
            ->with('success', "Kategori '{$category->name}' berhasil ditambahkan.");
    }

    /**
     * Perbarui nama kategori artikel.
     */
    public function update(UpdateArticleCategoryRequest $request, ArticleCategory $articleCategory): RedirectResponse
    {
        $this->categoryService->updateCategory($articleCategory, $request->validated());

        return redirect()
            ->back()
            ->with('success', "Kategori berhasil diperbarui menjadi '{$articleCategory->name}'.");
    }

    /**
     * Hapus kategori artikel dengan safety check.
     */
    public function destroy(ArticleCategory $articleCategory): RedirectResponse
    {
        try {
            $name = $articleCategory->name;
            $this->categoryService->deleteCategory($articleCategory);

            return redirect()
                ->back()
                ->with('success', "Kategori '{$name}' berhasil dihapus.");
        } catch (DomainException $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }
}
