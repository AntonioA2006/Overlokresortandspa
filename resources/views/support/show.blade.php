@extends('layouts.app')

@section('title', __('support.thread_title', ['name' => $conversation->user?->name]))

@section('meta_robots', 'noindex, nofollow')

@section('content')
    <section class="section support-page">
        <div class="container container--narrow stack">
            <a href="{{ route('support.dashboard') }}" class="link-cta">
                <span aria-hidden="true">←</span>
                <span>{{ __('support.back_inbox') }}</span>
            </a>

            @if (session('status'))
                <div class="inline-message inline-message--info" role="status">{{ session('status') }}</div>
            @endif

            @if (session('support_error'))
                <div class="inline-message inline-message--error" role="alert">{{ session('support_error') }}</div>
            @endif

            <header class="section__header">
                <h1 class="section__title">{{ $conversation->user?->name }}</h1>
                <p class="section__subtitle">{{ $conversation->user?->email }} · {{ $conversation->status->label() }}</p>
            </header>

            <div
                class="chat-window"
                data-chat-window
                data-messages-url="{{ route('api.conversations.messages', $conversation) }}"
                data-user-id="{{ auth()->id() }}"
            >
                <div class="chat-window__messages" data-chat-messages>
                    @foreach ($conversation->messages as $message)
                        <article @class(['chat-message', 'is-own' => $message->sender_id === auth()->id()])>
                            <p class="chat-message__meta">{{ $message->sender?->name }} · {{ $message->created_at?->timezone(config('overlook.timezone'))->format('H:i') }}</p>
                            <p class="chat-message__body">{{ $message->body }}</p>
                        </article>
                    @endforeach
                </div>

                @if ($conversation->status->value !== 'closed')
                    <form method="POST" action="{{ route('support.conversations.reply', $conversation) }}" class="chat-window__composer">
                        @csrf
                        <label class="sr-only" for="support-reply">{{ __('support.message_label') }}</label>
                        <textarea id="support-reply" name="body" class="form-textarea" rows="3" required maxlength="2000">{{ old('body') }}</textarea>
                        <div class="reception-toolbar">
                            <x-button type="submit" variant="primary">{{ __('support.reply') }}</x-button>
                        </div>
                    </form>

                    <form method="POST" action="{{ route('support.conversations.close', $conversation) }}">
                        @csrf
                        <x-button type="submit" variant="ghost">{{ __('support.close') }}</x-button>
                    </form>
                @endif
            </div>
        </div>
    </section>
@endsection
