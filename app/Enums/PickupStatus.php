<?php

namespace App\Enums;

enum PickupStatus: string
{
    case PENDING = 'pending';
    case ON_THE_WAY = 'on_the_way';
    case PICKED_UP = 'picked_up';
    case COMPLETED = 'completed';
    case CANCELED = 'canceled';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Menunggu Penugasan Driver',
            self::ON_THE_WAY => 'Driver Menuju Lokasi',
            self::PICKED_UP => 'Sampah Diambil / Ditimbang',
            self::COMPLETED => 'Selesai & Poin Diterbitkan',
            self::CANCELED => 'Penjemputan Dibatalkan',
        };
    }
}
