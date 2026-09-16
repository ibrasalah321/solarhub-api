<?php

namespace App\Http\Requests\Engineer;

use Illuminate\Foundation\Http\FormRequest;

class StoreEngineerCertificateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'certificate' => [
                'required',
                'file',
                'mimes:pdf,jpg,jpeg,png',
                'max:5120',
            ],
        ];
    }
}