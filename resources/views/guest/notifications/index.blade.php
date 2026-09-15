@extends('layouts.app')

@section('title', __('notifications.page_title'))

@section('meta_robots', 'noindex, nofollow')

@section('content')
    <section class="section notifications-page">
        <div class="container container--narrow">
            <header class="section__header" data-reveal>
                <span class="eyebrow">{{ __('notifications.eyebrow') }}</span>
                <h1 class="section__title">{{ __('notifications.heading') }}</h1>
                <p class="section__subtitle">{{ __('notifications.lead') }}</p>
            </header>

            @if (session('status'))
                <div class="inline-message inline-message--info" role="status">{{ session('status') }}</div>
            @endif

            @if ($unreadCount > 0)
                <form method="POST" action="{{ route('guest.notifications.read-all') }}" class="notifications-toolbar" data-reveal>
                    @csrf
                    <x-button type="submit" variant="ghost">{{ __('notifications.mark_all_read') }}</x-button>
                </form>
            @endif

            @if ($notifications->isEmpty())
                <div class="my-stays__empty" data-reveal>
                    <h2 class="my-stays__empty-title">{{ __('notifications.empty_title') }}</h2>
                    <p class="my-stays__empty-lead">{{ __('notifications.empty_lead') }}</p>
                </div>
            @else
                <ul class="notification-list" data-notifications-poll="{{ route('api.notifications.index') }}">
                    @foreach ($notifications as $notification)
                        <li @class(['notification-item', 'is-unread' => $notification->read_at === null])>
                            <div>
                                <p class="notification-item__title">{{ $notification->title }}</p>
                                <p class="notification-item__message">{{ $notification->message }}</p>
                                <p class="notification-item__meta">
                                    {{ $notification->sent_at?->timezone(config('overlook.timezone'))->format('d M Y H:i') }}
                                </p>
                            </div>
                            @if ($notification->read_at === null)
                                <form method="POST" action="{{ route('guest.notifications.read', $notification) }}">
                                    @csrf
                                    <button type="submit" class="btn btn--ghost btn--small">{{ __('notifications.mark_read') }}</button>
                                </form>
                            @endif
                        </li>
                    @endforeach
                </ul>

                <div class="notifications-pagination reception-toolbar">
                    @if ($notifications->previousPageUrl())
                        <a class="btn btn--ghost btn--small" href="{{ $notifications->previousPageUrl() }}">{{ __('common.previous') }}</a>
                    @endif
                    @if ($notifications->nextPageUrl())
                        <a class="btn btn--ghost btn--small" href="{{ $notifications->nextPageUrl() }}">{{ __('common.next') }}</a>
                    @endif
                </div>
            @endif
        </div>
    </section>
@endsection
