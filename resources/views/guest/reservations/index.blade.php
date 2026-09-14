@extends('layouts.app')

@section('title', __('reservations.page_title_my_stays'))

@section('meta_robots', 'noindex, nofollow')

@section('content')
    <section class="my-stays section">
        <div class="container">
            <header class="my-stays__intro" data-reveal>
                <span class="eyebrow">{{ __('reservations.my_stays_eyebrow') }}</span>
                <h1 class="my-stays__title">{{ __('reservations.my_stays_title') }}</h1>
                <p class="my-stays__lead">{{ __('reservations.my_stays_intro') }}</p>
            </header>

            @php
                $hasReservations = $currentReservations->isNotEmpty()
                    || $upcomingReservations->isNotEmpty()
                    || $pastReservations->isNotEmpty();
            @endphp

            @if (! $hasReservations)
                <div class="my-stays__empty" data-reveal>
                    <h2 class="my-stays__empty-title">{{ __('reservations.empty_title') }}</h2>
                    <p class="my-stays__empty-lead">{{ __('reservations.empty_lead') }}</p>
                    <x-button href="{{ route('guest.reservations.search') }}" variant="secondary">
                        {{ __('reservations.search_cta') }}
                    </x-button>
                </div>
            @else
                @if ($currentReservations->isNotEmpty())
                    <section class="my-stays__group" aria-labelledby="my-stays-current-heading" data-reveal>
                        <h2 id="my-stays-current-heading" class="my-stays__group-title">{{ __('reservations.section_current') }}</h2>
                        <div class="my-stays__list">
                            @foreach ($currentReservations as $reservation)
                                @include('guest.reservations.partials.card', ['reservation' => $reservation])
                            @endforeach
                        </div>
                    </section>
                @endif

                @if ($upcomingReservations->isNotEmpty())
                    <section class="my-stays__group" aria-labelledby="my-stays-upcoming-heading" data-reveal>
                        <h2 id="my-stays-upcoming-heading" class="my-stays__group-title">{{ __('reservations.section_upcoming') }}</h2>
                        <div class="my-stays__list">
                            @foreach ($upcomingReservations as $reservation)
                                @include('guest.reservations.partials.card', ['reservation' => $reservation])
                            @endforeach
                        </div>
                    </section>
                @endif

                @if ($pastReservations->isNotEmpty())
                    <section class="my-stays__group" aria-labelledby="my-stays-past-heading" data-reveal>
                        <h2 id="my-stays-past-heading" class="my-stays__group-title">{{ __('reservations.section_past') }}</h2>
                        <div class="my-stays__list">
                            @foreach ($pastReservations as $reservation)
                                @include('guest.reservations.partials.card', ['reservation' => $reservation])
                            @endforeach
                        </div>
                    </section>
                @endif
            @endif
        </div>
    </section>
@endsection

@section('meta_description', __('reservations.meta_my_stays'))
