@extends('layouts_.vertical', ['page_title' => 'News'])

@section('css')
    <style>
        .nav-tabs .nav-link.active {
            background-color: #ab2f2b !important;
            /* Bootstrap primary color */
            color: white !important;
            font-weight: bold;
            border-radius: 0.375rem;
        }

        .nav-tabs .nav-link {
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .table thead th {
            white-space: nowrap;
            vertical-align: middle;
        }

        .table thead {
            display: table-header-group;
        }

        .table-responsive {
            overflow-x: auto;
            overflow-y: visible;
        }
        
        table.dataTable tbody tr>.dtfc-fixed-left, table.dataTable tbody tr>.dtfc-fixed-right {
            z-index: 3;
            background-color: white !important;
        }
    </style>
@endsection

@section('content')
<div class="container-fluid">
<form method="POST" action="{{ route('news.update', $news->encrypted_id) }}" class="needs-validation" novalidate enctype="multipart/form-data">
    @csrf
    @method('PUT')
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-body p-2 p-md-4">
                    <div class="card bg-light">
                        <div class="card-body px-2 px-md-3 row g-3">
                            <div class="col-md-4">
                                <label for="category" class="form-label">Category</label>
                                <select class="form-select" id="category" name="category" required>
                                    <option value="" selected disabled>Please select</option>
                                    <option value="CSR" {{ $news->category == 'CSR' ? 'selected' : '' }}>CSR</option>
                                    <option value="Event" {{ $news->category == 'Event' ? 'selected' : '' }}>Event</option>
                                    <option value="Sport" {{ $news->category == 'Sport' ? 'selected' : '' }} >Sport</option>
                                    <option value="Training" {{ $news->category == 'Training' ? 'selected' : '' }} >Training</option>
                                </select>
                                <div class="invalid-feedback">
                                    {{ $invalidFeedback }}
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label for="business_unit" class="form-label">Belong to Business Unit</label>
                                <select class="select2 form-control select2-multiple" name="business_unit[]" id="business_unit" data-toggle="select2" multiple="multiple" data-placeholder="Please select">
                                    @php
                                        $selectedBU = old('business_unit', (array) $news->businessUnit);
                                    @endphp
                                    @foreach($bisnisunits as $bu)
                                        <option value="{{ $bu }}" {{ in_array($bu, (array) $selectedBU) ? 'selected' : '' }}>{{ $bu }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted"><i class="ri-information-line me-1"></i>Blank means it applies to every Business Unit.</small>
                            </div>
                            <div class="col-md-4">
                                <label for="hashtag" class="form-label">Hashtag</label>
                                <input type="text" class="form-control" name="hashtag" id="hashtag" placeholder="create your hashtag.." value="{{ old('hashtag', $news->hashtag) }}" required>
                                <div class="invalid-feedback">
                                    {{ $invalidFeedback }}
                                </div>
                                <small class="text-muted"><i class="ri-information-line me-1"></i>Separate each hashtag with a coma.</small>
                            </div>
                        </div>
                    </div>
                    <div class="card bg-light mb-4">
                        <div class="card-body px-2 px-md-3 row g-3">
                            <div class="col-md-12">
                                <div class="row">
                                    <div class="col-md-2">
                                        <label for="image" class="form-label">News Banner</label>
                                        <button type="button" class="form-control btn btn-outline-primary" onclick="document.getElementById('image').click()"><i class="ri-image-line me-1"></i>Select Image</button>
                                        <input 
                                            type="file" 
                                            name="image" 
                                            id="image" 
                                            accept="image/*" 
                                            class="form-control d-none" 
                                            onchange="previewImage(event)"
                                            {{ isset($news) ? '' : 'required' }} {{-- only required if creating --}}
                                        >
                                        <div class="invalid-feedback">
                                            {{ $invalidFeedback }}
                                        </div>
                                        <small class="text-muted">Maximum file size 2MB</small>
                                    </div>
                                </div>
                                <div class="row">
                                    @if(isset($news->image))
                                        <div class="col-md-6 mt-3" id="image-preview-container">
                                            <div class="row">
                                                <div class="col">
                                                    <label class="form-label">Image Preview</label>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col">
                                                    <img 
                                                        id="image-preview"
                                                        src="{{ asset('storage/' . $news->image) }}" 
                                                        alt="Preview" 
                                                        class="img-fluid rounded"
                                                        style="max-height: 200px; object-fit: contain;"
                                                    >
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <div class="col-md-4 mt-3" id="image-preview-container" style="display: none;">
                                            <label class="form-label">Preview</label>
                                            <img 
                                                id="image-preview"
                                                src="#" 
                                                alt="Preview" 
                                                class="img-fluid rounded"
                                                style="max-height: 200px; object-fit: contain;"
                                            >
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="row">
                                    <div class="col-md-2">
                                        <label for="publish_date" class="form-label">Published Date</label>
                                        <input type="date" class="form-control" name="publish_date" id="publish_date" value="{{ old('publish_date', $news->publish_date) }}" required>
                                        <div class="invalid-feedback">
                                            {{ $invalidFeedback }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="row">
                                    <div class="col-md-6">
                                        <label for="title" class="form-label">Headline</label>
                                        <input name="title" class="form-control" id="title" placeholder="create your headlines here.." required value="{{ old('title', $news->title) }}">
                                        <div class="invalid-feedback">
                                            {{ $invalidFeedback }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <label for="content" class="form-label">Body</label>
                                <textarea name="content" class="form-control" rows="4" style="height:50px" id="description" placeholder="write your news here.." required>{{ old('content', $news->content) }}</textarea>
                                <div class="invalid-feedback">
                                    {{ $invalidFeedback }}
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="row">
                                    <div class="col-md-6">
                                        <label for="link" class="form-label">Youtube video ID</label>
                                        <input type="text" class="form-control" name="link" id="link" placeholder="Add video ID here.." value="{{ old('link', $news->link) }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex text-center text-md-end justify-content-around justify-content-md-end mb-2">
                        <button type="submit" name="action" value="draft" class="btn btn-secondary me-md-2 {{ $news->link == 'Draft' ? '' : 'd-none' }}">Save as Draft</button>
                        <a href="{{ url()->previous() }}" class="btn btn-outline-secondary me-md-2">Cancel</a>
                        <button type="submit" name="action" value="create" class="btn btn-primary">Save</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
</div>
@endsection