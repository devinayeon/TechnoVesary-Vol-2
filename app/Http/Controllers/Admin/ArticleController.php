<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreArticleRequest;
use App\Http\Requests\Admin\UpdateArticleRequest;
use App\Models\Admin;
use App\Models\Article;
use App\Models\ArticleCategory;
use App\Services\Article\ArticleManagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ArticleController extends Controller
{
    public function __construct(
        protected ArticleManagementService $articleService
    ) {}

    /**
     * Menampilkan daftar artikel terpaginasi dengan pencarian, filter kategori, dan status.
     */
    public function index(Request $request): Response
    {
        $filters = $request->only(['search', 'status', 'category_id', 'sort_by', 'sort_direction']);
        $articles = $this->articleService->getPaginatedArticles($filters, 10);
        $categories = ArticleCategory::withCount('articles')->orderBy('name')->get();

        return Inertia::render('Admin/Articles/Index', [
            'articles' => $articles,
            'categories' => $categories,
            'filters' => $filters,
        ]);
    }

    /**
     * Menampilkan form pembuatan artikel baru.
     */
    public function create(): Response
    {
        $categories = ArticleCategory::withCount('articles')->orderBy('name')->get();

        return Inertia::render('Admin/Articles/Create', [
            'categories' => $categories,
        ]);
    }

    /**
     * Menyimpan artikel baru ke database.
     */
    public function store(StoreArticleRequest $request): RedirectResponse
    {
        $adminId = $request->user()->admin?->id ?? Admin::first()?->id;

        if (!$adminId) {
            $admin = Admin::create(['user_id' => $request->user()->id]);
            $adminId = $admin->id;
        }

        $this->articleService->createArticle(
            $request->validated(),
            $adminId,
            $request->file('photo')
        );

        return redirect()
            ->route('admin.articles.index')
            ->with('success', 'Artikel edukasi berhasil ditambahkan.');
    }

    /**
     * Menampilkan detail artikel (bisa digunakan untuk preview atau redirect ke edit).
     */
    public function show(Article $article): RedirectResponse
    {
        return redirect()->route('admin.articles.edit', $article);
    }

    /**
     * Menampilkan form edit artikel.
     */
    public function edit(Article $article): Response
    {
        $article->load(['category', 'admin.user']);
        $categories = ArticleCategory::withCount('articles')->orderBy('name')->get();

        return Inertia::render('Admin/Articles/Edit', [
            'article' => $article,
            'categories' => $categories,
        ]);
    }

    /**
     * Memperbarui data artikel.
     */
    public function update(UpdateArticleRequest $request, Article $article): RedirectResponse
    {
        $this->articleService->updateArticle(
            $article,
            $request->validated(),
            $request->file('photo'),
            $request->boolean('remove_photo')
        );

        return redirect()
            ->route('admin.articles.index')
            ->with('success', 'Artikel edukasi berhasil diperbarui.');
    }

    /**
     * Menghapus artikel secara soft delete.
     */
    public function destroy(Article $article): RedirectResponse
    {
        $this->articleService->deleteArticle($article);

        return redirect()
            ->route('admin.articles.index')
            ->with('success', 'Artikel berhasil dinonaktifkan.');
    }

    /**
     * Memulihkan artikel yang telah di-soft-delete.
     */
    public function restore(int $id): RedirectResponse
    {
        $this->articleService->restoreArticle($id);

        return redirect()
            ->route('admin.articles.index')
            ->with('success', 'Artikel berhasil dipulihkan.');
    }

    /**
     * Endpoint API untuk upload gambar dari Rich Text Editor (Tiptap).
     */
    public function uploadImage(Request $request): JsonResponse
    {
        $request->validate([
            'image' => ['required', 'image', 'mimes:jpeg,png,jpg,webp,gif', 'max:3072'],
        ]);

        $url = $this->articleService->uploadContentImage($request->file('image'));

        return response()->json([
            'url' => $url,
        ]);
    }
}
