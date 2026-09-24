<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WellnessBlacklistRequest extends FormRequest
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
            'reason' => ['required', 'string', 'max:500'],
            // Optional: leaving it empty means the blacklist does not expire.
            'end_date' => ['nullable', 'date', 'after_or_equal:today'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'employee_id.exists' => __('That employee ID was not found in the HR database.'),
            'end_date.after_or_equal' => __('The blacklist end date cannot be in the past.'),
        ];
    }
}
