@extends('layouts.app')

@section('title', $roomType->name)

@section('content')
    <section class="section">
        <div class="container container--narrow">
            <span class="eyebrow">{{ __('reservations.availability') }}</span>
            <h1 class="section__title">{{ $roomType->name }}</h1>
            <p class="section__subtitle">{{ __('reservations.detail_coming_soon') }}</p>
        </div>
    </section>
@endsection

@section('meta_description', $roomType->name.' — '.config('overlook.hotel_name'))
