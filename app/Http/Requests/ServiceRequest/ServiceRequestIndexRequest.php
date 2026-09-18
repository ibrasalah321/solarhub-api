<?php

namespace App\Http\Requests\ServiceRequest;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ServiceRequestIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => [
                'sometimes',
                'nullable',
                Rule::in([
                    'open_for_bids',
                    'awarded',
                    'in_progress',
                    'completed',
                    'cancelled',
                ]),
            ],

            'service_type_id' => [
                'sometimes',
                'nullable',
                'integer',
                Rule::exists('service_types', 'id')
                    ->where('is_active', true),
            ],

            'governorate_id' => [
                'sometimes',
                'nullable',
                'integer',
                'exists:governorates,id',
            ],

            'per_page' => [
                'sometimes',
                'integer',
                'min:1',
                'max:50',
            ],
        ];
    }
}