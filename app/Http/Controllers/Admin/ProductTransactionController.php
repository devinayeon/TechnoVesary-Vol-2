<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\Transaction;
use App\Services\ProductTransaction\ProductTransactionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductTransactionController extends Controller
{
    public function __construct(
        private readonly ProductTransactionService $service
    ) {}

    public function index(Request $request): Response
    {
        $filters = $request->only(['search', 'status', 'payment_method']);

        $transactions = $this->service->getPaginatedTransactions($filters, 15);

        $statuses = array_map(
            fn (OrderStatus $s) => ['value' => $s->value, 'label' => $s->label()],
            OrderStatus::cases()
        );

        $paymentMethods = array_map(
            fn (PaymentMethod $m) => ['value' => $m->value, 'label' => $m->label()],
            PaymentMethod::cases()
        );

        $drivers = Driver::with('user:id,name,email,photo')->get()->map(fn ($d) => [
            'id' => $d->id,
            'name' => $d->user?->name ?? "Driver #{$d->id}",
            'email' => $d->user?->email ?? '',
            'is_available' => (bool) $d->is_available,
        ]);

        return Inertia::render('Admin/ProductTransactions/Index', [
            'transactions' => $transactions,
            'filters' => $filters,
            'statuses' => $statuses,
            'paymentMethods' => $paymentMethods,
            'drivers' => $drivers,
            'basemapApiKey' => config('osm.basemap_api_key'),
        ]);
    }

    /**
     * Menugaskan kurir/driver untuk pesanan yang sudah lunas/diproses.
     */
    public function assignDriver(Request $request, Transaction $transaction): RedirectResponse
    {
        $validated = $request->validate([
            'driver_id' => 'required|exists:drivers,id',
        ], [
            'driver_id.required' => 'Pilih driver yang akan ditugaskan.',
            'driver_id.exists' => 'Driver tidak valid.',
        ]);

        $transaction->update([
            'driver_id' => $validated['driver_id'],
            'status' => OrderStatus::DELIVERING,
        ]);

        return back()->with('success', "Driver berhasil ditugaskan untuk mengantar Pesanan #{$transaction->id}. Status pesanan kini Sedang Dikirim.");
    }
}
