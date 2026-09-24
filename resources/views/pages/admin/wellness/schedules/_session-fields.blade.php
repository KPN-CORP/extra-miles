{{-- The fields that describe one session. Shared verbatim by the schedule modal
     on the Sessions page and by the repeatable rows on the activity form, so the
     two can never drift apart again.

     The two surfaces name and address their inputs differently -- the modal posts
     `start_at`, the activity form posts `schedules[3][start_at]` -- so the caller
     supplies closures rather than the partial guessing:

       $nameFor   fn(string $field): string   the name attribute
       $idFor     fn(string $field): string   the element id, unique on the page
       $valueFor  fn(string $field): ?string  the current value, null for a blank row
       $errorFor  fn(string $field): ?string  the validation message, or null
       $statuses  array<string, string>       schedule status options
       $quotaMin  int                         seats already held, where the row is locked
       $wide      bool                        true on the full-width activity form

     Every control also carries a js-* class. The modal's script addresses fields
     by id; the activity form's script works inside a row and addresses them by
     class. Emitting both keeps each script working without either knowing about
     the other. --}}
@php
    $quotaMin = $quotaMin ?? 1;
    $wide = $wide ?? false;

    // The two sections sit side by side as soon as there is room for two usable
    // columns. How much room there is depends on the surface, which Bootstrap's
    // viewport breakpoints cannot see: a modal is 800px wide whatever the screen
    // is. So the caller says, and only these two classes change.
    //
    // Inside a section, a pair of fields goes 2-up only on the activity form --
    // halving the modal's 376px column again leaves a datetime input too narrow
    // to read its own placeholder.
    $sectionCol = $wide ? 'col-xl-6' : 'col-lg-6';
    $pairCol = $wide ? 'col-md-6' : 'col-12';
    // Location and quota share a line only when wide. In a modal column the
    // quota's "seats" suffix has nowhere to go and the input group wraps.
    $locationCol = $wide ? 'col-md-8' : 'col-12';
    $quotaCol = $wide ? 'col-md-4' : 'col-12';

    $cls = fn (string $field, string $js, string $base = 'form-control') => trim(
        $base.' '.$js.($errorFor($field) ? ' is-invalid' : '')
    );
@endphp

<div class="row g-3">
    {{-- ------------------------------------------------------- session --}}
    <div class="{{ $sectionCol }}">
        <div class="border rounded-3 p-3 h-100">
            <div class="text-uppercase text-muted fw-bold small mb-3">
                <i class="ri-calendar-event-line me-1"></i>{{ __('Session') }}
            </div>

            <div class="row g-3">
                <div class="{{ $pairCol }}">
                    <label for="{{ $idFor('start_at') }}" class="form-label">
                        {{ __('Starts At') }} <span class="text-danger">*</span>
                    </label>
                    <input type="datetime-local" class="{{ $cls('start_at', 'js-start-at') }}"
                        id="{{ $idFor('start_at') }}" name="{{ $nameFor('start_at') }}"
                        value="{{ $valueFor('start_at') }}">
                    @if ($message = $errorFor('start_at'))
                        <div class="invalid-feedback">{{ $message }}</div>
                    @endif
                </div>

                <div class="{{ $pairCol }}">
                    <label for="{{ $idFor('end_at') }}" class="form-label">
                        {{ __('Ends At') }} <span class="text-danger">*</span>
                    </label>
                    <input type="datetime-local" class="{{ $cls('end_at', 'js-end-at') }}"
                        id="{{ $idFor('end_at') }}" name="{{ $nameFor('end_at') }}"
                        value="{{ $valueFor('end_at') }}">
                    @if ($message = $errorFor('end_at'))
                        <div class="invalid-feedback">{{ $message }}</div>
                    @endif
                    {{-- Filled in by script; the nbsp holds the line so the row
                         does not jump the first time a duration appears. --}}
                    <div class="form-text js-duration-hint">&nbsp;</div>
                </div>

                <div class="{{ $locationCol }}">
                    <label for="{{ $idFor('location') }}" class="form-label">{{ __('Location') }}</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="ri-map-pin-line"></i></span>
                        <input type="text" class="{{ $cls('location', 'js-location') }}"
                            id="{{ $idFor('location') }}" name="{{ $nameFor('location') }}"
                            maxlength="150" placeholder="{{ __('e.g. Studio A') }}"
                            value="{{ $valueFor('location') }}">
                        @if ($message = $errorFor('location'))
                            <div class="invalid-feedback">{{ $message }}</div>
                        @endif
                    </div>
                </div>

                <div class="{{ $quotaCol }}">
                    <label for="{{ $idFor('quota') }}" class="form-label">{{ __('Quota') }}</label>
                    <div class="input-group">
                        <input type="number" class="{{ $cls('quota', 'js-quota') }}"
                            id="{{ $idFor('quota') }}" name="{{ $nameFor('quota') }}"
                            min="{{ $quotaMin }}" placeholder="{{ __('Unlimited') }}"
                            value="{{ $valueFor('quota') }}">
                        <span class="input-group-text">{{ __('seats') }}</span>
                        @if ($message = $errorFor('quota'))
                            <div class="invalid-feedback">{{ $message }}</div>
                        @endif
                    </div>
                    <div class="form-text">
                        @if ($quotaMin > 1)
                            {{ __('At least :count, already held.', ['count' => $quotaMin]) }}
                        @else
                            {{ __('Empty = unlimited.') }}
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- -------------------------------------------------- registration --}}
    <div class="{{ $sectionCol }}">
        <div class="border rounded-3 p-3 h-100">
            <div class="text-uppercase text-muted fw-bold small mb-3">
                <i class="ri-user-add-line me-1"></i>{{ __('Registration') }}
            </div>

            <div class="row g-3">
                <div class="{{ $pairCol }}">
                    <label for="{{ $idFor('registration_start_at') }}" class="form-label">{{ __('Opens') }}</label>
                    <input type="datetime-local" class="{{ $cls('registration_start_at', 'js-reg-start') }}"
                        id="{{ $idFor('registration_start_at') }}" name="{{ $nameFor('registration_start_at') }}"
                        value="{{ $valueFor('registration_start_at') }}">
                    @if ($message = $errorFor('registration_start_at'))
                        <div class="invalid-feedback">{{ $message }}</div>
                    @endif
                    <div class="form-text">{{ __('Empty = immediately.') }}</div>
                </div>

                <div class="{{ $pairCol }}">
                    <label for="{{ $idFor('registration_end_at') }}" class="form-label">{{ __('Closes') }}</label>
                    <input type="datetime-local" class="{{ $cls('registration_end_at', 'js-reg-end') }}"
                        id="{{ $idFor('registration_end_at') }}" name="{{ $nameFor('registration_end_at') }}"
                        value="{{ $valueFor('registration_end_at') }}">
                    @if ($message = $errorFor('registration_end_at'))
                        <div class="invalid-feedback">{{ $message }}</div>
                    @endif
                    <div class="form-text">{{ __('Empty = when the session ends.') }}</div>
                </div>

                <div class="{{ $pairCol }}">
                    <label for="{{ $idFor('confirmation_deadline') }}" class="form-label">{{ __('Confirm By') }}</label>
                    <input type="datetime-local" class="{{ $cls('confirmation_deadline', 'js-confirm-by') }}"
                        id="{{ $idFor('confirmation_deadline') }}" name="{{ $nameFor('confirmation_deadline') }}"
                        value="{{ $valueFor('confirmation_deadline') }}">
                    @if ($message = $errorFor('confirmation_deadline'))
                        <div class="invalid-feedback">{{ $message }}</div>
                    @endif
                    <div class="form-text">{{ __('Empty = no confirmation step.') }}</div>
                </div>

                <div class="{{ $pairCol }}">
                    <label for="{{ $idFor('status') }}" class="form-label">
                        {{ __('Status') }} <span class="text-danger">*</span>
                    </label>
                    <select class="{{ $cls('status', 'js-status', 'form-select') }}"
                        id="{{ $idFor('status') }}" name="{{ $nameFor('status') }}">
                        @foreach ($statuses as $value => $label)
                            <option value="{{ $value }}" @selected(($valueFor('status') ?? 'open') === $value)>{{ __($label) }}</option>
                        @endforeach
                    </select>
                    @if ($message = $errorFor('status'))
                        <div class="invalid-feedback">{{ $message }}</div>
                    @endif
                    <div class="form-text">{!! __('Only :status accepts sign-ups.', ['status' => '<strong>'.__('Open').'</strong>']) !!}</div>
                </div>

                <div class="col-12">
                    {{-- Confirm By changes how every seat on the session is handed
                         out, so this says which of the two is in force. --}}
                    <div class="alert alert-secondary py-2 px-3 mb-0 small js-confirm-hint" role="status">
                        {{ __('Seats are given out and taken straight away.') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
