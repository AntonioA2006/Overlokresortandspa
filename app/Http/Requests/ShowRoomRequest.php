<?php

namespace App\Http\Requests;

use Illuminate\Validation\Validator;

class ShowRoomRequest extends SearchAvailabilityRequest
{
    public function hasSearchContext(): bool
    {
        return $this->filled('check_in_date')
            || $this->filled('check_out_date')
            || $this->filled('adults')
            || $this->filled('children');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        if (! $this->hasSearchContext()) {
            return [];
        }

        return parent::rules();
    }

    public function withValidator(Validator $validator): void
    {
        if (! $this->hasSearchContext()) {
            return;
        }

        parent::withValidator($validator);
    }
}
