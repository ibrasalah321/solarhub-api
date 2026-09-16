<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $categoryId = $this->route('category') ?? $this->route('id');

        return [
            'parent_id' => 'nullable|exists:categories,id',
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:categories,slug,' . $categoryId,
            'icon' => 'nullable|string|max:255',
        ];
    }
}