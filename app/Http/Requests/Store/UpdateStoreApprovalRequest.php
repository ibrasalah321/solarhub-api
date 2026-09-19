<?php

namespace App\Http\Requests\Store;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStoreApprovalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'approval_status' => [
                'required',
                'string',
                'in:approved,rejected',
            ],

            'rejection_reason' => [
                'required_if:approval_status,rejected',
                'nullable',
                'string',
                'max:255',
            ],
        ];
    }
}
