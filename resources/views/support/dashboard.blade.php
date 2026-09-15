@extends('layouts.app')

@section('title', __('support.inbox_title'))

@section('meta_robots', 'noindex, nofollow')

@section('content')
    <section class="section support-page">
        <div class="container stack">
            <header class="section__header" data-reveal>
                <span class="eyebrow">{{ $hotelName }}</span>
                <h1 class="section__title">{{ __('support.inbox_heading') }}</h1>
                <p class="section__subtitle">{{ __('support.inbox_lead') }}</p>
            </header>

            @if (session('status'))
                <div class="inline-message inline-message--info" role="status">{{ session('status') }}</div>
            @endif

            @if ($conversations->isEmpty())
                <p class="card__text">{{ __('support.inbox_empty') }}</p>
            @else
                <ul class="support-inbox">
                    @foreach ($conversations as $conversation)
                        <li class="support-inbox__item">
                            <div>
                                <p class="support-inbox__guest">{{ $conversation->user?->name }}</p>
                                <p class="support-inbox__preview">
                                    {{ $conversation->latestMessage?->body ?? __('support.no_messages_yet') }}
                                </p>
                            </div>
                            <div class="support-inbox__aside">
                                <span class="badge">{{ $conversation->status->label() }}</span>
                                <a class="btn btn--secondary btn--small" href="{{ route('support.conversations.show', $conversation) }}">
                                    {{ __('support.open') }}
                                </a>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </section>
@endsection
