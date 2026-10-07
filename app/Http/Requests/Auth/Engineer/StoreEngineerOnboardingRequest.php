<?php

namespace App\Http\Requests\Auth\Engineer;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreEngineerOnboardingRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if (is_string($this->license_number)) {
            $this->merge([
                'license_number' => trim($this->license_number),
            ]);
        }
    }

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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'license_number' => ['required', 'string', 'max:100'],
            'cv' => ['required', 'file', 'mimes:pdf,doc,docx', 'max:5120'],
        ];
    }
}
