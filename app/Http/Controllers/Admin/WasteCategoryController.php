<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreWasteCategoryRequest;
use App\Http\Requests\Admin\UpdateWasteCategoryRequest;
use App\Models\WasteCategory;
use App\Services\Waste\WasteCategoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class WasteCategoryController extends Controller
{
    public function __construct(
        protected WasteCategoryService $wasteCategoryService
    ) {}

    /**
     * Menampilkan daftar jenis sampah terpaginasi dengan pencarian, filter status, dan sorting.
     */
    public function index(Request $request): Response
    {
        $filters = $request->only(['search', 'status', 'sort_by', 'sort_direction']);
        $categories = $this->wasteCategoryService->getPaginatedCategories($filters, 10);

        return Inertia::render('Admin/WasteCategories/Index', [
            'categories' => $categories,
            'filters' => $filters,
        ]);
    }

    /**
     * Menampilkan form penambahan jenis sampah baru.
     */
    public function create(): Response
    {
        return Inertia::render('Admin/WasteCategories/Create');
    }

    /**
     * Menyimpan jenis sampah baru ke basis data.
     */
    public function store(StoreWasteCategoryRequest $request): RedirectResponse
    {
        $this->wasteCategoryService->createCategory($request->validated());

        return redirect()
            ->route('admin.waste-categories.index')
            ->with('success', 'Jenis sampah baru berhasil ditambahkan.');
    }

    /**
     * Menampilkan rincian data jenis sampah.
     */
    public function show(WasteCategory $wasteCategory): Response
    {
        return Inertia::render('Admin/WasteCategories/Show', [
            'category' => $wasteCategory,
        ]);
    }

    /**
     * Menampilkan form edit jenis sampah.
     */
    public function edit(WasteCategory $wasteCategory): Response
    {
        return Inertia::render('Admin/WasteCategories/Edit', [
            'category' => $wasteCategory,
        ]);
    }

    /**
     * Memperbarui data jenis sampah (nama, deskripsi, dan poin per kg).
     */
    public function update(UpdateWasteCategoryRequest $request, WasteCategory $wasteCategory): RedirectResponse
    {
        $this->wasteCategoryService->updateCategory($wasteCategory, $request->validated());

        return redirect()
            ->route('admin.waste-categories.index')
            ->with('success', 'Data jenis sampah berhasil diperbarui.');
    }

    /**
     * Menghapus jenis sampah secara Soft Delete.
     */
    public function destroy(WasteCategory $wasteCategory): RedirectResponse
    {
        $this->wasteCategoryService->deleteCategory($wasteCategory);

        return redirect()
            ->route('admin.waste-categories.index')
            ->with('success', 'Jenis sampah berhasil dinonaktifkan.');
    }

    /**
     * Mengaktifkan kembali jenis sampah yang dihapus (restore).
     */
    public function restore(int|string $id): RedirectResponse
    {
        $this->wasteCategoryService->restoreCategory($id);

        return back()->with('success', 'Jenis sampah berhasil dipulihkan.');
    }
}
