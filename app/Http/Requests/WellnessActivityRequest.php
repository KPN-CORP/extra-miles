<?php

namespace App\Http\Requests;

use App\Enums\WellnessActivityStatus;
use App\Enums\WellnessRegistrationMethod;
use App\Enums\WellnessScheduleStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WellnessActivityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * The create screen renders a blank session row ready to fill in, and lets
     * admins add more. Rows left completely untouched are dropped here so that
     * "sessions are optional" holds -- otherwise an ignored blank row would fail
     * the required rules below. `status` is ignored when deciding emptiness
     * because its select always has a value, and rows that carry an id are kept
     * regardless because dropping one would read as a deletion.
     */
    protected function prepareForValidation(): void
    {
        if (! is_array($this->input('schedules'))) {
            return;
        }

        $filled = array_filter($this->input('schedules'), function ($row) {
            if (! is_array($row)) {
                return false;
            }

            // A row carrying an id is an existing session. Keep it even if the
            // admin blanked its fields, so validation reports the empty dates
            // instead of the row vanishing -- and being read as a deletion.
            if (($row['id'] ?? '') !== '') {
                return true;
            }

            unset($row['status']);

            return array_filter($row, fn ($value) => $value !== null && $value !== '') !== [];
        });

        // Re-key so the indices stay contiguous for the error bag and old().
        $this->merge(['schedules' => array_values($filled)]);
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

            // Optional sessions submitted alongside the activity by the create
            // screen's Schedules tab. Absent on update, where `sometimes` skips
            // the whole block. Rules mirror WellnessActivityScheduleRequest --
            // Laravel substitutes the concrete index into the `*` references.
            'schedules' => ['sometimes', 'array', 'max:50'],
            // Present on rows that already exist (edit screen), absent on new
            // ones. The controller decrypts it and checks it belongs to this
            // activity before trusting it.
            'schedules.*.id' => ['nullable', 'string'],
            'schedules.*.start_at' => ['required', 'date'],
            'schedules.*.end_at' => ['required', 'date', 'after:schedules.*.start_at'],
            'schedules.*.location' => ['nullable', 'string', 'max:150'],
            'schedules.*.quota' => ['nullable', 'integer', 'min:1', 'max:100000'],
            'schedules.*.registration_start_at' => ['nullable', 'date'],
            'schedules.*.registration_end_at' => [
                'nullable', 'date',
                'after_or_equal:schedules.*.registration_start_at',
                'before_or_equal:schedules.*.end_at',
            ],
            'schedules.*.confirmation_deadline' => [
                'nullable',
                'date',
                'after_or_equal:schedules.*.registration_start_at',
                'before_or_equal:schedules.*.start_at',
            ],
            'schedules.*.status' => ['required', Rule::enum(WellnessScheduleStatus::class)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'schedules.*.end_at.after' => __('Session :position: the end time must be later than the start time.'),
            // Spelled out because :position is not substituted inside the name of
            // a *referenced* field, which would leak the raw placeholder.
            'schedules.*.registration_end_at.after_or_equal' => __('Session :position: registration must close no earlier than it opens.'),
            'schedules.*.registration_end_at.before_or_equal' => __('Session :position: registration must close no later than the session ends.'),
            'schedules.*.quota.min' => __('Session :position: leave the quota empty for unlimited seats, or set it to at least 1.'),
            'schedules.*.confirmation_deadline.before_or_equal' => __('Session :position: the confirmation deadline must fall before the session starts.'),
            'schedules.*.confirmation_deadline.after_or_equal' => __('Session :position: the confirmation deadline cannot be before registration opens.'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'wellness_activity_type_id' => __('activity type'),
            'registration_method' => __('registration method'),
            // Without these the messages would read "schedules.0.start_at".
            'schedules.*.start_at' => __('session :position start time'),
            'schedules.*.end_at' => __('session :position end time'),
            'schedules.*.location' => __('session :position location'),
            'schedules.*.quota' => __('session :position quota'),
            'schedules.*.registration_start_at' => __('session :position registration opening time'),
            'schedules.*.registration_end_at' => __('session :position registration closing time'),
            'schedules.*.confirmation_deadline' => __('session :position confirmation deadline'),
            'schedules.*.status' => __('session :position status'),
        ];
    }
}
