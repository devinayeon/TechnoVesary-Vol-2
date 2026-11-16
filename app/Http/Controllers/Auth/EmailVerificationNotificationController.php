<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EmailVerificationNotificationController extends Controller
{
    /**
     * Send a new email verification notification.
     */
    public function store(Request $request): RedirectResponse
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

        $user->sendEmailVerificationNotification();

        return back()->with('status', 'verification-link-sent');
    }
}
