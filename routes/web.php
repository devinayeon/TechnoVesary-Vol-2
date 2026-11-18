<?php

use App\Http\Controllers\Admin\ArticleCategoryController;
use App\Http\Controllers\Admin\ArticleController as AdminArticleController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\PickupHistoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProductTransactionController;
use App\Http\Controllers\Admin\SubscriptionController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\WasteCategoryController;
use App\Http\Controllers\ArticleController as PublicArticleController;
use App\Http\Controllers\Customer\ArticleController as CustomerArticleController;
use App\Http\Controllers\Customer\CheckoutController as CustomerCheckoutController;
use App\Http\Controllers\Customer\DashboardController as CustomerDashboardController;
use App\Http\Controllers\Customer\PickupController as CustomerPickupController;
use App\Http\Controllers\Customer\PointHistoryController as CustomerPointHistoryController;
use App\Http\Controllers\Customer\ProductController as CustomerProductController;
use App\Http\Controllers\Customer\SubscriptionHistoryController as CustomerSubscriptionHistoryController;
use App\Http\Controllers\Customer\SubscriptionPlanController as CustomerSubscriptionPlanController;
use App\Http\Controllers\Customer\TransactionHistoryController as CustomerTransactionHistoryController;
use App\Http\Controllers\Driver\DashboardController as DriverDashboardController;
use App\Http\Controllers\Driver\DeliveryController as DriverDeliveryController;
use App\Http\Controllers\Driver\PickupController as DriverPickupController;
use App\Http\Controllers\MapController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Webhook\MidtransWebhookController;
use App\Http\Controllers\WelcomeController;
use Illuminate\Support\Facades\Route;

// Landing Page Publik
Route::get('/', [WelcomeController::class, 'index'])->name('home');

// Detail Artikel Publik
Route::get('/artikel/{slug}', [PublicArticleController::class, 'show'])->name('articles.show');

// Webhook Notifikasi Pembayaran Midtrans (Tanpa Auth, Validasi via Signature Key)
Route::post('/api/midtrans/notification', [MidtransWebhookController::class, 'handle'])->name('midtrans.notification');

/*
|--------------------------------------------------------------------------
| Map / OSM Public API Endpoints (Internal Proxy — Rate Limited)
|--------------------------------------------------------------------------
| Endpoints ini bersifat semi-publik (accessible tanpa login) untuk mendukung
| Landing Page, namun dibatasi throttle untuk mencegah penyalahgunaan API.
*/
Route::prefix('map')->name('map.')->middleware(['throttle:60,1'])->group(function () {
    Route::get('/config', [MapController::class, 'config'])->name('config');
    Route::get('/route', [MapController::class, 'route'])->name('route');
    Route::get('/reverse-geocode', [MapController::class, 'reverseGeocode'])->name('reverse-geocode');
    Route::get('/search', [MapController::class, 'search'])->name('search');
});

/*
|--------------------------------------------------------------------------
| Protected Application Routes (Bank Sampah Digital)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    // Rute yang WAJIB terverifikasi email (Gated Dashboards)
    Route::middleware(['verified'])->group(function () {

        // 1. Admin Group
        Route::middleware(['role:admin'])
            ->prefix('admin')
            ->name('admin.')
            ->group(function () {
                Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

                // Manajemen Pengguna (Admin Only)
                Route::post('/users/{id}/restore', [UserController::class, 'restore'])->name('users.restore');
                Route::resource('users', UserController::class)->withTrashed(['show', 'edit']);

                // Manajemen Jenis Sampah (Admin Only)
                Route::post('/waste-categories/{id}/restore', [WasteCategoryController::class, 'restore'])->name('waste-categories.restore');
                Route::resource('waste-categories', WasteCategoryController::class)->withTrashed(['show', 'edit', 'update']);

                // Manajemen Langganan (Admin Only)
                Route::get('/subscriptions', [SubscriptionController::class, 'index'])->name('subscriptions.index');
                Route::put('/subscriptions/{subscription}', [SubscriptionController::class, 'update'])->name('subscriptions.update');

                // Manajemen Kategori Artikel (Admin Only)
                Route::resource('article-categories', ArticleCategoryController::class)->only(['index', 'store', 'update', 'destroy']);

                // Manajemen Artikel Edukasi (Admin Only)
                Route::post('/articles/upload-image', [AdminArticleController::class, 'uploadImage'])->name('articles.upload-image');
                Route::post('/articles/{id}/restore', [AdminArticleController::class, 'restore'])->name('articles.restore');
                Route::resource('articles', AdminArticleController::class)->withTrashed(['show', 'edit', 'update']);

                // Manajemen Produk Upcycle Store (Admin Only)
                Route::post('/products/{id}/restore', [ProductController::class, 'restore'])->name('products.restore');
                Route::resource('products', ProductController::class)->withTrashed(['show', 'edit', 'update']);

                // Riwayat Penjemputan (Admin Only)
                Route::get('/pickup-history', [PickupHistoryController::class, 'index'])->name('pickup-history.index');

                // Riwayat Transaksi Produk (Admin Only)
                Route::post('/product-transactions/{transaction}/assign-driver', [ProductTransactionController::class, 'assignDriver'])->name('product-transactions.assign-driver');
                Route::get('/product-transactions', [ProductTransactionController::class, 'index'])->name('product-transactions.index');
            });

        // 2. Driver Group
        Route::middleware(['role:driver'])
            ->prefix('driver')
            ->name('driver.')
            ->group(function () {
                Route::get('/dashboard', [DriverDashboardController::class, 'index'])->name('dashboard');

                // Tugas Penjemputan (Driver)
                Route::get('/pickups', [DriverPickupController::class, 'index'])->name('pickups.index');
                Route::get('/pickups/history', [DriverPickupController::class, 'history'])->name('pickups.history');
                Route::get('/pickups/{pickup}', [DriverPickupController::class, 'show'])->name('pickups.show');
                Route::post('/pickups/{pickup}/claim', [DriverPickupController::class, 'claim'])->name('pickups.claim');
                Route::post('/pickups/{pickup}/picked-up', [DriverPickupController::class, 'markPickedUp'])->name('pickups.picked-up');
                Route::post('/pickups/{pickup}/complete', [DriverPickupController::class, 'complete'])->name('pickups.complete');
                Route::post('/location', [DriverPickupController::class, 'updateLocation'])
                    ->middleware('throttle:60,1')
                    ->name('location.update');

                // Pengantaran Pesanan Toko (Driver)
                Route::get('/deliveries', [DriverDeliveryController::class, 'index'])->name('deliveries.index');
                Route::get('/deliveries/{transaction}', [DriverDeliveryController::class, 'show'])->name('deliveries.show');
                Route::post('/deliveries/{transaction}/complete', [DriverDeliveryController::class, 'completeDelivery'])->name('deliveries.complete');
            });

        // 3. Customer Group
        Route::middleware(['role:customer'])
            ->prefix('customer')
            ->name('customer.')
            ->group(function () {
                Route::get('/dashboard', [CustomerDashboardController::class, 'index'])->name('dashboard');

                // Penjemputan Sampah (Customer)
                Route::get('/pickups', [CustomerPickupController::class, 'index'])->name('pickups.index');
                Route::get('/pickups/create', [CustomerPickupController::class, 'create'])
                    ->middleware('member')
                    ->name('pickups.create');
                Route::post('/pickups', [CustomerPickupController::class, 'store'])
                    ->middleware('member')
                    ->name('pickups.store');
                Route::get('/pickups/{pickup}', [CustomerPickupController::class, 'show'])->name('pickups.show');
                Route::post('/pickups/{pickup}/cancel', [CustomerPickupController::class, 'cancel'])->name('pickups.cancel');

                // Riwayat Poin (Customer)
                Route::get('/points', [CustomerPointHistoryController::class, 'index'])->name('points.index');

                // Artikel Edukasi (Customer)
                Route::get('/articles', [CustomerArticleController::class, 'index'])->name('articles.index');
                Route::get('/articles/{slug}', [CustomerArticleController::class, 'show'])->name('articles.show');

                // Upcycle Store (Customer)
                Route::get('/products', [CustomerProductController::class, 'index'])->name('products.index');
                Route::get('/products/{product}', [CustomerProductController::class, 'show'])->name('products.show');

                // Checkout Pesanan (Customer)
                Route::get('/checkout', [CustomerCheckoutController::class, 'index'])->name('checkout.index');
                Route::post('/checkout', [CustomerCheckoutController::class, 'store'])->name('checkout.store');

                // Riwayat Pesanan Saya (Customer)
                Route::get('/transactions', [CustomerTransactionHistoryController::class, 'index'])->name('transactions.index');
                Route::post('/transactions/{transaction}/cancel', [CustomerTransactionHistoryController::class, 'cancel'])->name('transactions.cancel');
                Route::post('/transactions/{transaction}/repay', [CustomerTransactionHistoryController::class, 'repay'])->name('transactions.repay');
                Route::post('/transactions/{transaction}/check-status', [CustomerTransactionHistoryController::class, 'checkStatus'])->name('transactions.check-status');
                Route::post('/transactions/{transaction}/confirm-received', [CustomerTransactionHistoryController::class, 'confirmReceived'])->name('transactions.confirm-received');
                // Langganan ReValue+ (Customer)
                Route::get('/subscription', [CustomerSubscriptionPlanController::class, 'index'])->name('subscription.index');
                Route::get('/subscription/checkout', [CustomerSubscriptionPlanController::class, 'checkout'])->name('subscription.checkout');
                Route::post('/subscription/checkout', [CustomerSubscriptionPlanController::class, 'processCheckout'])->name('subscription.checkout.process');
                Route::get('/subscription/history', [CustomerSubscriptionHistoryController::class, 'index'])->name('subscription.history');
                Route::post('/subscription/history/{transaction}/check-status', [CustomerSubscriptionHistoryController::class, 'checkStatus'])->name('subscription.history.check-status');
                Route::post('/subscription/history/{transaction}/repay', [CustomerSubscriptionHistoryController::class, 'repay'])->name('subscription.history.repay');
                Route::post('/subscription/history/{transaction}/cancel', [CustomerSubscriptionHistoryController::class, 'cancel'])->name('subscription.history.cancel');
            });
    });

    // 4. Manajemen Profil Pengguna (Dapat diakses untuk perbaikan data/email)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
