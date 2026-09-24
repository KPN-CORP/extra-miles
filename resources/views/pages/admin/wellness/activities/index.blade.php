@extends('layouts_.vertical', ['page_title' => __('Wellness Activities')])

@section('css')
    @include('layouts_.shared.admin-datatable-css')
    @include('pages.admin.wellness.partials.page-css')
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h4 class="page-title mb-0">{{ __('Wellness Activities') }}</h4>
        <div class="d-flex gap-2">
            @can('viewmenuwellnesstype')
                <a href="{{ route('admin.wellness.types.index') }}" class="btn btn-outline-secondary">
                    <i class="ri-price-tag-3-line me-1"></i> {{ __('Activity Types') }}
                </a>
            @endcan
            <a href="{{ route('wellness.activities.create') }}" class="btn btn-primary">
                <i class="ri-add-line me-1"></i> {{ __('Create Activity') }}
            </a>
        </div>
    </div>

    @include('pages.admin.wellness.partials.errors')

    @include('pages.admin.wellness.activities._filters')

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <ul class="nav nav-tabs mb-3" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#activity-active" type="button" role="tab">
                                {{ __('Active') }}
                                <span class="badge {{ $filtersActive ? 'bg-primary' : 'bg-secondary' }} ms-1">{{ $activities->count() }}</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#activity-archive" type="button" role="tab">
                                {{ __('Archive') }} <span class="badge bg-secondary ms-1">{{ $archived->count() }}</span>
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="activity-active" role="tabpanel">
                            {{-- Rendered instead of the table, not inside it: a DataTable
                                 needs one cell per column in every body row, so a spanning
                                 "nothing here" row trips its column-count check. --}}
                            @if ($activities->isEmpty())
                                <div class="text-center text-muted border border-dashed rounded py-5">
                                    @if ($filtersActive)
                                        <i class="ri-filter-off-line fs-2 d-block mb-2"></i>
                                        <div class="fw-semibold">{{ __('No activities match these filters') }}</div>
                                        <a href="{{ route('admin.wellness.activities.index') }}"
                                            class="btn btn-sm btn-outline-secondary mt-3">
                                            <i class="ri-close-line me-1"></i>{{ __('Reset') }}
                                        </a>
                                    @else
                                        <i class="ri-heart-pulse-line fs-2 d-block mb-2"></i>
                                        <div class="fw-semibold">{{ __('No activities yet') }}</div>
                                        <a href="{{ route('wellness.activities.create') }}"
                                            class="btn btn-sm btn-primary mt-3">
                                            <i class="ri-add-line me-1"></i>{{ __('Create Activity') }}
                                        </a>
                                    @endif
                                </div>
                            @else
                            <div class="table-responsive">
                                <table class="table table-hover table-sm nowrap w-100 align-middle js-datatable">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="no-sort">{{ __('No') }}</th>
                                            <th>{{ __('Activity') }}</th>
                                            <th>{{ __('Type') }}</th>
                                            <th>{{ __('Method') }}</th>
                                            <th>{{ __('Schedules') }}</th>
                                            <th>{{ __('Status') }}</th>
                                            <th>{{ __('Created') }}</th>
                                            <th class="no-sort">{{ __('Action') }}</th>
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
                                                <td data-order="{{ $activity->schedules_count }}">
                                                    @if ($activity->schedules_count)
                                                        {{ __(':count session(s)', ['count' => $activity->schedules_count]) }}
                                                    @else
                                                        <span class="text-muted">{{ __('None') }}</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="badge {{ $activity->status->badgeClass() }}">{{ $activity->status->label() }}</span>
                                                </td>
                                                <td>{{ $activity->created_at?->translatedFormat('d M Y') }}</td>
                                                <td>
                                                    <a href="{{ route('admin.wellness.schedules.index', $activity->encrypted_id) }}"
                                                        class="btn btn-outline-primary btn-sm" title="{{ __('Manage schedules') }}">
                                                        <i class="ri-calendar-line"></i>
                                                    </a>
                                                    <a href="{{ route('wellness.activities.edit', $activity->encrypted_id) }}"
                                                        class="btn btn-outline-warning btn-sm" title="{{ __('Edit') }}">
                                                        <i class="ri-edit-box-line"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-outline-danger btn-sm js-archive"
                                                        data-form="archive-activity-{{ $activity->id }}"
                                                        data-text="{{ __('This activity and its schedules will be archived.') }}">
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
                            @endif

                        </div>

                        <div class="tab-pane fade" id="activity-archive" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-hover table-sm nowrap w-100 align-middle js-datatable">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="no-sort">{{ __('No') }}</th>
                                            <th>{{ __('Activity') }}</th>
                                            <th>{{ __('Type') }}</th>
                                            <th>{{ __('Archived At') }}</th>
                                            <th class="no-sort">{{ __('Action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($archived as $activity)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $activity->name }}</td>
                                                <td>{{ $activity->type?->name ?? '-' }}</td>
                                                <td>{{ $activity->deleted_at?->translatedFormat('d M Y H:i') }}</td>
                                                <td>
                                                    <form action="{{ route('wellness.activities.restore', $activity->encrypted_id) }}" method="POST">
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
</div>
@endsection

@push('scripts')
    @include('layouts_.shared.admin-datatable-js')
    @include('pages.admin.wellness.partials.confirm-js')

@endpush
