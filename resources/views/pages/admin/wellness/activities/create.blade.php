@extends('layouts_.vertical', ['page_title' => 'Create Wellness Activity'])

@section('css')
    @include('pages.admin.wellness.partials.page-css')
@endsection

@section('content')
<div class="container-fluid">
    {{-- The page title, breadcrumb and back arrow are rendered by
         layouts_/shared/topbar from $link / $parentLink / $back, so this page
         does not repeat them. --}}

    @include('pages.admin.wellness.partials.errors')

    <form action="{{ route('wellness.activities.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        @include('pages.admin.wellness.activities._form', ['activity' => null])

        <div class="d-flex justify-content-between align-items-center gap-2 mt-3">
            <span class="text-muted small">
                <i class="ri-information-line me-1"></i>You will add the sessions next.
            </span>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.wellness.activities.index') }}" class="btn btn-light">Cancel</a>
                <button type="submit" class="btn btn-primary" @disabled($types->isEmpty())>
                    Save &amp; Add Schedules <i class="ri-arrow-right-line ms-1"></i>
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
