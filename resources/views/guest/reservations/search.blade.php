@extends('layouts.app')

@section('title', __('reservations.page_title_search'))

@section('content')
    <section class="booking-search section">
        <div class="container container--narrow">
            <header class="booking-search__header" data-reveal>
                <span class="eyebrow">{{ strtoupper($hotelName) }}</span>
                <h1 class="booking-search__title">{{ __('reservations.find_your_stay') }}</h1>
                <p class="booking-search__intro lead">
                    {{ __('reservations.search_intro') }}
                </p>
            </header>

            <div data-reveal>
                <x-booking-search-bar variant="page" :max-guests="$maxGuests" />
            </div>
        </div>
    </section>
@endsection

@section('meta_description', __('reservations.meta_search'))
