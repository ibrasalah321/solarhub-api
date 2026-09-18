<?php

namespace App\Http\Requests\Catalog;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'parent_id' => [
                'sometimes',
                'nullable',
                'integer',
                'exists:categories,id',
            ],

            'name_ar' => [
                'sometimes',
                'required',
                'string',
                'max:255',
            ],

            'name_en' => [
                'sometimes',
                'required',
                'string',
                'max:255',
            ],

            'slug' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                'alpha_dash',
                Rule::unique('categories', 'slug')->ignore($this->route('category')),
            ],

            'icon' => [
                'sometimes',
                'nullable',
                'file',
                'image',
                'max:1024',
            ],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $category = $this->route('category');

            if ($category && (int) $this->input('parent_id') === $category->id) {
                $validator->errors()->add('parent_id', 'A category cannot be its own parent.');
            }
        });
    }
}
