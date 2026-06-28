<?php

namespace App\Http\Requests\Booking;

use App\Models\Payment;
use App\Rules\ScheduleAvailable;
use App\Services\Booking\ScheduleAvailabilityService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', \App\Models\Booking::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'service_ids' => ['required', 'array', 'min:1'],
            'service_ids.*' => ['required', 'integer', 'exists:services,id'],
            'brands' => ['nullable', 'array'],
            'brands.*' => ['nullable', 'string', 'max:255'],
            'customer_name' => ['required', 'string', 'max:255'],
            'contact_number' => ['required', 'string', 'max:50'],
            'vehicle_make' => ['required', 'string', 'max:100'],
            'vehicle_model' => ['required', 'string', 'max:100'],
            'vehicle_year' => ['required', 'integer', 'min:1900', 'max:'.(now()->year + 1)],
            'plate_number' => ['required', 'string', 'max:20'],
            'preferred_date' => ['required', 'date', 'after_or_equal:today'],
            'preferred_time' => [
                'required',
                'string',
                'max:20',
                new ScheduleAvailable(app(ScheduleAvailabilityService::class)),
            ],
            'notes' => ['nullable', 'string', 'max:2000'],
            'payment_method' => ['required', Rule::in([Payment::METHOD_GCASH, Payment::METHOD_MAYA])],
            'reference_number' => ['required', 'string', 'max:50'],
            'payment_screenshot' => ['required', 'image', 'mimes:png,jpg,jpeg', 'max:5120'],
            'agreed_to_terms' => ['accepted'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'service_ids.required' => 'Please select at least one service.',
            'agreed_to_terms.accepted' => 'You must agree to the terms and conditions.',
            'payment_screenshot.required' => 'Please upload a screenshot of your payment proof.',
            'payment_screenshot.image' => 'The payment proof must be an image file.',
            'payment_screenshot.mimes' => 'The payment proof must be a PNG, JPG, or JPEG file.',
            'payment_screenshot.max' => 'The payment proof may not be larger than 5MB.',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function bookingPayload(): array
    {
        $validated = $this->validated();
        $brands = [];

        foreach ($validated['brands'] ?? [] as $serviceId => $brand) {
            $brands[(int) $serviceId] = $brand;
        }

        return [
            'service_ids' => array_map('intval', $validated['service_ids']),
            'brands' => $brands,
            'customer_name' => $validated['customer_name'],
            'contact_number' => $validated['contact_number'],
            'vehicle_make' => $validated['vehicle_make'],
            'vehicle_model' => $validated['vehicle_model'],
            'vehicle_year' => (int) $validated['vehicle_year'],
            'plate_number' => $validated['plate_number'],
            'preferred_date' => $validated['preferred_date'],
            'preferred_time' => $validated['preferred_time'],
            'notes' => $validated['notes'] ?? null,
            'payment_method' => $validated['payment_method'],
            'reference_number' => $validated['reference_number'],
            'payment_screenshot' => $this->file('payment_screenshot'),
        ];
    }
}
