@props([
    'title' => null,
    'description' => null,
])

@php
    $title = $title ?? __('common.placeholder_title');
    $description = $description ?? __('common.placeholder_description');
@endphp

<section class="page-placeholder">
    <div class="container page-placeholder__inner" data-reveal>
        <span class="eyebrow">{{ __('common.placeholder_eyebrow') }}</span>
        <h1 class="page-placeholder__title">{{ $title }}</h1>
        <p class="page-placeholder__text">{{ $description }}</p>
        <x-button href="{{ route('home') }}" variant="ghost">{{ __('common.back_home') }}</x-button>
    </div>
</section>
