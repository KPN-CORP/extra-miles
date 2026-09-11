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
            data-title="{{ __('Confirm seat') }}"
            data-body="{{ __('Give :name a confirmed seat for this session?', ['name' => $name]) }}"
            data-confirm="{{ __('Confirm') }}"
            data-variant="btn-success"
            data-bs-toggle="modal" data-bs-target="#actionModal" title="{{ __('Confirm') }}">
            <i class="ri-check-line"></i>
        </button>
    @endif

    @if ($status === WellnessRegistrationStatus::Confirmed || $status === WellnessRegistrationStatus::Blacklisted)
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

    @if ($status->canTransitionTo(WellnessRegistrationStatus::Cancelled))
        <button type="button" class="btn btn-outline-secondary js-action"
            data-url="{{ route('wellness.registrations.cancel', $registration->encrypted_id) }}"
            data-title="{{ __('Cancel registration') }}"
            data-body="{{ __('Cancel the registration of :name? Any seat they hold is released to the queue.', ['name' => $name]) }}"
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
