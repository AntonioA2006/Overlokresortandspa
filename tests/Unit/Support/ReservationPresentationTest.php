<?php

namespace Tests\Unit\Support;

use App\Support\ReservationPresentation;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReservationPresentationTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_formats_date_range_in_spanish(): void
    {
        app()->setLocale('es');

        $summary = ReservationPresentation::dateRangeSummary(
            Carbon::parse('2026-09-12'),
            Carbon::parse('2026-09-15'),
        );

        $this->assertSame('12 — 15 septiembre', $summary);
    }

    public function test_it_formats_date_range_in_english(): void
    {
        app()->setLocale('en');

        $summary = ReservationPresentation::dateRangeSummary(
            Carbon::parse('2026-09-12'),
            Carbon::parse('2026-09-15'),
        );

        $this->assertSame('September 12 — 15, 2026', $summary);
    }

    public function test_it_formats_guests_summary_in_both_locales(): void
    {
        app()->setLocale('es');
        $this->assertSame('2 adultos · 0 niños', ReservationPresentation::guestsSummary(2, 0));

        app()->setLocale('en');
        $this->assertSame('2 adults · 0 children', ReservationPresentation::guestsSummary(2, 0));
    }
}
