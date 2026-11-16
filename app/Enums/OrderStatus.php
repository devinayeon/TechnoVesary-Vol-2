<?php

namespace App\Enums;

enum OrderStatus: string
{
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case DELIVERING = 'delivering';
    case COMPLETED = 'completed';
    case CANCELED = 'canceled';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Menunggu Pembayaran',
            self::PROCESSING => 'Sedang Diproses Gudang',
            self::DELIVERING => 'Sedang Dikirim Driver',
            self::COMPLETED => 'Pesanan Diterima',
            self::CANCELED => 'Pesanan Dibatalkan',
        };
    }
}
