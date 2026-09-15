<form method="POST" action="{{ $action }}" class="stack">
    @csrf

    <x-field
        :label="__('auth.name')"
        name="name"
        :value="old('name', $user?->name)"
        :error="$errors->first('name')"
        required
        autocomplete="name"
    />

    <x-field
        :label="__('auth.email')"
        name="email"
        type="email"
        :value="old('email', $user?->email)"
        :error="$errors->first('email')"
        required
        autocomplete="email"
    />

    <x-field
        :label="__('profile.phone')"
        name="phone"
        type="tel"
        :value="old('phone', $user?->phone)"
        :error="$errors->first('phone')"
        autocomplete="tel"
    />

    <div @class(['form-group', 'is-error' => $errors->has('role')])>
        <label class="form-label" for="role">{{ __('admin.role') }}</label>
        <select id="role" name="role" class="form-select" required>
            @foreach ($roles as $role)
                <option value="{{ $role->value }}" @selected(old('role', $user?->role?->value) === $role->value)>
                    {{ $role->label() }}
                </option>
            @endforeach
        </select>
        @error('role')
            <p class="form-error">{{ $message }}</p>
        @enderror
    </div>

    @if ($includePassword)
        <x-field
            :label="__('auth.password')"
            name="password"
            type="password"
            :error="$errors->first('password')"
            required
            autocomplete="new-password"
        />
        <x-field
            :label="__('auth.password_confirmation')"
            name="password_confirmation"
            type="password"
            :error="$errors->first('password_confirmation')"
            required
            autocomplete="new-password"
        />
    @endif

    <div class="reception-toolbar">
        <x-button type="submit" variant="primary">{{ __('admin.save') }}</x-button>
        <a class="btn btn--ghost" href="{{ route('admin.users.index') }}">{{ __('admin.cancel') }}</a>
    </div>
</form>
