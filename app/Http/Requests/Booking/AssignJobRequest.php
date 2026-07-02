<?php

namespace App\Http\Requests\Booking;

use App\Enums\RoleSlug;
use App\Models\JobOrder;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignJobRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('approvals.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'mechanic_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->whereNull('deleted_at'),
                function (string $attribute, mixed $value, \Closure $fail) {
                    $user = User::find($value);
                    if ($user && ! $user->isMechanic()) {
                        $fail('The selected user is not a mechanic.');
                    }
                    if ($user && ! $user->isActive()) {
                        $fail('The selected mechanic is not active.');
                    }
                },
            ],
            'priority' => [
                'required',
                Rule::in([JobOrder::PRIORITY_LOW, JobOrder::PRIORITY_MEDIUM, JobOrder::PRIORITY_HIGH]),
            ],
            'estimated_completion_date' => ['nullable', 'date', 'after_or_equal:today'],
            'internal_notes'            => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'mechanic_id.required' => 'Please select a mechanic to assign.',
            'mechanic_id.exists'   => 'The selected mechanic does not exist.',
            'priority.required'    => 'Please set a priority level.',
            'priority.in'          => 'Priority must be low, medium, or high.',
            'estimated_completion_date.after_or_equal' => 'The estimated completion date must not be in the past.',
        ];
    }
}
