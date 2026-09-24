@extends('layouts_.vertical', ['page_title' => __('Events')])

@section('content')
<div class="container-fluid bg-white py-3 px-3">
    <form method="POST" action="{{ route('events.store') }}" enctype="multipart/form-data">
        @csrf
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <div class="card bg-light shadow">
            <div class="card-header">
                <h4 class="mb-0">{{ __('Event Info') }}</h4>
            </div>
            <div class="card-body row g-3">
                <div class="col-md-4">
                    <label for="category" class="form-label">{{ __('Category') }}</label>
                    <select class="form-select" id="category" name="category" required>
                        <option selected disabled>{{ __('Please select') }}</option>
                        <option value="Event">{{ __('Event') }}</option>
                        <option value="Sport">{{ __('Sport') }}</option>
                        <option value="Training">{{ __('Training') }}</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="start_date" class="form-label">{{ __('Start Date') }}</label>
                    <input type="datetime-local" class="form-control" name="start_date" id="start_date" required>
                </div>
                <div class="col-md-4">
                    <label for="end_date" class="form-label">{{ __('End Date') }}</label>
                    <input type="datetime-local" class="form-control" name="end_date" id="end_date" required>
                </div>
                <div class="col-md-12">
                    <label for="event_name" class="form-label">{{ __('Event Name') }}</label>
                    <input type="text" class="form-control" name="event_name" id="event_name" required>
                </div>
                <div class="col-md-12">
                    <label for="event_name" class="form-label">{{ __('Event Location') }}</label>
                    <input type="text" class="form-control" name="event_location" id="event_location" required>
                </div>
                <div class="col-md-12">
                    <label for="description" class="form-label">{{ __('Description') }}</label>
                    <textarea name="description" class="form-control" rows="4" style="height:50px" id="description"></textarea>
                </div>
                <div class="col-md-12">
                    <label for="banner" class="form-label">{{ __('Event Banner') }}</label>
                    <input type="file" name="banner" id="banner" class="form-control" required>
                    <small class="text-muted">{{ __('Maximum file size 2MB') }}</small>
                </div>
            </div>
        </div>
        {{-- Filter --}}
        <div class="card bg-light shadow mb-4 border-0">
            <div class="card-header">
                <h5 class="mb-0">{{ __('Filter') }}</h5>
            </div>
            <div class="card-body row g-3">
                <div class="col-md-3">
                    <input type="checkbox" class="form-check-input" id="need_survey" name="need_survey">
                    <label class="form-check-label" for="need_survey">{{ __('Does this event need a survey?') }}</label>
                </div>
                <div class="col-md-9">
                    <input type="checkbox" class="form-check-input" id="need_voting" name="need_voting">
                    <label class="form-check-label" for="need_voting">{{ __('Does this event need a voting?') }}</label>
                </div>
                <div class="col-md-4">
                    <label for="participants" class="form-label">{{ __('Target Participants') }} *</label>
                    <input type="number" class="form-control" name="participants" id="participants">
                </div>
                <div class="col-md-4">
                    <label for="registration_deadline" class="form-label">{{ __('Registration Deadline') }}</label>
                    <input type="date" class="form-control" name="registration_deadline" id="registration_deadline">
                </div>
                <div class="col-md-4">
                    <label for="business_unit" class="form-label">{{ __('Business Unit') }}</label>
                    <select class="select2 form-control select2-multiple" name="business_unit[]" id="business_unit" data-toggle="select2" multiple="multiple" data-placeholder="{{ __('Choose ...') }}">
                        <option value="" disabled>{{ __('Please select') }}</option>
                        @foreach($bisnisunits as $bisnisunit)
                            <option value="{{ $bisnisunit }}">{{ $bisnisunit }}</option>
                        @endforeach
                    </select>
                    <small class="text-muted">{{ __('Blank means it applies to every Business Unit.') }}</small>
                </div>
                <div class="col-md-4">
                    <label for="unit" class="form-label">{{ __('Unit') }}</label>
                    <select class="select2 form-control select2-multiple" name="unit[]" id="unit" data-toggle="select2" multiple="multiple" data-placeholder="{{ __('Choose ...') }}">
                        <option value="" disabled>{{ __('Please select') }}</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->unit }}">{{ $department->unit." - ".$department->group_company." - ".$department->office_area}}</option>
                        @endforeach
                    </select>
                    <small class="text-muted">{{ __('Blank means it applies to every Unit.') }}</small>
                </div>
                <div class="col-md-4">
                    <label for="job_level" class="form-label">{{ __('Job Level') }}</label>
                    <select class="select2 form-control select2-multiple" name="job_level[]" id="job_level" data-toggle="select2" multiple="multiple" data-placeholder="{{ __('Choose ...') }}">
                        <option value="" disabled>{{ __('Please select') }}</option>
                        @foreach($grades as $grade)
                            <option value="{{ $grade->group_name }}">{{ $grade->group_name }}</option>
                        @endforeach
                    </select>
                    <small class="text-muted">{{ __('Blank means it applies to every Job Level.') }}</small>
                </div>
                <div class="col-md-4">
                    <label for="location" class="form-label">{{ __('Location') }}</label>
                    <select class="select2 form-control select2-multiple" name="location[]" id="location" data-toggle="select2" multiple="multiple" data-placeholder="{{ __('Choose ...') }}">
                        <option value="" disabled>{{ __('Please select') }}</option>
                        @foreach($locations as $location)
                            <option value="{{ $location->office_area }}">{{ $location->office_area." (".$location->group_company.")" }}</option>
                        @endforeach
                    </select>
                    <small class="text-muted">{{ __('Blank means it applies to every Location.') }}</small>
                </div>
            </div>
        </div>
        <div class="card bg-light shadow">
            <div class="card-header">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="custom_form" id="custom_form">
                    <label class="form-check-label" for="custom_form">
                        {{ __('Custom Registration Form') }}
                    </label>
                </div>
            </div>
            <div class="card-body">
                <strong>{{ __('CUSTOM EVENT FORM BUILDER') }}</strong>
                <div class="row">
                    <div class="col-md-4 d-none" id="form-select-wrapper">
                        <select class="form-select" id="form_id" name="form_id">
                            <option selected disabled>{{ __('Please select') }}</option>
                            @foreach($formTemplates as $form)
                                <option value="{{ $form->id }}">{{ $form->title." (".$form->created_at->translatedFormat('d M Y').")" }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-8 d-none" id="form-preview-wrapper">
                        <div id="form-preview" class="bg-white p-3 rounded"></div>
                    </div>
                </div>
            </div>                
        </div>

        {{-- Buttons --}}
        <div class="d-flex justify-content-end mb-4">
            <button type="submit" name="action" value="draft" class="btn btn-secondary me-2">{{ __('Save as Draft') }}</button>
            <button type="submit" name="action" value="create" class="btn btn-primary me-2">{{ __('Create Event') }}</button>
            <a href="{{ route('admin.events.index') }}" class="btn btn-outline-secondary">{{ __('Cancel') }}</a>
        </div>
    </form>
</div>
@endsection