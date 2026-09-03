<?php

namespace App\Http\Requests;

use App\Enums\WellnessActivityStatus;
use App\Enums\WellnessRegistrationMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WellnessActivityRequest extends FormRequest
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
            'wellness_activity_type_id' => ['required', 'integer', Rule::exists('wellness_activity_types', 'id')->whereNull('deleted_at')],
            'registration_method' => ['required', Rule::enum(WellnessRegistrationMethod::class)],
            'name' => ['required', 'string', 'max:150'],
            // Rich text (CKEditor) -- the markup counts toward the length.
            'description' => ['nullable', 'string', 'max:20000'],
            'status' => ['required', Rule::enum(WellnessActivityStatus::class)],
            'image' => ['nullable', 'image', 'max:2048'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'wellness_activity_type_id' => 'activity type',
            'registration_method' => 'registration method',
        ];
    }
}
