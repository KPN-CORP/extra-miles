@php
    $blacklisted = $blacklisted ?? collect();
    $onBlacklist = $blacklisted->has($registration->employee_id);
@endphp

{{-- The HR columns are a snapshot taken at registration time, not a live join
     against kpncorp -- they show the employee as they were when they signed up. --}}
<td>
    <div class="fw-semibold">
        {{ $registration->fullname ?: '-' }}
        @if ($onBlacklist)
            <i class="ri-forbid-2-line text-dark" title="{{ __('On the wellness blacklist') }}"></i>
        @endif
    </div>
    <small class="text-muted">{{ $registration->employee_id }}</small>
</td>
<td>{{ $registration->business_unit ?: '-' }}</td>
<td>{{ $registration->unit ?: '-' }}</td>
<td>{{ $registration->job_level ?: '-' }}</td>
<td>
    <span title="{{ $registration->registered_at?->translatedFormat('d M Y H:i') }}">
        {{ $registration->registered_at?->translatedFormat('d M H:i') }}
    </span>
</td>
<td>
    <span class="badge {{ $registration->source === \App\Enums\WellnessRegistrationSource::Admin ? 'bg-primary-subtle text-primary' : 'bg-light text-dark' }}">
        {{ $registration->source->label() }}
    </span>
</td>
