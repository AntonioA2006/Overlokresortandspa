@extends('layouts.app')

@section('title', __('auth.verify_page_title'))

@section('meta_robots', 'noindex, nofollow')

@section('content')
    <section class="auth-page">
        <div class="container">
            <div class="auth-card" data-reveal>
                <div class="auth-card__header">
                    <span class="auth-card__eyebrow">{{ $hotelName }}</span>
                    <h1 class="auth-card__title">{{ __('auth.verify_title') }}</h1>
                    <p class="auth-card__subtitle">
                        {{ __('auth.verify_subtitle', ['email' => $user->email]) }}
                    </p>
                </div>

                @if (session('status'))
                    <div class="inline-message inline-message--info" role="status">
                        {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('verification.send') }}" class="stack">
                    @csrf
                    <x-button type="submit" variant="primary">{{ __('auth.verify_resend') }}</x-button>
                </form>

                <div class="auth-card__footer">
                    <p class="auth-card__future">
                        <a href="{{ route('profile.edit') }}" class="auth-card__inline-link">{{ __('auth.verify_update_email') }}</a>
                    </p>
                    <a href="{{ route('home') }}" class="auth-card__back">{{ __('auth.back_home') }}</a>
                </div>
            </div>
        </div>
    </section>
@endsection
