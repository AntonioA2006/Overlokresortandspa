@extends('layouts.app')

@section('title', __('support.guest_title'))

@section('meta_robots', 'noindex, nofollow')

@section('content')
    <section class="section support-page">
        <div class="container container--narrow stack">
            <header class="section__header" data-reveal>
                <span class="eyebrow">{{ __('support.eyebrow') }}</span>
                <h1 class="section__title">{{ __('support.guest_heading') }}</h1>
                <p class="section__subtitle">{{ __('support.guest_lead') }}</p>
            </header>

            @if (session('status'))
                <div class="inline-message inline-message--info" role="status">{{ session('status') }}</div>
            @endif

            @if (session('support_error'))
                <div class="inline-message inline-message--error" role="alert">{{ session('support_error') }}</div>
            @endif

            <div
                class="chat-window"
                data-chat-window
                data-messages-url="{{ route('api.conversations.messages', $conversation) }}"
                data-user-id="{{ auth()->id() }}"
            >
                <div class="chat-window__messages" data-chat-messages>
                    @forelse ($conversation->messages as $message)
                        <article @class(['chat-message', 'is-own' => $message->sender_id === auth()->id()])>
                            <p class="chat-message__meta">{{ $message->sender?->name }} · {{ $message->created_at?->timezone(config('overlook.timezone'))->format('H:i') }}</p>
                            <p class="chat-message__body">{{ $message->body }}</p>
                        </article>
                    @empty
                        <p class="card__text">{{ __('support.empty_thread') }}</p>
                    @endforelse
                </div>

                <form
                    method="POST"
                    action="{{ route('guest.support.messages', $conversation) }}"
                    class="chat-window__composer"
                >
                    @csrf
                    <label class="sr-only" for="support-body">{{ __('support.message_label') }}</label>
                    <textarea
                        id="support-body"
                        name="body"
                        class="form-textarea"
                        rows="3"
                        required
                        maxlength="2000"
                        placeholder="{{ __('support.message_placeholder') }}"
                    >{{ old('body') }}</textarea>
                    @error('body')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                    <x-button type="submit" variant="primary">{{ __('support.send') }}</x-button>
                </form>
            </div>
        </div>
    </section>
@endsection
