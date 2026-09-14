<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class SearchAvailabilityRequest extends FormRequest
{
    protected $redirectRoute = 'guest.reservations.search';

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $maxGuests = (int) config('overlook.max_guests_per_search', 8);

        return [
            'check_in_date' => ['required', 'date', 'after_or_equal:today'],
            'check_out_date' => ['required', 'date', 'after:check_in_date'],
            'adults' => ['required', 'integer', 'min:1', 'max:'.$maxGuests],
            'children' => ['nullable', 'integer', 'min:0', 'max:'.$maxGuests],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $adults = (int) $this->input('adults', 0);
            $children = (int) $this->input('children', 0);
            $totalGuests = $adults + $children;
            $maxGuests = (int) config('overlook.max_guests_per_search', 8);

            if ($totalGuests < 1) {
                $validator->errors()->add('adults', __('reservations.validation.guests_min'));
            }

            if ($totalGuests > $maxGuests) {
                $validator->errors()->add(
                    'adults',
                    __('reservations.validation.guests_max', ['max' => $maxGuests]),
                );
            }
        });
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'check_in_date.required' => __('reservations.validation.check_in_required'),
            'check_in_date.after_or_equal' => __('reservations.validation.check_in_past'),
            'check_out_date.required' => __('reservations.validation.check_out_required'),
            'check_out_date.after' => __('reservations.validation.check_out_after_check_in'),
            'adults.required' => __('reservations.validation.adults_required'),
            'adults.min' => __('reservations.validation.adults_min'),
            'children.min' => __('reservations.validation.children_min'),
        ];
    }

    public function guestsCount(): int
    {
        return (int) $this->input('adults') + (int) $this->input('children', 0);
    }
}
