@props([
    'variant' => 'primary',
    'size' => '',
    'href' => null,
    'type' => 'button',
    'loading' => false,
])

@php
    $classes = collect([
        'btn',
        "btn--{$variant}",
        $size ? "btn--{$size}" : null,
        $loading ? 'is-loading' : null,
    ])->filter()->implode(' ');
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }} @disabled($loading)>
        {{ $slot }}
    </button>
@endif
