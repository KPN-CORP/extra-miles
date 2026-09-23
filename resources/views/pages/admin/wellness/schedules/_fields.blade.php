{{-- Shared schedule fields. $prefix keeps the element ids unique between the
     create and edit modals on the same page.

     $showRepeat  -- create only: turns one session into a repeating series.
     $showScope   -- edit only: asks whether the edit carries to later occurrences. --}}
@php
    // Only the create form repopulates from old(): the edit modal is filled by
    // JS from whichever row opened it, which would overwrite anything put here.
    $isCreate = $prefix === 'create';
    $was = fn (string $field, $default = null) => $isCreate ? old($field, $default) : null;
    $invalid = fn (string $field) => $isCreate && $errors->has($field) ? ' is-invalid' : '';
@endphp

<div class="row g-3">
    {{-- ------------------------------------------------------- session --}}
    <div class="col-12">
        <div class="text-uppercase text-muted fw-bold small">
            <i class="ri-calendar-event-line me-1"></i>{{ __('Session') }}
        </div>
    </div>

    <div class="col-md-6">
        <label for="{{ $prefix }}-start_at" class="form-label">{{ __('Starts At') }} <span class="text-danger">*</span></label>
        <input type="datetime-local" class="form-control js-session-bound{{ $invalid('start_at') }}"
            id="{{ $prefix }}-start_at" name="start_at" value="{{ $was('start_at') }}" required>
        @if ($isCreate) @error('start_at') <div class="invalid-feedback">{{ $message }}</div> @enderror @endif
    </div>
    <div class="col-md-6">
        <label for="{{ $prefix }}-end_at" class="form-label">{{ __('Ends At') }} <span class="text-danger">*</span></label>
        <input type="datetime-local" class="form-control js-session-bound{{ $invalid('end_at') }}"
            id="{{ $prefix }}-end_at" name="end_at" value="{{ $was('end_at') }}" required>
        @if ($isCreate) @error('end_at') <div class="invalid-feedback">{{ $message }}</div> @enderror @endif
        <div class="form-text js-duration-hint">&nbsp;</div>
    </div>

    <div class="col-md-8">
        <label for="{{ $prefix }}-location" class="form-label">{{ __('Location') }}</label>
        <div class="input-group">
            <span class="input-group-text"><i class="ri-map-pin-line"></i></span>
            <input type="text" class="form-control{{ $invalid('location') }}" id="{{ $prefix }}-location"
                name="location" maxlength="150" value="{{ $was('location') }}"
                placeholder="{{ __('e.g. Gym, 3rd floor') }}">
            @if ($isCreate) @error('location') <div class="invalid-feedback">{{ $message }}</div> @enderror @endif
        </div>
    </div>
    <div class="col-md-4">
        <label for="{{ $prefix }}-quota" class="form-label">{{ __('Quota') }}</label>
        <div class="input-group">
            <input type="number" class="form-control{{ $invalid('quota') }}" id="{{ $prefix }}-quota"
                name="quota" min="1" value="{{ $was('quota') }}" placeholder="{{ __('Unlimited') }}">
            <span class="input-group-text">{{ __('seats') }}</span>
            @if ($isCreate) @error('quota') <div class="invalid-feedback">{{ $message }}</div> @enderror @endif
        </div>
        <div class="form-text">{{ __('Empty = unlimited.') }}</div>
    </div>

    {{-- -------------------------------------------------- registration --}}
    <div class="col-12">
        <hr class="mt-2 mb-0">
    </div>
    <div class="col-12">
        <div class="text-uppercase text-muted fw-bold small">
            <i class="ri-user-add-line me-1"></i>{{ __('Registration') }}
        </div>
    </div>

    <div class="col-md-4">
        <label for="{{ $prefix }}-registration_start_at" class="form-label">{{ __('Opens') }}</label>
        <input type="datetime-local" class="form-control{{ $invalid('registration_start_at') }}"
            id="{{ $prefix }}-registration_start_at" name="registration_start_at"
            value="{{ $was('registration_start_at') }}">
        @if ($isCreate) @error('registration_start_at') <div class="invalid-feedback">{{ $message }}</div> @enderror @endif
        <div class="form-text">{{ __('Empty = immediately.') }}</div>
    </div>
    <div class="col-md-4">
        <label for="{{ $prefix }}-registration_end_at" class="form-label">{{ __('Closes') }}</label>
        <input type="datetime-local" class="form-control{{ $invalid('registration_end_at') }}"
            id="{{ $prefix }}-registration_end_at" name="registration_end_at"
            value="{{ $was('registration_end_at') }}">
        @if ($isCreate) @error('registration_end_at') <div class="invalid-feedback">{{ $message }}</div> @enderror @endif
        <div class="form-text">{{ __('Empty = when the session ends.') }}</div>
    </div>
    <div class="col-md-4">
        <label for="{{ $prefix }}-status" class="form-label">{{ __('Status') }} <span class="text-danger">*</span></label>
        <select class="form-select{{ $invalid('status') }}" id="{{ $prefix }}-status" name="status" required>
            @foreach ($statuses as $value => $label)
                <option value="{{ $value }}" @selected($was('status', 'open') === $value)>{{ __($label) }}</option>
            @endforeach
        </select>
        @if ($isCreate) @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror @endif
        <div class="form-text">{!! __('Only :status accepts sign-ups.', ['status' => '<strong>'.__('Open').'</strong>']) !!}</div>
    </div>

    {{-- --------------------------------------------------------- repeat --}}
    @if ($showRepeat ?? false)
        <div class="col-12">
            <hr class="mt-2 mb-0">
        </div>
        <div class="col-12">
            <div class="text-uppercase text-muted fw-bold small">
                <i class="ri-repeat-line me-1"></i>{{ __('Repeat') }}
                <span class="badge bg-secondary-subtle text-secondary fw-normal ms-1">{{ __('optional') }}</span>
            </div>
        </div>

        <div class="col-md-4">
            <label for="{{ $prefix }}-repeat_frequency" class="form-label">{{ __('Frequency') }}</label>
            <select class="form-select{{ $invalid('repeat_frequency') }}"
                id="{{ $prefix }}-repeat_frequency" name="repeat_frequency">
                <option value="">{{ __('Does not repeat') }}</option>
                @foreach ($frequencies as $value => $label)
                    <option value="{{ $value }}" @selected($was('repeat_frequency') === $value)>{{ __($label) }}</option>
                @endforeach
            </select>
            @if ($isCreate) @error('repeat_frequency') <div class="invalid-feedback">{{ $message }}</div> @enderror @endif
        </div>
        <div class="col-md-4">
            <label for="{{ $prefix }}-repeat_until" class="form-label">{{ __('Repeat Until') }}</label>
            <input type="date" class="form-control{{ $invalid('repeat_until') }}"
                id="{{ $prefix }}-repeat_until" name="repeat_until" value="{{ $was('repeat_until') }}" disabled>
            @if ($isCreate) @error('repeat_until') <div class="invalid-feedback">{{ $message }}</div> @enderror @endif
        </div>
        <div class="col-md-4 d-flex align-items-end">
            {{-- Says up front how many rows Save is about to write, so a mistyped
                 year is caught before it becomes 260 sessions. --}}
            <div class="alert alert-secondary py-2 px-3 mb-0 w-100 small js-repeat-summary" role="status">
                {{ __('One session only.') }}
            </div>
        </div>
    @endif

    {{-- ---------------------------------------------------------- scope --}}
    @if ($showScope ?? false)
        <div class="col-12 d-none" id="{{ $prefix }}-scope-wrapper">
            <hr class="mt-2 mb-3">
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
</div>
