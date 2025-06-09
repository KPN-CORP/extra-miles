@extends('layouts_.vertical', ['page_title' => 'Survey/Voting'])

@section('content')
<div class="container-fluid bg-white py-3 px-3">
    <form method="POST" action="{{ route('survey.update', $survey->id) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="card bg-light shadow">
            <div class="card-header">
                <h4 class="mb-0">{{ ucfirst($link) }}</h4>
            </div>
            <div class="card-body row g-3">
                <div class="col-md-12">
                    <label for="form_name" class="form-label">Form Name</label>
                    <input type="text" class="form-control" value="{{ old('form_name', $survey->title) }}" name="form_name" id="form_name" required>
                </div>
                <div class="col-md-4">
                    <label for="start_date" class="form-label">Start Date</label>
                    <input type="datetime-local" class="form-control" value="{{ old('start_date', $survey->start_date." ".$survey->time_start) }}" name="start_date" id="start_date" required>
                </div>
                <div class="col-md-4">
                    <label for="end_date" class="form-label">End Date</label>
                    <input type="datetime-local" class="form-control" value="{{ old('end_date', $survey->end_date." ".$survey->time_end) }}" name="end_date" id="end_date" required>
                </div>
                <div class="col-md-4">
                    <label for="related" class="form-label">Related to Event</label>
                    <select class="form-select" id="related" name="related">
                        <option selected disabled>Please select</option>
                        @foreach($events as $event)
                            <option value="{{ $event->id }}" @if($survey->event_id === $event->id) selected @endif>{{ $event->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-12">
                    <label for="description" class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="4" style="height:50px" id="description">
                        {{ old('description', $survey->description ?? '') }}
                    </textarea>
                </div>
                <div class="col-md-12">
                    <label for="banner" class="form-label">Banner</label>
                    <input type="file" name="banner" id="banner" class="form-control">
                    <small class="text-muted">Maximum file size 2MB</small>
                    @if($survey->banner)
                        <!-- Button trigger modal -->
                        <br><button type="button" class="btn btn-primary btn-sm mt-2" data-bs-toggle="modal" data-bs-target="#viewBannerModal">
                            View Full Banner
                        </button>

                        <!-- Modal -->
                        <div class="modal fade" id="viewBannerModal" tabindex="-1" aria-labelledby="viewBannerModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="viewBannerModalLabel">Banner Preview</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body text-center">
                                        <img src="{{ asset('storage/' . $survey->banner) }}" alt="Full Banner" class="img-fluid">
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        {{-- Filter --}}
        <div class="card bg-light shadow mb-4 border-0">
            <div class="card-header">
                <h5 class="mb-0">Filter</h5>
            </div>
            <div class="card-body row g-3">
                <div class="col-md-4">
                    <label for="participants" class="form-label">Target Participants</label>
                    <input type="number" class="form-control" name="participants" id="participants" value="{{ old('participants', $survey->quota) }}">
                </div>
                <div class="col-md-4">
                    <label for="business_unit" class="form-label">Business Unit</label>
                    <select class="select2 form-control select2-multiple" name="business_unit[]" id="business_unit" data-toggle="select2" multiple="multiple" data-placeholder="Choose ...">
                        <option value="" disabled>Please select</option>
                        @php
                            $selectedBU = old('business_unit', (array) $survey->businessUnit);
                        @endphp
                        @foreach($bisnisunits as $bu)
                            <option value="{{ $bu }}" {{ in_array($bu, (array) $selectedBU) ? 'selected' : '' }}>{{ $bu }}</option>
                        @endforeach
                    </select>
                    <small class="text-muted">Blank means it applies to every Business Unit.</small>
                </div>
                <div class="col-md-4">
                    <label for="unit" class="form-label">Unit</label>
                    <select class="select2 form-control select2-multiple" name="unit[]" id="unit" data-toggle="select2" multiple="multiple" data-placeholder="Choose ...">
                        <option value="" disabled>Please select</option>
                        @php
                            $selectedunit = old('unit', (array) $survey->unit);
                        @endphp
                        @foreach($departments as $dep)
                            <option value="{{ $dep->department_code }}" {{ in_array($dep->department_code, (array) $selectedunit) ? 'selected' : '' }}>{{ $dep->department_name." (".$dep->parent_company_id.")" }}</option>
                        @endforeach
                    </select>
                    <small class="text-muted">Blank means it applies to every Unit.</small>
                </div>
                <div class="col-md-6">
                    <label for="job_level" class="form-label">Job Level</label>
                    <select class="select2 form-control select2-multiple" name="job_level[]" id="job_level" data-toggle="select2" multiple="multiple" data-placeholder="Choose ...">
                        <option value="" disabled>Please select</option>
                        @php
                            $selectedGrade = old('job_level', (array) $survey->jobLevel);
                        @endphp
                        @foreach($grades as $grade)
                            <option value="{{ $grade->group_name }}" {{ in_array($grade->group_name, (array) $selectedGrade) ? 'selected' : '' }}>{{ $grade->group_name }}</option>
                        @endforeach
                    </select>
                    <small class="text-muted">Blank means it applies to every Job Level.</small>
                </div>
                <div class="col-md-6">
                    <label for="location" class="form-label">Location</label>
                    <select class="select2 form-control select2-multiple" name="location[]" id="location" data-toggle="select2" multiple="multiple" data-placeholder="Choose ...">
                        <option value="" disabled>Please select</option>
                        @php
                            $selectedlocation = old('location', (array) $survey->location);
                        @endphp
                        <option value="" disabled>Please select</option>
                        @foreach($locations as $loc)
                            <option value="{{ $loc->work_area }}" {{ in_array($loc->work_area, (array) $selectedlocation) ? 'selected' : '' }}>{{ $loc->area." (".$loc->company_name.")" }}</option>
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
                <strong>CUSTOM REGISTRATION FORM BUILDER</strong>
            </div>
        </div>

        {{-- Buttons --}}
        <div class="d-flex justify-content-end mb-4">
            @if($survey->status == 'Draft')
                <button type="submit" name="action" value="draft" class="btn btn-secondary me-2">Save as Draft</button>
            @endif
            <button type="submit" name="action" value="update" class="btn btn-primary me-2">Update Survey</button>
            <a href="{{ route('admin.survey.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection