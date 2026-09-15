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
            self::Guest => __('enums.roles.guest'),
            self::Reception => __('enums.roles.reception'),
            self::Support => __('enums.roles.support'),
            self::Admin => __('enums.roles.admin'),
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
