<?php

namespace App\Http\Controllers\Customer;

use App\Enums\PickupStatus;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Customer\Concerns\ResolvesCustomerProfile;
use App\Http\Requests\Customer\StorePickupRequest;
use App\Models\Customer;
use App\Models\WasteCategory;
use App\Models\WasteDeposit;
use App\Services\Pickup\PickupManagementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PickupController extends Controller
{
    use ResolvesCustomerProfile;

    public function __construct(
        private readonly PickupManagementService $pickupService
    ) {}

    /**
     * Riwayat penjemputan milik customer.
     */
    public function index(Request $request): Response
    {
        $customer = $this->resolveCustomer($request);
        $filters = $request->only(['search', 'status']);

        return Inertia::render('Customer/Pickups/Index', [
            'pickups' => $this->pickupService->getCustomerPickups($customer, $filters, 10),
            'filters' => $filters,
            'statuses' => $this->statusOptions(),
            'membership' => $this->membershipPayload($customer),
        ]);
    }

    /**
     * Form pengajuan penjemputan baru.
     */
    public function create(Request $request): Response
    {
        $customer = $this->resolveCustomer($request);
        $user = $request->user();

        return Inertia::render('Customer/Pickups/Create', [
            'wasteCategories' => WasteCategory::query()
                ->select(['id', 'name', 'description', 'points_per_kg'])
                ->orderBy('name')
                ->get(),
            'defaultLocation' => [
                'address' => $user->address ?? '',
                'latitude' => $user->latitude !== null ? (float) $user->latitude : null,
                'longitude' => $user->longitude !== null ? (float) $user->longitude : null,
            ],
            'basemapApiKey' => config('osm.basemap_api_key'),
            'membership' => $this->membershipPayload($customer),
        ]);
    }

    /**
     * Simpan pengajuan penjemputan baru.
     */
    public function store(StorePickupRequest $request): RedirectResponse
    {
        $customer = $this->resolveCustomer($request);

        $pickup = $this->pickupService->createPickup(
            $customer,
            $request->validated(),
            $request->file('photo')
        );

        return redirect()
            ->route('customer.pickups.show', $pickup)
            ->with('success', 'Pengajuan penjemputan berhasil dikirim. Driver akan segera menuju lokasi Anda.');
    }

    /**
     * Detail penjemputan milik customer.
     */
    public function show(Request $request, WasteDeposit $pickup): Response
    {
        $customer = $this->resolveCustomer($request);

        $pickup = $this->pickupService->findCustomerPickup($customer, $pickup->id);

        return Inertia::render('Customer/Pickups/Show', [
            'pickup' => $pickup,
            'basemapApiKey' => config('osm.basemap_api_key'),
        ]);
    }

    /**
     * Batalkan pengajuan yang masih menunggu driver.
     */
    public function cancel(Request $request, WasteDeposit $pickup): RedirectResponse
    {
        $customer = $this->resolveCustomer($request);
        $pickup = $this->pickupService->findCustomerPickup($customer, $pickup->id);

        $this->pickupService->cancelPickup($customer, $pickup);

        return redirect()
            ->route('customer.pickups.show', $pickup)
            ->with('success', 'Pengajuan penjemputan berhasil dibatalkan.');
    }

    /**
     * Opsi status penjemputan untuk filter.
     *
     * @return array<int, array{value: string, label: string, tone: string}>
     */
    protected function statusOptions(): array
    {
        return array_map(fn (PickupStatus $status) => [
            'value' => $status->value,
            'label' => $status->shortLabel(),
            'tone' => $status->tone(),
        ], PickupStatus::cases());
    }

    /**
     * Ringkasan status keanggotaan untuk banner.
     *
     * @return array{is_member: bool, expires_at: string|null}
     */
    protected function membershipPayload(Customer $customer): array
    {
        return [
            'is_member' => $customer->isActiveMember(),
            'expires_at' => $customer->membership_expires_at?->toIso8601String(),
        ];
    }
}
