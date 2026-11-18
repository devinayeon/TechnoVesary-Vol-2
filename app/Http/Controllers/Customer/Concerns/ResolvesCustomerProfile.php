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

        if ($user->customer instanceof Customer) {
            return $user->customer;
        }

        $customer = new Customer;
        $customer->user_id = $user->id;
        $customer->points = 0;
        $customer->is_member = false;
        $customer->save();

        return $customer;
    }
}
