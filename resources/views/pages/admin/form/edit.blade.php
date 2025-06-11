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
            <div class="card bg-light shadow mb-4 border-0 position-relative form-row-item">
                <button type="button" class="btn btn-danger btn-sm remove-row position-absolute" style="top: 10px; right: 10px; {{ $loop->first ? 'display:none;' : '' }}">X</button>

                <div class="card-body row g-3">
                    <div class="col-md-2">
                        <select class="form-select" name="type[]" required>
                            <option disabled>Please select Type</option>
                            <option value="text" {{ $field['type'] == 'text' ? 'selected' : '' }}>Text</option>
                            <option value="textarea" {{ $field['type'] == 'textarea' ? 'selected' : '' }}>Textarea</option>
                        </select>
                    </div>
                    <div class="col-md-5">
                        <input type="text" class="form-control" name="label[]" placeholder="Label" value="{{ $field['label'] }}" required>
                    </div>
                    <div class="col-md-3">
                        <input type="text" class="form-control" name="validation[]" placeholder="Validation" value="{{ $field['validation'] ?? '' }}">
                    </div>
                    <div class="col-md-2">
                        <input type="checkbox" class="form-check-input" name="required[]" {{ isset($field['required']) && $field['required'] ? 'checked' : '' }}>
                        <label class="form-check-label">required?</label>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <div class="mb-3">
            <button type="button" class="btn btn-primary" id="add-row">Add Row</button>
        </div>

        <div class="d-flex justify-content-end mb-4">
            <button type="submit" class="btn btn-success me-2">Update Form</button>
            <a href="{{ route('form.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function () {
    $('#add-row').click(function () {
        let newRow = $('.form-row-item').first().clone();
        newRow.find('input, select').val('');
        newRow.find('input[type="checkbox"]').prop('checked', false);
        newRow.find('.remove-row').show();
        $('#form-builder-wrapper').append(newRow);
    });

    $(document).on('click', '.remove-row', function () {
        if ($('.form-row-item').length > 1) {
            $(this).closest('.form-row-item').remove();
        }
    });
});
</script>
@endpush