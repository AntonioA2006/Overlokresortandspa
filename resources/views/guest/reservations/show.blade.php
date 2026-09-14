@extends('layouts.app')

@php
    use App\Enums\ReservationStatus;
    use App\Support\ReservationPresentation;
@endphp

@section('title', __('reservations.page_title_reservation', ['code' => $reservation->code]))

@section('meta_robots', 'noindex, nofollow')

@section('content')
    <section class="reservation-show section">
        <div class="container container--narrow">
            @if (session('status'))
                <div class="inline-message inline-message--info" role="status" data-reveal>
                    {{ session('status') }}
                </div>
            @endif

            @if (session('reservation_error'))
                <div class="inline-message inline-message--error" role="alert" data-reveal>
                    {{ session('reservation_error') }}
                </div>
            @endif

            <header class="reservation-show__header" data-reveal>
                <span class="eyebrow">{{ ReservationPresentation::statusEyebrow($reservation->status) }}</span>
                <h1 class="reservation-show__title">{{ $roomType->name }}</h1>
                <p class="reservation-show__code">{{ __('reservations.reservation_code', ['code' => $reservation->code]) }}</p>
            </header>

            <div class="reservation-show__media" data-reveal>
                <img
                    src="{{ $photo['url'] }}"
                    alt="{{ $photo['alt'] }}"
                    width="1200"
                    height="900"
                    loading="eager"
                    decoding="async"
                >
            </div>

            <div class="reservation-show__details" data-reveal>
                <dl class="reservation-show__list">
                    <div class="reservation-show__item">
                        <dt>{{ __('reservations.status_label') }}</dt>
                        <dd>
                            <span @class(['badge', ReservationPresentation::statusBadgeClass($reservation->status)])>
                                {{ ReservationPresentation::statusLabel($reservation->status) }}
                            </span>
                        </dd>
                    </div>
                    <div class="reservation-show__item">
                        <dt>{{ __('reservations.your_stay') }}</dt>
                        <dd>{{ $dateSummary }}</dd>
                    </div>
                    <div class="reservation-show__item">
                        <dt>{{ __('reservations.guests_field') }}</dt>
                        <dd>{{ $guestSummary }}</dd>
                    </div>
                    <div class="reservation-show__item">
                        <dt>{{ __('reservations.total_label') }}</dt>
                        <dd>
                            ${{ number_format((float) $reservation->price_total, 0, '.', ',') }}
                            {{ config('overlook.currency', 'MXN') }}
                        </dd>
                    </div>
                </dl>

                @if ($checkInUrl)
                    <div
                        id="reservation-qr"
                        class="reservation-show__qr"
                        data-reservation-qr
                        data-check-url="{{ $checkInUrl }}"
                    >
                        <h2 class="reservation-show__qr-title">{{ __('reservations.qr_title') }}</h2>
                        <p class="reservation-show__qr-text">{{ __('reservations.qr_instructions') }}</p>
                        <div class="reservation-show__qr-frame">
                            <canvas
                                role="img"
                                aria-label="{{ __('reservations.qr_alt', ['code' => $reservation->code]) }}"
                            ></canvas>
                        </div>
                        <p class="reservation-show__qr-note">{{ __('reservations.qr_reception_note') }}</p>
                    </div>
                @endif

                @switch($reservation->status)
                    @case(ReservationStatus::Cancelled)
                        <p class="reservation-show__note">{{ __('reservations.cancelled_note') }}</p>
                        @break
                    @case(ReservationStatus::Pending)
                        <p class="reservation-show__note">{{ __('reservations.pending_payment_note') }}</p>
                        @break
                    @case(ReservationStatus::CheckedOut)
                        <p class="reservation-show__note">{{ __('reservations.checked_out_note') }}</p>
                        @break
                    @case(ReservationStatus::NoShow)
                        <p class="reservation-show__note">{{ __('reservations.no_show_note') }}</p>
                        @break
                    @default
                        <p class="reservation-show__note">{{ __('reservations.confirmed_note') }}</p>
                @endswitch

                <div class="reservation-show__actions">
                    @can('cancel', $reservation)
                        <form
                            method="POST"
                            action="{{ route('guest.reservations.cancel', $reservation) }}"
                            class="reservation-show__cancel-form"
                        >
                            @csrf
                            <x-button type="submit" variant="ghost">
                                {{ __('reservations.cancel_reservation') }}
                            </x-button>
                        </form>
                    @endcan

                    <x-button href="{{ route('guest.reservations.index') }}" variant="secondary">
                        {{ __('reservations.view_my_stays') }}
                    </x-button>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('meta_description', __('reservations.meta_reservation', ['code' => $reservation->code]))
