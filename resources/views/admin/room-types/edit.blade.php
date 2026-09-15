@extends('layouts.app')

@section('title', __('admin.rates_edit_title', ['name' => $roomType->name]))

@section('meta_robots', 'noindex, nofollow')

@section('content')
    <section class="section admin-page">
        <div class="container container--narrow stack">
            @include('admin.partials.nav')

            <header class="section__header" data-reveal>
                <h1 class="section__title">{{ __('admin.rates_edit', ['name' => $roomType->name]) }}</h1>
            </header>

            <div class="card" data-reveal>
                <div class="card__body">
                    <form method="POST" action="{{ route('admin.room-types.update', $roomType) }}" class="stack">
                        @csrf

                        <x-field
                            :label="__('admin.rate_name')"
                            name="name"
                            :value="old('name', $roomType->name)"
                            :error="$errors->first('name')"
                            required
                        />

                        <x-field
                            :label="__('admin.rate_price')"
                            name="base_price_per_night"
                            type="number"
                            :value="old('base_price_per_night', $roomType->base_price_per_night)"
                            :error="$errors->first('base_price_per_night')"
                            required
                            min="0"
                            step="0.01"
                        />

                        <x-field
                            :label="__('admin.rate_max_guests')"
                            name="max_guests"
                            type="number"
                            :value="old('max_guests', $roomType->max_guests)"
                            :error="$errors->first('max_guests')"
                            required
                            min="1"
                            max="20"
                        />

                        <div class="form-group">
                            <label class="form-label" for="is_active">
                                <input id="is_active" type="checkbox" name="is_active" value="1" @checked(old('is_active', $roomType->is_active))>
                                {{ __('admin.rate_active') }}
                            </label>
                        </div>

                        <div @class(['form-group', 'is-error' => $errors->has('description')])>
                            <label class="form-label" for="description">{{ __('admin.description') }}</label>
                            <textarea id="description" name="description" class="form-textarea" rows="4">{{ old('description', $roomType->description) }}</textarea>
                            @error('description')
                                <p class="form-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="reception-toolbar">
                            <x-button type="submit" variant="primary">{{ __('admin.save') }}</x-button>
                            <a class="btn btn--ghost" href="{{ route('admin.room-types.index') }}">{{ __('admin.cancel') }}</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection
