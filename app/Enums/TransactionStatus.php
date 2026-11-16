<?php

namespace App\Enums;

enum TransactionStatus: string
{
    case PENDING = 'pending';
    case SETTLED = 'settled';
    case FAILED = 'failed';
    case CANCELED = 'canceled';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Menunggu Pembayaran',
            self::SETTLED => 'Berhasil / Lunas',
            self::FAILED => 'Gagal',
            self::CANCELED => 'Dibatalkan',
        };
    }
}
