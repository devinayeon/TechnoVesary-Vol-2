<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = $request->user();

        // Gating: Jika email belum diverifikasi, arahkan ke halaman verifikasi
        if (! $user->hasVerifiedEmail()) {
            return redirect()->route('verification.notice');
        }

        // Redirect dinamis berbasis role Bank Sampah Digital
        return redirect()->intended($this->resolveDashboardRoute($user->role));
    }

    /**
     * Handle an instant demo login request for evaluators.
     */
    public function demo(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'role' => ['required', 'string', 'in:admin,driver,customer'],
        ]);

        $role = is_string($validated['role']) ? $validated['role'] : 'admin';

        $targetEmail = match ($role) {
            'admin' => 'admin@banksampah.test',
            'driver' => 'driver@banksampah.test',
            'customer' => 'nasabah@banksampah.test',
            default => 'admin@banksampah.test',
        };

        // Prioritas cari akun seeder berdasarkan email, atau fallback ke user pertama dengan role yang sesuai
        $user = User::where('email', $targetEmail)->first();

        if (! $user) {
            $roleEnum = match ($role) {
                'admin' => UserRole::ADMIN,
                'driver' => UserRole::DRIVER,
                'customer' => UserRole::CUSTOMER,
                default => UserRole::ADMIN,
            };
            $user = User::where('role', $roleEnum)->first();
        }

        if (! $user) {
            return back()->withErrors([
                'email' => 'Akun demo untuk role ini belum tersedia di database.',
            ]);
        }

        Auth::login($user, true);

        $request->session()->regenerate();

        return redirect()->intended($this->resolveDashboardRoute($user->role));
    }

    /**
     * Resolve target dashboard route based on user role.
     */
    private function resolveDashboardRoute(UserRole $role): string
    {
        return match ($role) {
            UserRole::ADMIN => route('admin.dashboard'),
            UserRole::DRIVER => route('driver.dashboard'),
            UserRole::CUSTOMER => route('customer.dashboard'),
        };
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
