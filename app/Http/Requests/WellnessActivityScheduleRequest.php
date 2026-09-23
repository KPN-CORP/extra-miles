<?php

namespace App\Http\Requests;

use App\Enums\WellnessRecurrenceFrequency;
use App\Enums\WellnessScheduleStatus;
use App\Enums\WellnessScheduleUpdateScope;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
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
        $rules = [
            'start_at' => ['required', 'date'],
            'end_at' => ['required', 'date', 'after:start_at'],
            'location' => ['nullable', 'string', 'max:150'],
            'quota' => ['nullable', 'integer', 'min:1', 'max:100000'],
            'registration_start_at' => ['nullable', 'date'],
            'registration_end_at' => ['nullable', 'date', 'after_or_equal:registration_start_at', 'before_or_equal:end_at'],
            'status' => ['required', Rule::enum(WellnessScheduleStatus::class)],
        ];

        if ($this->isMethod('POST')) {
            // Repeating only ever applies when the series is first laid down;
            // an existing occurrence is edited, not re-generated.
            $rules['repeat_frequency'] = ['nullable', Rule::enum(WellnessRecurrenceFrequency::class)];
            $rules['repeat_until'] = [
                Rule::requiredIf(fn () => filled($this->input('repeat_frequency'))),
                'nullable',
                'date',
                'after_or_equal:start_at',
            ];
        } else {
            $rules['update_scope'] = ['nullable', Rule::enum(WellnessScheduleUpdateScope::class)];
        }

        return $rules;
    }

    /**
     * The schedule columns only -- the repeat and scope inputs steer the
     * controller and must never reach the model.
     *
     * @return array<string, mixed>
     */
    public function scheduleAttributes(): array
    {
        return collect($this->validated())
            ->except(['repeat_frequency', 'repeat_until', 'update_scope'])
            ->all();
    }

    public function repeatFrequency(): ?WellnessRecurrenceFrequency
    {
        return WellnessRecurrenceFrequency::tryFrom((string) $this->validated('repeat_frequency'));
    }

    public function repeatUntil(): ?Carbon
    {
        $until = $this->validated('repeat_until');

        return filled($until) ? Carbon::parse($until) : null;
    }

    public function updateScope(): WellnessScheduleUpdateScope
    {
        return WellnessScheduleUpdateScope::tryFrom((string) $this->validated('update_scope'))
            ?? WellnessScheduleUpdateScope::This;
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
            'repeat_until.required' => 'Choose the date the repetition should run until.',
            'repeat_until.after_or_equal' => 'The repeat-until date cannot be before the first session.',
        ];
    }
}
