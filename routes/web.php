<?php

use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\WasteCategoryController;
use App\Http\Controllers\MapController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WelcomeController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Landing Page Publik
Route::get('/', [WelcomeController::class, 'index'])->name('home');

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
                Route::get('/dashboard', function () {
                    return Inertia::render('Admin/Dashboard');
                })->name('dashboard');
                
                // Manajemen Pengguna (Admin Only)
                Route::post('/users/{id}/restore', [UserController::class, 'restore'])->name('users.restore');
                Route::resource('users', UserController::class);

                // Manajemen Jenis Sampah (Admin Only)
                Route::post('/waste-categories/{id}/restore', [WasteCategoryController::class, 'restore'])->name('waste-categories.restore');
                Route::resource('waste-categories', WasteCategoryController::class);
            });

        // 2. Driver Group
        Route::middleware(['role:driver'])
            ->prefix('driver')
            ->name('driver.')
            ->group(function () {
                Route::get('/dashboard', function () {
                    return Inertia::render('Driver/Dashboard');
                })->name('dashboard');
            });

        // 3. Customer Group (Nasabah)
        Route::middleware(['role:customer'])
            ->prefix('customer')
            ->name('customer.')
            ->group(function () {
                Route::get('/dashboard', function () {
                    return Inertia::render('Customer/Dashboard');
                })->name('dashboard');
            });
    });

    // 4. Manajemen Profil Pengguna (Dapat diakses untuk perbaikan data/email)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
