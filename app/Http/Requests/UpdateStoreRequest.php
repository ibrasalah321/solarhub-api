<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $storeId = $this->route('store') ?? $this->route('id');

        return [
            'company_name' => 'sometimes|required|string|max:255',
            'commercial_registry' => 'nullable|string|max:100',
            'commercial_file_path' => 'nullable|string|max:255',
            'tax_number' => 'nullable|string|max:100',
            'bio' => 'nullable|string',
            'company_logo_path' => 'sometimes|required|string|max:255',
            'whatsapp_number' => 'nullable|string|max:30',
            'store_type' => 'sometimes|required|in:wholesaler,retailer,authorized_agent',
            'address_details' => 'sometimes|required|string',
            'location_coordinates' => 'nullable',
        ];
    }
}