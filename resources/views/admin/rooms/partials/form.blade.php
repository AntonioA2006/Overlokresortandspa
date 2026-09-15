<form method="POST" action="{{ $action }}" class="stack">
    @csrf

    <div @class(['form-group', 'is-error' => $errors->has('room_type_id')])>
        <label class="form-label" for="room_type_id">{{ __('admin.room_type') }}</label>
        <select id="room_type_id" name="room_type_id" class="form-select" required>
            <option value="">{{ __('admin.room_type_placeholder') }}</option>
            @foreach ($roomTypes as $roomType)
                <option value="{{ $roomType->id }}" @selected(old('room_type_id', $room?->room_type_id) == $roomType->id)>
                    {{ $roomType->name }}
                </option>
            @endforeach
        </select>
        @error('room_type_id')
            <p class="form-error">{{ $message }}</p>
        @enderror
    </div>

    <x-field
        :label="__('admin.room_number_label')"
        name="number"
        :value="old('number', $room?->number)"
        :error="$errors->first('number')"
        required
    />

    <x-field
        :label="__('admin.floor')"
        name="floor"
        type="number"
        :value="old('floor', $room?->floor)"
        :error="$errors->first('floor')"
    />

    <div @class(['form-group', 'is-error' => $errors->has('status')])>
        <label class="form-label" for="status">{{ __('admin.status') }}</label>
        <select id="status" name="status" class="form-select" required>
            @foreach ($statuses as $status)
                <option value="{{ $status->value }}" @selected(old('status', $room?->status?->value) === $status->value)>
                    {{ $status->label() }}
                </option>
            @endforeach
        </select>
        @error('status')
            <p class="form-error">{{ $message }}</p>
        @enderror
    </div>

    <div @class(['form-group', 'is-error' => $errors->has('description')])>
        <label class="form-label" for="description">{{ __('admin.description') }}</label>
        <textarea id="description" name="description" class="form-textarea" rows="4">{{ old('description', $room?->description) }}</textarea>
        @error('description')
            <p class="form-error">{{ $message }}</p>
        @enderror
    </div>

    <div class="reception-toolbar">
        <x-button type="submit" variant="primary">{{ __('admin.save') }}</x-button>
        <a class="btn btn--ghost" href="{{ route('admin.rooms.index') }}">{{ __('admin.cancel') }}</a>
    </div>
</form>
