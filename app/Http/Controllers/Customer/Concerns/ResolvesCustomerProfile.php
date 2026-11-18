<?php

namespace App\Http\Controllers\Customer\Concerns;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\Request;

trait ResolvesCustomerProfile
{
    /**
     * Pastikan profil customer tersedia (kompatibilitas data lama).
     */
    protected function resolveCustomer(Request $request): Customer
    {
        /** @var User $user */
        $user = $request->user();

        if ($user->customer) {
            return $user->customer;
        }

        return Customer::create([
            'user_id' => $user->id,
            'points' => 0,
            'is_member' => false,
        ]);
    }
}
