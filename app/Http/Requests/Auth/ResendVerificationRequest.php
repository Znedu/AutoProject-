<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class ResendVerificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null || session()->has('verification_email');
    }

    public function rules(): array
    {
        return [];
    }
}
