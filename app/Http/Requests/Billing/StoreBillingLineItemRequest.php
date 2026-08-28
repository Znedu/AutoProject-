<?php

namespace App\Http\Requests\Billing;

use App\Models\QuotationLineItem;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class StoreBillingLineItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        $booking = $this->route('booking');

        return $booking && Gate::allows('billing.manage', $booking);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'item_type' => [
                'required',
                'string',
                Rule::in([
                    QuotationLineItem::ITEM_TYPE_SERVICE,
                    QuotationLineItem::ITEM_TYPE_PRODUCT,
                    QuotationLineItem::ITEM_TYPE_MATERIAL,
                    QuotationLineItem::ITEM_TYPE_ADDITIONAL_SERVICE,
                    QuotationLineItem::ITEM_TYPE_LABOR,
                    QuotationLineItem::ITEM_TYPE_DISCOUNT,
                    QuotationLineItem::ITEM_TYPE_FEE,
                ]),
            ],
            'description' => ['required', 'string', 'max:255'],
            'quantity' => ['required', 'numeric', 'min:0.01'],
            'unit_final' => ['nullable', 'numeric', 'min:0'],
            'unit_min' => ['nullable', 'numeric', 'min:0'],
            'unit_max' => ['nullable', 'numeric', 'min:0'],
            'product_id' => ['nullable', 'integer', 'exists:products,id'],
            'service_id' => ['nullable', 'integer', 'exists:services,id'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
