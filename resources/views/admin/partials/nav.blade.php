<nav class="admin-nav" aria-label="{{ __('admin.nav_label') }}">
    <a href="{{ route('admin.dashboard') }}" @class(['is-active' => request()->routeIs('admin.dashboard')])>{{ __('admin.overview') }}</a>
    <a href="{{ route('admin.rooms.index') }}" @class(['is-active' => request()->routeIs('admin.rooms.*')])>{{ __('admin.rooms') }}</a>
    <a href="{{ route('admin.users.index') }}" @class(['is-active' => request()->routeIs('admin.users.*')])>{{ __('admin.users') }}</a>
    <a href="{{ route('admin.room-types.index') }}" @class(['is-active' => request()->routeIs('admin.room-types.*')])>{{ __('admin.rates') }}</a>
</nav>
