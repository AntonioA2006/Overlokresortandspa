<?php

namespace App\Http\Requests\Admin;

use App\Enums\RoomStatus;
use App\Models\Room;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Room::class) ?? false;
    }

    protected function prepareForValidation(): void
    {
        if ($this->input('floor') === '') {
            $this->merge(['floor' => null]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'room_type_id' => ['required', 'integer', 'exists:room_types,id'],
            'number' => ['required', 'string', 'max:20', 'unique:rooms,number'],
            'floor' => ['nullable', 'integer', 'min:0', 'max:50'],
            'status' => ['required', Rule::enum(RoomStatus::class)],
            'description' => ['nullable', 'string', 'max:2000'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'room_type_id.required' => __('admin.validation.room_type_required'),
            'room_type_id.exists' => __('admin.validation.room_type_invalid'),
            'number.required' => __('admin.validation.room_number_required'),
            'number.unique' => __('admin.validation.room_number_unique'),
            'status.required' => __('admin.validation.room_status_required'),
            'photo.image' => __('admin.validation.photo_image'),
            'photo.mimes' => __('admin.validation.photo_mimes'),
            'photo.max' => __('admin.validation.photo_max'),
        ];
    }
}
