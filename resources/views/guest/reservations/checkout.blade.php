@extends('layouts.app')

@section('title', __('reservations.page_title_checkout'))

@section('meta_robots', __('reservations.results_robots'))

@section('content')
    <section class="checkout section">
        <div class="container">
            <header class="checkout__intro" data-reveal>
                <a href="{{ $roomShowUrl }}" class="checkout__back link-cta">
                    <span aria-hidden="true">←</span>
                    <span>{{ __('reservations.back_to_room') }}</span>
                </a>

                <div class="checkout__summary">
                    <span class="eyebrow">{{ __('reservations.checkout_title') }}</span>
                    <h1 class="checkout__heading">{{ __('reservations.checkout_heading') }}</h1>
                    <p class="checkout__dates">{{ $dateSummary }}</p>
                    <p class="checkout__guests">{{ $guestSummary }}</p>
                </div>
            </header>

            @if (session('reservation_error'))
                <div class="inline-message inline-message--error" role="alert" data-reveal>
                    {{ session('reservation_error') }}
                </div>
            @endif

            <div class="checkout__layout">
                <article class="checkout__room" data-reveal>
                    <div class="checkout__media">
                        <img
                            src="{{ $photo['url'] }}"
                            alt="{{ $photo['alt'] }}"
                            width="1200"
                            height="900"
                            loading="eager"
                            decoding="async"
                        >
                    </div>

                    <div class="checkout__room-copy">
                        <h2 class="checkout__room-name">{{ $roomType->name }}</h2>
                        <p class="checkout__room-meta">
                            {{ __('reservations.up_to_guests', ['count' => $roomType->max_guests]) }}
                        </p>

                        @if ($roomType->description)
                            <p class="checkout__room-description">{{ $roomType->description }}</p>
                        @endif
                    </div>
                </article>

                <aside class="checkout__panel" data-reveal>
                    <h2 class="checkout__panel-title">{{ __('reservations.booking_summary') }}</h2>

                    <dl class="checkout__breakdown">
                        <div class="checkout__breakdown-row">
                            <dt>{{ __('reservations.per_night') }}</dt>
                            <dd>
                                ${{ number_format((float) $pricing['price_per_night'], 0, '.', ',') }}
                                {{ $pricing['currency'] }}
                            </dd>
                        </div>
                        <div class="checkout__breakdown-row">
                            <dt>{{ trans_choice('reservations.nights_label', $pricing['nights'], ['count' => $pricing['nights']]) }}</dt>
                            <dd>{{ $pricing['nights'] }}</dd>
                        </div>
                        <div class="checkout__breakdown-row checkout__breakdown-row--total">
                            <dt>{{ __('reservations.estimated_total') }}</dt>
                            <dd>
                                ${{ number_format((float) $pricing['total'], 0, '.', ',') }}
                                {{ $pricing['currency'] }}
                            </dd>
                        </div>
                    </dl>

                    <p class="checkout__note">{{ __('reservations.checkout_payment_note') }}</p>

                    <form method="POST" action="{{ route('guest.reservations.store') }}" class="checkout__form">
                        @csrf
                        <input type="hidden" name="room_id" value="{{ $room->id }}">
                        <input type="hidden" name="check_in_date" value="{{ $searchQuery['check_in_date'] }}">
                        <input type="hidden" name="check_out_date" value="{{ $searchQuery['check_out_date'] }}">
                        <input type="hidden" name="adults" value="{{ $searchQuery['adults'] }}">
                        <input type="hidden" name="children" value="{{ $searchQuery['children'] ?? 0 }}">
                        <input type="hidden" name="idempotency_key" value="{{ $idempotencyKey }}">

                        <button type="submit" class="btn btn--primary btn--large checkout__submit">
                            {{ __('reservations.confirm_reservation') }}
                        </button>
                    </form>
                </aside>
            </div>
        </div>
    </section>
@endsection

@section('meta_description', __('reservations.meta_checkout'))
