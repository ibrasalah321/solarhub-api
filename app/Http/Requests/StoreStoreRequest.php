<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStoreRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $storeId = $this->route('store') ?? $this->route('id');

        return [
            'user_id' => 'required|exists:users,id|unique:stores,user_id,' . $storeId,
            'company_name' => 'required|string|max:255',
            'commercial_registry' => 'nullable|string|max:100',
            'commercial_file_path' => 'nullable|string|max:255',
            'tax_number' => 'nullable|string|max:100',
            'bio' => 'nullable|string',
            'company_logo_path' => 'nullable|string|max:255',
            'whatsapp_number' => 'nullable|string|max:30',
            'approval_status' => 'nullable|in:pending,approved,rejected',
            'rejection_reason' => 'nullable|string',
            'store_type' => 'required|in:wholesaler,retailer,authorized_agent',
            'address_details' => 'required|string',
            'approved_at' => 'nullable|date',
            'location_coordinates' => 'nullable',
        ];
    }
}