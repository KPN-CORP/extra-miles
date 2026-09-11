{{-- The sessions panel that drops in under an activity row on the list.

     Rendered server-side into a hidden holder and moved into the DataTables child
     row on demand, so expanding costs no request and the markup stays in Blade
     rather than being assembled in JavaScript. --}}

<div class="p-2 bg-light-subtle border-start border-3 border-primary">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
        <div class="fw-semibold small">
            <i class="ri-calendar-line me-1"></i>{{ __('Sessions of :name', ['name' => '“'.$activity->name.'”']) }}
        </div>
        <div class="d-flex gap-1">
            <a href="{{ route('wellness.activities.edit', $activity->encrypted_id) }}"
                class="btn btn-sm btn-outline-warning">
                <i class="ri-edit-box-line me-1"></i>{{ __('Edit sessions') }}
            </a>
            <a href="{{ route('admin.wellness.schedules.index', $activity->encrypted_id) }}"
                class="btn btn-sm btn-outline-primary">
                <i class="ri-external-link-line me-1"></i>{{ __('Open full view') }}
            </a>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-sm mb-0 align-middle bg-body">
            <thead>
                <tr class="text-muted small">
                    <th>{{ __('Session') }}</th>
                    <th>{{ __('Location') }}</th>
                    <th>{{ __('Registration Window') }}</th>
                    <th class="text-center">{{ __('Seats') }}</th>
                    <th class="text-center">{{ __('Queue') }}</th>
                    <th class="text-center">{{ __('Attended') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th class="text-end">{{ __('Participants') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($activity->schedules as $schedule)
                    <tr>
                        <td>
                            <div class="fw-semibold small">{{ $schedule->start_at->format('d M Y') }}</div>
                            <small class="text-muted">
                                {{ $schedule->start_at->format('H:i') }} &ndash; {{ $schedule->end_at->format('H:i') }}
                            </small>
                        </td>
                        <td class="small">{{ $schedule->location ?: '-' }}</td>
                        <td class="small">
                            @if ($schedule->registration_start_at || $schedule->registration_end_at)
                                {{ $schedule->registration_start_at?->format('d M H:i') ?? __('anytime') }}
                                &rarr;
                                {{ $schedule->registration_end_at?->format('d M H:i') ?? __('session end') }}
                            @else
                                <span class="text-muted">{{ __('Always open') }}</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if ($schedule->quota === null)
                                <span class="badge bg-info-subtle text-info">{{ $schedule->taken_seats }} / &infin;</span>
                            @else
                                <span class="badge {{ $schedule->taken_seats >= $schedule->quota ? 'bg-danger-subtle text-danger' : 'bg-success-subtle text-success' }}">
                                    {{ $schedule->taken_seats }} / {{ $schedule->quota }}
                                </span>
                            @endif
                        </td>
                        <td class="text-center small">{{ $schedule->queued_seats ?: '-' }}</td>
                        <td class="text-center small">{{ $schedule->attended_seats ?: '-' }}</td>
                        <td>
                            <span class="badge {{ $schedule->status->badgeClass() }}">{{ $schedule->status->label() }}</span>
                        </td>
                        <td class="text-end">
                            <a href="{{ route('admin.wellness.registrations.index', $schedule->encrypted_id) }}"
                                class="btn btn-sm btn-outline-primary" title="{{ __('Participants') }}">
                                <i class="ri-group-line"></i>
                            </a>
                            <a href="{{ route('wellness.schedules.qr', $schedule->encrypted_id) }}"
                                class="btn btn-sm btn-outline-secondary" title="{{ __('Attendance QR') }}" target="_blank">
                                <i class="ri-qr-code-line"></i>
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
