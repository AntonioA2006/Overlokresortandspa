@extends('layouts.app')

@php
    use App\Enums\ReservationStatus;
    use App\Support\ReservationPresentation;
@endphp

@section('title', __('reception.check_title', ['code' => $reservation->code]))

@section('meta_robots', 'noindex, nofollow')

@section('content')
    <section class="section reception-page">
        <div class="container container--narrow stack">
            <a href="{{ route('reception.dashboard') }}" class="link-cta">
                <span aria-hidden="true">←</span>
                <span>{{ __('reception.back_dashboard') }}</span>
            </a>

            @if (session('status'))
                <div class="inline-message inline-message--info" role="status">{{ session('status') }}</div>
            @endif

            @if (session('reception_error'))
                <div class="inline-message inline-message--error" role="alert">{{ session('reception_error') }}</div>
            @endif

            <header class="section__header" data-reveal>
                <span class="eyebrow">{{ ReservationPresentation::statusEyebrow($reservation->status) }}</span>
                <h1 class="section__title">{{ $reservation->code }}</h1>
                <p class="section__subtitle">{{ $reservation->room->roomType->name }}</p>
            </header>

            <div class="card" data-reveal>
                <div class="card__body">
                    <dl class="reception-check__list">
                        <div>
                            <dt>{{ __('reception.guest') }}</dt>
                            <dd>{{ $reservation->user->name }}</dd>
                        </div>
                        <div>
                            <dt>{{ __('reception.email') }}</dt>
                            <dd>{{ $reservation->user->email }}</dd>
                        </div>
                        <div>
                            <dt>{{ __('reception.room') }}</dt>
                            <dd>{{ __('reception.room_number', ['number' => $reservation->room->number]) }}</dd>
                        </div>
                        <div>
                            <dt>{{ __('reception.stay') }}</dt>
                            <dd>{{ ReservationPresentation::dateRangeSummary($reservation->check_in_date, $reservation->check_out_date) }}</dd>
                        </div>
                        <div>
                            <dt>{{ __('reception.guests') }}</dt>
                            <dd>{{ ReservationPresentation::guestsCountSummary($reservation) }}</dd>
                        </div>
                        <div>
                            <dt>{{ __('reception.status') }}</dt>
                            <dd>
                                <span @class(['badge', ReservationPresentation::statusBadgeClass($reservation->status)])>
                                    {{ ReservationPresentation::statusLabel($reservation->status) }}
                                </span>
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            <div class="card" data-reveal>
                <div class="card__body stack">
                    <h2 class="card__title">{{ __('reception.check_in_steps') }}</h2>

                    <ul class="reception-steps">
                        <li @class(['is-done' => $checkIn?->qr_scanned_at])>
                            {{ __('reception.step_qr') }}
                            @if ($checkIn?->qr_scanned_at)
                                <span>{{ $checkIn->qr_scanned_at->timezone(config('overlook.timezone'))->format('H:i') }}</span>
                            @endif
                        </li>
                        <li @class(['is-done' => $checkIn?->identity_verified_at])>
                            {{ __('reception.step_identity') }}
                            @if ($checkIn?->identity_verified_at)
                                <span>{{ $checkIn->identity_verified_at->timezone(config('overlook.timezone'))->format('H:i') }}</span>
                            @endif
                        </li>
                        <li @class(['is-done' => $checkIn?->room_delivered_at])>
                            {{ __('reception.step_delivery') }}
                            @if ($checkIn?->room_delivered_at)
                                <span>{{ $checkIn->room_delivered_at->timezone(config('overlook.timezone'))->format('H:i') }}</span>
                            @endif
                        </li>
                    </ul>

                    @if ($reservation->status === ReservationStatus::Confirmed)
                        @if (! $checkIn?->identity_verified_at)
                            <form method="POST" action="{{ route('reception.check.verify', $token) }}">
                                @csrf
                                <x-button type="submit" variant="secondary">{{ __('reception.verify_identity') }}</x-button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('reception.check.complete', $token) }}" class="stack">
                                @csrf
                                <div class="form-group">
                                    <label class="form-label" for="check-in-notes">{{ __('reception.notes') }}</label>
                                    <textarea id="check-in-notes" class="form-textarea" name="notes" rows="3" maxlength="1000">{{ old('notes') }}</textarea>
                                </div>
                                <x-button type="submit" variant="primary">{{ __('reception.complete_check_in') }}</x-button>
                            </form>
                        @endif
                    @elseif ($reservation->status === ReservationStatus::CheckedIn)
                        <form method="POST" action="{{ route('reception.check.checkout', $token) }}">
                            @csrf
                            <x-button type="submit" variant="secondary">{{ __('reception.complete_checkout') }}</x-button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endsection
