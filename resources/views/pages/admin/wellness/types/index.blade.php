@extends('layouts_.vertical', ['page_title' => __('Wellness Activity Types')])

@section('css')
    @include('layouts_.shared.admin-datatable-css')
    @include('pages.admin.wellness.partials.page-css')
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h4 class="page-title mb-0">{{ __('Wellness Activity Types') }}</h4>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createTypeModal">
            <i class="ri-add-line me-1"></i> {{ __('Create Type') }}
        </button>
    </div>

    @include('pages.admin.wellness.partials.errors')

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <ul class="nav nav-tabs mb-3" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#type-active" type="button" role="tab">
                                {{ __('Active') }} <span class="badge bg-secondary ms-1">{{ $types->count() }}</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#type-archive" type="button" role="tab">
                                {{ __('Archive') }} <span class="badge bg-secondary ms-1">{{ $archived->count() }}</span>
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="type-active" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-hover table-sm nowrap w-100 align-middle js-datatable">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="no-sort">{{ __('No') }}</th>
                                            <th>{{ __('Name') }}</th>
                                            <th>{{ __('Description') }}</th>
                                            <th>{{ __('Activities') }}</th>
                                            <th>{{ __('Scan Window') }}</th>
                                            <th>{{ __('Status') }}</th>
                                            <th class="no-sort">{{ __('Action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($types as $type)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $type->name }}</td>
                                                <td>{{ \Illuminate\Support\Str::limit($type->description, 80) ?: '-' }}</td>
                                                <td>{{ $type->activities_count }}</td>
                                                <td>
                                                    <span class="text-muted small">
                                                        &minus;{{ $type->checkInOpensMinutesBefore() }}' / +{{ $type->checkInClosesMinutesAfter() }}'
                                                    </span>
                                                    @if($type->check_in_opens_minutes_before === null && $type->check_in_closes_minutes_after === null)
                                                        <span class="badge bg-light text-muted ms-1">{{ __('Default') }}</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="badge {{ $type->is_active ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }}">
                                                        {{ $type->is_active ? __('Active') : __('Inactive') }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="{{ route('wellness.types.qr', $type->encrypted_id) }}" target="_blank"
                                                        class="btn btn-outline-dark btn-sm" title="{{ __('Attendance QR') }}">
                                                        <i class="ri-qr-code-line"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-outline-secondary btn-sm js-confirm"
                                                        data-form="rotate-qr-{{ $type->id }}"
                                                        title="{{ __('Rotate QR') }}"
                                                        data-text="{{ __('A new QR code will be generated. Every printed copy of the current code will stop working.') }}">
                                                        <i class="ri-refresh-line"></i>
                                                    </button>
                                                    <form id="rotate-qr-{{ $type->id }}" class="d-none"
                                                        action="{{ route('wellness.types.rotateQr', $type->encrypted_id) }}" method="POST">
                                                        @csrf
                                                    </form>
                                                    <button type="button"
                                                        class="btn btn-outline-warning btn-sm js-edit-type"
                                                        data-url="{{ route('wellness.types.update', $type->encrypted_id) }}"
                                                        data-name="{{ $type->name }}"
                                                        data-description="{{ $type->description }}"
                                                        data-active="{{ $type->is_active ? 1 : 0 }}"
                                                        data-before="{{ $type->check_in_opens_minutes_before }}"
                                                        data-after="{{ $type->check_in_closes_minutes_after }}"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#editTypeModal">
                                                        <i class="ri-edit-box-line"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-outline-danger btn-sm js-archive"
                                                        data-form="archive-type-{{ $type->id }}"
                                                        data-text="{{ __('This activity type will be archived.') }}">
                                                        <i class="ri-archive-line"></i>
                                                    </button>
                                                    <form id="archive-type-{{ $type->id }}" class="d-none"
                                                        action="{{ route('wellness.types.archive', $type->encrypted_id) }}" method="POST">
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

                        <div class="tab-pane fade" id="type-archive" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-hover table-sm nowrap w-100 align-middle js-datatable">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="no-sort">{{ __('No') }}</th>
                                            <th>{{ __('Name') }}</th>
                                            <th>{{ __('Description') }}</th>
                                            <th>{{ __('Archived At') }}</th>
                                            <th class="no-sort">{{ __('Action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($archived as $type)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $type->name }}</td>
                                                <td>{{ \Illuminate\Support\Str::limit($type->description, 80) ?: '-' }}</td>
                                                <td>{{ $type->deleted_at?->format('d M Y H:i') }}</td>
                                                <td>
                                                    <form action="{{ route('wellness.types.restore', $type->encrypted_id) }}" method="POST">
                                                        @csrf
                                                        <button type="submit" class="btn btn-outline-secondary btn-sm">
                                                            <i class="ri-arrow-go-back-line"></i> {{ __('Restore') }}
                                                        </button>
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
        </div>
    </div>

    {{-- Create --}}
    <div class="modal fade" id="createTypeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('wellness.types.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('Create Activity Type') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="create-type-name" class="form-label">{{ __('Name') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="create-type-name" name="name" maxlength="100" required>
                        </div>
                        <div class="mb-3">
                            <label for="create-type-description" class="form-label">{{ __('Description') }}</label>
                            <textarea class="form-control" id="create-type-description" name="description" rows="3"></textarea>
                        </div>

                        <div class="row g-2">
                            <div class="col-6">
                                <label for="create-type-before" class="form-label">{{ __('Scan opens (min before start)') }}</label>
                                <input type="number" class="form-control" id="create-type-before"
                                    name="check_in_opens_minutes_before" min="0" max="1440"
                                    placeholder="{{ config('wellness.check_in.opens_minutes_before') }}">
                            </div>
                            <div class="col-6">
                                <label for="create-type-after" class="form-label">{{ __('Scan closes (min after end)') }}</label>
                                <input type="number" class="form-control" id="create-type-after"
                                    name="check_in_closes_minutes_after" min="0" max="1440"
                                    placeholder="{{ config('wellness.check_in.closes_minutes_after') }}">
                            </div>
                            <div class="col-12">
                                <div class="form-text">
                                    {{ __('How long before and after a session the attendance QR of this type accepts a scan. Leave blank to follow the system default.') }}
                                </div>
                            </div>
                        </div>

                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="create-type-active" name="is_active" value="1" checked>
                            <label class="form-check-label" for="create-type-active">{{ __('Active') }}</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Edit --}}
    <div class="modal fade" id="editTypeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="" id="editTypeForm">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('Edit Activity Type') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="edit-type-name" class="form-label">{{ __('Name') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit-type-name" name="name" maxlength="100" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit-type-description" class="form-label">{{ __('Description') }}</label>
                            <textarea class="form-control" id="edit-type-description" name="description" rows="3"></textarea>
                        </div>

                        <div class="row g-2">
                            <div class="col-6">
                                <label for="edit-type-before" class="form-label">{{ __('Scan opens (min before start)') }}</label>
                                <input type="number" class="form-control" id="edit-type-before"
                                    name="check_in_opens_minutes_before" min="0" max="1440"
                                    placeholder="{{ config('wellness.check_in.opens_minutes_before') }}">
                            </div>
                            <div class="col-6">
                                <label for="edit-type-after" class="form-label">{{ __('Scan closes (min after end)') }}</label>
                                <input type="number" class="form-control" id="edit-type-after"
                                    name="check_in_closes_minutes_after" min="0" max="1440"
                                    placeholder="{{ config('wellness.check_in.closes_minutes_after') }}">
                            </div>
                            <div class="col-12">
                                <div class="form-text">
                                    {{ __('How long before and after a session the attendance QR of this type accepts a scan. Leave blank to follow the system default.') }}
                                </div>
                            </div>
                        </div>

                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="edit-type-active" name="is_active" value="1">
                            <label class="form-check-label" for="edit-type-active">{{ __('Active') }}</label>
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
            document.querySelectorAll('.js-edit-type').forEach(function (button) {
                button.addEventListener('click', function () {
                    document.getElementById('editTypeForm').action = this.dataset.url;
                    document.getElementById('edit-type-name').value = this.dataset.name;
                    document.getElementById('edit-type-description').value = this.dataset.description || '';
                    document.getElementById('edit-type-active').checked = this.dataset.active === '1';
                    document.getElementById('edit-type-before').value = this.dataset.before || '';
                    document.getElementById('edit-type-after').value = this.dataset.after || '';
                });
            });
        });
    </script>
@endpush
