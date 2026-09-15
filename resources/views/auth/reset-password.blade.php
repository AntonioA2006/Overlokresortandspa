@extends('layouts.app')

@section('title', __('auth.reset_page_title'))

@section('content')
    <section class="auth-page">
        <div class="container">
            <div class="auth-card" data-reveal>
                <div class="auth-card__header">
                    <span class="auth-card__eyebrow">{{ $hotelName }}</span>
                    <h1 class="auth-card__title">{{ __('auth.reset_title') }}</h1>
                    <p class="auth-card__subtitle">{{ __('auth.reset_subtitle') }}</p>
                </div>

                <form method="POST" action="{{ route('password.update') }}" class="auth-card__form stack">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">

                    <x-field
                        :label="__('auth.email')"
                        name="email"
                        type="email"
                        :value="old('email', $email)"
                        :error="$errors->first('email')"
                        required
                        autocomplete="email"
                    />

                    <x-field
                        :label="__('auth.password')"
                        name="password"
                        type="password"
                        :error="$errors->first('password')"
                        required
                        autocomplete="new-password"
                    />

                    <x-field
                        :label="__('auth.password_confirmation')"
                        name="password_confirmation"
                        type="password"
                        :error="$errors->first('password_confirmation')"
                        required
                        autocomplete="new-password"
                    />

                    <x-button type="submit" variant="primary">
                        {{ __('auth.reset_password') }}
                    </x-button>
                </form>
            </div>
        </div>
    </section>
@endsection
