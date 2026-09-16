<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStoreRatingRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $ratingId = $this->route('store_rating') ?? $this->route('id');

        return [
            'order_store_id' => 'required|exists:order_stores,id|unique:store_ratings,order_store_id,' . $ratingId,
            'customer_id' => 'required|exists:users,id',
            'store_id' => 'required|exists:stores,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string',
            'is_approved' => 'boolean',
        ];
    }
}