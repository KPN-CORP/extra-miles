@extends('layouts_.vertical', ['page_title' => 'Form Builder'])

@section('content')
<div class="container-fluid bg-white py-3 px-3">
    <form method="POST" action="{{ route('form-builder.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="card bg-light shadow">
            <div class="card-header">
                <h4 class="mb-0">Form Info</h4>
            </div>
            <div class="card-body row g-3">
                <div class="col-md-6">
                    <label for="category" class="form-label">Category</label>
                    <select class="form-select" id="category" name="category" required>
                        <option selected disabled>Please select</option>
                        <option value="event">Event</option>
                        <option value="survey">Survey</option>
                        <option value="vote">Vote</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="event_name" class="form-label">Form Name</label>
                    <input type="text" class="form-control" name="title" id="title" required>
                </div>
                
            </div>
        </div>
        <div class="alert alert-success bg-transparent text-success" role="alert">
            <h4 class="mb-0 text-center">Form Builder</h4>
        </div>
        <div id="form-builder-wrapper">
            <div class="card bg-light shadow mb-4 border-0 position-relative form-row-item">
                    <button type="button" class="btn btn-danger btn-sm remove-row position-absolute" style="top: 10px; right: 10px; display:none;">X</button>
                
                <div class="card-body row g-3">
                    <div class="col-md-2">
                        <select class="form-select" name="type[]" required>
                            <option selected disabled>Please select Type</option>
                            <option value="text">Text</option>
                            <option value="textarea">Textarea</option>
                        </select>
                    </div>
                    <div class="col-md-5">
                        <input type="text" class="form-control" name="label[]" placeholder="Label" required>
                    </div>
                    <div class="col-md-3">
                        <input type="text" class="form-control" name="validation[]" placeholder="Validation">
                    </div>
                    <div class="col-md-2">
                        <input type="checkbox" class="form-check-input" name="required[]" value="1">
                        <label class="form-check-label">required?</label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tombol tambah -->
        <div class="mb-3">
            <button type="button" class="btn btn-primary" id="add-row">Add Row</button>
        </div>

        {{-- Buttons --}}
        <div class="d-flex justify-content-end mb-4">
            <button type="submit" name="action" value="create" class="btn btn-primary me-2">Create Form</button>
            <a href="{{ route('form.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection