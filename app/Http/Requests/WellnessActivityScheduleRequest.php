<?php

namespace App\Http\Requests;

use App\Enums\WellnessScheduleStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WellnessActivityScheduleRequest extends FormRequest
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
            'start_at' => ['required', 'date'],
            'end_at' => ['required', 'date', 'after:start_at'],
            'location' => ['nullable', 'string', 'max:150'],
            'quota' => ['nullable', 'integer', 'min:1', 'max:100000'],
            'registration_start_at' => ['nullable', 'date'],
            'registration_end_at' => ['nullable', 'date', 'after_or_equal:registration_start_at', 'before_or_equal:end_at'],
            'status' => ['required', Rule::enum(WellnessScheduleStatus::class)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'end_at.after' => 'The end time must be later than the start time.',
            'registration_end_at.before_or_equal' => 'Registration must close no later than the session ends.',
            'quota.min' => 'Leave the quota empty for unlimited seats, or set it to at least 1.',
        ];
    }
}
