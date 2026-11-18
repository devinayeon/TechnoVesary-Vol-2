<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\ArticleCategory;
use App\Services\Article\ArticleManagementService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ArticleController extends Controller
{
    public function __construct(
        protected ArticleManagementService $articleService
    ) {}

    /**
     * Menampilkan daftar artikel edukasi untuk customer dengan pencarian,
     * filter kategori, sorting, dan pagination.
     */
    public function index(Request $request): Response
    {
        $filters = $request->only(['search', 'category_id', 'sort']);

        return Inertia::render('Customer/Articles/Index', [
            'articles' => $this->articleService->getPublishedArticles($filters, 9),
            'categories' => $this->getCategories(),
            'filters' => $filters,
        ]);
    }

    /**
     * Menampilkan detail artikel edukasi beserta artikel terkait.
     */
    public function show(string $slug): Response
    {
        $article = $this->articleService->findPublishedBySlug($slug);

        return Inertia::render('Customer/Articles/Show', [
            'article' => $article,
            'related' => $this->articleService->getRelatedArticles($article),
        ]);
    }

    /**
     * Daftar kategori artikel beserta jumlah artikel aktifnya.
     *
     * @return Collection<int, ArticleCategory>
     */
    protected function getCategories()
    {
        return ArticleCategory::query()
            ->withCount('articles')
            ->orderBy('name')
            ->get();
    }
}
