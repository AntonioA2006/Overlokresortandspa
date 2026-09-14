@props([
    'label',
    'name',
    'type' => 'text',
    'id' => null,
    'value' => null,
    'help' => null,
    'error' => null,
    'required' => false,
])

@php
    $fieldId = $id ?? $name;
@endphp

<div @class(['field', 'is-error' => filled($error)]) data-field>
    <label class="field__label" for="{{ $fieldId }}">{{ $label }}</label>

    <input
        type="{{ $type }}"
        name="{{ $name }}"
        id="{{ $fieldId }}"
        value="{{ old($name, $value) }}"
        @if($required) required @endif
        {{ $attributes->merge(['class' => 'field__input']) }}
    >

    @if ($help)
        <p class="field__help">{{ $help }}</p>
    @endif

    <p class="field__error" @if(!filled($error)) hidden @endif data-field-error role="alert">
        {{ $error }}
    </p>
</div>
