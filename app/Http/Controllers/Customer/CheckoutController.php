<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Customer\Concerns\ResolvesCustomerProfile;
use App\Models\Product;
use App\Models\User;
use App\Services\Customer\ProductOrderService;
use App\Services\Payment\MidtransService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CheckoutController extends Controller
{
    use ResolvesCustomerProfile;

    public function __construct(
        private readonly ProductOrderService $orderService,
        private readonly MidtransService $midtransService
    ) {}

    /**
     * Menampilkan halaman checkout untuk produk yang dipilih.
     */
    public function index(Request $request): Response|RedirectResponse
    {
        $productId = $request->query('product_id');
        $quantity = max(1, (int) $request->query('quantity', 1));

        if (! $productId) {
            return redirect()->route('customer.products.index')
                ->with('error', 'Silakan pilih produk yang ingin dibeli terlebih dahulu.');
        }

        $product = Product::findOrFail($productId);

        if ($product->stock <= 0) {
            return redirect()->route('customer.products.show', $product)
                ->with('error', 'Maaf, stok produk ini sedang habis.');
        }

        /** @var User $user */
        $user = $request->user();
        $customer = $this->resolveCustomer($request);

        return Inertia::render('Customer/Checkout/Index', [
            'product' => $product,
            'quantity' => min($quantity, $product->stock),
            'customerPoints' => (int) $customer->points,
            'defaultAddress' => [
                'address' => $user->address ?? '',
                'latitude' => $user->latitude ? (float) $user->latitude : null,
                'longitude' => $user->longitude ? (float) $user->longitude : null,
            ],
            'clientKey' => $this->midtransService->getClientKey(),
            'snapJsUrl' => $this->midtransService->getSnapJsUrl(),
            'basemapApiKey' => config('osm.basemap_api_key'),
        ]);
    }

    /**
     * Memproses pesanan produk (Pembayaran Poin atau Midtrans).
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'delivery_address' => 'required|string|max:500',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'use_points' => 'nullable|boolean',
        ], [
            'delivery_address.required' => 'Alamat pengiriman wajib diisi.',
            'quantity.min' => 'Jumlah pembelian minimal 1 item.',
        ]);

        /** @var User $user */
        $user = $request->user();
        $customer = $this->resolveCustomer($request);

        $product = Product::findOrFail($validated['product_id']);

        try {
            $orderResult = $this->orderService->createOrder(
                customer: $customer,
                product: $product,
                quantity: (int) $validated['quantity'],
                deliveryAddress: $validated['delivery_address'],
                latitude: isset($validated['latitude']) ? (float) $validated['latitude'] : null,
                longitude: isset($validated['longitude']) ? (float) $validated['longitude'] : null,
                usePoints: (bool) ($validated['use_points'] ?? false)
            );

            // Jika pesanan lunas penuh via Poin ReValue
            if ($orderResult['is_paid_fully']) {
                if ($request->wantsJson()) {
                    return response()->json([
                        'success' => true,
                        'is_paid_fully' => true,
                        'redirect_url' => route('customer.transactions.index'),
                        'message' => 'Pembayaran berhasil menggunakan Poin ReValue! Pesanan Anda segera diproses gudang.',
                    ]);
                }

                return redirect()->route('customer.transactions.index')
                    ->with('success', 'Pembayaran berhasil menggunakan Poin ReValue! Pesanan Anda segera diproses gudang.');
            }

            // Jika ada sisa pembayaran yang memerlukan Midtrans Snap
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'is_paid_fully' => false,
                    'snap_token' => $orderResult['snap_token'],
                    'snap_url' => $orderResult['snap_url'],
                    'transaction_id' => $orderResult['transaction']->id,
                    'redirect_url' => route('customer.transactions.index'),
                ]);
            }

            return redirect()->route('customer.transactions.index')
                ->with('success', 'Pesanan dibuat. Silakan selesaikan pembayaran.');
        } catch (\Throwable $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }

            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
