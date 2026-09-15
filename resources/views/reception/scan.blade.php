@extends('layouts.app')

@section('title', __('reception.scan_title'))

@section('meta_robots', 'noindex, nofollow')

@section('content')
    <section class="section reception-page">
        <div class="container container--narrow stack">
            <div class="section__header" data-reveal>
                <span class="eyebrow">{{ $hotelName }}</span>
                <h1 class="section__title">{{ __('reception.scan_heading') }}</h1>
                <p class="section__subtitle">{{ __('reception.scan_lead') }}</p>
            </div>

            @if (session('status'))
                <div class="inline-message inline-message--info" role="status">{{ session('status') }}</div>
            @endif

            @if (session('reception_error'))
                <div class="inline-message inline-message--error" role="alert">{{ session('reception_error') }}</div>
            @endif

            <div
                class="card reception-scan"
                data-reveal
                data-reception-scan-page
                data-i18n-scanning="{{ __('reception.camera_scanning') }}"
                data-i18n-detected="{{ __('reception.camera_detected') }}"
                data-i18n-denied="{{ __('reception.camera_denied') }}"
                data-i18n-insecure="{{ __('reception.camera_insecure') }}"
                data-i18n-missing="{{ __('reception.camera_missing') }}"
                data-i18n-unavailable="{{ __('reception.camera_unavailable') }}"
            >
                <div class="card__body stack">
                    <div class="reception-camera" data-reception-camera-wrap hidden>
                        <p class="reception-camera__label">{{ __('reception.camera_primary') }}</p>
                        <div class="reception-camera__frame">
                            <video data-reception-camera playsinline muted></video>
                        </div>
                        <p class="reception-camera__status" data-reception-camera-status role="status"></p>
                        <div class="reception-toolbar">
                            <button type="button" class="btn btn--primary" data-reception-camera-start>
                                {{ __('reception.start_camera') }}
                            </button>
                            <button type="button" class="btn btn--ghost" data-reception-camera-stop hidden>
                                {{ __('reception.stop_camera') }}
                            </button>
                        </div>
                    </div>

                    <p class="card__text" data-reception-camera-fallback>
                        {{ __('reception.camera_unsupported') }}
                    </p>

                    <form
                        method="POST"
                        action="{{ route('reception.lookup') }}"
                        class="stack"
                        data-reception-lookup-form
                    >
                        @csrf
                        <h2 class="reception-scan__fallback-title">{{ __('reception.fallback_heading') }}</h2>
                        <p class="card__text">{{ __('reception.fallback_help') }}</p>
                        <div class="form-group">
                            <label class="form-label" for="reception-scan-token">{{ __('reception.token_label') }}</label>
                            <input
                                id="reception-scan-token"
                                class="form-input"
                                type="text"
                                name="lookup"
                                data-reception-token-input
                                autocomplete="off"
                                required
                                value="{{ old('lookup') }}"
                                placeholder="{{ __('reception.token_placeholder') }}"
                            >
                            @error('lookup')
                                <p class="form-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="reception-toolbar">
                            <button type="submit" class="btn btn--secondary" data-reception-lookup>
                                {{ __('reception.lookup') }}
                            </button>
                            <a href="{{ route('reception.dashboard') }}" class="btn btn--ghost">{{ __('reception.back_dashboard') }}</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection
