<form method="POST" action="{{ $action }}" class="stack" enctype="multipart/form-data">
    @csrf

    <x-field
        :label="__('admin.rate_name')"
        name="name"
        :value="old('name', $roomType?->name)"
        :error="$errors->first('name')"
        required
    />

    <x-field
        :label="__('admin.rate_price')"
        name="base_price_per_night"
        type="number"
        :value="old('base_price_per_night', $roomType?->base_price_per_night)"
        :error="$errors->first('base_price_per_night')"
        required
        min="0"
        step="0.01"
    />

    <x-field
        :label="__('admin.rate_max_guests')"
        name="max_guests"
        type="number"
        :value="old('max_guests', $roomType?->max_guests ?? 2)"
        :error="$errors->first('max_guests')"
        required
        min="1"
        max="20"
    />

    <div class="form-group">
        <label class="form-label" for="is_active">
            <input id="is_active" type="checkbox" name="is_active" value="1" @checked(old('is_active', $roomType?->is_active ?? true))>
            {{ __('admin.rate_active') }}
        </label>
    </div>

    <div @class(['form-group', 'is-error' => $errors->has('description')])>
        <label class="form-label" for="description">{{ __('admin.description') }}</label>
        <textarea id="description" name="description" class="form-textarea" rows="4">{{ old('description', $roomType?->description) }}</textarea>
        @error('description')
            <p class="form-error">{{ $message }}</p>
        @enderror
    </div>

    @if ($roomType?->cover_path)
        <div class="form-group">
            <p class="form-label">{{ __('admin.current_cover') }}</p>
            <img class="admin-photo-preview" src="{{ asset($roomType->cover_path) }}" alt="">
        </div>
    @endif

    <div @class(['form-group', 'is-error' => $errors->has('cover')])>
        <label class="form-label" for="cover">{{ __('admin.cover_photo') }}</label>
        <input id="cover" class="form-input" type="file" name="cover" accept="image/jpeg,image/png,image/webp">
        <p class="form-help">{{ __('admin.photo_help') }}</p>
        @error('cover')
            <p class="form-error">{{ $message }}</p>
        @enderror
    </div>

    <div class="reception-toolbar">
        <x-button type="submit" variant="primary">{{ __('admin.save') }}</x-button>
        <a class="btn btn--ghost" href="{{ route('admin.room-types.index') }}">{{ __('admin.cancel') }}</a>
    </div>
</form>
