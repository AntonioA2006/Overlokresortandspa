@extends('layouts.app')

@section('title', __('auth.register_page_title'))

@section('content')
    <section class="auth-page">
        <div class="container">
            <div class="auth-card" data-reveal>
                <div class="auth-card__header">
                    <span class="auth-card__eyebrow">{{ $hotelName }}</span>
                    <h1 class="auth-card__title">{{ __('auth.register_title') }}</h1>
                    <p class="auth-card__subtitle">{{ __('auth.register_subtitle') }}</p>
                </div>

                <form method="POST" action="{{ route('register.store') }}" class="auth-card__form stack">
                    @csrf

                    <x-field
                        :label="__('auth.name')"
                        name="name"
                        :value="old('name')"
                        :error="$errors->first('name')"
                        required
                        autocomplete="name"
                    />

                    <x-field
                        :label="__('auth.email')"
                        name="email"
                        type="email"
                        :value="old('email')"
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
                        {{ __('auth.create_account') }}
                    </x-button>
                </form>

                <div class="auth-card__footer">
                    <p class="auth-card__future">
                        {{ __('auth.already_registered') }}
                        <a href="{{ route('login') }}" class="auth-card__inline-link">{{ __('auth.sign_in') }}</a>
                    </p>
                    <a href="{{ route('home') }}" class="auth-card__back">{{ __('auth.back_home') }}</a>
                </div>
            </div>
        </div>
    </section>
@endsection
