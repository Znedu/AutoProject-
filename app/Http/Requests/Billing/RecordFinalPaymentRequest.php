<?php

namespace App\Http\Requests\Billing;

use App\Models\Payment;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class RecordFinalPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $booking = $this->route('booking');

        return $booking && Gate::allows('billing.record-payment', $booking);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'type' => [
                'required',
                'string',
                Rule::in([
                    Payment::TYPE_DEPOSIT,
                    Payment::TYPE_FINAL_PAYMENT,
                ]),
            ],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'method' => ['required', 'string', Rule::in(['cash', 'gcash', 'maya', 'bank_transfer'])],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'payment_proof' => ['nullable', 'file', 'image', 'max:5120'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
