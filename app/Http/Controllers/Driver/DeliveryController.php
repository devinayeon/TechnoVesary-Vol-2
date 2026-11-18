<?php

namespace App\Http\Controllers\Driver;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DeliveryController extends Controller
{
    /**
     * Menampilkan daftar tugas pengantaran produk untuk driver yang sedang login.
     */
    public function index(Request $request): Response
    {
        /** @var User $user */
        $user = $request->user();
        $driver = $user->driver;

        if (! $driver) {
            abort(403, 'Anda tidak terdaftar sebagai driver.');
        }

        $filters = $request->only(['search', 'status']);

        $baseQuery = Transaction::where('driver_id', $driver->id);

        // Ringkasan metrik
        $activeCount = (clone $baseQuery)->where('status', OrderStatus::DELIVERING)->count();
        $deliveredCount = (clone $baseQuery)->where('status', OrderStatus::DELIVERED)->count();
        $completedCount = (clone $baseQuery)->where('status', OrderStatus::COMPLETED)->count();

        $query = (clone $baseQuery)->with([
            'customer.user:id,name,email,photo',
            'details.product',
        ]);

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                    ->orWhere('delivery_address', 'like', "%{$search}%")
                    ->orWhereHas('customer.user', function ($q2) use ($search) {
                        $q2->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('details.product', function ($q3) use ($search) {
                        $q3->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $deliveries = $query->orderByRaw("
            CASE 
                WHEN status = 'delivering' THEN 1 
                WHEN status = 'delivered' THEN 2 
                WHEN status = 'completed' THEN 3 
                ELSE 4 
            END ASC
        ")->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        $statuses = [
            ['value' => OrderStatus::DELIVERING->value, 'label' => OrderStatus::DELIVERING->label()],
            ['value' => OrderStatus::DELIVERED->value, 'label' => OrderStatus::DELIVERED->label()],
            ['value' => OrderStatus::COMPLETED->value, 'label' => OrderStatus::COMPLETED->label()],
        ];

        return Inertia::render('Driver/Deliveries/Index', [
            'deliveries' => $deliveries,
            'filters' => $filters,
            'statuses' => $statuses,
            'metrics' => [
                'active' => $activeCount,
                'delivered' => $deliveredCount,
                'completed' => $completedCount,
            ],
        ]);
    }

    /**
     * Menampilkan detail spesifik pengantaran pesanan.
     */
    public function show(Request $request, Transaction $transaction): Response
    {
        /** @var User $user */
        $user = $request->user();
        $driver = $user->driver;

        if (! $driver || $transaction->driver_id !== $driver->id) {
            abort(403, 'Akses tidak sah untuk pengantaran ini.');
        }

        $transaction->load([
            'customer.user:id,name,email,photo',
            'details.product',
        ]);

        return Inertia::render('Driver/Deliveries/Show', [
            'delivery' => $transaction,
            'driverLocation' => [
                'latitude' => $driver->current_latitude ? (float) $driver->current_latitude : null,
                'longitude' => $driver->current_longitude ? (float) $driver->current_longitude : null,
            ],
            'basemapApiKey' => config('osm.basemap_api_key'),
        ]);
    }

    /**
     * Mengunggah foto bukti serah terima dan memperbarui status menjadi delivered (terkirim).
     */
    public function completeDelivery(Request $request, Transaction $transaction): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $driver = $user->driver;

        if (! $driver || $transaction->driver_id !== $driver->id) {
            abort(403, 'Akses tidak sah untuk pengantaran ini.');
        }

        if ($transaction->status !== OrderStatus::DELIVERING) {
            return back()->with('error', 'Hanya pesanan yang sedang dikirim yang dapat ditandai selesai antar.');
        }

        $request->validate([
            'delivery_proof_photo' => 'required|image|mimes:jpeg,png,jpg,webp|max:4096',
        ], [
            'delivery_proof_photo.required' => 'Wajib mengunggah foto bukti serah terima pesanan.',
            'delivery_proof_photo.image' => 'File harus berupa gambar foto.',
            'delivery_proof_photo.max' => 'Ukuran foto maksimal 4 MB.',
        ]);

        DB::transaction(function () use ($request, $transaction) {
            $path = $request->file('delivery_proof_photo')->store('delivery_proofs', 'public');
            $photoUrl = '/storage/'.$path;

            $transaction->update([
                'status' => OrderStatus::DELIVERED,
                'delivery_proof_photo' => $photoUrl,
                'delivered_at' => now(),
            ]);
        });

        return redirect()->route('driver.deliveries.index')->with(
            'success',
            "Bukti pengantaran berhasil diunggah! Pesanan #TRX-{$transaction->id} kini berstatus Terkirim dan menunggu konfirmasi penerimaan oleh pelanggan."
        );
    }
}
