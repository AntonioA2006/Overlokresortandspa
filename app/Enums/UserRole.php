<?php

namespace App\Enums;

enum UserRole: string
{
    case Guest = 'guest';
    case Reception = 'reception';
    case Support = 'support';
    case Admin = 'admin';

    public function label(): string
    {
        return match ($this) {
            self::Guest => 'Huésped',
            self::Reception => 'Recepción',
            self::Support => 'Soporte',
            self::Admin => 'Administrador',
        };
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
