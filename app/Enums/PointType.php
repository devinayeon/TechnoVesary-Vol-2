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

    /**
     * Label ringkas untuk chip pada tabel riwayat.
     */
    public function shortLabel(): string
    {
        return match ($this) {
            self::IN => 'Poin Masuk',
            self::OUT => 'Poin Keluar',
        };
    }

    /**
     * Tone warna chip yang konsisten di seluruh halaman.
     */
    public function tone(): string
    {
        return match ($this) {
            self::IN => 'Green',
            self::OUT => 'Amber',
        };
    }
}
