<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EmailVerificationPromptController extends Controller
{
    /**
     * Display the email verification prompt.
     */
    public function __invoke(Request $request): Response|RedirectResponse
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            $targetRoute = match ($user->role) {
                UserRole::ADMIN => route('admin.dashboard'),
                UserRole::DRIVER => route('driver.dashboard'),
                UserRole::CUSTOMER => route('customer.dashboard'),
                default => route('home'),
            };

            return redirect()->intended($targetRoute);
        }

        return Inertia::render('Auth/VerifyEmail', ['status' => session('status')]);
    }
}
