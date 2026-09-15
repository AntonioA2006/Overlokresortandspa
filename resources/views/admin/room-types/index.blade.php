@extends('layouts.app')

@section('title', __('admin.rates_page_title'))

@section('meta_robots', 'noindex, nofollow')

@section('content')
    <section class="section admin-page">
        <div class="container stack">
            @include('admin.partials.nav')

            <header class="section__header" data-reveal>
                <span class="eyebrow">{{ $hotelName }}</span>
                <h1 class="section__title">{{ __('admin.rates_heading') }}</h1>
                <p class="section__subtitle">{{ __('admin.rates_lead') }}</p>
            </header>

            @if (session('status'))
                <div class="inline-message inline-message--info" role="status">{{ session('status') }}</div>
            @endif

            <ul class="reception-arrivals__list" data-reveal>
                @foreach ($roomTypes as $roomType)
                    <li class="reception-arrivals__item">
                        <div>
                            <p class="reception-arrivals__guest">{{ $roomType->name }}</p>
                            <p class="reception-arrivals__meta">
                                ${{ number_format((float) $roomType->base_price_per_night, 0, '.', ',') }} {{ config('overlook.currency') }}
                                · {{ __('admin.rate_rooms_count', ['count' => $roomType->rooms_count]) }}
                            </p>
                            <p class="reception-arrivals__code">
                                {{ $roomType->is_active ? __('admin.rate_active') : __('admin.rate_inactive') }}
                            </p>
                        </div>
                        <a class="btn btn--secondary btn--small" href="{{ route('admin.room-types.edit', $roomType) }}">{{ __('admin.edit') }}</a>
                    </li>
                @endforeach
            </ul>
        </div>
    </section>
@endsection
