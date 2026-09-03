@extends('layouts_.vertical', ['page_title' => 'Edit Wellness Activity'])

@section('css')
    @include('pages.admin.wellness.partials.page-css')
@endsection

@section('content')
<div class="container-fluid">
    {{-- Title, breadcrumb and back arrow come from layouts_/shared/topbar. --}}
    <div class="d-flex justify-content-end mb-2">
        <a href="{{ route('admin.wellness.schedules.index', $activity->encrypted_id) }}" class="btn btn-outline-primary btn-sm">
            <i class="ri-calendar-line me-1"></i> Manage Schedules
        </a>
    </div>

    @include('pages.admin.wellness.partials.errors')

    <form action="{{ route('wellness.activities.update', $activity->encrypted_id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        @include('pages.admin.wellness.activities._form', ['activity' => $activity])

        <div class="d-flex justify-content-between align-items-center gap-2 mt-3">
            <span class="text-muted small">
                <i class="ri-time-line me-1"></i>Last updated {{ $activity->updated_at?->diffForHumans() }}
            </span>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.wellness.activities.index') }}" class="btn btn-light">Cancel</a>
                <button type="submit" class="btn btn-primary">Update Activity</button>
            </div>
        </div>
    </form>
</div>
@endsection
