<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UpdatePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = [
            'password' => ['required', 'confirmed', Password::min(8)],
        ];

        if ($this->user()?->hasPassword()) {
            $rules['current_password'] = ['required', 'current_password'];
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'current_password.required' => __('profile.validation.current_password_required'),
            'current_password.current_password' => __('profile.validation.current_password_invalid'),
            'password.required' => __('auth.validation.password_required'),
            'password.confirmed' => __('auth.validation.password_confirmed'),
            'password.min' => __('auth.validation.password_min'),
        ];
    }
}
