<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWellnessRegistrationRequest extends FormRequest
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
            // Employees live in the `kpncorp` connection, so the rule has to
            // name it explicitly -- the default connection has no such table.
            'employee_id' => ['required', 'string', 'max:50', Rule::exists('kpncorp.employees', 'employee_id')],
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
            'employee_id.exists' => 'That employee ID was not found in the HR database.',
        ];
    }
}
