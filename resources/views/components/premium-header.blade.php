@props([
    'transparent' => false,
])

@php
    use App\Services\Auth\PostLoginRedirectService;

    $currentPath = request()->path();
    $navItems = [
        ['label' => __('navigation.nav.stays'), 'href' => route('guest.reservations.search'), 'match' => 'guest/reservations/search'],
        ['label' => __('navigation.nav.rooms'), 'href' => route('guest.rooms.index'), 'match' => 'guest/rooms'],
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

    $user = auth()->user();
    $unreadCount = $unreadNotificationCount ?? 0;
    $dashboardUrl = $user ? app(PostLoginRedirectService::class)->redirectPath($user) : null;
    $showMyStay = $user?->isGuest() ?? false;
    $showGuestSupport = $user?->isGuest() ?? false;
    $showStaffPanel = $user?->isStaff() ?? false;
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
                <a
                    href="{{ route('guest.notifications.index') }}"
                    class="btn btn--ghost btn--small premium-header__account-link"
                    @if($unreadCount > 0) aria-label="{{ __('navigation.notifications_unread', ['count' => $unreadCount]) }}" @endif
                >
                    {{ __('navigation.notifications') }}
                    @if ($unreadCount > 0)
                        <span class="badge badge--count">{{ $unreadCount }}</span>
                    @endif
                </a>

                @if ($showMyStay)
                    <a href="{{ route('guest.reservations.index') }}" class="btn btn--ghost btn--small premium-header__account-link">
                        {{ __('navigation.my_stay') }}
                    </a>
                @endif

                @if ($showGuestSupport)
                    <a href="{{ route('guest.support.index') }}" class="btn btn--ghost btn--small premium-header__account-link">
                        {{ __('navigation.support') }}
                    </a>
                @endif

                @if ($showStaffPanel && $dashboardUrl)
                    <a href="{{ $dashboardUrl }}" class="btn btn--ghost btn--small premium-header__account-link">
                        {{ __('navigation.panel') }}
                    </a>
                @endif

                <div class="premium-header__user" aria-label="{{ $user->name }}">
                    @if ($user->avatar)
                        <img
                            src="{{ $user->avatar }}"
                            alt=""
                            class="premium-header__avatar"
                        >
                    @else
                        <span class="premium-header__avatar-fallback" aria-hidden="true">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </span>
                    @endif
                    <span class="premium-header__name">{{ $user->name }}</span>
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
                <a href="{{ route('guest.notifications.index') }}" class="btn btn--secondary">
                    {{ __('navigation.notifications') }}
                    @if ($unreadCount > 0)
                        <span class="badge badge--count">{{ $unreadCount }}</span>
                    @endif
                </a>
                @if ($showMyStay)
                    <a href="{{ route('guest.reservations.index') }}" class="btn btn--secondary">{{ __('navigation.my_stay') }}</a>
                @endif
                @if ($showGuestSupport)
                    <a href="{{ route('guest.support.index') }}" class="btn btn--secondary">{{ __('navigation.support') }}</a>
                @endif
                @if ($showStaffPanel && $dashboardUrl)
                    <a href="{{ $dashboardUrl }}" class="btn btn--secondary">{{ __('navigation.panel') }}</a>
                @endif
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
