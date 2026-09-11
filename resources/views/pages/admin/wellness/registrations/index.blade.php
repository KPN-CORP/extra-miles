@extends('layouts_.vertical', ['page_title' => __('Wellness Participants')])

@section('css')
    @include('layouts_.shared.admin-datatable-css')
    @include('pages.admin.wellness.partials.page-css')
@endsection

@section('content')
@php
    use App\Enums\WellnessRegistrationStatus;
@endphp
<div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <div class="fw-semibold">{{ $schedule->activity->name }}</div>
            <small class="text-muted">
                {{ $schedule->start_at->format('d M Y, H:i') }} &ndash; {{ $schedule->end_at->format('H:i') }}
                @if ($schedule->location) &middot; {{ $schedule->location }} @endif
                &middot;
                <span class="badge {{ $method->badgeClass() }}">
                    <i class="{{ $method->icon() }} me-1"></i>{{ $method->label() }}
                </span>
            </small>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('wellness.schedules.qr', $schedule->encrypted_id) }}" target="_blank" class="btn btn-outline-dark">
                <i class="ri-qr-code-line me-1"></i> {{ __('QR') }}
            </a>
            {{-- data-no-loader: this serves a file, the page never navigates, so the
                 preloader would have nothing to clear it. --}}
            <a href="{{ route('wellness.registrations.export', $schedule->encrypted_id) }}" class="btn btn-outline-success" data-no-loader>
                <i class="ri-file-excel-2-line me-1"></i> {{ __('Export') }}
            </a>
            <a href="{{ route('admin.wellness.schedules.index', $schedule->activity->encrypted_id) }}" class="btn btn-outline-secondary">
                <i class="ri-arrow-left-line me-1"></i> {{ __('Back') }}
            </a>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addParticipantModal">
                <i class="ri-user-add-line me-1"></i> {{ __('Add Employee') }}
            </button>
        </div>
    </div>

    @include('pages.admin.wellness.partials.errors')

    <div class="alert alert-light border d-flex align-items-start gap-2" role="alert">
        <i class="{{ $method->icon() }} mt-1"></i>
        <div class="small mb-0">{{ $method->description() }}</div>
    </div>

    <div class="row g-3 mb-3">
        @php
            $stats = [
                [__('Confirmed'), $takenSeats.' / '.($schedule->quota === null ? '∞' : $schedule->quota), 'ri-check-double-line'],
                [$queueStatus->label(), $queuedCount, 'ri-list-ordered'],
                [__('Attended'), $attendedCount, 'ri-user-follow-line'],
                [__('Blacklisted'), ($groups[WellnessRegistrationStatus::Blacklisted->value] ?? collect())->count(), 'ri-forbid-2-line'],
            ];
        @endphp
        @foreach ($stats as [$label, $value, $icon])
            <div class="col-6 col-lg-3">
                <div class="card mb-0">
                    <div class="card-body py-3">
                        <div class="d-flex align-items-center gap-2">
                            <i class="{{ $icon }} fs-3 text-muted"></i>
                            <div>
                                <div class="text-muted small">{{ $label }}</div>
                                <div class="fs-5 fw-semibold">{{ $value }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    {{-- Tabs follow the method's own vocabulary. --}}
                    <ul class="nav nav-tabs mb-3" role="tablist">
                        @foreach ($statuses as $status)
                            <li class="nav-item" role="presentation">
                                <button class="nav-link @if ($loop->first) active @endif"
                                    data-bs-toggle="tab" data-bs-target="#tab-{{ $status->value }}" type="button" role="tab">
                                    {{ $status->label() }}
                                    <span class="badge bg-secondary ms-1">{{ ($groups[$status->value] ?? collect())->count() }}</span>
                                </button>
                            </li>
                        @endforeach
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-feedback" type="button" role="tab">
                                {{ __('Feedback') }}
                                <span class="badge bg-secondary ms-1">{{ $feedback->count() }}</span>
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content">
                        @foreach ($statuses as $status)
                            @php
                                $rows = $groups[$status->value] ?? collect();
                                $isQueueTab = $status === $queueStatus;
                                // No/# + 6 employee cells + Action, plus the
                                // checkbox on the queue tab and Attendance on Confirmed.
                                $columnCount = 8
                                    + ($isQueueTab ? 1 : 0)
                                    + ($status === WellnessRegistrationStatus::Confirmed ? 1 : 0);
                            @endphp
                            <div class="tab-pane fade @if ($loop->first) show active @endif"
                                id="tab-{{ $status->value }}" role="tabpanel">

                                @if ($status === $queueStatus)
                                    <form action="{{ route('wellness.registrations.bulkConfirm') }}" method="POST">
                                        @csrf
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <small class="text-muted">
                                                @if ($method->autoConfirms())
                                                    {{ __('Listed in registration order. A freed seat is confirmed automatically from the top, skipping blacklisted employees.') }}
                                                @else
                                                    {{ __('Listed in registration order. Select who gets a seat.') }}
                                                @endif
                                            </small>
                                            <button type="submit" class="btn btn-success btn-sm" @disabled($rows->isEmpty())>
                                                <i class="ri-check-line me-1"></i> {{ __('Confirm Selected') }}
                                            </button>
                                        </div>
                                @endif

                                <div class="table-responsive">
                                    <table class="table table-hover table-sm nowrap w-100 align-middle {{ $isQueueTab ? '' : 'js-datatable' }}">
                                        <thead class="table-light">
                                            <tr>
                                                @if ($status === $queueStatus)
                                                    <th class="no-sort" style="width:2rem;">
                                                        <input type="checkbox" class="form-check-input js-check-all"
                                                            data-group="{{ $status->value }}">
                                                    </th>
                                                    <th style="width:3rem;">#</th>
                                                @else
                                                    <th class="no-sort">{{ __('No') }}</th>
                                                @endif
                                                <th>{{ __('Employee') }}</th>
                                                <th>{{ __('Business Unit') }}</th>
                                                <th>{{ __('Unit') }}</th>
                                                <th>{{ __('Job Level') }}</th>
                                                <th>{{ __('Registered') }}</th>
                                                <th>{{ __('Via') }}</th>
                                                @if ($status === WellnessRegistrationStatus::Confirmed)
                                                    <th>{{ __('Attendance') }}</th>
                                                @endif
                                                <th class="no-sort">{{ __('Action') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($rows as $registration)
                                                <tr>
                                                    @if ($status === $queueStatus)
                                                        <td>
                                                            <input type="checkbox" class="form-check-input js-row-check"
                                                                data-group="{{ $status->value }}"
                                                                name="selected_ids[]" value="{{ $registration->encrypted_id }}">
                                                        </td>
                                                    @endif
                                                    <td>{{ $loop->iteration }}</td>

                                                    @include('pages.admin.wellness.registrations._employee-cells', [
                                                        'registration' => $registration,
                                                        'blacklisted' => $blacklisted,
                                                    ])

                                                    @if ($status === WellnessRegistrationStatus::Confirmed)
                                                        <td>
                                                            @if ($registration->attended_at)
                                                                <span class="badge bg-success-subtle text-success">
                                                                    {{ $registration->attended_at->format('d M H:i') }}
                                                                </span>
                                                            @else
                                                                <span class="badge bg-secondary-subtle text-secondary">{{ __('Not yet') }}</span>
                                                            @endif
                                                        </td>
                                                    @endif

                                                    <td>
                                                        @include('pages.admin.wellness.registrations._actions', [
                                                            'registration' => $registration,
                                                            'queueStatus' => $queueStatus,
                                                        ])
                                                    </td>
                                                </tr>
                                            @empty
                                                {{-- Only the plain queue table needs this: the DataTables
                                                     initializer renders its own "No data available" row, and
                                                     a placeholder here would be counted as a data row. --}}
                                                @if ($isQueueTab)
                                                    <tr>
                                                        <td colspan="{{ $columnCount }}" class="text-center text-muted py-3">
                                                            {{ __('No :status participants.', ['status' => strtolower($status->label())]) }}
                                                        </td>
                                                    </tr>
                                                @endif
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>

                                @if ($status === $queueStatus)
                                    </form>
                                @endif
                            </div>
                        @endforeach

                        {{-- Written by attendees from the mobile app; read-only here. --}}
                        <div class="tab-pane fade" id="tab-feedback" role="tabpanel">
                            @include('pages.admin.wellness.registrations._feedback', ['feedback' => $feedback])
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('pages.admin.wellness.registrations._modals')
</div>
@endsection

@push('scripts')
    @include('layouts_.shared.admin-datatable-js')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // ---- bulk select, per tab ---------------------------------------
            document.querySelectorAll('.js-check-all').forEach(function (master) {
                master.addEventListener('change', function () {
                    document.querySelectorAll('.js-row-check[data-group="' + master.dataset.group + '"]')
                        .forEach(function (box) { box.checked = master.checked; });
                });
            });

            // ---- confirm / requeue / cancel ---------------------------------
            document.querySelectorAll('.js-action').forEach(function (button) {
                button.addEventListener('click', function () {
                    document.getElementById('actionForm').action = this.dataset.url;
                    document.getElementById('actionModalTitle').textContent = this.dataset.title;
                    document.getElementById('actionModalBody').textContent = this.dataset.body;
                    document.getElementById('action-remark').value = '';

                    var submit = document.getElementById('actionSubmit');
                    submit.textContent = this.dataset.confirm;
                    submit.className = 'btn ' + (this.dataset.variant || 'btn-primary');
                });
            });

            // ---- blacklist --------------------------------------------------
            document.querySelectorAll('.js-blacklist').forEach(function (button) {
                button.addEventListener('click', function () {
                    document.getElementById('blacklistForm').action = this.dataset.url;
                    document.getElementById('blacklistName').textContent = this.dataset.name;
                    document.getElementById('blacklist-reason').value = '';
                    document.getElementById('blacklist-end-date').value = '';
                    document.getElementById('blacklist-add-master').checked = true;
                });
            });

            // ---- status history ---------------------------------------------
            document.querySelectorAll('.js-history').forEach(function (button) {
                button.addEventListener('click', function () {
                    var template = document.getElementById('history-' + this.dataset.registration);
                    var body = document.getElementById('historyBody');
                    body.innerHTML = '';
                    if (template) {
                        body.appendChild(template.content.cloneNode(true));
                    }
                });
            });

            // ---- employee typeahead -----------------------------------------
            var search = document.getElementById('employee-search');
            var results = document.getElementById('employee-results');
            var hidden = document.getElementById('employee-id');
            var selected = document.getElementById('employee-selected');
            var timer = null;

            if (search) {
                search.addEventListener('input', function () {
                    var term = this.value.trim();
                    hidden.value = '';
                    selected.textContent = @json(__('No employee selected yet.'));
                    window.clearTimeout(timer);

                    if (term.length < 2) {
                        results.innerHTML = '';
                        return;
                    }

                    timer = window.setTimeout(function () {
                        fetch('{{ route('wellness.employees.search') }}?q=' + encodeURIComponent(term), {
                            headers: { 'Accept': 'application/json' }
                        })
                            .then(function (response) { return response.json(); })
                            .then(function (employees) {
                                results.innerHTML = '';
                                employees.forEach(function (employee) {
                                    var item = document.createElement('button');
                                    item.type = 'button';
                                    item.className = 'list-group-item list-group-item-action';
                                    item.innerHTML = employee.fullname + ' (' + employee.employee_id + ')' +
                                        (employee.blacklisted
                                            ? ' <span class="badge bg-dark-subtle text-dark">' + @json(__('Blacklisted')) + '</span>'
                                            : '');
                                    item.addEventListener('click', function () {
                                        hidden.value = employee.employee_id;
                                        search.value = employee.fullname;
                                        selected.textContent = @json(__('Selected')) + ': ' + employee.fullname +
                                            ' - ' + (employee.group_company || '-') + ' / ' + (employee.unit || '-') +
                                            (employee.blacklisted ? ' (' + @json(__('on the wellness blacklist')) + ')' : '');
                                        results.innerHTML = '';
                                    });
                                    results.appendChild(item);
                                });
                            });
                    }, 300);
                });
            }
        });
    </script>
@endpush
