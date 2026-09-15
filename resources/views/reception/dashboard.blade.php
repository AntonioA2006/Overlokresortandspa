@extends('layouts.app')

@php
    use App\Enums\ReservationStatus;
    use App\Support\ReservationPresentation;
@endphp

@section('title', __('reception.dashboard_title'))

@section('meta_robots', 'noindex, nofollow')

@section('content')
    <section class="section reception-page">
        <div class="container stack">
            <div class="section__header" data-reveal>
                <span class="eyebrow">{{ $hotelName }}</span>
                <h1 class="section__title">{{ __('reception.dashboard_heading') }}</h1>
                <p class="section__subtitle">{{ __('reception.dashboard_lead') }}</p>
            </div>

            @if (session('status'))
                <div class="inline-message inline-message--info" role="status">{{ session('status') }}</div>
            @endif

            @if (session('reception_error'))
                <div class="inline-message inline-message--error" role="alert">{{ session('reception_error') }}</div>
            @endif

            <div class="card" data-reveal>
                <div class="card__body stack">
                    <p class="card__text">{{ __('reception.lookup_help') }}</p>
                    <div class="reception-toolbar">
                        <a href="{{ route('reception.scan') }}" class="btn btn--primary">{{ __('reception.scan_mode') }}</a>
                    </div>
                    <form
                        method="POST"
                        action="{{ route('reception.lookup') }}"
                        class="stack"
                        data-reception-lookup-form
                    >
                        @csrf
                        <div class="form-group">
                            <label class="form-label" for="reception-token">{{ __('reception.token_label') }}</label>
                            <input
                                id="reception-token"
                                class="form-input"
                                type="text"
                                name="lookup"
                                data-reception-token-input
                                autocomplete="off"
                                required
                                value="{{ old('lookup') }}"
                                placeholder="{{ __('reception.token_placeholder') }}"
                            >
                            @error('lookup')
                                <p class="form-error">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="reception-toolbar">
                            <button type="submit" class="btn btn--secondary" data-reception-lookup>
                                {{ __('reception.lookup') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <section class="reception-arrivals" data-reveal>
                <h2 class="reception-arrivals__title">{{ __('reception.todays_arrivals') }}</h2>

                @if ($arrivals->isEmpty())
                    <p class="card__text">{{ __('reception.no_arrivals') }}</p>
                @else
                    <ul class="reception-arrivals__list">
                        @foreach ($arrivals as $reservation)
                            <li class="reception-arrivals__item">
                                <div>
                                    <p class="reception-arrivals__code">{{ $reservation->code }}</p>
                                    <p class="reception-arrivals__guest">{{ $reservation->user->name }}</p>
                                    <p class="reception-arrivals__meta">
                                        {{ $reservation->room->roomType->name }}
                                        · {{ __('reception.room_number', ['number' => $reservation->room->number]) }}
                                    </p>
                                </div>
                                <div class="reception-arrivals__aside">
                                    <span @class(['badge', ReservationPresentation::statusBadgeClass($reservation->status)])>
                                        {{ ReservationPresentation::statusLabel($reservation->status) }}
                                    </span>
                                    <a
                                        class="btn btn--secondary btn--small"
                                        href="{{ route('reception.check', $reservation->isTokenActive() ? $reservation->check_in_token : $reservation->code) }}"
                                    >
                                        {{ __('reception.open') }}
                                    </a>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>
        </div>
    </section>
@endsection
