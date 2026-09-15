@extends('layouts.app')

@section('title', __('rooms.page_title'))

@section('content')
    <section class="booking-results section">
        <div class="container">
            <header class="booking-results__intro" data-reveal>
                <span class="eyebrow">{{ $hotelName }}</span>
                <h1 class="booking-results__title">{{ __('rooms.heading') }}</h1>
                <p class="booking-results__guests">{{ __('rooms.lead') }}</p>
                <a href="{{ route('guest.reservations.search') }}" class="booking-results__modify">
                    {{ __('rooms.search_dates') }}
                </a>
            </header>

            @if ($roomTypes->isEmpty())
                <div class="booking-results__empty" data-reveal>
                    <h2 class="booking-results__empty-title">{{ __('rooms.empty_title') }}</h2>
                    <p class="booking-results__empty-lead">{{ __('rooms.empty_lead') }}</p>
                </div>
            @else
                <div class="booking-results__list">
                    @foreach ($roomTypes as $index => $item)
                        @php
                            $roomType = $item['room_type'];
                            $photo = $item['photo'];
                        @endphp
                        <article @class(['booking-results__room', 'booking-results__room--reverse' => $index % 2 === 1]) data-reveal>
                            <div class="booking-results__media">
                                <img
                                    src="{{ $photo['url'] }}"
                                    alt="{{ $photo['alt'] }}"
                                    width="1400"
                                    height="875"
                                    loading="lazy"
                                    decoding="async"
                                >
                            </div>
                            <div class="booking-results__content">
                                <div class="booking-results__copy">
                                    <h2 class="booking-results__name">{{ $roomType->name }}</h2>
                                    <p class="booking-results__meta">{{ __('reservations.up_to_guests', ['count' => $roomType->max_guests]) }}</p>
                                    @if ($roomType->description)
                                        <p class="booking-results__description">{{ $roomType->description }}</p>
                                    @endif
                                    @if ($roomType->amenities->isNotEmpty())
                                        <p class="booking-results__amenities">{{ $roomType->amenities->pluck('name')->join(' · ') }}</p>
                                    @endif
                                </div>
                                <div class="booking-results__aside">
                                    <div class="booking-results__price">
                                        <span class="booking-results__price-label">{{ __('reservations.from_price') }}</span>
                                        <span class="booking-results__price-value">${{ number_format((float) $roomType->base_price_per_night, 0, '.', ',') }} {{ config('overlook.currency') }}</span>
                                        <span class="booking-results__price-note">{{ __('reservations.per_night') }}</span>
                                    </div>
                                    <x-button :href="$item['show_url']" variant="secondary">
                                        {{ __('rooms.view_room') }}
                                    </x-button>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
@endsection
