@php
    use App\Enums\WellnessRegistrationStatus;

    $status = $registration->status;
    $name = $registration->fullname ?: $registration->employee_id;
@endphp

{{-- Row actions. Every transition goes through a modal so a remark can be
     captured into the registration's audit trail. Which actions are offered
     depends on the current status, not on the tab. --}}
<div class="btn-group btn-group-sm" role="group">
    @if ($status->canTransitionTo(WellnessRegistrationStatus::Confirmed))
        <button type="button" class="btn btn-outline-success js-action"
            data-url="{{ route('wellness.registrations.confirm', $registration->encrypted_id) }}"
            data-title="Confirm seat"
            data-body="Give {{ $name }} a confirmed seat for this session?"
            data-confirm="Confirm"
            data-variant="btn-success"
            data-bs-toggle="modal" data-bs-target="#actionModal" title="Confirm">
            <i class="ri-check-line"></i>
        </button>
    @endif

    @if ($status === WellnessRegistrationStatus::Confirmed || $status === WellnessRegistrationStatus::Blacklisted)
        <button type="button" class="btn btn-outline-info js-action"
            data-url="{{ route('wellness.registrations.requeue', $registration->encrypted_id) }}"
            data-title="Move back to the queue"
            data-body="Move {{ $name }} back to {{ $queueStatus->label() }}? Their seat is released to the queue."
            data-confirm="Move to {{ $queueStatus->label() }}"
            data-variant="btn-info"
            data-bs-toggle="modal" data-bs-target="#actionModal" title="Move to queue">
            <i class="ri-arrow-go-back-line"></i>
        </button>
    @endif

    @if ($status->canTransitionTo(WellnessRegistrationStatus::Blacklisted))
        <button type="button" class="btn btn-outline-dark js-blacklist"
            data-url="{{ route('wellness.registrations.blacklist', $registration->encrypted_id) }}"
            data-name="{{ $name }}"
            data-bs-toggle="modal" data-bs-target="#blacklistModal" title="Blacklist">
            <i class="ri-forbid-2-line"></i>
        </button>
    @endif

    @if ($status->canTransitionTo(WellnessRegistrationStatus::Cancelled))
        <button type="button" class="btn btn-outline-secondary js-action"
            data-url="{{ route('wellness.registrations.cancel', $registration->encrypted_id) }}"
            data-title="Cancel registration"
            data-body="Cancel {{ $name }}'s registration? Any seat they hold is released to the queue."
            data-confirm="Cancel registration"
            data-variant="btn-secondary"
            data-bs-toggle="modal" data-bs-target="#actionModal" title="Cancel">
            <i class="ri-close-circle-line"></i>
        </button>
    @endif

    <button type="button" class="btn btn-outline-primary js-history"
        data-registration="{{ $registration->id }}"
        data-bs-toggle="modal" data-bs-target="#historyModal" title="History">
        <i class="ri-history-line"></i>
    </button>
</div>
