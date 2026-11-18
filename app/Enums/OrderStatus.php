<?php

namespace App\Enums;

enum OrderStatus: string
{
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case DELIVERING = 'delivering';
    case DELIVERED = 'delivered';
    case COMPLETED = 'completed';
    case CANCELED = 'canceled';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Menunggu Pembayaran',
            self::PROCESSING => 'Sedang Diproses Gudang',
            self::DELIVERING => 'Sedang Dikirim Driver',
            self::DELIVERED => 'Pesanan Terkirim',
            self::COMPLETED => 'Pesanan Selesai',
            self::CANCELED => 'Pesanan Dibatalkan',
        };
    }
}
