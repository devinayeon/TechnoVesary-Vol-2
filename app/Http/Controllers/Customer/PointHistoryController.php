<?php

namespace App\Http\Controllers\Customer;

use App\Enums\PointType;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Customer\Concerns\ResolvesCustomerProfile;
use App\Services\Point\PointHistoryService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PointHistoryController extends Controller
{
    use ResolvesCustomerProfile;

    public function __construct(
        private readonly PointHistoryService $pointHistoryService
    ) {}

    /**
     * Riwayat poin masuk dan poin keluar milik customer.
     */
    public function index(Request $request): Response
    {
        $customer = $this->resolveCustomer($request);
        $filters = $request->only(['search', 'type']);

        return Inertia::render('Customer/Points/Index', [
            'histories' => $this->pointHistoryService->getCustomerHistories($customer, $filters, 10),
            'filters' => $filters,
            'types' => $this->typeOptions(),
            'summary' => $this->pointHistoryService->getCustomerSummary($customer),
        ]);
    }

    /**
     * Opsi tipe mutasi poin untuk filter.
     *
     * @return array<int, array{value: string, label: string, short_label: string, tone: string}>
     */
    protected function typeOptions(): array
    {
        return array_map(fn (PointType $type) => [
            'value' => $type->value,
            'label' => $type->label(),
            'short_label' => $type->shortLabel(),
            'tone' => $type->tone(),
        ], PointType::cases());
    }
}
