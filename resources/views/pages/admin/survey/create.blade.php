@extends('layouts_.vertical', ['page_title' => 'Survey/Voting'])

@section('content')
<div class="container-fluid bg-white py-3 px-3">
    <form method="POST" action="{{ route('survey.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="card bg-light shadow">
            <div class="card-header">
                <h4 class="mb-0">{{ ucfirst($type) }} Info</h4>
            </div>
            <div class="card-body row g-3">
                <div class="col-md-12">
                    <label for="form_name" class="form-label">Form Name</label>
                    <input type="text" 
                           class="form-control @error('form_name') is-invalid @enderror" 
                           name="form_name" 
                           id="form_name" 
                           value="{{ old('form_name') }}" 
                           required>
                    <input type="hidden" name="survey_type" value="{{ $type }}">
                    @error('form_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4">
                    <label for="start_date" class="form-label">Start Date</label>
                    <input type="datetime-local" class="form-control" name="start_date" id="start_date" value="{{ old('start_date') }}" required>
                </div>
                <div class="col-md-4">
                    <label for="end_date" class="form-label">End Date</label>
                    <input type="datetime-local" class="form-control" name="end_date" id="end_date" value="{{ old('end_date') }}" required>
                </div>
                <div class="col-md-4">
                    <label for="related" class="form-label">Related to Event</label>
                    <select class="form-select" id="related" name="related">
                        <option disabled {{ old('related') ? '' : 'selected' }}>Please select</option>
                        @foreach($events as $event)
                            <option value="{{ $event->id }}" {{ old('related') == $event->id ? 'selected' : '' }}>
                                {{ $event->title }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-12">
                    <label for="description" class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="4" style="height:50px" id="description">{{ old('description') }}</textarea>
                </div>
                <div class="col-md-6">
                    <label for="banner" class="form-label">Banner</label>
                    <input type="file" name="banner" id="banner" class="form-control">
                    <small class="text-muted">Maximum file size 2MB</small>
                </div>
                <div class="col-md-6">
                    <label for="content_link" class="form-label">Link Youtube</label>
                    <div class="input-group">
                        <span class="input-group-text">https://www.youtube.com/watch?v=</span>
                        <input type="text" name="content_link" id="content_link" class="form-control" value="{{ old('content_link') }}">
                    </div>
                </div>
            </div>
        </div>
        {{-- Filter --}}
        <div class="card bg-light shadow mb-4 border-0">
            <div class="card-header">
                <h5 class="mb-0">Filter</h5>
            </div>
            <div class="card-body row g-3">
                <div class="col-md-4" style="{{ $type === 'vote' ? 'display: none;' : '' }}">
                    <label for="participants" class="form-label">Target Participants</label>
                    <input type="number" class="form-control" name="participants" id="participants" value="{{ old('participants', 0) }}">
                </div>
                <div class="col-md-4">
                    <label for="business_unit" class="form-label">Business Unit</label>
                    <select class="select2 form-control select2-multiple" 
                            name="business_unit[]" 
                            id="business_unit" 
                            data-toggle="select2" 
                            multiple="multiple" 
                            data-placeholder="Choose ...">
                        <option value="" disabled>Please select</option>
                        @foreach($bisnisunits as $bisnisunit)
                            <option value="{{ $bisnisunit }}" 
                                {{ (collect(old('business_unit'))->contains($bisnisunit)) ? 'selected' : '' }}>
                                {{ $bisnisunit }}
                            </option>
                        @endforeach
                    </select>
                    <small class="text-muted">Blank means it applies to every Business Unit.</small>
                </div>
                <div class="col-md-4">
                    <label for="unit" class="form-label">Unit</label>
                    <select class="select2 form-control select2-multiple" name="unit[]" id="unit" data-toggle="select2" multiple="multiple" data-placeholder="Choose ...">
                        <option value="" disabled>Please select</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->unit }}"
                                {{ collect(old('unit'))->contains($department->unit) ? 'selected' : '' }}>
                                {{ $department->unit." - ".$department->group_company." - ".$department->office_area }}
                            </option>
                        @endforeach
                    </select>
                    <small class="text-muted">Blank means it applies to every Unit.</small>
                </div>
                <div class="col-md-6">
                    <label for="job_level" class="form-label">Job Level</label>
                    <select class="select2 form-control select2-multiple" name="job_level[]" id="job_level" data-toggle="select2" multiple="multiple" data-placeholder="Choose ...">
                        <option value="" disabled>Please select</option>
                        @foreach($grades as $grade)
                            <option value="{{ $grade->group_name }}"
                                {{ collect(old('job_level'))->contains($grade->group_name) ? 'selected' : '' }}>
                                {{ $grade->group_name }}
                            </option>
                        @endforeach
                    </select>
                    <small class="text-muted">Blank means it applies to every Job Level.</small>
                </div>
                <div class="col-md-6">
                    <label for="location" class="form-label">Location</label>
                    <select class="select2 form-control select2-multiple" name="location[]" id="location" data-toggle="select2" multiple="multiple" data-placeholder="Choose ...">
                        <option value="" disabled>Please select</option>
                        @foreach($locations as $location)
                            <option value="{{ $location->work_area }}"
                                {{ collect(old('location'))->contains($location->work_area) ? 'selected' : '' }}>
                                {{ $location->area }}
                            </option>
                        @endforeach
                    </select>
                    <small class="text-muted">Blank means it applies to every Location.</small>
                </div>
            </div>
        </div>
        <div class="card bg-light shadow">
            <div class="card-header">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="custom_form" id="custom_form">
                    <label class="form-check-label" for="custom_form">
                        Custom Registration Form
                    </label>
                </div>
            </div>
            <div class="card-body">
                <strong>CUSTOM EVENT FORM BUILDER</strong>
                <div class="row">
                    <div class="col-md-4 d-none" id="form-select-wrapper">
                        <select class="form-select" id="form_id" name="form_id">
                            <option selected disabled>Please select</option>
                            @foreach($formTemplates as $form)
                                <option value="{{ $form->id }}">{{ $form->title." (".$form->created_at->format('d M Y').")" }}</option>
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
            <button type="submit" name="action" value="draft" class="btn btn-secondary me-2">Save as Draft</button>
            <button type="submit" name="action" value="create" class="btn btn-primary me-2">Create Event</button>
            <a href="{{ route('admin.events.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection