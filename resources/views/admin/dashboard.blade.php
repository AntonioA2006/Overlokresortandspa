@extends('layouts.app')

@php
    use App\Support\ReservationPresentation;
@endphp

@section('title', __('admin.page_title'))

@section('meta_robots', 'noindex, nofollow')

@section('content')
    <section class="section admin-page">
        <div class="container stack">
            @include('admin.partials.nav')

            <header class="section__header" data-reveal>
                <span class="eyebrow">{{ $hotelName }}</span>
                <h1 class="section__title">{{ __('admin.heading') }}</h1>
                <p class="section__subtitle">{{ __('admin.lead') }}</p>
            </header>

            <div class="grid-3" data-reveal>
                <article class="card"><div class="card__body"><p class="card__text">{{ __('admin.users') }}</p><p class="admin-stat">{{ $stats['users'] }}</p></div></article>
                <article class="card"><div class="card__body"><p class="card__text">{{ __('admin.rooms') }}</p><p class="admin-stat">{{ $stats['rooms'] }}</p></div></article>
                <article class="card"><div class="card__body"><p class="card__text">{{ __('admin.reservations') }}</p><p class="admin-stat">{{ $stats['reservations'] }}</p></div></article>
            </div>

            <div class="reception-toolbar" data-reveal>
                <a class="btn btn--secondary" href="{{ route('admin.rooms.index') }}">{{ __('admin.manage_rooms') }}</a>
                <a class="btn btn--secondary" href="{{ route('admin.users.index') }}">{{ __('admin.manage_users') }}</a>
                <a class="btn btn--secondary" href="{{ route('admin.room-types.index') }}">{{ __('admin.manage_rates') }}</a>
                <a class="btn btn--secondary" href="{{ route('reception.dashboard') }}">{{ __('admin.open_reception') }}</a>
                <a class="btn btn--secondary" href="{{ route('support.dashboard') }}">{{ __('admin.open_support') }}</a>
            </div>

            <section data-reveal>
                <h2 class="reception-arrivals__title">{{ __('admin.recent_reservations') }}</h2>
                @if ($recentReservations->isEmpty())
                    <p class="card__text">{{ __('admin.no_reservations') }}</p>
                @else
                    <ul class="reception-arrivals__list">
                        @foreach ($recentReservations as $reservation)
                            <li class="reception-arrivals__item">
                                <div>
                                    <p class="reception-arrivals__code">{{ $reservation->code }}</p>
                                    <p class="reception-arrivals__guest">{{ $reservation->user?->name }}</p>
                                    <p class="reception-arrivals__meta">{{ $reservation->room?->roomType?->name }}</p>
                                </div>
                                <span @class(['badge', ReservationPresentation::statusBadgeClass($reservation->status)])>
                                    {{ ReservationPresentation::statusLabel($reservation->status) }}
                                </span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>

            <section data-reveal>
                <h2 class="reception-arrivals__title">{{ __('admin.recent_audit') }}</h2>
                @if ($recentAuditLogs->isEmpty())
                    <p class="card__text">{{ __('admin.no_audit') }}</p>
                @else
                    <ul class="reception-arrivals__list">
                        @foreach ($recentAuditLogs as $log)
                            <li class="reception-arrivals__item">
                                <div>
                                    <p class="reception-arrivals__code">{{ $log->action->label() }}</p>
                                    <p class="reception-arrivals__meta">{{ $log->user?->name ?? __('admin.system') }} · {{ $log->created_at?->timezone(config('overlook.timezone'))->format('d M Y H:i') }}</p>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>
        </div>
    </section>
@endsection
