@extends('layouts.app')

@section('title', __('admin.users_page_title'))

@section('meta_robots', 'noindex, nofollow')

@section('content')
    <section class="section admin-page">
        <div class="container stack">
            @include('admin.partials.nav')

            <header class="section__header" data-reveal>
                <span class="eyebrow">{{ $hotelName }}</span>
                <h1 class="section__title">{{ __('admin.users_heading') }}</h1>
                <p class="section__subtitle">{{ __('admin.users_lead') }}</p>
            </header>

            @if (session('status'))
                <div class="inline-message inline-message--info" role="status">{{ session('status') }}</div>
            @endif

            <div class="reception-toolbar" data-reveal>
                <a class="btn btn--primary" href="{{ route('admin.users.create') }}">{{ __('admin.users_create') }}</a>
            </div>

            <ul class="reception-arrivals__list" data-reveal>
                @foreach ($users as $user)
                    <li class="reception-arrivals__item">
                        <div>
                            <p class="reception-arrivals__guest">{{ $user->name }}</p>
                            <p class="reception-arrivals__meta">{{ $user->email }}</p>
                            <p class="reception-arrivals__code">{{ $user->role->label() }}</p>
                        </div>
                        <a class="btn btn--secondary btn--small" href="{{ route('admin.users.edit', $user) }}">{{ __('admin.edit') }}</a>
                    </li>
                @endforeach
            </ul>
        </div>
    </section>
@endsection
