<?php

namespace App\Http\Requests\Admin;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        $model = $this->route('user');

        return $model instanceof User
            && ($this->user()?->can('update', $model) ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $model = $this->route('user');
        $userId = $model instanceof User ? $model->id : null;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'role' => ['required', Rule::enum(UserRole::class)],
            'phone' => ['nullable', 'string', 'max:40'],
        ];
    }

    /**
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $model = $this->route('user');

                if (! $model instanceof User) {
                    return;
                }

                $newRole = UserRole::from((string) $this->input('role'));

                if ($model->role !== UserRole::Admin || $newRole === UserRole::Admin) {
                    return;
                }

                $otherAdmins = User::query()
                    ->where('role', UserRole::Admin)
                    ->whereKeyNot($model->id)
                    ->exists();

                if (! $otherAdmins) {
                    $validator->errors()->add('role', __('admin.validation.last_admin'));
                }
            },
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => __('admin.validation.user_name_required'),
            'email.required' => __('auth.validation.email_required'),
            'email.email' => __('auth.validation.email_invalid'),
            'email.unique' => __('auth.validation.email_unique'),
            'role.required' => __('admin.validation.role_required'),
        ];
    }
}
