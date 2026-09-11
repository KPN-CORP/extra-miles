{{-- Add employee --}}
<div class="modal fade" id="addParticipantModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('wellness.registrations.store', $schedule->encrypted_id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Add Employee') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3 position-relative">
                        <label for="employee-search" class="form-label">{{ __('Employee') }} <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="employee-search"
                            placeholder="{{ __('Search by name or employee ID') }}" autocomplete="off">
                        <input type="hidden" name="employee_id" id="employee-id">
                        <div id="employee-results" class="list-group position-absolute w-100 shadow"
                            style="z-index: 1056; max-height: 240px; overflow-y: auto;"></div>
                        <div class="form-text" id="employee-selected">{{ __('No employee selected yet.') }}</div>
                    </div>
                    <div class="mb-3">
                        <label for="participant-remark" class="form-label">{{ __('Remark') }}</label>
                        <textarea class="form-control" id="participant-remark" name="remark" rows="2"
                            placeholder="{{ __('Why is this employee being added?') }}"></textarea>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="allow-over-quota" name="allow_over_quota" value="1">
                        <label class="form-check-label" for="allow-over-quota">{{ __('Allow over quota') }}</label>
                        <div class="form-text">
                            {{ __('Tick this to add the employee even when the session is already full.') }}
                        </div>
                    </div>
                    <div class="alert alert-light border small mt-3 mb-0">
                        {{ __('Adding an employee here confirms their seat immediately, whichever registration method the activity uses.') }}
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('Add & Confirm') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Confirm / requeue / cancel, with a remark --}}
<div class="modal fade" id="actionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="" id="actionForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="actionModalTitle">{{ __('Confirm') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3" id="actionModalBody"></p>
                    <div class="mb-0">
                        <label for="action-remark" class="form-label">{{ __('Remark') }}</label>
                        <textarea class="form-control" id="action-remark" name="remark" rows="3"></textarea>
                        <div class="form-text">{{ __('Saved to the history of this registration.') }}</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary" id="actionSubmit">{{ __('Confirm') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Blacklist: reason is required, and by default it also joins the master list --}}
<div class="modal fade" id="blacklistModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="" id="blacklistForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Blacklist Participant') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3">
                        {!! __('Blacklist :name for this session. Any seat they hold is released to the queue.', ['name' => '<strong id="blacklistName"></strong>']) !!}
                    </p>
                    <div class="mb-3">
                        <label for="blacklist-reason" class="form-label">{{ __('Reason') }} <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="blacklist-reason" name="reason" rows="3" required
                            placeholder="{{ __('e.g. Registered but did not attend, twice in a row.') }}"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="blacklist-end-date" class="form-label">{{ __('Blacklist End Date') }}</label>
                        <input type="date" class="form-control" id="blacklist-end-date" name="end_date"
                            min="{{ now()->toDateString() }}">
                        <div class="form-text">{{ __('Leave empty for a blacklist that does not expire.') }}</div>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="blacklist-add-master"
                            name="add_to_master_list" value="1" checked>
                        <label class="form-check-label" for="blacklist-add-master">
                            {{ __('Add to the wellness blacklist') }}
                        </label>
                        <div class="form-text">
                            {{ __('They can still register for other sessions, but will never be confirmed automatically while the blacklist is in force.') }}
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-dark">{{ __('Blacklist') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Status history --}}
<div class="modal fade" id="historyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Registration History') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
            </div>
            <div class="modal-body" id="historyBody"></div>
        </div>
    </div>
</div>

{{-- History payloads, rendered server-side and copied into the modal on click. --}}
@foreach ($registrations as $registration)
    <template id="history-{{ $registration->id }}">
        <h6 class="mb-3">{{ $registration->fullname ?: $registration->employee_id }}</h6>
        <ul class="list-group">
            @foreach ($registration->statusHistories as $history)
                <li class="list-group-item">
                    <div class="d-flex justify-content-between flex-wrap gap-2">
                        <div>
                            <span class="badge bg-light text-dark">{{ $history->from_status?->label() ?? __('New') }}</span>
                            <i class="ri-arrow-right-line mx-1"></i>
                            <span class="badge {{ $history->status->badgeClass() }}">{{ $history->status->label() }}</span>
                        </div>
                        <small class="text-muted">{{ $history->changed_at->format('d M Y H:i') }}</small>
                    </div>
                    <div class="small text-muted mt-1">
                        {{ $history->remark ?: __('No remark') }}
                        &middot; {{ __('by') }} {{ $history->creator?->name ?? __('system') }}
                    </div>
                </li>
            @endforeach
        </ul>
    </template>
@endforeach
