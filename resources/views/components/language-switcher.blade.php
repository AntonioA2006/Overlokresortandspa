@php
    $currentLocale = app()->getLocale();
    $locales = [
        'es' => __('navigation.locale_es'),
        'en' => __('navigation.locale_en'),
    ];
@endphp

<nav class="language-switcher" aria-label="{{ __('navigation.language') }}">
    @foreach ($locales as $code => $label)
        @if ($code === $currentLocale)
            <span class="language-switcher__current" aria-current="true">{{ $label }}</span>
        @else
            <a
                href="{{ route('locale.switch', ['locale' => $code, 'redirect' => url()->full()]) }}"
                class="language-switcher__link"
                hreflang="{{ $code }}"
                lang="{{ $code }}"
            >
                {{ $label }}
            </a>
        @endif

        @unless ($loop->last)
            <span class="language-switcher__separator" aria-hidden="true">|</span>
        @endunless
    @endforeach
</nav>
