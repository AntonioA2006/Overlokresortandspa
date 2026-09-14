@props([
    'transparent' => false,
])

@php
    $currentPath = request()->path();
    $navItems = [
        ['label' => __('navigation.nav.stays'), 'href' => route('guest.reservations.search'), 'match' => 'guest/reservations/search'],
        ['label' => __('navigation.nav.experiences'), 'href' => route('home').'#experiencias', 'match' => null],
        ['label' => __('navigation.nav.spa'), 'href' => route('home').'#spa', 'match' => null],
        ['label' => __('navigation.nav.gastronomy'), 'href' => route('home').'#gastronomia', 'match' => null],
    ];

    $isActive = static function (string $href, ?string $match) use ($currentPath): bool {
        if ($match !== null && request()->is($match)) {
            return true;
        }

        return $href !== '#' && url($currentPath) === $href;
    };
@endphp

<header
    class="premium-header"
    data-premium-header
    @if($transparent) data-header-transparent @endif
>
    <div class="container premium-header__inner">
        <a href="{{ route('home') }}" class="premium-header__brand" aria-label="{{ config('overlook.hotel_name') }}">
            Overlook
        </a>

        <nav class="premium-header__nav" aria-label="{{ __('navigation.main_nav') }}">
            @foreach ($navItems as $item)
                <a
                    href="{{ $item['href'] }}"
                    @class(['is-active' => $isActive($item['href'], $item['match'])])
                >
                    {{ $item['label'] }}
                </a>
            @endforeach
        </nav>

        <div class="premium-header__actions">
            <x-language-switcher />

            @auth
                <a href="{{ route('guest.reservations.index') }}" class="btn btn--ghost btn--small premium-header__account-link">
                    {{ __('navigation.my_stay') }}
                </a>

                <div class="premium-header__user" aria-label="{{ __('navigation.my_stay') }}">
                    @if (auth()->user()->avatar)
                        <img
                            src="{{ auth()->user()->avatar }}"
                            alt=""
                            class="premium-header__avatar"
                        >
                    @else
                        <span class="premium-header__avatar-fallback" aria-hidden="true">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </span>
                    @endif
                    <span class="premium-header__name">{{ auth()->user()->name }}</span>
                </div>

                <form action="{{ route('logout') }}" method="POST" class="logout-form premium-header__logout-form">
                    @csrf
                    <button type="submit" class="btn btn--ghost btn--small">{{ __('navigation.logout') }}</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="btn btn--ghost btn--small">{{ __('navigation.login') }}</a>
            @endauth

            <a href="{{ route('guest.reservations.search') }}" class="btn btn--primary btn--small">
                {{ __('navigation.book') }}
            </a>

            <button
                type="button"
                class="premium-header__toggle"
                data-header-toggle
                aria-expanded="false"
                aria-controls="premium-header-menu"
                aria-label="{{ __('navigation.open_menu') }}"
            >
                <span class="premium-header__toggle-bar" aria-hidden="true"></span>
            </button>
        </div>
    </div>

    <div
        id="premium-header-menu"
        class="premium-header__mobile-panel"
        data-header-panel
        aria-hidden="true"
        inert
    >
        <nav class="premium-header__mobile-nav" aria-label="{{ __('navigation.mobile_nav') }}">
            @foreach ($navItems as $item)
                <a href="{{ $item['href'] }}">{{ $item['label'] }}</a>
            @endforeach
        </nav>

        <div class="premium-header__mobile-locale">
            <x-language-switcher />
        </div>

        <div class="premium-header__mobile-actions">
            @auth
                <a href="{{ route('guest.reservations.index') }}" class="btn btn--secondary">{{ __('navigation.my_stay') }}</a>
                <form action="{{ route('logout') }}" method="POST" class="logout-form">
                    @csrf
                    <button type="submit" class="btn btn--ghost">{{ __('navigation.logout') }}</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="btn btn--secondary">{{ __('navigation.login') }}</a>
            @endauth

            <a href="{{ route('guest.reservations.search') }}" class="btn btn--primary">
                {{ __('navigation.book') }}
            </a>
        </div>
    </div>
</header>
