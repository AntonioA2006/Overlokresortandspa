@props([
    'variant' => 'page',
    'maxGuests' => 8,
    'formId' => 'booking',
])

@php
    use App\Support\ReservationPresentation;

    $adults = (int) old('adults', request('adults', 2));
    $children = (int) old('children', request('children', 0));
    $checkInValue = old('check_in_date', request('check_in_date'));
    $checkOutValue = old('check_out_date', request('check_out_date'));
    $guestSummary = ReservationPresentation::guestsSummary($adults, $children);
    $guestLabels = [
        'adultOne' => __('reservations.js_adult_one', ['count' => 1]),
        'adultOther' => __('reservations.js_adult_other', ['count' => 2]),
        'childZero' => __('reservations.js_child_zero', ['count' => 0]),
        'childOne' => __('reservations.js_child_one', ['count' => 1]),
        'childOther' => __('reservations.js_child_other', ['count' => 2]),
        'join' => ' · ',
    ];
@endphp

<form
    {{ $attributes->merge([
        'class' => 'booking-bar booking-bar--'.$variant,
        'method' => 'GET',
        'action' => route('guest.reservations.results'),
        'data-booking-search-form' => true,
        'novalidate' => true,
    ]) }}
    data-max-guests="{{ $maxGuests }}"
    data-searching-text="{{ __('reservations.searching') }}"
    data-error-check-in-required="{{ __('reservations.validation.check_in_required') }}"
    data-error-check-out-required="{{ __('reservations.validation.check_out_required') }}"
    data-error-check-out-after-check-in="{{ __('reservations.validation.check_out_after_check_in_client') }}"
    data-error-guests-max="{{ __('reservations.validation.guests_max_client', ['max' => $maxGuests]) }}"
    data-guest-labels="{{ json_encode($guestLabels, JSON_HEX_APOS | JSON_HEX_QUOT) }}"
>
    <div class="booking-bar__inner">
        <div @class(['booking-bar__field', 'is-error' => $errors->has('check_in_date')]) data-field data-field-check-in>
            <label class="booking-bar__label" for="{{ $formId }}-check-in">{{ __('reservations.check_in') }}</label>
            <input
                type="text"
                id="{{ $formId }}-check-in"
                class="booking-bar__input booking-bar__input--date"
                value=""
                placeholder="{{ __('reservations.check_in') }}"
                data-date-display="check-in"
                autocomplete="off"
                inputmode="none"
                readonly
            >
            <input type="hidden" name="check_in_date" value="{{ $checkInValue }}" data-date-value="check-in">
            @if ($errors->has('check_in_date'))
                <p class="booking-bar__error" data-field-error role="alert">{{ $errors->first('check_in_date') }}</p>
            @else
                <p class="booking-bar__error" data-field-error role="alert" hidden></p>
            @endif
        </div>

        <div class="booking-bar__divider" aria-hidden="true"></div>

        <div @class(['booking-bar__field', 'is-error' => $errors->has('check_out_date')]) data-field data-field-check-out>
            <label class="booking-bar__label" for="{{ $formId }}-check-out">{{ __('reservations.check_out') }}</label>
            <input
                type="text"
                id="{{ $formId }}-check-out"
                class="booking-bar__input booking-bar__input--date"
                value=""
                placeholder="{{ __('reservations.check_out') }}"
                data-date-display="check-out"
                autocomplete="off"
                inputmode="none"
                readonly
            >
            <input type="hidden" name="check_out_date" value="{{ $checkOutValue }}" data-date-value="check-out">
            @if ($errors->has('check_out_date'))
                <p class="booking-bar__error" data-field-error role="alert">{{ $errors->first('check_out_date') }}</p>
            @else
                <p class="booking-bar__error" data-field-error role="alert" hidden></p>
            @endif
        </div>

        <div class="booking-bar__divider" aria-hidden="true"></div>

        <div
            @class([
                'booking-bar__field',
                'booking-bar__field--guests',
                'is-error' => $errors->has('adults') || $errors->has('children'),
            ])
            data-field
            data-guests-field
        >
            <label class="booking-bar__label" id="{{ $formId }}-guests-label">{{ __('reservations.guests_field') }}</label>
            <button
                type="button"
                class="booking-bar__guests-trigger"
                data-guests-trigger
                aria-expanded="false"
                aria-controls="{{ $formId }}-guests-panel"
                aria-labelledby="{{ $formId }}-guests-label"
            >
                <span data-guests-summary aria-live="polite" aria-atomic="true">{{ $guestSummary }}</span>
            </button>

            <div
                id="{{ $formId }}-guests-panel"
                class="booking-bar__guests-panel"
                data-guests-panel
                hidden
            >
                <div class="booking-bar__guest-row" data-guest-stepper="adults">
                    <div class="booking-bar__guest-copy">
                        <span class="booking-bar__guest-name">{{ __('reservations.adults') }}</span>
                    </div>
                    <div class="booking-bar__guest-controls">
                        <button type="button" class="booking-bar__stepper" data-stepper-decrease aria-label="{{ __('reservations.decrease_adults') }}">−</button>
                        <span class="booking-bar__stepper-value" data-stepper-value>{{ $adults }}</span>
                        <button type="button" class="booking-bar__stepper" data-stepper-increase aria-label="{{ __('reservations.increase_adults') }}">+</button>
                    </div>
                </div>

                <div class="booking-bar__guest-row" data-guest-stepper="children">
                    <div class="booking-bar__guest-copy">
                        <span class="booking-bar__guest-name">{{ __('reservations.children') }}</span>
                    </div>
                    <div class="booking-bar__guest-controls">
                        <button type="button" class="booking-bar__stepper" data-stepper-decrease aria-label="{{ __('reservations.decrease_children') }}">−</button>
                        <span class="booking-bar__stepper-value" data-stepper-value>{{ $children }}</span>
                        <button type="button" class="booking-bar__stepper" data-stepper-increase aria-label="{{ __('reservations.increase_children') }}">+</button>
                    </div>
                </div>
            </div>

            <input type="hidden" name="adults" value="{{ $adults }}" data-guest-input="adults" min="1" max="{{ $maxGuests }}">
            <input type="hidden" name="children" value="{{ $children }}" data-guest-input="children" min="0" max="{{ $maxGuests }}">

            @if ($errors->has('adults'))
                <p class="booking-bar__error" data-field-error role="alert">{{ $errors->first('adults') }}</p>
            @elseif ($errors->has('children'))
                <p class="booking-bar__error" data-field-error role="alert">{{ $errors->first('children') }}</p>
            @else
                <p class="booking-bar__error" data-field-error role="alert" hidden></p>
            @endif
        </div>

        <div class="booking-bar__submit-wrap">
            <button type="submit" class="booking-bar__submit" data-search-submit>
                <span class="booking-bar__submit-text">{{ __('reservations.search_cta') }}</span>
                <span class="booking-bar__submit-icon" aria-hidden="true">→</span>
            </button>
        </div>
    </div>
</form>
