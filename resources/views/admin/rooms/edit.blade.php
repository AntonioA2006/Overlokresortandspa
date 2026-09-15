@extends('layouts.app')

@section('title', __('admin.rooms_edit_title', ['number' => $room->number]))

@section('meta_robots', 'noindex, nofollow')

@section('content')
    <section class="section admin-page">
        <div class="container container--narrow stack">
            @include('admin.partials.nav')

            <header class="section__header" data-reveal>
                <h1 class="section__title">{{ __('admin.rooms_edit', ['number' => $room->number]) }}</h1>
            </header>

            <div class="card" data-reveal>
                <div class="card__body">
                    @include('admin.rooms.partials.form', ['room' => $room, 'action' => route('admin.rooms.update', $room)])
                </div>
            </div>
        </div>
    </section>
@endsection
