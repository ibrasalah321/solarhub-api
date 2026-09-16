<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductImageRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'product_id' => 'required|exists:master_products,id',
            'image_path' => 'required|string|max:255',
            'is_featured' => 'boolean',
        ];
    }
}