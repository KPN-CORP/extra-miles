@extends('layouts_.vertical', ['page_title' => 'Events'])

@section('content')
<div class="container-fluid bg-white py-3 px-3">
    <form method="POST" action="{{ route('events.update', $event->id) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="card bg-light shadow">
            <div class="card-header">
                <h4 class="mb-0">Event Info</h4>
            </div>
            <div class="card-body row g-3">
                <div class="col-md-4">
                    <label for="category" class="form-label">Category</label>
                    <select class="form-select" id="category" name="category" required>
                        <option selected disabled>Please select</option>
                        <option value="Sport" {{ $event->category == 'Sport' ? 'selected' : '' }}>Sport</option>
                        <option value="Event" {{ $event->category == 'Event' ? 'selected' : '' }}>Event</option>
                        {{-- Populate options --}}
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="start_date" class="form-label">Start Date</label>
                    <input type="datetime-local" class="form-control {{$event->status === 'Draft' ? '' : 'bg-light'}}" name="start_date" id="start_date" value="{{ old('start_date', $event->start_date." ".$event->time_start) }}" {{$event->status === 'Draft' ? 'required' : 'readonly'}}>
                </div>
                <div class="col-md-4">
                    <label for="end_date" class="form-label">End Date</label>
                    <input type="datetime-local" class="form-control {{$event->status === 'Draft' ? '' : 'bg-light'}}" name="end_date" id="end_date" value="{{ old('end_date', $event->end_date." ".$event->time_end) }}" {{$event->status === 'Draft' ? 'required' : 'readonly'}}>
                    {{-- <small class="text-muted">Leave blank for one-day event</small> --}}
                </div>
                <div class="col-md-12">
                    <label for="event_name" class="form-label">Event Name</label>
                    <input type="text" name="event_name" class="form-control" value="{{ old('event_name', $event->title) }}" required>
                </div>
                <div class="col-md-12">
                    <label for="event_name" class="form-label">Event Location</label>
                    <input type="text" class="form-control" name="event_location" id="event_location" value="{{ old('event_location', $event->event_location) }}" required>
                </div>
                <div class="col-md-12">
                    <label for="description" class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="4" style="height:50px" id="description">
                        {{ old('description', $event->description ?? '') }}
                    </textarea>
                </div>
                <div class="col-md-12">
                    <label for="banner" class="form-label">Event Banner</label>
                    <input type="file" name="banner" id="banner" class="form-control">
                    <small class="text-muted">Maximum file size 2MB</small>
                    @if($event->image)
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
                                        <img src="{{ asset('storage/' . $event->image) }}" alt="Full Banner" class="img-fluid">
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
                <div class="col-md-3">
                    <input type="checkbox" class="form-check-input" id="need_survey" name="need_survey" {{ $event->status_survey ? 'checked' : '' }}>
                    <label class="form-check-label" for="need_survey">Does this event need a survey?</label>
                </div>
                <div class="col-md-9">
                    <input type="checkbox" class="form-check-input" id="need_voting" name="need_voting" {{ $event->status_voting ? 'checked' : '' }}>
                    <label class="form-check-label" for="need_voting">Does this event need a voting?</label>
                </div>
                <div class="col-md-4">
                    <label for="participants" class="form-label">Target Participants *</label>
                    <input type="number" name="participants" class="form-control" value="{{ old('participants', $event->quota) }}">
                </div>
                <div class="col-md-4">
                    <label for="registration_deadline" class="form-label">Registration Deadline</label>
                    <input type="date" class="form-control" name="registration_deadline" id="registration_deadline" value="{{ old('registration_deadline', $event->regist_deadline) }}">
                </div>
                
                <div class="col-md-4">
                    <label for="business_unit" class="form-label">Business Unit</label>
                    <select class="select2 form-control select2-multiple" name="business_unit[]" id="business_unit" data-toggle="select2" multiple="multiple" data-placeholder="Choose ...">
                        <option value="" disabled>Please select</option>
                        @php
                            $selectedBU = old('business_unit', (array) $event->businessUnit);
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
                            $selectedunit = old('unit', (array) $event->unit);
                        @endphp
                        @foreach($departments as $dep)
                            <option value="{{ $dep->unit }}" {{ in_array($dep->unit, (array) $selectedunit) ? 'selected' : '' }}>{{ $dep->unit." - ".$dep->group_company." - ".$dep->office_area }}</option>
                        @endforeach
                    </select>
                    <small class="text-muted">Blank means it applies to every Unit.</small>
                </div>
                <div class="col-md-4">
                    <label for="job_level" class="form-label">Job Level</label>
                    <select class="select2 form-control select2-multiple" name="job_level[]" id="job_level" data-toggle="select2" multiple="multiple" data-placeholder="Choose ...">
                        <option value="" disabled>Please select</option>
                        @php
                            $selectedGrade = old('job_level', (array) $event->jobLevel);
                        @endphp
                        @foreach($grades as $grade)
                            <option value="{{ $grade->group_name }}" {{ in_array($grade->group_name, (array) $selectedGrade) ? 'selected' : '' }}>{{ $grade->group_name }}</option>
                        @endforeach
                    </select>
                    <small class="text-muted">Blank means it applies to every Job Level.</small>
                </div>
                <div class="col-md-4">
                    <label for="location" class="form-label">Location</label>
                    <select class="select2 form-control select2-multiple" name="location[]" id="location" data-toggle="select2" multiple="multiple" data-placeholder="Choose ...">
                        @php
                            $selectedlocation = old('location', (array) $event->location);
                        @endphp
                        <option value="" disabled>Please select</option>
                        @foreach($locations as $loc)
                            <option value="{{ $loc->office_area }}" {{ in_array($loc->office_area, (array) $selectedlocation) ? 'selected' : '' }}>{{ $loc->office_area." (".$loc->group_company.")" }}</option>
                        @endforeach
                    </select>
                    <small class="text-muted">Blank means it applies to every Location.</small>
                </div>
            </div>
        </div>
        <div class="card bg-light shadow">
            <div class="card-header">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="custom_form" id="custom_form"
                        {{ $event->form_id ? 'checked' : '' }}>
                    <label class="form-check-label" for="custom_form">
                        Custom Registration Form
                    </label>
                </div>
            </div>
            <div class="card-body">
                <strong>CUSTOM REGISTRATION FORM BUILDER</strong>
                <div class="row">
                    <div class="col-md-4 {{ $event->form_id ? '' : 'd-none' }}" id="form-select-wrapper">
                        <select class="form-select" id="form_id" name="form_id">
                            <option disabled {{ !$event->form_id ? 'selected' : '' }}>Please select</option>
                            @foreach($formTemplates as $form)
                                <option value="{{ $form->id }}" {{ $form->id == $event->form_id ? 'selected' : '' }}>
                                    {{ $form->title." (".$form->created_at->format('d M Y').")" }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-8 {{ $event->form_id ? '' : 'd-none' }}" id="form-preview-wrapper">
                        <div id="form-preview" class="bg-white p-3 rounded"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit Buttons -->
        <div class="d-flex justify-content-end mb-4">
            @if($event->status == 'Draft')
                <button type="submit" name="action" value="draft" class="btn btn-secondary me-2">Save as Draft</button>
            @endif
            <button type="submit" name="action" value="update" class="btn btn-primary me-2">Update Event</button>
            <a href="{{ route('admin.events.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const checkbox = document.getElementById('custom_form');
        const formSelectWrapper = document.getElementById('form-select-wrapper');
        const formPreviewWrapper = document.getElementById('form-preview-wrapper');
        const formSelect = document.getElementById('form_id');
        const formPreview = document.getElementById('form-preview');
    
        checkbox.addEventListener('change', function () {
            if (checkbox.checked) {
                formSelectWrapper.classList.remove('d-none');
                formPreviewWrapper.classList.remove('d-none');
            } else {
                formSelectWrapper.classList.add('d-none');
                formPreviewWrapper.classList.add('d-none');
                formPreview.innerHTML = '';
                formSelect.value = '';
            }
        });
    
        document.getElementById('form_id').addEventListener('change', function () {
            const formId = this.value;
            if (!formId) return;
    
            fetch(`/admin/forms/${formId}/schema`)
                .then(response => {
                    console.log('Raw response:', response);
                    if (!response.ok) throw new Error("Fetch error: " + response.status);
                    return response.json();
                })
                .then(data => {
                    const previewDiv = document.getElementById('form-preview');
                    previewDiv.innerHTML = '';
    
                    data.fields.forEach(field => {
                        const fieldWrapper = document.createElement('div');
                        fieldWrapper.className = 'mb-3';
    
                        const label = document.createElement('label');
                        label.className = 'form-label';
                        label.textContent = field.label;
                        fieldWrapper.appendChild(label);
    
                        let input;
                        if (field.type === 'textarea') {
                            input = document.createElement('textarea');
                            input.className = 'form-control';
                        } else {
                            input = document.createElement('input');
                            input.type = field.type;
                            input.className = 'form-control';
                        }
    
                        input.name = field.name;
                        //input.required = field.required || false;
                        fieldWrapper.appendChild(input);
    
                        previewDiv.appendChild(fieldWrapper);
                    });
                })
                .catch(error => {
                    console.error('Gagal load schema:', error);
                });
        });
    });
</script>