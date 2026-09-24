@php
    use App\Enums\WellnessRegistrationStatus;

    $status = $registration->status;
    $name = $registration->fullname ?: $registration->employee_id;

    // On a session with a confirmation deadline the admin hands out a seat but
    // does not take it: the employee still has to accept before it lapses.
    // $schedule comes from the including view -- reading it off the registration
    // would lazy-load the same row once per participant.
    $asksToConfirm = $schedule->requiresConfirmation();

    // A seat -- taken or merely offered -- is *revoked*, which passes it down
    // the queue. A registration that holds no seat is simply cancelled.
    $holdsSeat = $status->consumesSlot();
@endphp

{{-- Row actions. Every transition goes through a modal so a remark can be
     captured into the registration's audit trail. Which actions are offered
     depends on the current status, not on the tab. --}}
<div class="btn-group btn-group-sm" role="group">
    @if (! $holdsSeat && $status->canTransitionTo(WellnessRegistrationStatus::Confirmed))
        <button type="button" class="btn btn-outline-success js-action"
            data-url="{{ route('wellness.registrations.confirm', $registration->encrypted_id) }}"
            data-title="{{ $asksToConfirm ? __('Give a seat') : __('Confirm seat') }}"
            data-body="{{ $asksToConfirm
                ? __('Give :name a seat? They will be asked to confirm it before the deadline, or it passes to the next person in the queue.', ['name' => $name])
                : __('Give :name a confirmed seat for this session?', ['name' => $name]) }}"
            data-confirm="{{ $asksToConfirm ? __('Give seat') : __('Confirm') }}"
            data-variant="btn-success"
            data-bs-toggle="modal" data-bs-target="#actionModal"
            title="{{ $asksToConfirm ? __('Give seat') : __('Confirm') }}">
            <i class="ri-check-line"></i>
        </button>
    @endif

    @if ($holdsSeat || $status === WellnessRegistrationStatus::Blacklisted)
        <button type="button" class="btn btn-outline-info js-action"
            data-url="{{ route('wellness.registrations.requeue', $registration->encrypted_id) }}"
            data-title="{{ __('Move back to the queue') }}"
            data-body="{{ __('Move :name back to :status? Their seat is released to the queue.', ['name' => $name, 'status' => $queueStatus->label()]) }}"
            data-confirm="{{ __('Move to :status', ['status' => $queueStatus->label()]) }}"
            data-variant="btn-info"
            data-bs-toggle="modal" data-bs-target="#actionModal" title="{{ __('Move to queue') }}">
            <i class="ri-arrow-go-back-line"></i>
        </button>
    @endif

    @if ($status->canTransitionTo(WellnessRegistrationStatus::Blacklisted))
        <button type="button" class="btn btn-outline-dark js-blacklist"
            data-url="{{ route('wellness.registrations.blacklist', $registration->encrypted_id) }}"
            data-name="{{ $name }}"
            data-bs-toggle="modal" data-bs-target="#blacklistModal" title="{{ __('Blacklist') }}">
            <i class="ri-forbid-2-line"></i>
        </button>
    @endif

    {{-- Rejecting is the admin's way out for anyone still in play, seated or
         queued: the seat moves down the queue and the employee cannot come
         back. Only offered while the session is still ahead. --}}
    @if ($status->holdsTheEmployee() && $schedule->start_at->isFuture())
        <button type="button" class="btn btn-outline-danger js-action"
            data-url="{{ route('wellness.registrations.reject', $registration->encrypted_id) }}"
            data-title="{{ __('Reject participant') }}"
            data-body="{{ $holdsSeat
                ? __("Reject :name? Their seat is offered to the next person in the queue, and they cannot register for this session again.", ['name' => $name])
                : __('Reject :name? They cannot register for this session again.', ['name' => $name]) }}"
            data-confirm="{{ __('Reject') }}"
            data-variant="btn-danger"
            data-bs-toggle="modal" data-bs-target="#actionModal" title="{{ __('Reject') }}">
            <i class="ri-user-unfollow-line"></i>
        </button>
    @endif

    @if (! $holdsSeat && $status->canTransitionTo(WellnessRegistrationStatus::Cancelled))
        <button type="button" class="btn btn-outline-secondary js-action"
            data-url="{{ route('wellness.registrations.cancel', $registration->encrypted_id) }}"
            data-title="{{ __('Cancel registration') }}"
            data-body="{{ __('Cancel the registration of :name?', ['name' => $name]) }}"
            data-confirm="{{ __('Cancel registration') }}"
            data-variant="btn-secondary"
            data-bs-toggle="modal" data-bs-target="#actionModal" title="{{ __('Cancel') }}">
            <i class="ri-close-circle-line"></i>
        </button>
    @endif

    <button type="button" class="btn btn-outline-primary js-history"
        data-registration="{{ $registration->id }}"
        data-bs-toggle="modal" data-bs-target="#historyModal" title="{{ __('History') }}">
        <i class="ri-history-line"></i>
    </button>
</div>
