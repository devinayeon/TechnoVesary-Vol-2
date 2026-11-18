<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    /**
     * Menampilkan katalog produk Upcycle Store untuk customer (9 per halaman).
     */
    public function index(Request $request): Response
    {
        $filters = $request->only(['search', 'sort', 'availability']);

        $query = Product::query();

        // Filter Pencarian
        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter Ketersediaan Stok
        if (! empty($filters['availability'])) {
            if ($filters['availability'] === 'in_stock') {
                $query->where('stock', '>', 0);
            } elseif ($filters['availability'] === 'out_of_stock') {
                $query->where('stock', '<=', 0);
            }
        }

        // Pengurutan
        $sort = $filters['sort'] ?? 'latest';
        match ($sort) {
            'price_low' => $query->orderBy('price_rupiah', 'asc'),
            'price_high' => $query->orderBy('price_rupiah', 'desc'),
            'name_asc' => $query->orderBy('name', 'asc'),
            default => $query->orderBy('created_at', 'desc'),
        };

        $products = $query->paginate(9)->withQueryString();

        return Inertia::render('Customer/Products/Index', [
            'products' => $products,
            'filters' => $filters,
        ]);
    }

    /**
     * Menampilkan halaman detail produk daur ulang.
     */
    public function show(Product $product): Response
    {
        return Inertia::render('Customer/Products/Show', [
            'product' => $product,
        ]);
    }
}
