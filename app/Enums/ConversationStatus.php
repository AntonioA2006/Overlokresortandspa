<?php

namespace App\Enums;

enum ConversationStatus: string
{
    case Open = 'open';
    case Waiting = 'waiting';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::Open => 'Abierta',
            self::Waiting => 'En espera',
            self::Closed => 'Cerrada',
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
