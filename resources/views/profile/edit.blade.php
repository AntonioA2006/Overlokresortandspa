@extends('layouts.app')

@section('title', __('profile.page_title'))

@section('meta_robots', 'noindex, nofollow')

@section('content')
    <section class="section profile-page">
        <div class="container container--narrow stack">
            <header class="section__header" data-reveal>
                <span class="eyebrow">{{ $hotelName }}</span>
                <h1 class="section__title">{{ __('profile.heading') }}</h1>
                <p class="section__subtitle">{{ __('profile.lead') }}</p>
            </header>

            @if (session('status'))
                <div class="inline-message inline-message--info" role="status">{{ session('status') }}</div>
            @endif

            @if (! $user->hasVerifiedEmail())
                <div class="inline-message inline-message--info" role="status">
                    {{ __('profile.unverified') }}
                    <form method="POST" action="{{ route('verification.send') }}" class="stack">
                        @csrf
                        <x-button type="submit" variant="secondary">{{ __('auth.verify_resend') }}</x-button>
                    </form>
                </div>
            @endif

            <div class="card" data-reveal>
                <div class="card__body stack">
                    <h2 class="card__title">{{ __('profile.account') }}</h2>
                    <form method="POST" action="{{ route('profile.update') }}" class="stack">
                        @csrf

                        <x-field
                            :label="__('auth.name')"
                            name="name"
                            :value="old('name', $user->name)"
                            :error="$errors->first('name')"
                            required
                            autocomplete="name"
                        />

                        <x-field
                            :label="__('auth.email')"
                            name="email"
                            type="email"
                            :value="old('email', $user->email)"
                            :error="$errors->first('email')"
                            required
                            autocomplete="email"
                        />

                        <x-field
                            :label="__('profile.phone')"
                            name="phone"
                            type="tel"
                            :value="old('phone', $user->phone)"
                            :error="$errors->first('phone')"
                            autocomplete="tel"
                        />

                        <p class="card__text">{{ __('profile.role_label', ['role' => $user->role->label()]) }}</p>

                        <x-button type="submit" variant="primary">{{ __('profile.save') }}</x-button>
                    </form>
                </div>
            </div>

            <div class="card" data-reveal>
                <div class="card__body stack">
                    <h2 class="card__title">{{ __('profile.password_heading') }}</h2>
                    @if ($user->isGoogleAccount() && ! $user->hasPassword())
                        <p class="card__text">{{ __('profile.google_password_help') }}</p>
                    @endif

                    <form method="POST" action="{{ route('profile.password') }}" class="stack">
                        @csrf

                        @if ($user->hasPassword())
                            <x-field
                                :label="__('profile.current_password')"
                                name="current_password"
                                type="password"
                                :error="$errors->first('current_password')"
                                required
                                autocomplete="current-password"
                            />
                        @endif

                        <x-field
                            :label="__('profile.new_password')"
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

                        <x-button type="submit" variant="secondary">
                            {{ $user->hasPassword() ? __('profile.change_password') : __('profile.set_password') }}
                        </x-button>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection
