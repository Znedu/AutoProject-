<?php

namespace App\Http\Requests\Payment;

use Illuminate\Foundation\Http\FormRequest;

class ResubmitPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('customer') ?? false;
    }

    public function rules(): array
    {
        return [
            'payment_method'     => ['required', 'string', 'in:gcash,maya,bank_transfer,cash'],
            'reference_number'   => ['required', 'string', 'max:100'],
            'payment_screenshot' => ['required', 'image', 'mimes:png,jpg,jpeg', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'payment_method.required'     => 'Please select a payment method.',
            'payment_method.in'           => 'Invalid payment method selected.',
            'reference_number.required'   => 'Reference number is required.',
            'payment_screenshot.required' => 'Please upload your payment screenshot.',
            'payment_screenshot.image'    => 'The file must be an image.',
            'payment_screenshot.mimes'    => 'Only PNG, JPG, or JPEG images are accepted.',
            'payment_screenshot.max'      => 'Image must be under 5 MB.',
        ];
    }
}
