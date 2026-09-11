{{-- Repeatable session rows, shared by the activity create and edit screens.

     Fields post as schedules[i][...] alongside the activity, so one submit saves
     both. WellnessActivityRequest validates them; the controller creates, updates
     or removes sessions to match what was posted.

     Existing sessions carry their encrypted id in a hidden field. The controller
     decrypts it and checks it belongs to this activity before trusting it, so a
     tampered or stale id is refused rather than silently creating a row.

     Each row collapses to a one-line summary so a long list stays scannable.

     There are deliberately no `required` attributes here. Rows collapse and the
     whole tab can be hidden, and a browser refuses to report validity on a hidden
     control -- it would block submit with no visible message. The server decides. --}}

@php
    $schedules = $schedules ?? collect();

    // Seats keyed by plain id, so a row restored from old() can still be locked.
    $seatsById = $schedules->keyBy('id')->map(fn ($s) => [
        'taken' => $s->taken_seats,
        'queued' => $s->queued_seats,
        'attended' => $s->attended_seats,
    ]);

    $seatsFor = function ($encrypted) use ($seatsById) {
        if (! is_string($encrypted) || $encrypted === '') {
            return null;
        }

        try {
            return $seatsById[(int) \Illuminate\Support\Facades\Crypt::decryptString($encrypted)] ?? null;
        } catch (\Throwable) {
            return null;
        }
    };

    if (old('schedules') !== null) {
        // Came back from a failed validation -- show exactly what was submitted.
        $rows = old('schedules');
    } elseif ($schedules->isNotEmpty()) {
        $rows = $schedules->map(fn ($s) => [
            'id' => $s->encrypted_id,
            'start_at' => $s->start_at?->format('Y-m-d\TH:i'),
            'end_at' => $s->end_at?->format('Y-m-d\TH:i'),
            'location' => $s->location,
            'quota' => $s->quota,
            'registration_start_at' => $s->registration_start_at?->format('Y-m-d\TH:i'),
            'registration_end_at' => $s->registration_end_at?->format('Y-m-d\TH:i'),
            'status' => $s->status->value,
        ])->all();
    } else {
        // Create screen: one blank row ready to fill. Left untouched it is
        // dropped server-side, so sessions stay optional.
        $rows = [[]];
    }
@endphp

<div class="card mb-0">
    <div class="card-header bg-transparent d-flex flex-wrap justify-content-between align-items-center gap-2 py-2">
        <div>
            <h5 class="card-title mb-0 fs-6">{{ __('Sessions') }}</h5>
            <small class="text-muted">{{ __('The dated sessions employees register for.') }}</small>
        </div>

        <div class="d-flex gap-2">
            <button type="button" class="btn btn-sm btn-light" id="wa_toggle_all" hidden>
                <i class="ri-contract-up-down-line me-1"></i><span data-label>{{ __('Collapse all') }}</span>
            </button>
            <button type="button" class="btn btn-sm btn-primary" id="wa_add_schedule">
                <i class="ri-add-line me-1"></i>{{ __('Add Session') }}
            </button>
        </div>
    </div>

    <div class="card-body">
        <div id="wa_schedule_rows">
            @foreach ($rows as $index => $row)
                @php
                    $seats = $seatsFor($row['id'] ?? null);
                    $locked = ($seats['taken'] ?? 0) > 0;

                    // A row that failed validation must not hide behind a
                    // collapsed header, or the admin cannot see what to fix.
                    $rowHasError = $errors->hasAny([
                        "schedules.$index.id", "schedules.$index.start_at", "schedules.$index.end_at",
                        "schedules.$index.location", "schedules.$index.quota",
                        "schedules.$index.registration_start_at",
                        "schedules.$index.registration_end_at", "schedules.$index.status",
                    ]);
                @endphp

                <div class="card border mb-2 js-schedule-row {{ $rowHasError ? 'border-danger' : '' }}"
                    @if ($locked) data-locked="1" data-taken="{{ $seats['taken'] }}" @endif>

                    @if (! empty($row['id']))
                        <input type="hidden" name="schedules[{{ $index }}][id]" value="{{ $row['id'] }}">
                    @endif

                    <div class="card-header bg-light-subtle d-flex align-items-center gap-2 py-2">
                        <button type="button"
                            class="btn btn-sm btn-link text-muted p-0 js-schedule-toggle"
                            data-bs-toggle="collapse" data-bs-target="#wa_sched_body_{{ $index }}"
                            aria-expanded="true" aria-controls="wa_sched_body_{{ $index }}"
                            title="{{ __('Show or hide this session') }}">
                            <i class="ri-arrow-up-s-line fs-5"></i>
                        </button>

                        <div class="flex-grow-1 min-w-0">
                            <div class="fw-semibold small js-schedule-title">{{ __('Session') }} {{ $index + 1 }}</div>
                            <div class="text-muted small text-truncate js-schedule-summary">{{ __('Not scheduled yet') }}</div>
                        </div>

                        @if ($locked)
                            <span class="badge bg-info-subtle text-info" title="{{ __('This session already has registrations') }}">
                                <i class="ri-lock-line me-1"></i>{{ __(':count registered', ['count' => $seats['taken']]) }}
                            </span>
                        @endif

                        <span class="badge js-schedule-badge"></span>

                        <button type="button" class="btn btn-sm btn-outline-secondary js-duplicate-schedule"
                            title="{{ __('Duplicate this session') }}">
                            <i class="ri-file-copy-line"></i>
                        </button>

                        {{-- Removing a session that holds seats is refused server-side
                             (registrations would be orphaned), so do not offer it. --}}
                        <button type="button" class="btn btn-sm btn-outline-danger js-remove-schedule"
                            title="{{ $locked ? __('Cancel its registrations before removing this session') : __('Remove this session') }}"
                            @disabled($locked)>
                            <i class="ri-delete-bin-line"></i>
                        </button>
                    </div>

                    <div class="collapse show" id="wa_sched_body_{{ $index }}">
                        <div class="card-body">
                            @if ($locked)
                                <div class="alert alert-info d-flex align-items-start gap-2 py-2 small mb-3">
                                    <i class="ri-information-line mt-1"></i>
                                    <div>
                                        <strong>{{ __(':count seat(s) already held', ['count' => $seats['taken']]) }}</strong>
                                        @if ($seats['queued'])
                                            &middot; {{ __(':count queued', ['count' => $seats['queued']]) }}
                                        @endif
                                        @if ($seats['attended'])
                                            &middot; {{ __(':count attended', ['count' => $seats['attended']]) }}
                                        @endif
                                        <div>{{ __('The quota cannot go below :count, and the session cannot be removed until those registrations are cancelled.', ['count' => $seats['taken']]) }}</div>
                                    </div>
                                </div>
                            @endif

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('Starts At') }} <span class="text-danger">*</span></label>
                                    <input type="datetime-local"
                                        class="form-control js-start-at @error("schedules.$index.start_at") is-invalid @enderror"
                                        name="schedules[{{ $index }}][start_at]"
                                        value="{{ $row['start_at'] ?? '' }}">
                                    @error("schedules.$index.start_at")
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">{{ __('Ends At') }} <span class="text-danger">*</span></label>
                                    <input type="datetime-local"
                                        class="form-control js-end-at @error("schedules.$index.end_at") is-invalid @enderror"
                                        name="schedules[{{ $index }}][end_at]"
                                        value="{{ $row['end_at'] ?? '' }}">
                                    @error("schedules.$index.end_at")
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-5">
                                    <label class="form-label">{{ __('Location') }}</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="ri-map-pin-line"></i></span>
                                        <input type="text"
                                            class="form-control js-location @error("schedules.$index.location") is-invalid @enderror"
                                            name="schedules[{{ $index }}][location]" maxlength="150"
                                            placeholder="{{ __('e.g. Studio A') }}"
                                            value="{{ $row['location'] ?? '' }}">
                                        @error("schedules.$index.location")
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label">{{ __('Quota') }}</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="ri-group-line"></i></span>
                                        <input type="number"
                                            class="form-control js-quota @error("schedules.$index.quota") is-invalid @enderror"
                                            name="schedules[{{ $index }}][quota]"
                                            min="{{ $locked ? $seats['taken'] : 1 }}" placeholder="{{ __('Unlimited') }}"
                                            value="{{ $row['quota'] ?? '' }}">
                                        @error("schedules.$index.quota")
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="form-text">{{ __('Empty = unlimited.') }}</div>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">{{ __('Status') }} <span class="text-danger">*</span></label>
                                    <select class="form-select js-status @error("schedules.$index.status") is-invalid @enderror"
                                        name="schedules[{{ $index }}][status]">
                                        @foreach ($scheduleStatuses as $value => $label)
                                            <option value="{{ $value }}"
                                                @selected(($row['status'] ?? 'open') === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error("schedules.$index.status")
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">{!! __('Only :status takes registrations.', ['status' => '<strong>'.__('Open').'</strong>']) !!}</div>
                                </div>

                                <div class="col-12">
                                    <div class="border rounded p-2 bg-light-subtle">
                                        <div class="small fw-semibold text-muted mb-2">
                                            <i class="ri-time-line me-1"></i>{{ __('Registration Window') }}
                                            <span class="fw-normal">&mdash; {{ __('leave empty to accept sign-ups any time before the session.') }}</span>
                                        </div>
                                        <div class="row g-2">
                                            <div class="col-md-6">
                                                <label class="form-label small mb-1">{{ __('Opens') }}</label>
                                                <input type="datetime-local"
                                                    class="form-control form-control-sm js-reg-start @error("schedules.$index.registration_start_at") is-invalid @enderror"
                                                    name="schedules[{{ $index }}][registration_start_at]"
                                                    value="{{ $row['registration_start_at'] ?? '' }}">
                                                @error("schedules.$index.registration_start_at")
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label small mb-1">{{ __('Closes') }}</label>
                                                <input type="datetime-local"
                                                    class="form-control form-control-sm js-reg-end @error("schedules.$index.registration_end_at") is-invalid @enderror"
                                                    name="schedules[{{ $index }}][registration_end_at]"
                                                    value="{{ $row['registration_end_at'] ?? '' }}">
                                                @error("schedules.$index.registration_end_at")
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @error("schedules.$index.id")
                                <div class="alert alert-danger py-2 small mb-0 mt-3">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div id="wa_schedule_empty" class="text-center text-muted border border-dashed rounded py-4 {{ count($rows) ? 'd-none' : '' }}">
            <i class="ri-calendar-line fs-2 d-block mb-1"></i>
            <div class="fw-semibold small">{{ __('No sessions yet') }}</div>
            <div class="small">{{ __('The activity will be saved without any — you can add them later.') }}</div>
            <button type="button" class="btn btn-sm btn-outline-primary mt-2 js-add-first">
                <i class="ri-add-line me-1"></i>{{ __('Add the first session') }}
            </button>
        </div>
    </div>
</div>

<template id="wa_schedule_template">
    <div class="card border mb-2 js-schedule-row">
        <div class="card-header bg-light-subtle d-flex align-items-center gap-2 py-2">
            <button type="button" class="btn btn-sm btn-link text-muted p-0 js-schedule-toggle"
                data-bs-toggle="collapse" data-bs-target="#wa_sched_body___INDEX__"
                aria-expanded="true" aria-controls="wa_sched_body___INDEX__"
                title="{{ __('Show or hide this session') }}">
                <i class="ri-arrow-up-s-line fs-5"></i>
            </button>

            <div class="flex-grow-1 min-w-0">
                <div class="fw-semibold small js-schedule-title">{{ __('Session') }}</div>
                <div class="text-muted small text-truncate js-schedule-summary">{{ __('Not scheduled yet') }}</div>
            </div>

            <span class="badge bg-success-subtle text-success">{{ __('New') }}</span>
            <span class="badge js-schedule-badge"></span>

            <button type="button" class="btn btn-sm btn-outline-secondary js-duplicate-schedule" title="{{ __('Duplicate this session') }}">
                <i class="ri-file-copy-line"></i>
            </button>
            <button type="button" class="btn btn-sm btn-outline-danger js-remove-schedule" title="{{ __('Remove this session') }}">
                <i class="ri-delete-bin-line"></i>
            </button>
        </div>

        <div class="collapse show" id="wa_sched_body___INDEX__">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">{{ __('Starts At') }} <span class="text-danger">*</span></label>
                        <input type="datetime-local" class="form-control js-start-at" name="schedules[__INDEX__][start_at]">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ __('Ends At') }} <span class="text-danger">*</span></label>
                        <input type="datetime-local" class="form-control js-end-at" name="schedules[__INDEX__][end_at]">
                    </div>

                    <div class="col-md-5">
                        <label class="form-label">{{ __('Location') }}</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="ri-map-pin-line"></i></span>
                            <input type="text" class="form-control js-location" name="schedules[__INDEX__][location]"
                                maxlength="150" placeholder="{{ __('e.g. Studio A') }}">
                        </div>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">{{ __('Quota') }}</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="ri-group-line"></i></span>
                            <input type="number" class="form-control js-quota" name="schedules[__INDEX__][quota]"
                                min="1" placeholder="{{ __('Unlimited') }}">
                        </div>
                        <div class="form-text">{{ __('Empty = unlimited.') }}</div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">{{ __('Status') }} <span class="text-danger">*</span></label>
                        <select class="form-select js-status" name="schedules[__INDEX__][status]">
                            @foreach ($scheduleStatuses as $value => $label)
                                <option value="{{ $value }}" @selected($value === 'open')>{{ $label }}</option>
                            @endforeach
                        </select>
                        <div class="form-text">{!! __('Only :status takes registrations.', ['status' => '<strong>'.__('Open').'</strong>']) !!}</div>
                    </div>

                    <div class="col-12">
                        <div class="border rounded p-2 bg-light-subtle">
                            <div class="small fw-semibold text-muted mb-2">
                                <i class="ri-time-line me-1"></i>{{ __('Registration Window') }}
                                <span class="fw-normal">&mdash; {{ __('leave empty to accept sign-ups any time before the session.') }}</span>
                            </div>
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <label class="form-label small mb-1">{{ __('Opens') }}</label>
                                    <input type="datetime-local" class="form-control form-control-sm js-reg-start"
                                        name="schedules[__INDEX__][registration_start_at]">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small mb-1">{{ __('Closes') }}</label>
                                    <input type="datetime-local" class="form-control form-control-sm js-reg-end"
                                        name="schedules[__INDEX__][registration_end_at]">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
