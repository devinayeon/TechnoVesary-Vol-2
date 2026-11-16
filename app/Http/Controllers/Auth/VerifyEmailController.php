<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        $user = $request->user();

        $targetRoute = match ($user->role) {
            UserRole::ADMIN => route('admin.dashboard', ['verified' => 1]),
            UserRole::DRIVER => route('driver.dashboard', ['verified' => 1]),
            UserRole::CUSTOMER => route('customer.dashboard', ['verified' => 1]),
            default => route('home'),
        };

        if ($user->hasVerifiedEmail()) {
            return redirect()->intended($targetRoute);
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return redirect()->intended($targetRoute);
    }
}
