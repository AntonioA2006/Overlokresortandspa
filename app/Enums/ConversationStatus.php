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
            self::Open => __('support.status_open'),
            self::Waiting => __('support.status_waiting'),
            self::Closed => __('support.status_closed'),
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
