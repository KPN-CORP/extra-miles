@extends('layouts_.vertical', ['page_title' => 'Edit Form Builder'])

@section('content')
<div class="container-fluid bg-white py-3 px-3">
    <form method="POST" action="{{ route('formbuilder.update', $form->id) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="card bg-light shadow">
            <div class="card-header">
                <h4 class="mb-0">Edit Form Info</h4>
            </div>
            <div class="card-body row g-3">
                <div class="col-md-6">
                    <label for="category" class="form-label">Category</label>
                    <select class="form-select" id="category" name="category" required>
                        <option disabled>Please select</option>
                        <option value="event" {{ $form->category == 'event' ? 'selected' : '' }}>Event</option>
                        <option value="survey" {{ $form->category == 'survey' ? 'selected' : '' }}>Survey</option>
                        <option value="vote" {{ $form->category == 'vote' ? 'selected' : '' }}>Vote</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="title" class="form-label">Form Name</label>
                    <input type="text" class="form-control" name="title" id="title" required value="{{ old('title', $form->title) }}">
                </div>
            </div>
        </div>

        <div class="alert alert-success bg-transparent text-success mt-4" role="alert">
            <h4 class="mb-0 text-center">Edit Form Builder</h4>
        </div>

        <div id="form-builder-wrapper">
            @foreach($formSchema['fields'] as $index => $field)
                @if (!str_contains($field['name'], 'reason'))
                    <div class="card bg-light shadow mb-4 border-0 position-relative form-row-item">
                        <button type="button" class="btn btn-danger btn-sm remove-row position-absolute" style="top: 10px; right: 10px; {{ $loop->first ? 'display:none;' : '' }}">X</button>

                        <div class="card-body row g-3">
                            <div class="col-md-2">
                                <select class="form-select" name="type[{{ $index }}]" required>
                                    <option disabled>Please select Type</option>
                                    <option value="text" {{ $field['type'] == 'text' ? 'selected' : '' }}>Text</option>
                                    <option value="textarea" {{ $field['type'] == 'textarea' ? 'selected' : '' }}>Textarea</option>
                                    <option value="checkbox" {{ $field['type'] == 'checkbox' ? 'selected' : '' }}>Checkbox</option>
                                    <option value="radio" {{ $field['type'] == 'radio' ? 'selected' : '' }}>Radio</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <input type="text" class="form-control" name="edit_label[{{ $index }}]" placeholder="Label" value="{{ $field['label'] ?? '' }}">
                            </div>
                            <div class="col-md-3 options-wrapper {{ isset($field['options']) ? '' : 'd-none' }}">
                                <input type="text" class="form-control" name="options[{{ isset($field['options']) ? $index : '' }}]" placeholder="Example: Option 1, Option 2, Option 3" value="{{ isset($field['options']) && is_array($field['options']) ? implode(', ', $field['options']) : '' }}">
                            </div>
                            <div class="col-md-2">
                                <input type="text" class="form-control" name="validation[{{ $index }}]" placeholder="Validation" value="{{ $field['validation'] ?? '' }}">
                            </div>
                            <div class="col-md-2">
                                <input type="checkbox" class="form-check-input" name="required[{{ $index }}]" value="1"
                                {{ isset($field['required']) && $field['required'] ? 'checked' : '' }}>
                                <label class="form-check-label">required?</label><br>
                                <input type="checkbox" class="form-check-input options-confirmation {{ $field['type'] == 'radio' ? '' : 'd-none' }}" name="confirmation[{{ (str_contains($field['name'], 'confirmation') && $field['type'] == 'radio') ? $index : '' }}]" value="1" {{ (str_contains($field['name'], 'confirmation') && $field['type'] == 'radio') ? 'checked' : '' }}>
                                <label class="form-check-label options-confirmation {{ $field['type'] == 'radio' ? '' : 'd-none' }}">Confirmation?</label>
                            </div>
                        </div>
                    
                        <div class="card-body row g-3 checkbox-confirmation {{ (str_contains($field['name'], 'confirmation') && $field['type'] == 'radio') ? '' : 'd-none' }}">
                            <div class="col-md-2">
                                <input type="text" class="form-control bg-light text-confirmation" name="type_confirmation[]" value="text" readonly>
                            </div>
                            <div class="col-md-3">
                                @php
                                    $isConfirmationRadio = str_contains($field['name'], 'confirmation') && $field['type'] == 'radio';
                                    $nextLabel = $isConfirmationRadio && isset($formSchema['fields'][$index + 1]) ? $formSchema['fields'][$index + 1]['label'] : '';
                                @endphp
                                <input type="text" class="form-control label-confirm"
                                    name="label_confirmation[{{ $isConfirmationRadio ? $index : '' }}]"
                                    placeholder="Label"
                                    value="{{ $nextLabel }}">
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>

        <div class="mb-3">
            <button type="button" class="btn btn-primary" id="add-row-edit">Add Row</button>
        </div>

        <div class="d-flex justify-content-end mb-4">
            <button type="submit" class="btn btn-success me-2">Update Form</button>
            <a href="{{ route('form.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection