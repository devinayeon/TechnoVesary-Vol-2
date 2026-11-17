<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\Article;
use App\Models\Product;
use App\Models\Subscription;
use App\Models\WasteCategory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Inertia\Inertia;

class WelcomeController extends Controller
{
    public function index()
    {
        // Pengguna yang sudah login diarahkan ke dashboard role masing-masing
        if (Auth::check()) {
            $user = Auth::user();

            if (! $user->hasVerifiedEmail()) {
                return redirect()->route('verification.notice');
            }

            return match ($user->role) {
                UserRole::ADMIN => redirect()->route('admin.dashboard'),
                UserRole::DRIVER => redirect()->route('driver.dashboard'),
                UserRole::CUSTOMER => redirect()->route('customer.dashboard'),
                default => redirect()->route('customer.dashboard'),
            };
        }

        $wasteCategories = WasteCategory::select(['id', 'name', 'description', 'points_per_kg'])
            ->whereNull('deleted_at')
            ->get();

        $subscriptions = Subscription::select(['id', 'name', 'price', 'description'])
            ->orderBy('price')
            ->get();

        $products = Product::select(['id', 'name', 'description', 'price_rupiah', 'price_points', 'photo'])
            ->whereNull('deleted_at')
            ->latest()
            ->take(4)
            ->get();

        $articles = Article::with('category:id,name')
            ->select(['id', 'title', 'slug', 'photo', 'content', 'category_id'])
            ->whereNull('deleted_at')
            ->latest()
            ->take(3)
            ->get()
            ->map(function ($article) {
                return [
                    'id' => $article->id,
                    'title' => $article->title,
                    'slug' => $article->slug,
                    'photo' => $article->photo,
                    'thumbnail' => $article->photo,
                    'content' => $article->content,
                    'excerpt' => Str::limit(strip_tags($article->content), 120),
                    'category' => $article->category ? [
                        'id' => $article->category->id,
                        'name' => $article->category->name,
                    ] : null,
                ];
            });

        $basemapApiKey = config('osm.basemap_api_key');

        return Inertia::render('Welcome', compact('wasteCategories', 'subscriptions', 'products', 'articles', 'basemapApiKey'));
    }
}
