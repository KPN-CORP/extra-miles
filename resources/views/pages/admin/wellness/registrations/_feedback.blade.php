{{--
    Read-only list of what attendees wrote about this session. Employees submit
    it from the mobile app once the session has finished; there is nothing for
    an admin to do here but read it.
--}}
<div class="d-flex justify-content-between align-items-center mb-2">
    <small class="text-muted">
        {{ __('Newest first. Only employees who checked in can leave feedback, and only after the session has ended.') }}
    </small>
</div>

<div class="table-responsive">
    <table class="table table-hover table-sm w-100 align-middle js-datatable">
        <thead class="table-light">
            <tr>
                <th class="no-sort">{{ __('No') }}</th>
                <th>{{ __('Employee') }}</th>
                <th>{{ __('Business Unit') }}</th>
                <th>{{ __('Unit') }}</th>
                <th>{{ __('Attended') }}</th>
                <th>{{ __('Submitted') }}</th>
                <th class="no-sort">{{ __('Feedback') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($feedback as $entry)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>
                        {{-- Snapshot taken when they registered, same as the participant tabs. --}}
                        <div class="fw-semibold">{{ $entry->fullname ?: ($entry->registration?->fullname ?: '-') }}</div>
                        <small class="text-muted">{{ $entry->employee_id }}</small>
                    </td>
                    <td>{{ $entry->registration?->business_unit ?: '-' }}</td>
                    <td>{{ $entry->registration?->unit ?: '-' }}</td>
                    <td>
                        @if ($entry->registration?->attended_at)
                            <span class="badge bg-success-subtle text-success">
                                {{ $entry->registration->attended_at->format('d M H:i') }}
                            </span>
                        @else
                            <span class="badge bg-secondary-subtle text-secondary">{{ __('Not yet') }}</span>
                        @endif
                    </td>
                    <td>
                        <span title="{{ $entry->submitted_at?->format('d M Y H:i') }}">
                            {{ $entry->submitted_at?->format('d M H:i') }}
                        </span>
                        @if ($entry->wasEdited())
                            <span class="badge bg-light text-muted ms-1" title="{{ __('The employee revised this feedback.') }}">
                                {{ __('edited') }}
                            </span>
                        @endif
                    </td>
                    <td class="wf-message">{{ $entry->message }}</td>
                </tr>
            @empty
                {{-- DataTables renders its own empty-table row. --}}
            @endforelse
        </tbody>
    </table>
</div>
