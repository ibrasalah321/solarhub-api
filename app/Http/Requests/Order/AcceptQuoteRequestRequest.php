<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;

/**
 * قبول عرض السعر يُنشئ طلب شراء مباشرة بالسعر المتفق عليه، لذلك يحتاج عنوان التوصيل
 * (نفس حقول إتمام الطلب من السلة).
 */
class AcceptQuoteRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return (new StoreOrderRequest())->rules();
    }

    public function messages(): array
    {
        return [
            'delivery_address.required' => 'عنوان التوصيل مطلوب لإنشاء الطلب.',
        ];
    }
}
