@extends('layouts.app')

@section('title', $resultGroups->isNotEmpty()
    ? __('reservations.page_title_results')
    : __('reservations.page_title_search'))

@section('meta_robots', __('reservations.results_robots'))

@section('content')
    <section class="booking-results section">
        <div class="container">
            <header class="booking-results__intro" data-reveal>
                <span class="eyebrow">{{ __('reservations.results_stay_title') }}</span>

                <div class="booking-results__summary-block">
                    <p class="booking-results__dates">{{ $dateSummary }}</p>
                    <p class="booking-results__guests">{{ $guestSummary }}</p>
                </div>

                <a href="{{ route('guest.reservations.search', $searchQuery) }}" class="booking-results__modify">
                    {{ __('reservations.modify_search') }}
                </a>
            </header>

            @if ($resultGroups->isEmpty())
                <div class="booking-results__empty" data-reveal>
                    <h1 class="booking-results__empty-title">{{ __('reservations.no_rooms_title') }}</h1>
                    <p class="booking-results__empty-lead">{{ __('reservations.no_rooms_lead') }}</p>
                    <p class="booking-results__empty-text">{{ __('reservations.empty_message') }}</p>
                    <x-button :href="route('guest.reservations.search', $searchQuery)" variant="secondary">
                        {{ __('reservations.change_dates') }}
                    </x-button>
                </div>
            @else
                <div class="booking-results__section-header" data-reveal>
                    <h1 class="booking-results__title">{{ __('reservations.available_rooms') }}</h1>
                </div>

                <div class="booking-results__list">
                    @foreach ($resultGroups as $index => $group)
                        @php
                            $roomType = $group['room_type'];
                            $photo = $group['photo'];
                            $pricing = $group['pricing'];
                        @endphp

                        <article @class(['booking-results__room', 'booking-results__room--reverse' => $index % 2 === 1]) data-reveal>
                            <div class="booking-results__media" data-image-hover>
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

                                    <p class="booking-results__meta">
                                        {{ __('reservations.up_to_guests', ['count' => $roomType->max_guests]) }}
                                    </p>

                                    @if ($roomType->description)
                                        <p class="booking-results__description">{{ $roomType->description }}</p>
                                    @endif

                                    @if ($group['featured_amenities']->isNotEmpty())
                                        <p class="booking-results__amenities">
                                            {{ $group['featured_amenities']->pluck('name')->join(' · ') }}
                                        </p>
                                    @endif
                                </div>

                                <div class="booking-results__aside">
                                    <div class="booking-results__price">
                                        <span class="booking-results__price-label">{{ __('reservations.from_price') }}</span>
                                        <span class="booking-results__price-value">
                                            ${{ number_format((float) $pricing['price_per_night'], 0, '.', ',') }}
                                            {{ $pricing['currency'] }}
                                        </span>
                                        <span class="booking-results__price-unit">{{ __('reservations.per_night') }}</span>
                                        <span class="booking-results__price-nights">
                                            {{ trans_choice('reservations.nights_label', $pricing['nights'], ['count' => $pricing['nights']]) }}
                                        </span>
                                    </div>

                                    <a href="{{ $group['show_url'] }}" class="link-cta">
                                        <span>{{ __('reservations.view_room') }}</span>
                                        <span aria-hidden="true">→</span>
                                    </a>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
@endsection

@section('meta_description', __('reservations.meta_results'))
