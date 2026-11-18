<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Http\Requests\Admin\UpdateProductRequest;
use App\Models\Admin;
use App\Models\Product;
use App\Services\Product\ProductManagementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function __construct(
        protected ProductManagementService $productService
    ) {}

    /**
     * Menampilkan daftar produk terpaginasi dengan pencarian dan filter status.
     */
    public function index(Request $request): Response
    {
        $filters = $request->only(['search', 'status', 'sort_by', 'sort_direction']);
        $products = $this->productService->getPaginatedProducts($filters, 12);

        return Inertia::render('Admin/Products/Index', [
            'products' => $products,
            'filters' => $filters,
        ]);
    }

    /**
     * Menampilkan form pembuatan produk baru.
     */
    public function create(): Response
    {
        return Inertia::render('Admin/Products/Create');
    }

    /**
     * Menyimpan produk baru ke database.
     */
    public function store(StoreProductRequest $request): RedirectResponse
    {
        $adminId = $request->user()->admin?->id ?? Admin::first()?->id;

        if (! $adminId) {
            $admin = Admin::create(['user_id' => $request->user()->id]);
            $adminId = $admin->id;
        }

        $this->productService->createProduct(
            $request->validated(),
            $adminId,
            $request->file('photo')
        );

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Produk berhasil ditambahkan ke Upcycle Store.');
    }

    /**
     * Menampilkan detail produk (redirect ke edit).
     */
    public function show(Product $product): RedirectResponse
    {
        return redirect()->route('admin.products.edit', $product);
    }

    /**
     * Menampilkan form edit produk.
     */
    public function edit(Product $product): Response
    {
        $product->load(['admin.user']);

        return Inertia::render('Admin/Products/Edit', [
            'product' => $product,
        ]);
    }

    /**
     * Memperbarui data produk.
     */
    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $this->productService->updateProduct(
            $product,
            $request->validated(),
            $request->file('photo'),
            $request->boolean('remove_photo')
        );

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Produk berhasil diperbarui.');
    }

    /**
     * Menghapus produk secara soft delete.
     */
    public function destroy(Product $product): RedirectResponse
    {
        $this->productService->deleteProduct($product);

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Produk berhasil dinonaktifkan dari Upcycle Store.');
    }

    /**
     * Memulihkan produk yang telah di-soft-delete.
     */
    public function restore(int $id): RedirectResponse
    {
        $this->productService->restoreProduct($id);

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Produk berhasil dipulihkan ke Upcycle Store.');
    }
}
