<?php

namespace App\Http\Controllers;

use App\Services\Article\ArticleManagementService;
use Inertia\Inertia;
use Inertia\Response;

class ArticleController extends Controller
{
    public function __construct(
        protected ArticleManagementService $articleService
    ) {}

    /**
     * Halaman detail artikel publik (dapat diakses tanpa login).
     */
    public function show(string $slug): Response
    {
        $article = $this->articleService->findPublishedBySlug($slug);

        return Inertia::render('Article/Show', [
            'article' => $article,
            'related' => $this->articleService->getRelatedArticles($article),
        ]);
    }
}
