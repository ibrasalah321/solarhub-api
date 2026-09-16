<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCartRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // استخراج معرف السلة في حال كانت العملية تحديث (Update) لتفادي خطأ التحقق من الفرادة
        $cartId = $this->route('cart') ?? $this->route('id');

        return [
            'user_id' => 'required|exists:users,id|unique:carts,user_id,' . $cartId,
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'user_id' => 'المستخدم',
        ];
    }
}