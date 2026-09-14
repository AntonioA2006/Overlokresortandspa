@extends('layouts.app')

@section('title', __('reservations.page_title_room', ['name' => $roomType->name]))

@if ($hasSearchContext)
    @section('meta_robots', __('reservations.results_robots'))
@endif

@section('content')
    <section class="room-detail section">
        <div class="container">
            <header class="room-detail__intro" data-reveal>
                @if (session('reservation_error'))
                    <div class="inline-message inline-message--error" role="alert">
                        {{ session('reservation_error') }}
                    </div>
                @endif

                <a href="{{ $resultsUrl }}" class="room-detail__back link-cta">
                    <span aria-hidden="true">←</span>
                    <span>{{ $hasSearchContext ? __('reservations.back_to_results') : __('reservations.back_to_search') }}</span>
                </a>

                @if ($hasSearchContext)
                    <div class="room-detail__summary">
                        <span class="eyebrow">{{ __('reservations.your_stay') }}</span>
                        <p class="room-detail__dates">{{ $dateSummary }}</p>
                        <p class="room-detail__guests">{{ $guestSummary }}</p>
                        <a href="{{ route('guest.reservations.search', $searchQuery) }}" class="room-detail__modify">
                            {{ __('reservations.modify_search') }}
                        </a>
                    </div>
                @endif
            </header>

            <div class="room-detail__layout">
                <div class="room-detail__gallery" data-reveal>
                    <div
                        class="room-detail__gallery-main"
                        role="group"
                        aria-label="{{ __('reservations.gallery_aria', ['name' => $roomType->name]) }}"
                    >
                        @foreach ($gallery as $index => $photo)
                            <figure @class(['room-detail__figure', 'room-detail__figure--lead' => $index === 0])>
                                <img
                                    src="{{ $photo['url'] }}"
                                    alt="{{ $photo['alt'] }}"
                                    @if ($index === 0)
                                        width="1400"
                                        height="1050"
                                        fetchpriority="high"
                                    @else
                                        width="900"
                                        height="675"
                                        loading="lazy"
                                    @endif
                                    decoding="async"
                                >
                            </figure>
                        @endforeach
                    </div>
                </div>

                <div class="room-detail__panel" data-reveal>
                    <div class="room-detail__copy">
                        <span class="eyebrow">{{ __('reservations.room_detail') }}</span>
                        <h1 class="room-detail__title">{{ $roomType->name }}</h1>

                        <p class="room-detail__meta">
                            {{ __('reservations.up_to_guests', ['count' => $roomType->max_guests]) }}
                        </p>

                        @if ($roomType->description)
                            <p class="room-detail__description">{{ $roomType->description }}</p>
                        @endif

                        @if ($roomType->amenities->isNotEmpty())
                            <div class="room-detail__amenities">
                                <h2 class="room-detail__amenities-title">{{ __('reservations.amenities_title') }}</h2>
                                <ul class="room-detail__amenities-list">
                                    @foreach ($roomType->amenities as $amenity)
                                        <li>{{ $amenity->name }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>

                    <aside class="room-detail__booking" aria-labelledby="room-detail-booking-title">
                        <h2 id="room-detail-booking-title" class="room-detail__booking-title">
                            {{ __('reservations.booking_summary') }}
                        </h2>

                        @if ($hasSearchContext)
                            @if ($isAvailable && $pricing)
                                <div class="room-detail__price">
                                    <div class="room-detail__price-row">
                                        <span class="room-detail__price-label">{{ __('reservations.per_night') }}</span>
                                        <span class="room-detail__price-value">
                                            ${{ number_format((float) $pricing['price_per_night'], 0, '.', ',') }}
                                            {{ $pricing['currency'] }}
                                        </span>
                                    </div>

                                    <div class="room-detail__price-row room-detail__price-row--total">
                                        <span class="room-detail__price-label">{{ __('reservations.estimated_total') }}</span>
                                        <span class="room-detail__price-total">
                                            ${{ number_format((float) $pricing['total'], 0, '.', ',') }}
                                            {{ $pricing['currency'] }}
                                        </span>
                                    </div>

                                    <p class="room-detail__price-note">
                                        {{ trans_choice('reservations.total_for_stay', $pricing['nights'], ['count' => $pricing['nights']]) }}
                                    </p>
                                </div>

                                <a href="{{ $reserveUrl }}" class="btn btn--primary btn--large room-detail__cta">
                                    {{ __('reservations.book_room') }}
                                </a>
                            @else
                                <div class="room-detail__unavailable" role="status">
                                    <p class="room-detail__unavailable-title">{{ __('reservations.unavailable_title') }}</p>
                                    <p class="room-detail__unavailable-text">
                                        @if ($unavailableReason === 'capacity')
                                            {{ __('reservations.unavailable_capacity', ['count' => $roomType->max_guests]) }}
                                        @else
                                            {{ __('reservations.unavailable_lead') }}
                                        @endif
                                    </p>
                                </div>

                                <x-button :href="route('guest.reservations.search', $searchQuery)" variant="secondary">
                                    {{ __('reservations.change_dates') }}
                                </x-button>
                            @endif
                        @else
                            <div class="room-detail__prompt">
                                <p class="room-detail__prompt-title">{{ __('reservations.select_dates_title') }}</p>
                                <p class="room-detail__prompt-text">{{ __('reservations.select_dates_lead') }}</p>
                            </div>

                            <x-button :href="route('guest.reservations.search')" variant="primary">
                                {{ __('reservations.search_for_dates') }}
                            </x-button>
                        @endif
                    </aside>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('meta_description', __('reservations.meta_room', ['name' => $roomType->name]))
