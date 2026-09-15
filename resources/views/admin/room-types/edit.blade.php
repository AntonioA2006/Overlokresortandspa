@extends('layouts.app')

@section('title', __('admin.rates_edit_title', ['name' => $roomType->name]))

@section('meta_robots', 'noindex, nofollow')

@section('content')
    <section class="section admin-page">
        <div class="container container--narrow stack">
            @include('admin.partials.nav')

            <header class="section__header" data-reveal>
                <h1 class="section__title">{{ __('admin.rates_edit', ['name' => $roomType->name]) }}</h1>
            </header>

            <div class="card" data-reveal>
                <div class="card__body">
                    @include('admin.room-types.partials.form', ['roomType' => $roomType, 'action' => route('admin.room-types.update', $roomType)])
                </div>
            </div>
        </div>
    </section>
@endsection
