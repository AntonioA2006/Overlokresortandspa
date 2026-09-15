@extends('layouts.app')

@section('title', __('admin.rooms_page_title'))

@section('meta_robots', 'noindex, nofollow')

@section('content')
    <section class="section admin-page">
        <div class="container stack">
            @include('admin.partials.nav')

            <header class="section__header" data-reveal>
                <span class="eyebrow">{{ $hotelName }}</span>
                <h1 class="section__title">{{ __('admin.rooms_heading') }}</h1>
                <p class="section__subtitle">{{ __('admin.rooms_lead') }}</p>
            </header>

            @if (session('status'))
                <div class="inline-message inline-message--info" role="status">{{ session('status') }}</div>
            @endif

            <div class="reception-toolbar" data-reveal>
                <a class="btn btn--primary" href="{{ route('admin.rooms.create') }}">{{ __('admin.rooms_create') }}</a>
            </div>

            @if ($rooms->isEmpty())
                <p class="card__text">{{ __('admin.rooms_empty') }}</p>
            @else
                <ul class="reception-arrivals__list" data-reveal>
                    @foreach ($rooms as $room)
                        <li class="reception-arrivals__item">
                            <div>
                                <p class="reception-arrivals__code">{{ __('admin.room_number', ['number' => $room->number]) }}</p>
                                <p class="reception-arrivals__guest">{{ $room->roomType?->name }}</p>
                                <p class="reception-arrivals__meta">{{ $room->status->label() }}</p>
                            </div>
                            <div class="reception-arrivals__aside">
                                <form method="POST" action="{{ route('admin.rooms.status', $room) }}">
                                    @csrf
                                    <input type="hidden" name="status" value="{{ $room->status === App\Enums\RoomStatus::Available ? App\Enums\RoomStatus::Maintenance->value : App\Enums\RoomStatus::Available->value }}">
                                    <button type="submit" class="btn btn--ghost btn--small">
                                        {{ $room->status === App\Enums\RoomStatus::Available ? __('admin.rooms_mark_maintenance') : __('admin.rooms_mark_available') }}
                                    </button>
                                </form>
                                <a class="btn btn--secondary btn--small" href="{{ route('admin.rooms.edit', $room) }}">{{ __('admin.edit') }}</a>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </section>
@endsection
