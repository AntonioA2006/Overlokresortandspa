<?php

namespace App\Http\Requests\Admin;

use App\Models\RoomType;
use Illuminate\Foundation\Http\FormRequest;

class UpdateRoomTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        $roomType = $this->route('roomType');

        return $roomType instanceof RoomType
            && ($this->user()?->can('update', $roomType) ?? false);
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'base_price_per_night' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'max_guests' => ['required', 'integer', 'min:1', 'max:20'],
            'is_active' => ['required', 'boolean'],
            'description' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => __('admin.validation.rate_name_required'),
            'base_price_per_night.required' => __('admin.validation.rate_price_required'),
            'base_price_per_night.min' => __('admin.validation.rate_price_min'),
            'max_guests.required' => __('admin.validation.rate_guests_required'),
            'max_guests.min' => __('admin.validation.rate_guests_min'),
        ];
    }
}
