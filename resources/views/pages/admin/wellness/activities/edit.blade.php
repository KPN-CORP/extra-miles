@extends('layouts_.vertical', ['page_title' => __('Edit Wellness Activity')])

@section('css')
    @include('pages.admin.wellness.partials.page-css')
@endsection

@section('content')
<div class="container-fluid">
    {{-- Title, breadcrumb and back arrow come from layouts_/shared/topbar. --}}
    <div class="d-flex justify-content-end mb-2">
        <a href="{{ route('admin.wellness.schedules.index', $activity->encrypted_id) }}"
            class="btn btn-outline-primary btn-sm">
            <i class="ri-group-line me-1"></i> {{ __('Sessions & Participants') }}
        </a>
    </div>

    @include('pages.admin.wellness.partials.errors')

    {{-- Same body as the create screen: details and sessions in one submit. --}}
    <form action="{{ route('wellness.activities.update', $activity->encrypted_id) }}" method="POST"
        enctype="multipart/form-data" id="wa_form" novalidate>
        @csrf
        @method('PUT')

        @include('pages.admin.wellness.activities._tabbed-form', [
            'activity' => $activity,
            'schedules' => $schedules,
            'submitLabel' => __('Update Activity'),
        ])
    </form>
</div>
@endsection
