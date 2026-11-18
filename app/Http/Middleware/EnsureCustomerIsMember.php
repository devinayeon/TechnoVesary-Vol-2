<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCustomerIsMember
{
    /**
     * Hanya izinkan customer dengan langganan ReValue+ aktif.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $customer = $request->user()?->customer;

        if (! $customer || ! $customer->isActiveMember()) {
            return redirect()
                ->route('customer.pickups.index')
                ->with('error', 'Fitur pengajuan penjemputan hanya tersedia untuk pelanggan ReValue+ yang aktif.');
        }

        return $next($request);
    }
}
