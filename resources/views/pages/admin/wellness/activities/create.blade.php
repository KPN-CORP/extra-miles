@extends('layouts_.vertical', ['page_title' => __('Create Wellness Activity')])

@section('css')
    @include('pages.admin.wellness.partials.page-css')
@endsection

@section('content')
<div class="container-fluid">
    {{-- The page title, breadcrumb and back arrow are rendered by
         layouts_/shared/topbar from $link / $parentLink / $back, so this page
         does not repeat them. --}}

    @include('pages.admin.wellness.partials.errors')

    {{-- One form around both tabs, so a single submit creates the activity and
         its sessions together. The body is shared with the edit screen. --}}
    <form action="{{ route('wellness.activities.store') }}" method="POST"
        enctype="multipart/form-data" id="wa_form" novalidate>
        @csrf

        @include('pages.admin.wellness.activities._tabbed-form', [
            'activity' => null,
            'schedules' => collect(),
            'submitLabel' => __('Save Activity'),
        ])
    </form>
</div>
@endsection
