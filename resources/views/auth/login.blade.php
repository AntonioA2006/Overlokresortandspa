@extends('layouts.app')

@section('title', __('auth.page_title'))

@section('content')
    <section class="auth-page">
        <div class="container">
            <div class="auth-card" data-reveal>
                <div class="auth-card__header">
                    <span class="auth-card__eyebrow">{{ $hotelName }}</span>
                    <h1 class="auth-card__title">{{ __('auth.title') }}</h1>
                    <p class="auth-card__subtitle">
                        {{ __('auth.subtitle') }}
                    </p>
                </div>

                @if (session('auth_error'))
                    <div class="inline-message inline-message--error" role="alert">
                        {{ session('auth_error') }}
                    </div>
                @endif

                @if (session('status'))
                    <div class="inline-message inline-message--info" role="status">
                        {{ session('status') }}
                    </div>
                @endif

                <div class="auth-card__actions stack">
                    <a href="{{ route('auth.google.redirect') }}" class="btn btn--google">
                        <span class="btn__google-icon" aria-hidden="true">G</span>
                        {{ __('auth.continue_with_google') }}
                    </a>

                    <p class="auth-card__note">
                        {{ __('auth.first_time_note') }}
                    </p>
                </div>

                <div class="auth-card__footer">
                    <p class="auth-card__future">
                        {{ __('auth.future_login_note') }}
                    </p>
                    <a href="{{ route('home') }}" class="auth-card__back">{{ __('auth.back_home') }}</a>
                </div>
            </div>
        </div>
    </section>
@endsection
