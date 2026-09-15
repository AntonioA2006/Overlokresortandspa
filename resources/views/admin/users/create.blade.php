@extends('layouts.app')

@section('title', __('admin.users_create_title'))

@section('meta_robots', 'noindex, nofollow')

@section('content')
    <section class="section admin-page">
        <div class="container container--narrow stack">
            @include('admin.partials.nav')

            <header class="section__header" data-reveal>
                <h1 class="section__title">{{ __('admin.users_create') }}</h1>
            </header>

            <div class="card" data-reveal>
                <div class="card__body">
                    @include('admin.users.partials.form', ['user' => null, 'action' => route('admin.users.store'), 'includePassword' => true])
                </div>
            </div>
        </div>
    </section>
@endsection
