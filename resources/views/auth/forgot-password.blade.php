@extends('layouts.app')

@section('title', __('auth.forgot_page_title'))

@section('content')
    <section class="auth-page">
        <div class="container">
            <div class="auth-card" data-reveal>
                <div class="auth-card__header">
                    <span class="auth-card__eyebrow">{{ $hotelName }}</span>
                    <h1 class="auth-card__title">{{ __('auth.forgot_title') }}</h1>
                    <p class="auth-card__subtitle">{{ __('auth.forgot_subtitle') }}</p>
                </div>

                @if (session('status'))
                    <div class="inline-message inline-message--info" role="status">
                        {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('password.email') }}" class="auth-card__form stack">
                    @csrf

                    <x-field
                        :label="__('auth.email')"
                        name="email"
                        type="email"
                        :value="old('email')"
                        :error="$errors->first('email')"
                        required
                        autocomplete="email"
                    />

                    <x-button type="submit" variant="primary">
                        {{ __('auth.send_reset_link') }}
                    </x-button>
                </form>

                <div class="auth-card__footer">
                    <a href="{{ route('login') }}" class="auth-card__back">{{ __('auth.back_to_login') }}</a>
                </div>
            </div>
        </div>
    </section>
@endsection
