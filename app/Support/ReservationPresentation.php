<?php

namespace App\Support;

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
}
