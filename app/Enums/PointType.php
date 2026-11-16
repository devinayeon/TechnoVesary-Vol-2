<?php

namespace App\Enums;

enum PointType: string
{
    case IN = 'in';
    case OUT = 'out';

    public function label(): string
    {
        return match ($this) {
            self::IN => 'Poin Masuk (Reward)',
            self::OUT => 'Poin Keluar (Penukaran / Belanja)',
        };
    }
}
