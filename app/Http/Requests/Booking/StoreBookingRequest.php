<?php

namespace App\Http\Requests\Booking;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\Service;
use App\Rules\ScheduleAvailable;
use App\Services\Booking\ScheduleAvailabilityService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Booking::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $serviceIds = (array) $this->input('service_ids', []);
        $selectedServices = Service::query()->whereIn('id', $serviceIds)->get();

        $hasPaintTouchUp = $selectedServices->contains(fn (Service $s) => $s->isPaintTouchUp());
        $hasCustomPaint = $selectedServices->contains(fn (Service $s) => $s->isCustomPaint());
        $hasBodyRepair = $selectedServices->contains(fn (Service $s) => $s->isBodyRepair());
        $hasPaintRelated = $selectedServices->contains(fn (Service $s) => $s->isPaintRelated());

        $rules = [
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

            // Paint scope, panel checklist, and damage photos
            'paint_scope' => ['nullable', 'string', Rule::in(['touch_up', 'custom_paint', 'body_repair', 'full_build'])],
            'vehicle_size_class' => ['nullable', 'string', 'max:50'],
            'panels' => ['nullable', 'array'],
            'panels.*' => ['string', 'exists:paint_panels,code'],
            'panel_severity' => ['nullable', 'array'],
            'panel_severity.*' => ['nullable', 'string', Rule::in([
                'light_scratch', 'paint_only', 'dent', 'body_work', 'replace_panel',
            ])],
            'damage_photos' => ['nullable', 'array'],
            'damage_photos.*' => ['file', 'image', 'mimes:png,jpg,jpeg', 'max:5120'],
        ];

        // Conditional validation for paint services
        if ($hasPaintRelated) {
            $rules['damage_photos'] = ['required', 'array', 'min:1', 'max:6'];
            $rules['damage_photos.*'] = ['required', 'file', 'image', 'mimes:png,jpg,jpeg', 'max:5120'];
        }

        if ($hasPaintTouchUp) {
            $rules['panels'] = ['required', 'array', 'min:1', 'max:2'];
        } elseif ($hasCustomPaint || $hasBodyRepair) {
            $rules['panels'] = ['required', 'array', 'min:1'];
        }

        return $rules;
    }

    /**
     * Extra validation hooks:
     * - Prevent conflicting primary paint services (touch up XOR custom paint XOR package)
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $serviceIds = (array) $this->input('service_ids', []);
            if (empty($serviceIds)) {
                return;
            }

            $selectedServices = Service::query()->whereIn('id', $serviceIds)->get();
            $primaryPaintCount = $selectedServices->filter(fn (Service $s) => $s->isPrimaryPaint())->count();

            if ($primaryPaintCount > 1) {
                $validator->errors()->add(
                    'service_ids',
                    'Please select only one primary paint service (Paint touch up, Custom paint job, or Full custom build package).'
                );
            }
        });
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'service_ids.required' => 'Please select at least one service.',
            'agreed_to_terms.accepted' => 'You must agree to the terms and conditions.',
            'payment_screenshot.required' => 'Please upload a screenshot of your proof of payment.',
            'payment_screenshot.image' => 'The proof of payment must be an image file.',
            'payment_screenshot.mimes' => 'The proof of payment must be a PNG, JPG, or JPEG file.',
            'payment_screenshot.max' => 'The proof of payment may not be larger than 5MB.',
            'damage_photos.required' => 'Please upload at least 1 photo of the vehicle / panels for estimating.',
            'damage_photos.min' => 'Please upload at least 1 photo for paint / body services.',
            'damage_photos.max' => 'You may upload up to 6 photos only.',
            'damage_photos.*.image' => 'Each uploaded photo must be a valid image file.',
            'damage_photos.*.mimes' => 'Damage photos must be PNG, JPG, or JPEG format.',
            'damage_photos.*.max' => 'Each damage photo may not be larger than 5MB.',
            'panels.required' => 'Please select the vehicle panels requiring paint or body repair.',
            'panels.min' => 'Please select at least one panel.',
            'panels.max' => 'Paint touch up is limited to a maximum of 2 panels.',
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

        $damagePhotos = $this->file('damage_photos');
        if ($damagePhotos && ! is_array($damagePhotos)) {
            $damagePhotos = [$damagePhotos];
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
            'paint_scope' => $validated['paint_scope'] ?? null,
            'vehicle_size_class' => $validated['vehicle_size_class'] ?? null,
            'panels' => $validated['panels'] ?? [],
            'panel_severity' => $validated['panel_severity'] ?? [],
            'damage_photos' => $damagePhotos ?? [],
        ];
    }
}
