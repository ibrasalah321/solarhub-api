<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateAdminUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => is_string($this->name) ? trim($this->name) : $this->name,
            'email' => is_string($this->email) ? mb_strtolower(trim($this->email)) : $this->email,
            'phone' => is_string($this->phone) ? trim($this->phone) : $this->phone,
            'status' => $this->status ?: 'active',
            'governorate_id' => $this->governorate_id ? (int) $this->governorate_id : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:150',
            ],
            'email' => [
                'required',
                'string',
                'email:rfc',
                'max:150',
                'unique:users,email',
            ],
            'phone' => [
                'required',
                'string',
                'max:30',
                'unique:users,phone',
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],
            'password_confirmation' => [
                'required_with:password',
            ],
            'governorate_id' => [
                'nullable',
                'integer',
                'exists:governorates,id',
            ],
            'status' => [
                'nullable',
                'string',
                Rule::in(['active', 'inactive', 'suspended']),
            ],
            'default_coordinates' => [
                'nullable',
                'string',
                'max:255',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'حقل الاسم الكامل مطلوب.',
            'name.max' => 'يجب ألا يتجاوز الاسم 150 حرفاً.',
            'email.required' => 'حقل البريد الإلكتروني مطلوب.',
            'email.email' => 'البريد الإلكتروني المدخل غير صالح.',
            'email.unique' => 'البريد الإلكتروني مستخدم بالفعل في النظام.',
            'phone.required' => 'حقل رقم الهاتف مطلوب.',
            'phone.unique' => 'رقم الهاتف مستخدم بالفعل في النظام.',
            'password.required' => 'حقل كلمة المرور مطلوب.',
            'password.min' => 'يجب ألا تقل كلمة المرور عن 8 أحرف.',
            'password.confirmed' => 'تأكيد كلمة المرور غير متطابق.',
            'governorate_id.exists' => 'المحافظة المختارة غير صالحة.',
            'status.in' => 'حالة الحساب المحددة غير صحيحة.',
        ];
    }
}
