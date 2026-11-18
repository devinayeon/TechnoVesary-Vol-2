<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Http\Controllers\Controller;
use App\Services\ProductTransaction\ProductTransactionService;
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

        return Inertia::render('Admin/ProductTransactions/Index', [
            'transactions' => $transactions,
            'filters' => $filters,
            'statuses' => $statuses,
            'paymentMethods' => $paymentMethods,
        ]);
    }
}
