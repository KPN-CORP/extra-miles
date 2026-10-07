<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImportWellnessRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv,txt', 'max:2048'],
            'remark' => ['nullable', 'string', 'max:500'],
            'allow_over_quota' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'allow_over_quota' => $this->boolean('allow_over_quota'),
        ]);
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'file.mimes' => __('The import file must be an Excel (.xlsx, .xls) or CSV file.'),
        ];
    }
}
