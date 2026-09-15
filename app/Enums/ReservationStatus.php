<?php

namespace App\Enums;

enum ReservationStatus: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case Cancelled = 'cancelled';
    case CheckedIn = 'checked_in';
    case CheckedOut = 'checked_out';
    case NoShow = 'no_show';

    public function label(): string
    {
        return match ($this) {
            self::Pending => __('enums.reservation_status.pending'),
            self::Confirmed => __('enums.reservation_status.confirmed'),
            self::Cancelled => __('enums.reservation_status.cancelled'),
            self::CheckedIn => __('enums.reservation_status.checked_in'),
            self::CheckedOut => __('enums.reservation_status.checked_out'),
            self::NoShow => __('enums.reservation_status.no_show'),
        };
    }

    /**
     * @return list<self>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Pending => [self::Confirmed, self::Cancelled],
            self::Confirmed => [self::Cancelled, self::CheckedIn, self::NoShow],
            self::CheckedIn => [self::CheckedOut],
            self::Cancelled, self::CheckedOut, self::NoShow => [],
        };
    }

    public function canTransitionTo(self $target): bool
    {
        return in_array($target, $this->allowedTransitions(), true);
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
