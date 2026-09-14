<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="@yield('meta_description', __('home.meta_description'))">
    @hasSection('meta_robots')
        <meta name="robots" content="@yield('meta_robots')">
    @endif
    <title>@yield('title', config('overlook.hotel_name'))</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body
    @class(['page-has-hero' => trim($__env->yieldContent('hero_layout')) === 'true'])
    data-menu-open-label="{{ __('navigation.open_menu') }}"
    data-menu-close-label="{{ __('navigation.close_menu') }}"
>
    <a href="#main-content" class="skip-link">{{ __('common.skip_to_content') }}</a>

    <div id="toast-container" class="toast-container" aria-live="polite" aria-atomic="true"></div>

    <x-premium-header :transparent="trim($__env->yieldContent('hero_layout')) === 'true'" />

    <main id="main-content" class="site-main" aria-label="{{ __('common.main_content') }}">
        @yield('content')
    </main>

    <footer class="site-footer">
        <div class="container site-footer__inner">
            <div>
                <div class="site-footer__brand">{{ __('navigation.footer.brand') }}</div>
                <p class="site-footer__text">
                    {{ __('navigation.footer.text') }}
                </p>
            </div>
            <div>
                <span class="badge">{{ __('navigation.footer.badge') }}</span>
            </div>
        </div>
    </footer>
</body>
</html>
