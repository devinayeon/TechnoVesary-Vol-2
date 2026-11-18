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

    /**
     * Label ringkas untuk chip pada tabel riwayat.
     */
    public function shortLabel(): string
    {
        return match ($this) {
            self::PENDING => 'Menunggu Driver',
            self::ON_THE_WAY => 'Driver Menuju',
            self::PICKED_UP => 'Sedang Ditimbang',
            self::COMPLETED => 'Selesai',
            self::CANCELED => 'Dibatalkan',
        };
    }

    /**
     * Tone warna chip yang konsisten di seluruh halaman.
     */
    public function tone(): string
    {
        return match ($this) {
            self::PENDING => 'Amber',
            self::ON_THE_WAY => 'Blue',
            self::PICKED_UP => 'Amber',
            self::COMPLETED => 'Green',
            self::CANCELED => 'Neutral',
        };
    }

    /**
     * Apakah driver sudah ditugaskan pada status ini.
     */
    public function hasDriver(): bool
    {
        return in_array($this, [self::ON_THE_WAY, self::PICKED_UP, self::COMPLETED], true);
    }

    /**
     * Apakah poin sudah diterbitkan pada status ini.
     */
    public function isSettled(): bool
    {
        return $this === self::COMPLETED;
    }
}
