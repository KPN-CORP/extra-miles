@extends('layouts_.vertical', ['page_title' => __('Wellness Blacklist')])

@section('css')
    @include('layouts_.shared.admin-datatable-css')
    @include('pages.admin.wellness.partials.page-css')
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-end mb-2">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createBlacklistModal">
            <i class="ri-user-forbid-line me-1"></i> {{ __('Add to Blacklist') }}
        </button>
    </div>

    @include('pages.admin.wellness.partials.errors')

    <div class="alert alert-light border d-flex align-items-start gap-2" role="alert">
        <i class="ri-information-line mt-1"></i>
        <div class="small mb-0">
            {{ __('Blacklisted employees can still register for wellness sessions, but the system never confirms their seat automatically — an admin decides each time. An entry with no end date stays in force until it is lifted.') }}
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <ul class="nav nav-tabs mb-3" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#bl-active" type="button" role="tab">
                                {{ __('Active') }} <span class="badge bg-secondary ms-1">{{ $active->count() }}</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#bl-expired" type="button" role="tab">
                                {{ __('Expired') }} <span class="badge bg-secondary ms-1">{{ $expired->count() }}</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#bl-archive" type="button" role="tab">
                                {{ __('Archive') }} <span class="badge bg-secondary ms-1">{{ $archived->count() }}</span>
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content">
                        {{-- Active --}}
                        <div class="tab-pane fade show active" id="bl-active" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-hover table-sm nowrap w-100 align-middle js-datatable">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="no-sort">{{ __('No') }}</th>
                                            <th>{{ __('Employee Name') }}</th>
                                            <th>{{ __('Employee ID') }}</th>
                                            <th>{{ __('Reason') }}</th>
                                            <th>{{ __('End Date') }}</th>
                                            <th>{{ __('Added') }}</th>
                                            <th class="no-sort">{{ __('Action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($active as $entry)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td class="fw-semibold">{{ $entry->fullname ?: '-' }}</td>
                                                <td>{{ $entry->employee_id }}</td>
                                                <td>{{ \Illuminate\Support\Str::limit($entry->reason, 80) }}</td>
                                                <td>
                                                    @if ($entry->end_date)
                                                        <span class="badge bg-warning-subtle text-warning">
                                                            {{ __('until :date', ['date' => $entry->end_date->translatedFormat('d M Y')]) }}
                                                        </span>
                                                    @else
                                                        <span class="badge bg-dark-subtle text-dark">{{ __('No expiry') }}</span>
                                                    @endif
                                                </td>
                                                <td>{{ $entry->created_at?->translatedFormat('d M Y') }}</td>
                                                <td>
                                                    <button type="button" class="btn btn-outline-warning btn-sm js-edit-blacklist"
                                                        data-url="{{ route('wellness.blacklist.update', $entry->encrypted_id) }}"
                                                        data-employee="{{ $entry->employee_id }}"
                                                        data-fullname="{{ $entry->fullname }}"
                                                        data-reason="{{ $entry->reason }}"
                                                        data-end="{{ $entry->end_date?->format('Y-m-d') }}"
                                                        data-bs-toggle="modal" data-bs-target="#editBlacklistModal" title="{{ __('Edit') }}">
                                                        <i class="ri-edit-box-line"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-outline-success btn-sm js-confirm"
                                                        data-form="lift-{{ $entry->id }}"
                                                        data-title="{{ __('Lift this blacklist?') }}"
                                                        data-text="{{ __(':name will be able to be confirmed automatically again.', ['name' => $entry->fullname ?: $entry->employee_id]) }}"
                                                        data-confirm="{{ __('Yes, lift it') }}" title="{{ __('Lift') }}">
                                                        <i class="ri-shield-check-line"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-outline-danger btn-sm js-archive"
                                                        data-form="archive-bl-{{ $entry->id }}"
                                                        data-text="{{ __('This blacklist entry will be archived.') }}" title="{{ __('Archive') }}">
                                                        <i class="ri-archive-line"></i>
                                                    </button>

                                                    <form id="lift-{{ $entry->id }}" class="d-none"
                                                        action="{{ route('wellness.blacklist.lift', $entry->encrypted_id) }}" method="POST">
                                                        @csrf
                                                    </form>
                                                    <form id="archive-bl-{{ $entry->id }}" class="d-none"
                                                        action="{{ route('wellness.blacklist.archive', $entry->encrypted_id) }}" method="POST">
                                                        @csrf
                                                        @method('DELETE')
                                                    </form>
                                                </td>
                                            </tr>
                                        @empty
                                            {{-- DataTables renders its own empty-table row. --}}
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Expired --}}
                        <div class="tab-pane fade" id="bl-expired" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-hover table-sm nowrap w-100 align-middle js-datatable">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="no-sort">{{ __('No') }}</th>
                                            <th>{{ __('Employee Name') }}</th>
                                            <th>{{ __('Employee ID') }}</th>
                                            <th>{{ __('Reason') }}</th>
                                            <th>{{ __('Ended') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($expired as $entry)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td class="fw-semibold">{{ $entry->fullname ?: '-' }}</td>
                                                <td>{{ $entry->employee_id }}</td>
                                                <td>{{ \Illuminate\Support\Str::limit($entry->reason, 80) }}</td>
                                                <td>{{ $entry->end_date?->translatedFormat('d M Y') }}</td>
                                            </tr>
                                        @empty
                                            {{-- DataTables renders its own empty-table row. --}}
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Archive --}}
                        <div class="tab-pane fade" id="bl-archive" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-hover table-sm nowrap w-100 align-middle js-datatable">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="no-sort">{{ __('No') }}</th>
                                            <th>{{ __('Employee Name') }}</th>
                                            <th>{{ __('Employee ID') }}</th>
                                            <th>{{ __('Reason') }}</th>
                                            <th>{{ __('Archived At') }}</th>
                                            <th class="no-sort">{{ __('Action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($archived as $entry)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td class="fw-semibold">{{ $entry->fullname ?: '-' }}</td>
                                                <td>{{ $entry->employee_id }}</td>
                                                <td>{{ \Illuminate\Support\Str::limit($entry->reason, 80) }}</td>
                                                <td>{{ $entry->deleted_at?->translatedFormat('d M Y H:i') }}</td>
                                                <td>
                                                    <form action="{{ route('wellness.blacklist.restore', $entry->encrypted_id) }}" method="POST">
                                                        @csrf
                                                        <button type="submit" class="btn btn-outline-secondary btn-sm">
                                                            <i class="ri-arrow-go-back-line"></i> {{ __('Restore') }}
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @empty
                                            {{-- DataTables renders its own empty-table row. --}}
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Create --}}
    <div class="modal fade" id="createBlacklistModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('wellness.blacklist.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('Add to Blacklist') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3 position-relative">
                            <label for="bl-search" class="form-label">{{ __('Employee') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="bl-search"
                                placeholder="{{ __('Search by name or employee ID') }}" autocomplete="off">
                            <input type="hidden" name="employee_id" id="bl-employee-id">
                            <div id="bl-results" class="list-group position-absolute w-100 shadow"
                                style="z-index: 1056; max-height: 240px; overflow-y: auto;"></div>
                            <div class="form-text" id="bl-selected">{{ __('No employee selected yet.') }}</div>
                        </div>
                        <div class="mb-3">
                            <label for="bl-reason" class="form-label">{{ __('Blacklist Reason') }} <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="bl-reason" name="reason" rows="3" required
                                placeholder="{{ __('e.g. Confirmed a seat twice and did not attend.') }}"></textarea>
                        </div>
                        <div class="mb-0">
                            <label for="bl-end" class="form-label">{{ __('Blacklist End Date') }}</label>
                            <input type="date" class="form-control" id="bl-end" name="end_date"
                                min="{{ now()->toDateString() }}">
                            <div class="form-text">{{ __('Optional. Leave empty for a blacklist that does not expire.') }}</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary">{{ __('Add') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Edit --}}
    <div class="modal fade" id="editBlacklistModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="" id="editBlacklistForm">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('Edit Blacklist Entry') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">{{ __('Employee') }}</label>
                            <input type="text" class="form-control" id="edit-bl-name" disabled>
                            <input type="hidden" name="employee_id" id="edit-bl-employee-id">
                        </div>
                        <div class="mb-3">
                            <label for="edit-bl-reason" class="form-label">{{ __('Blacklist Reason') }} <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="edit-bl-reason" name="reason" rows="3" required></textarea>
                        </div>
                        <div class="mb-0">
                            <label for="edit-bl-end" class="form-label">{{ __('Blacklist End Date') }}</label>
                            <input type="date" class="form-control" id="edit-bl-end" name="end_date"
                                min="{{ now()->toDateString() }}">
                            <div class="form-text">{{ __('Leave empty for a blacklist that does not expire.') }}</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary">{{ __('Update') }}</button>
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
            document.querySelectorAll('.js-edit-blacklist').forEach(function (button) {
                button.addEventListener('click', function () {
                    var d = this.dataset;
                    document.getElementById('editBlacklistForm').action = d.url;
                    document.getElementById('edit-bl-name').value = (d.fullname || '') + ' (' + d.employee + ')';
                    document.getElementById('edit-bl-employee-id').value = d.employee;
                    document.getElementById('edit-bl-reason').value = d.reason || '';
                    document.getElementById('edit-bl-end').value = d.end || '';
                });
            });

            // Employee typeahead, shared with the participants screen.
            var search = document.getElementById('bl-search');
            var results = document.getElementById('bl-results');
            var hidden = document.getElementById('bl-employee-id');
            var selected = document.getElementById('bl-selected');
            var timer = null;

            if (!search) {
                return;
            }

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
                                        ? ' <span class="badge bg-dark-subtle text-dark">' + @json(__('Already blacklisted')) + '</span>'
                                        : '');
                                item.addEventListener('click', function () {
                                    hidden.value = employee.employee_id;
                                    search.value = employee.fullname;
                                    selected.textContent = @json(__('Selected')) + ': ' + employee.fullname +
                                        ' - ' + (employee.group_company || '-') + ' / ' + (employee.unit || '-');
                                    results.innerHTML = '';
                                });
                                results.appendChild(item);
                            });
                        });
                }, 300);
            });
        });
    </script>
@endpush
