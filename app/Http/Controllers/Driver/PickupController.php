<?php

namespace App\Http\Controllers\Driver;

use App\Enums\PickupStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Driver\CompletePickupRequest;
use App\Http\Requests\Driver\UpdateDriverLocationRequest;
use App\Models\Driver;
use App\Models\User;
use App\Models\WasteDeposit;
use App\Services\Pickup\PickupManagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PickupController extends Controller
{
    public function __construct(
        private readonly PickupManagementService $pickupService
    ) {}

    /**
     * Daftar tugas penjemputan driver (antrean pending + tugas aktif).
     */
    public function index(Request $request): Response
    {
        $driver = $this->resolveDriver($request);
        $filters = $request->only(['search', 'status']);
        $activePickup = $this->pickupService->getActivePickup($driver);

        return Inertia::render('Driver/Pickups/Index', [
            'pickups' => $this->pickupService->getDriverTasks($driver, $filters, 10),
            'filters' => $filters,
            'statuses' => $this->statusOptions([
                PickupStatus::PENDING,
                PickupStatus::ON_THE_WAY,
                PickupStatus::PICKED_UP,
            ]),
            'activePickup' => $activePickup ? [
                'id' => $activePickup->id,
                'status' => $activePickup->status->value,
                'status_label' => $activePickup->status->shortLabel(),
                'address' => $activePickup->address,
                'customer_name' => $activePickup->customer?->user?->name,
            ] : null,
        ]);
    }

    /**
     * Riwayat penjemputan yang telah diselesaikan / dibatalkan driver.
     */
    public function history(Request $request): Response
    {
        $driver = $this->resolveDriver($request);
        $filters = $request->only(['search', 'status']);

        return Inertia::render('Driver/Pickups/History', [
            'pickups' => $this->pickupService->getDriverHistory($driver, $filters, 10),
            'filters' => $filters,
            'statuses' => $this->statusOptions([
                PickupStatus::COMPLETED,
                PickupStatus::CANCELED,
            ]),
        ]);
    }

    /**
     * Detail tugas penjemputan beserta titik jemput dan lokasi driver.
     */
    public function show(Request $request, WasteDeposit $pickup): Response
    {
        $driver = $this->resolveDriver($request);

        $pickup = $this->pickupService->findDriverTask($driver, $pickup->id);
        $activePickup = $this->pickupService->getActivePickup($driver, $pickup->id);

        return Inertia::render('Driver/Pickups/Show', [
            'pickup' => $pickup,
            'activePickup' => $activePickup ? [
                'id' => $activePickup->id,
                'status' => $activePickup->status->value,
                'status_label' => $activePickup->status->shortLabel(),
                'address' => $activePickup->address,
                'customer_name' => $activePickup->customer?->user?->name,
            ] : null,
            'driverLocation' => [
                'latitude' => $driver->current_latitude !== null ? (float) $driver->current_latitude : null,
                'longitude' => $driver->current_longitude !== null ? (float) $driver->current_longitude : null,
            ],
            'basemapApiKey' => config('osm.basemap_api_key'),
        ]);
    }

    /**
     * Ambil tugas penjemputan (status menjadi driver menuju lokasi).
     */
    public function claim(Request $request, WasteDeposit $pickup): RedirectResponse
    {
        $driver = $this->resolveDriver($request);
        $pickup = $this->pickupService->findDriverTask($driver, $pickup->id);

        $this->captureLocation($request, $driver);
        $this->pickupService->claimPickup($driver, $pickup);

        return redirect()
            ->route('driver.pickups.show', $pickup)
            ->with('success', 'Tugas diambil. Silakan menuju lokasi penjemputan.');
    }

    /**
     * Tandai sudah tiba di lokasi dan sampah siap ditimbang.
     */
    public function markPickedUp(Request $request, WasteDeposit $pickup): RedirectResponse
    {
        $driver = $this->resolveDriver($request);
        $pickup = $this->pickupService->findDriverTask($driver, $pickup->id);

        $this->captureLocation($request, $driver);
        $this->pickupService->markPickedUp($driver, $pickup);

        return redirect()
            ->route('driver.pickups.show', $pickup)
            ->with('success', 'Status diperbarui. Silakan timbang dan unggah bukti penjemputan.');
    }

    /**
     * Selesaikan penjemputan: simpan berat, bukti foto, dan terbitkan poin.
     */
    public function complete(CompletePickupRequest $request, WasteDeposit $pickup): RedirectResponse
    {
        $driver = $this->resolveDriver($request);
        $pickup = $this->pickupService->findDriverTask($driver, $pickup->id);

        $this->pickupService->completePickup(
            $driver,
            $pickup,
            $request->validated('details'),
            $request->file('pickup_proof_photo')
        );

        return redirect()
            ->route('driver.pickups.show', $pickup)
            ->with('success', 'Penjemputan selesai. Poin customer telah diterbitkan.');
    }

    /**
     * Perbarui koordinat terakhir driver untuk rute OSRM.
     */
    public function updateLocation(UpdateDriverLocationRequest $request): JsonResponse|RedirectResponse
    {
        $driver = $this->resolveDriver($request);

        $driver = $this->pickupService->updateDriverLocation(
            $driver,
            (float) $request->validated('latitude'),
            (float) $request->validated('longitude')
        );

        // Permintaan berkala dari pelacakan lokasi (fetch) tidak perlu reload halaman.
        if ($request->wantsJson()) {
            return response()->json([
                'ok' => true,
                'updated_at' => $driver->location_updated_at?->toIso8601String(),
            ]);
        }

        return back()->with('success', 'Lokasi armada diperbarui.');
    }

    /**
     * Simpan koordinat yang dikirim bersama aksi driver (opsional).
     */
    protected function captureLocation(Request $request, Driver $driver): void
    {
        $validated = $request->validate([
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        if (! isset($validated['latitude'], $validated['longitude'])) {
            return;
        }

        $this->pickupService->updateDriverLocation(
            $driver,
            (float) $validated['latitude'],
            (float) $validated['longitude']
        );
    }

    /**
     * Pastikan profil driver tersedia.
     */
    protected function resolveDriver(Request $request): Driver
    {
        /** @var User $user */
        $user = $request->user();

        if ($user->driver) {
            return $user->driver;
        }

        return Driver::create([
            'user_id' => $user->id,
            'is_available' => true,
        ]);
    }

    /**
     * Opsi status penjemputan untuk filter.
     *
     * @param  array<int, PickupStatus>|null  $statuses
     * @return array<int, array{value: string, label: string, tone: string}>
     */
    protected function statusOptions(?array $statuses = null): array
    {
        return array_map(fn (PickupStatus $status) => [
            'value' => $status->value,
            'label' => $status->shortLabel(),
            'tone' => $status->tone(),
        ], $statuses ?? PickupStatus::cases());
    }
}
