@extends('layouts.app')

@section('title', __('admin.users_edit_title', ['name' => $user->name]))

@section('meta_robots', 'noindex, nofollow')

@section('content')
    <section class="section admin-page">
        <div class="container container--narrow stack">
            @include('admin.partials.nav')

            <header class="section__header" data-reveal>
                <h1 class="section__title">{{ __('admin.users_edit', ['name' => $user->name]) }}</h1>
            </header>

            <div class="card" data-reveal>
                <div class="card__body">
                    @include('admin.users.partials.form', ['user' => $user, 'action' => route('admin.users.update', $user), 'includePassword' => false])
                </div>
            </div>
        </div>
    </section>
@endsection
