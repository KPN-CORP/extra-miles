@extends('layouts_.vertical', ['page_title' => 'Wellness Schedules'])

@section('css')
    @include('layouts_.shared.admin-datatable-css')
    @include('pages.admin.wellness.partials.page-css')
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h4 class="page-title mb-0">{{ $activity->name }}</h4>
            <small class="text-muted">
                {{ $activity->type?->name ?? 'No type' }} &middot;
                <span class="badge {{ $activity->status->badgeClass() }}">{{ $activity->status->label() }}</span>
            </small>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('wellness.activities.edit', $activity->encrypted_id) }}" class="btn btn-outline-warning">
                <i class="ri-edit-box-line me-1"></i> Edit Activity
            </a>
            <a href="{{ route('admin.wellness.activities.index') }}" class="btn btn-outline-secondary">
                <i class="ri-arrow-left-line me-1"></i> Back
            </a>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createScheduleModal">
                <i class="ri-add-line me-1"></i> Add Schedule
            </button>
        </div>
    </div>

    @include('pages.admin.wellness.partials.errors')

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-sm nowrap w-100 align-middle js-datatable">
                            <thead class="table-light">
                                <tr>
                                    <th class="no-sort">No</th>
                                    <th>Session</th>
                                    <th>Location</th>
                                    <th>Registration Window</th>
                                    <th>Seats</th>
                                    <th>Queue</th>
                                    <th>Attended</th>
                                    <th>Status</th>
                                    <th class="no-sort">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($schedules as $schedule)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            <div class="fw-semibold">{{ $schedule->start_at->format('d M Y') }}</div>
                                            <small class="text-muted">
                                                {{ $schedule->start_at->format('H:i') }} &ndash; {{ $schedule->end_at->format('H:i') }}
                                            </small>
                                        </td>
                                        <td>{{ $schedule->location ?: '-' }}</td>
                                        <td>
                                            @if ($schedule->registration_start_at || $schedule->registration_end_at)
                                                <small>
                                                    {{ $schedule->registration_start_at?->format('d M H:i') ?? 'anytime' }}
                                                    &rarr;
                                                    {{ $schedule->registration_end_at?->format('d M H:i') ?? 'session end' }}
                                                </small>
                                            @else
                                                <small class="text-muted">Always open</small>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($schedule->quota === null)
                                                <span class="badge bg-info-subtle text-info">{{ $schedule->taken_seats }} / unlimited</span>
                                            @else
                                                <span class="badge {{ $schedule->taken_seats >= $schedule->quota ? 'bg-danger-subtle text-danger' : 'bg-success-subtle text-success' }}">
                                                    {{ $schedule->taken_seats }} / {{ $schedule->quota }}
                                                </span>
                                            @endif
                                        </td>
                                        <td>{{ $schedule->queued_seats }}</td>
                                        <td>{{ $schedule->attended_seats }}</td>
                                        <td><span class="badge {{ $schedule->status->badgeClass() }}">{{ $schedule->status->label() }}</span></td>
                                        <td>
                                            <a href="{{ route('admin.wellness.registrations.index', $schedule->encrypted_id) }}"
                                                class="btn btn-outline-primary btn-sm" title="Participants">
                                                <i class="ri-group-line"></i>
                                            </a>
                                            <a href="{{ route('wellness.schedules.qr', $schedule->encrypted_id) }}" target="_blank"
                                                class="btn btn-outline-dark btn-sm" title="Attendance QR">
                                                <i class="ri-qr-code-line"></i>
                                            </a>
                                            <button type="button" class="btn btn-outline-warning btn-sm js-edit-schedule"
                                                data-url="{{ route('wellness.schedules.update', $schedule->encrypted_id) }}"
                                                data-start="{{ $schedule->start_at->format('Y-m-d\TH:i') }}"
                                                data-end="{{ $schedule->end_at->format('Y-m-d\TH:i') }}"
                                                data-location="{{ $schedule->location }}"
                                                data-quota="{{ $schedule->quota }}"
                                                data-regstart="{{ $schedule->registration_start_at?->format('Y-m-d\TH:i') }}"
                                                data-regend="{{ $schedule->registration_end_at?->format('Y-m-d\TH:i') }}"
                                                data-status="{{ $schedule->status->value }}"
                                                data-bs-toggle="modal" data-bs-target="#editScheduleModal" title="Edit">
                                                <i class="ri-edit-box-line"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-danger btn-sm js-archive"
                                                data-form="archive-schedule-{{ $schedule->id }}"
                                                data-text="This schedule will be archived.">
                                                <i class="ri-archive-line"></i>
                                            </button>
                                            <form id="archive-schedule-{{ $schedule->id }}" class="d-none"
                                                action="{{ route('wellness.schedules.archive', $schedule->encrypted_id) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Create --}}
    <div class="modal fade" id="createScheduleModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="{{ route('wellness.schedules.store', $activity->encrypted_id) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Add Schedule</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        @include('pages.admin.wellness.schedules._fields', ['prefix' => 'create', 'statuses' => $statuses])
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Schedule</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Edit --}}
    <div class="modal fade" id="editScheduleModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" action="" id="editScheduleForm">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Schedule</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        @include('pages.admin.wellness.schedules._fields', ['prefix' => 'edit', 'statuses' => $statuses])
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Schedule</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    @include('layouts_.shared.admin-datatable-js')
    @include('pages.admin.wellness.partials.confirm-js')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.js-edit-schedule').forEach(function (button) {
                button.addEventListener('click', function () {
                    var data = this.dataset;
                    document.getElementById('editScheduleForm').action = data.url;
                    document.getElementById('edit-start_at').value = data.start || '';
                    document.getElementById('edit-end_at').value = data.end || '';
                    document.getElementById('edit-location').value = data.location || '';
                    document.getElementById('edit-quota').value = data.quota || '';
                    document.getElementById('edit-registration_start_at').value = data.regstart || '';
                    document.getElementById('edit-registration_end_at').value = data.regend || '';
                    document.getElementById('edit-status').value = data.status || 'open';
                });
            });
        });
    </script>
@endpush
