<?php

use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Landing Page Publik
Route::inertia('/', 'Welcome')->name('home');

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
