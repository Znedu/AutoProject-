<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        $userId = $this->route('user')?->id ?? $this->route('user');

        return [
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'phone'    => ['nullable', 'string', 'max:20'],
            'role'     => ['required', 'string', 'exists:roles,slug'],
            'status'   => ['required', 'string', 'in:active,inactive'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'     => 'Please enter the user\'s name.',
            'email.required'    => 'Please enter an email address.',
            'email.email'       => 'Please enter a valid email address.',
            'email.unique'      => 'This email address is already registered.',
            'role.required'     => 'Please select a role.',
            'role.exists'       => 'The selected role is invalid.',
            'status.required'   => 'Please select account status.',
            'password.min'      => 'Password must be at least 8 characters long.',
            'password.confirmed'=> 'Password confirmation does not match.',
        ];
    }
}
