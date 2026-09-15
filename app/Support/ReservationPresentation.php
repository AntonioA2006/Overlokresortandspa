<?php

namespace App\Support;

use App\Enums\ReservationStatus;
use App\Models\Reservation;
use Carbon\CarbonInterface;

class ReservationPresentation
{
    public static function dateRangeSummary(CarbonInterface $checkIn, CarbonInterface $checkOut): string
    {
        if (app()->getLocale() === 'en') {
            if ($checkIn->month === $checkOut->month && $checkIn->year === $checkOut->year) {
                return $checkIn->format('F j').' — '.$checkOut->format('j, Y');
            }

            return $checkIn->format('F j, Y').' — '.$checkOut->format('F j, Y');
        }

        /** @var array<int, string> $months */
        $months = __('reservations.months_short');
        $checkInMonth = $months[$checkIn->month];
        $checkOutMonth = $months[$checkOut->month];

        if ($checkIn->year === $checkOut->year && $checkIn->month === $checkOut->month) {
            return "{$checkIn->day} — {$checkOut->day} {$checkInMonth}";
        }

        $summary = "{$checkIn->day} {$checkInMonth} — {$checkOut->day} {$checkOutMonth}";

        if ($checkIn->year !== $checkOut->year) {
            return "{$summary} {$checkOut->year}";
        }

        return $summary;
    }

    public static function guestsSummary(int $adults, int $children): string
    {
        return trans_choice('reservations.adults_label', $adults, ['count' => $adults])
            .' · '
            .trans_choice('reservations.children_label', $children, ['count' => $children]);
    }

    public static function statusLabel(ReservationStatus $status): string
    {
        return __('reservations.statuses.'.$status->value);
    }

    public static function statusEyebrow(ReservationStatus $status): string
    {
        return match ($status) {
            ReservationStatus::Confirmed => __('reservations.reservation_confirmed'),
            ReservationStatus::Pending => __('reservations.reservation_created'),
            ReservationStatus::Cancelled => __('reservations.reservation_cancelled'),
            ReservationStatus::CheckedIn => __('reservations.statuses.checked_in'),
            ReservationStatus::CheckedOut => __('reservations.statuses.checked_out'),
            default => self::statusLabel($status),
        };
    }

    public static function statusBadgeClass(ReservationStatus $status): string
    {
        return 'badge--'.$status->value;
    }

    public static function guestsCountSummary(Reservation $reservation): string
    {
        return trans_choice(
            'reservations.guests_count_label',
            $reservation->guests_count,
            ['count' => $reservation->guests_count],
        );
    }
}
