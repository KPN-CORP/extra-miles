{{-- The schedule modal's body. The session fields themselves come from
     _session-fields, which the activity form uses too -- only the two things
     that belong to this modal alone live here, panelled to match it.

     $prefix      keeps element ids unique between the create and edit modals.
     $showRepeat  create only: turns one session into a repeating series.
     $showScope   edit only: asks whether the edit carries to later occurrences. --}}
@php
    // Only the create form repopulates from old(): the edit modal is filled by
    // JS from whichever row opened it, which would overwrite anything put here.
    $isCreate = $prefix === 'create';
@endphp

@include('pages.admin.wellness.schedules._session-fields', [
    'nameFor' => fn (string $field) => $field,
    'idFor' => fn (string $field) => $prefix.'-'.$field,
    'valueFor' => fn (string $field) => $isCreate ? old($field) : null,
    'errorFor' => fn (string $field) => $isCreate ? $errors->first($field) : null,
    'statuses' => $statuses,
])

{{-- --------------------------------------------------------------- repeat --}}
@if ($showRepeat ?? false)
    <div class="border rounded-3 p-3 mt-3">
        <div class="text-uppercase text-muted fw-bold small mb-3">
            <i class="ri-repeat-line me-1"></i>{{ __('Repeat') }}
            <span class="badge bg-secondary-subtle text-secondary fw-normal ms-1">{{ __('optional') }}</span>
        </div>

        <div class="row g-3">
            <div class="col-md-4">
                <label for="{{ $prefix }}-repeat_frequency" class="form-label">{{ __('Frequency') }}</label>
                <select class="form-select @error('repeat_frequency') is-invalid @enderror"
                    id="{{ $prefix }}-repeat_frequency" name="repeat_frequency">
                    <option value="">{{ __('Does not repeat') }}</option>
                    @foreach ($frequencies as $value => $label)
                        <option value="{{ $value }}" @selected(old('repeat_frequency') === $value)>{{ __($label) }}</option>
                    @endforeach
                </select>
                @error('repeat_frequency') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-4">
                <label for="{{ $prefix }}-repeat_until" class="form-label">{{ __('Repeat Until') }}</label>
                <input type="date" class="form-control @error('repeat_until') is-invalid @enderror"
                    id="{{ $prefix }}-repeat_until" name="repeat_until" value="{{ old('repeat_until') }}" disabled>
                @error('repeat_until') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-4 d-flex align-items-end">
                {{-- Says up front how many rows Save is about to write, so a
                     mistyped year is caught before it becomes 260 sessions. --}}
                <div class="alert alert-secondary py-2 px-3 mb-0 w-100 small js-repeat-summary" role="status">
                    {{ __('One session only.') }}
                </div>
            </div>
        </div>
    </div>
@endif

{{-- ---------------------------------------------------------------- scope --}}
@if ($showScope ?? false)
    <div class="d-none mt-3" id="{{ $prefix }}-scope-wrapper">
        <div class="alert alert-warning mb-0">
            <div class="text-uppercase fw-bold small mb-2">
                <i class="ri-repeat-line me-1"></i>{{ __('This is a repeating session') }}
            </div>
            <div class="form-check py-1">
                <input class="form-check-input" type="radio" name="update_scope" value="this"
                    id="{{ $prefix }}-scope-this" checked>
                <label class="form-check-label" for="{{ $prefix }}-scope-this">
                    {{ __('This schedule only') }}
                </label>
            </div>
            <div class="form-check py-1">
                <input class="form-check-input" type="radio" name="update_scope" value="following"
                    id="{{ $prefix }}-scope-following">
                <label class="form-check-label" for="{{ $prefix }}-scope-following">
                    {{ __('This and the following schedules') }}
                    <span class="fw-bold js-scope-count"></span>
                </label>
            </div>
            <div class="small mb-0 mt-2 pt-2 border-top border-warning-subtle opacity-75">
                {{ __('Later occurrences keep their own date; the time, location, quota, status and registration window follow this one.') }}
            </div>
        </div>
    </div>
@endif
