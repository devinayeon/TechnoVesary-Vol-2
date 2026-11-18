<?php

namespace App\Http\Controllers\Admin;

use App\Enums\TransactionStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateSubscriptionPriceRequest;
use App\Models\Subscription;
use App\Services\Subscription\SubscriptionManagementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SubscriptionController extends Controller
{
    public function __construct(
        protected SubscriptionManagementService $subscriptionService
    ) {}

    /**
     * Menampilkan overview paket langganan dan riwayat transaksi customer.
     */
    public function index(Request $request): Response
    {
        $filters = $request->only(['search', 'status']);
        $subscription = $this->subscriptionService->getActiveSubscription();
        $transactions = $this->subscriptionService->getPaginatedTransactions($filters, 10);

        return Inertia::render('Admin/Subscriptions/Index', [
            'subscription' => $subscription,
            'transactions' => $transactions,
            'filters' => $filters,
            'statuses' => array_map(fn (TransactionStatus $status) => [
                'value' => $status->value,
                'label' => $status->label(),
            ], TransactionStatus::cases()),
        ]);
    }

    /**
     * Memperbarui harga dan deskripsi paket langganan.
     */
    public function update(UpdateSubscriptionPriceRequest $request, Subscription $subscription): RedirectResponse
    {
        $updated = $this->subscriptionService->updatePrice($subscription, $request->validated());

        return redirect()
            ->route('admin.subscriptions.index')
            ->with(
                'success',
                'Harga paket langganan berhasil diperbarui menjadi Rp '.number_format($updated->price, 0, ',', '.').'.'
            );
    }
}
