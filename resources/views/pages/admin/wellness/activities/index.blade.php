@extends('layouts_.vertical', ['page_title' => 'Wellness Activities'])

@section('css')
    @include('layouts_.shared.admin-datatable-css')
    @include('pages.admin.wellness.partials.page-css')
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h4 class="page-title mb-0">Wellness Activities</h4>
        <div class="d-flex gap-2">
            @can('viewmenuwellnesstype')
                <a href="{{ route('admin.wellness.types.index') }}" class="btn btn-outline-secondary">
                    <i class="ri-price-tag-3-line me-1"></i> Activity Types
                </a>
            @endcan
            <a href="{{ route('wellness.activities.create') }}" class="btn btn-primary">
                <i class="ri-add-line me-1"></i> Create Activity
            </a>
        </div>
    </div>

    @include('pages.admin.wellness.partials.errors')

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <ul class="nav nav-tabs mb-3" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#activity-active" type="button" role="tab">
                                Active <span class="badge bg-secondary ms-1">{{ $activities->count() }}</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#activity-archive" type="button" role="tab">
                                Archive <span class="badge bg-secondary ms-1">{{ $archived->count() }}</span>
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="activity-active" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-hover table-sm nowrap w-100 align-middle js-datatable">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="no-sort">No</th>
                                            <th>Activity</th>
                                            <th>Type</th>
                                            <th>Method</th>
                                            <th>Schedules</th>
                                            <th>Status</th>
                                            <th>Created</th>
                                            <th class="no-sort">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($activities as $activity)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>
                                                    <div class="fw-semibold">{{ $activity->name }}</div>
                                                    <small class="text-muted">{{ \Illuminate\Support\Str::limit(strip_tags($activity->description), 60) }}</small>
                                                </td>
                                                <td>{{ $activity->type?->name ?? '-' }}</td>
                                                <td>
                                                    <span class="badge {{ $activity->registration_method->badgeClass() }}">
                                                        <i class="{{ $activity->registration_method->icon() }} me-1"></i>{{ $activity->registration_method->shortLabel() }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="{{ route('admin.wellness.schedules.index', $activity->encrypted_id) }}">
                                                        {{ $activity->schedules_count }} session(s)
                                                    </a>
                                                </td>
                                                <td>
                                                    <span class="badge {{ $activity->status->badgeClass() }}">{{ $activity->status->label() }}</span>
                                                </td>
                                                <td>{{ $activity->created_at?->format('d M Y') }}</td>
                                                <td>
                                                    <a href="{{ route('admin.wellness.schedules.index', $activity->encrypted_id) }}"
                                                        class="btn btn-outline-primary btn-sm" title="Manage schedules">
                                                        <i class="ri-calendar-line"></i>
                                                    </a>
                                                    <a href="{{ route('wellness.activities.edit', $activity->encrypted_id) }}"
                                                        class="btn btn-outline-warning btn-sm" title="Edit">
                                                        <i class="ri-edit-box-line"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-outline-danger btn-sm js-archive"
                                                        data-form="archive-activity-{{ $activity->id }}"
                                                        data-text="This activity and its schedules will be archived.">
                                                        <i class="ri-archive-line"></i>
                                                    </button>
                                                    <form id="archive-activity-{{ $activity->id }}" class="d-none"
                                                        action="{{ route('wellness.activities.archive', $activity->encrypted_id) }}" method="POST">
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

                        <div class="tab-pane fade" id="activity-archive" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-hover table-sm nowrap w-100 align-middle js-datatable">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="no-sort">No</th>
                                            <th>Activity</th>
                                            <th>Type</th>
                                            <th>Archived At</th>
                                            <th class="no-sort">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($archived as $activity)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $activity->name }}</td>
                                                <td>{{ $activity->type?->name ?? '-' }}</td>
                                                <td>{{ $activity->deleted_at?->format('d M Y H:i') }}</td>
                                                <td>
                                                    <form action="{{ route('wellness.activities.restore', $activity->encrypted_id) }}" method="POST">
                                                        @csrf
                                                        <button type="submit" class="btn btn-outline-secondary btn-sm">
                                                            <i class="ri-arrow-go-back-line"></i> Restore
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
</div>
@endsection

@push('scripts')
    @include('layouts_.shared.admin-datatable-js')
    @include('pages.admin.wellness.partials.confirm-js')
@endpush
