<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case MIDTRANS = 'midtrans';
    case POINTS = 'points';

    public function label(): string
    {
        return match ($this) {
            self::MIDTRANS => 'Midtrans Payment Gateway',
            self::POINTS => 'Poin Bank Sampah (Full)',
        };
    }
}
