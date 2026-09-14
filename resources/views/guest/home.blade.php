@extends('layouts.app')

@section('hero_layout', 'true')
@section('title', $hotelName)

@section('content')
    <section class="landing-hero" data-landing-parallax>
        <div class="landing-hero__media" aria-hidden="true">
            <div class="landing-hero__image-wrap" data-landing-parallax-media>
                <img
                    src="{{ asset(__('home.images.hero')) }}"
                    alt="{{ __('home.images.hero_alt') }}"
                    class="landing-hero__image"
                    width="1920"
                    height="1280"
                    fetchpriority="high"
                    decoding="async"
                >
            </div>
            <div class="landing-hero__overlay"></div>
        </div>

        <div class="container landing-hero__content">
            <div class="landing-hero__copy" data-reveal>
                <span class="eyebrow">{{ __('home.hero_eyebrow') }}</span>
                <h1 class="landing-hero__title">{{ __('home.hero_title') }}</h1>
                <p class="landing-hero__text">{{ __('home.hero_text') }}</p>

                <div class="landing-hero__actions">
                    <a href="#booking" class="landing-hero__link">{{ __('home.book_stay') }}</a>
                    <a href="#experiencias" class="landing-hero__link">{{ __('home.discover_overlook') }}</a>
                </div>
            </div>

            <div id="booking" class="landing-hero__booking" data-reveal>
                <x-booking-search-bar variant="hero" form-id="hero-booking" :max-guests="$maxGuests" />
            </div>
        </div>
    </section>

    <section class="landing-section" id="experiencias">
        <div class="container landing-editorial">
            <div class="landing-editorial__media" data-image-reveal>
                <img
                    src="{{ asset(__('home.images.experience')) }}"
                    alt="{{ __('home.images.experience_alt') }}"
                    width="1400"
                    height="1750"
                    loading="lazy"
                    decoding="async"
                >
            </div>

            <div class="landing-editorial__content" data-reveal>
                <span class="eyebrow">{{ __('home.experiences_eyebrow') }}</span>
                <h2 class="landing-editorial__title">{{ __('home.experiences_title') }}</h2>
                <p class="landing-editorial__text">{{ __('home.experiences_text') }}</p>
                <x-button href="{{ route('guest.reservations.search') }}" variant="ghost">
                    {{ __('home.discover_stays') }}
                </x-button>
            </div>
        </div>
    </section>

    <section class="landing-section landing-section--soft" id="habitaciones">
        <div class="container landing-rooms">
            <div class="landing-rooms__intro" data-reveal>
                <span class="eyebrow">{{ __('home.rooms_eyebrow') }}</span>
                <h2 class="landing-editorial__title">{{ __('home.rooms_title') }}</h2>
                <p class="landing-editorial__text">{{ __('home.rooms_text') }}</p>
            </div>

            <div class="landing-rooms__list">
                @forelse ($roomTypes as $index => $roomType)
                    <article @class(['landing-room', 'landing-room--reverse' => $index % 2 === 1]) data-reveal>
                        <div class="landing-room__media">
                            <img
                                src="{{ asset(__('home.images.room')) }}"
                                alt="{{ $roomType->name }}"
                                width="1400"
                                height="875"
                                loading="lazy"
                                decoding="async"
                            >
                        </div>

                        <div class="landing-room__content">
                            <span class="eyebrow landing-room__eyebrow">{{ __('home.rooms_eyebrow') }}</span>
                            <h3 class="landing-room__title">{{ $roomType->name }}</h3>

                            @if ($roomType->description)
                                <p class="landing-room__text">{{ $roomType->description }}</p>
                            @endif

                            <div class="landing-room__meta">
                                <span>{{ __('home.rooms_from') }} ${{ number_format((float) $roomType->base_price_per_night, 0, '.', ',') }} MXN {{ __('home.rooms_per_night') }}</span>
                                <span>{{ __('home.rooms_guests', ['count' => $roomType->max_guests]) }}</span>
                            </div>
                        </div>
                    </article>
                @empty
                    <article class="landing-room" data-reveal>
                        <div class="landing-room__media">
                            <img
                                src="{{ asset(__('home.images.room')) }}"
                                alt="{{ __('home.images.room_alt') }}"
                                width="1400"
                                height="875"
                                loading="lazy"
                                decoding="async"
                            >
                        </div>

                        <div class="landing-room__content">
                            <span class="eyebrow landing-room__eyebrow">{{ __('home.rooms_eyebrow') }}</span>
                            <h3 class="landing-room__title">{{ __('home.rooms_title') }}</h3>
                            <p class="landing-room__text">{{ __('home.rooms_text') }}</p>
                        </div>
                    </article>
                @endforelse
            </div>
        </div>
    </section>

    <section class="landing-section" id="spa">
        <div class="container landing-spa landing-spa--reverse">
            <div class="landing-spa__media" data-image-reveal>
                <img
                    src="{{ asset(__('home.images.spa')) }}"
                    alt="{{ __('home.images.spa_alt') }}"
                    width="1400"
                    height="1120"
                    loading="lazy"
                    decoding="async"
                >
            </div>

            <div class="landing-spa__content" data-reveal>
                <span class="eyebrow">{{ __('home.spa_eyebrow') }}</span>
                <h2 class="landing-editorial__title">{{ __('home.spa_title') }}</h2>
                <p class="landing-editorial__text">{{ __('home.spa_text') }}</p>

                <ul class="landing-spa__features">
                    <li>{{ __('home.spa_feature_1') }}</li>
                    <li>{{ __('home.spa_feature_2') }}</li>
                    <li>{{ __('home.spa_feature_3') }}</li>
                </ul>
            </div>
        </div>
    </section>

    <section class="landing-section" id="gastronomia">
        <div class="container landing-editorial landing-editorial--reverse">
            <div class="landing-editorial__media" data-image-reveal>
                <img
                    src="{{ asset(__('home.images.dining')) }}"
                    alt="{{ __('home.images.dining_alt') }}"
                    width="1400"
                    height="1750"
                    loading="lazy"
                    decoding="async"
                >
            </div>

            <div class="landing-editorial__content" data-reveal>
                <span class="eyebrow">{{ __('home.gastronomy_eyebrow') }}</span>
                <h2 class="landing-editorial__title">{{ __('home.gastronomy_title') }}</h2>
                <p class="landing-editorial__text">{{ __('home.gastronomy_text') }}</p>
            </div>
        </div>
    </section>

    <section class="landing-final-cta">
        <div class="landing-final-cta__media" aria-hidden="true">
            <img
                src="{{ asset(__('home.images.hero')) }}"
                alt=""
                width="1920"
                height="1280"
                loading="lazy"
                decoding="async"
            >
            <div class="landing-final-cta__overlay"></div>
        </div>

        <div class="container landing-final-cta__content">
            <span class="eyebrow" data-reveal>{{ __('home.final_cta_eyebrow') }}</span>
            <h2 class="landing-final-cta__title" data-reveal>{{ __('home.final_cta_title') }}</h2>
            <p class="landing-final-cta__text" data-reveal>{{ __('home.final_cta_text') }}</p>

            <div data-reveal>
                <x-booking-search-bar variant="hero" form-id="final-booking" :max-guests="$maxGuests" />
            </div>
        </div>
    </section>
@endsection

@section('meta_description', __('home.meta_description'))
