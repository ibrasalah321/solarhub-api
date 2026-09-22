<?php

namespace App\Http\Requests\Rating;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStoreRatingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'rating' => [
                'sometimes',
                'required',
                'integer',
                'between:1,5',
            ],

            'comment' => [
                'sometimes',
                'nullable',
                'string',
                'max:1000',
            ],
        ];
    }
}
