<?php

namespace App\Enums;

enum AuthProvider: string
{
    case Google = 'google';
    // case Apple = 'apple';
    // case Facebook = 'facebook';

    public function label(): string
    {
        return match ($this) {
            self::Google => 'Google',
        };
    }
}
