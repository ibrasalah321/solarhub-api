<?php

namespace App\Http\Requests\Favorite;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFavoriteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'store_product_id' => [
                'required',
                'integer',
                'exists:store_products,id',
                Rule::unique('favorites')->where(function ($query) {
                    return $query->where('user_id', $this->user()->id);
                }),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'store_product_id.unique' => 'المنتج مضاف مسبقاً إلى قائمة المفضلة.',
            'store_product_id.exists' => 'المنتج المحدد غير متوفر.',
        ];
    }
}