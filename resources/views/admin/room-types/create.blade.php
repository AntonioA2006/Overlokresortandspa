@extends('layouts.app')

@section('title', __('admin.rates_create_title'))

@section('meta_robots', 'noindex, nofollow')

@section('content')
    <section class="section admin-page">
        <div class="container container--narrow stack">
            @include('admin.partials.nav')

            <header class="section__header" data-reveal>
                <h1 class="section__title">{{ __('admin.rates_create') }}</h1>
            </header>

            <div class="card" data-reveal>
                <div class="card__body">
                    @include('admin.room-types.partials.form', ['roomType' => null, 'action' => route('admin.room-types.store')])
                </div>
            </div>
        </div>
    </section>
@endsection
