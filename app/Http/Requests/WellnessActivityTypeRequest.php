<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Validation\Rule;

class WellnessActivityTypeRequest extends FormRequest
{
    /**
     * Access is already gated by the `permission:viewmenuwellness` route middleware.
     */
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
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('wellness_activity_types', 'name')
                    ->ignore($this->route('encryptedId') ? $this->resolvedId() : null)
                    ->whereNull('deleted_at'),
            ],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
            // Empty means "use the config default", so the column stays null.
            'check_in_opens_minutes_before' => ['nullable', 'integer', 'min:0', 'max:1440'],
            'check_in_closes_minutes_after' => ['nullable', 'integer', 'min:0', 'max:1440'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    /**
     * The route carries an encrypted id; the unique rule needs the real one.
     */
    protected function resolvedId(): ?int
    {
        try {
            return (int) Crypt::decryptString($this->route('encryptedId'));
        } catch (\Throwable) {
            return null;
        }
    }
}
