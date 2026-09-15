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

            <div class="card" data-reveal>
                <div class="card__body stack">
                    <div class="form-group">
                        <label class="form-label" for="reception-scan-token">{{ __('reception.token_label') }}</label>
                        <input
                            id="reception-scan-token"
                            class="form-input"
                            type="text"
                            data-reception-token-input
                            autocomplete="off"
                            placeholder="{{ __('reception.token_placeholder') }}"
                        >
                    </div>

                    <div class="reception-toolbar">
                        <button type="button" class="btn btn--primary" data-reception-lookup>
                            {{ __('reception.lookup') }}
                        </button>
                        <a href="{{ route('reception.dashboard') }}" class="btn btn--ghost">{{ __('reception.back_dashboard') }}</a>
                    </div>

                    <div class="reception-camera" data-reception-camera-wrap hidden>
                        <video data-reception-camera playsinline muted></video>
                        <button type="button" class="btn btn--secondary" data-reception-camera-start>
                            {{ __('reception.start_camera') }}
                        </button>
                    </div>

                    <p class="card__text" data-reception-camera-fallback>
                        {{ __('reception.camera_fallback') }}
                    </p>
                </div>
            </div>
        </div>
    </section>
@endsection
