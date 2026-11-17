<?php

namespace App\Enums;

enum UserRole: string
{
    case ADMIN = 'admin';
    case DRIVER = 'driver';
    case CUSTOMER = 'customer';

    /**
     * Mendapatkan label deskriptif dalam Bahasa Indonesia
     */
    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Admin',
            self::DRIVER => 'Driver',
            self::CUSTOMER => 'Customer',
        };
    }

    /**
     * Mengembalikan seluruh nilai role sebagai array
     *
     * @return array<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
