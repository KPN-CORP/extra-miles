@extends('layouts_.vertical', ['page_title' => 'Social Media'])

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
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0"></h3>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createSocialModal">
            Add Social Media
        </button>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow mb-4">
                <div class="card-body">
                    <ul class="nav nav-tabs mb-3" id="eventTab" role="tablist">
                        @foreach (['Active', 'Archive'] as $tab)
                            <li class="nav-item" role="presentation">
                                <button class="nav-link @if ($loop->first) active @endif"
                                    id="{{ strtolower(str_replace(' ', '-', $tab)) }}-tab" data-bs-toggle="tab"
                                    data-bs-target="#{{ strtolower(str_replace(' ', '-', $tab)) }}" type="button"
                                    role="tab">
                                    {{ $tab }}
                                </button>
                            </li>
                        @endforeach
                    </ul>
                    <div class="tab-content" id="eventTabContent">
                        <div class="tab-pane fade show active" id="active" role="tabpanel">
                            <div class="d-flex justify-content-between align-items-center">
                                <h3 class="card-title"></h3>
                                <div class="input-group" style="width: 30%;">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-white border-dark-subtle"><i class="ri-search-line"></i></span>
                                    </div>
                                    <input type="text" name="customsearch" id="customsearch" class="form-control w-  border-dark-subtle border-left-0" placeholder="Search.." aria-label="search" aria-describedby="search" >
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover table-sm dt-responsive nowrap mt-2" id="scheduleTable" width="100%"
                                        cellspacing="0">
                                    <thead class="table-light">
                                        <tr class="text-center">
                                            <th>No</th>
                                            <th>Category</th>
                                            <th>Business Unit</th>
                                            <th>Direct Link</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($listSocial as $social)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $social->category }}</td>
                                                <td>{{ $social->businessUnit }}</td>
                                                <td>{{ $social->link }}</td>
                                                <td>
                                                    <a href="#" 
                                                        class="btn btn-outline-warning btn-sm edit-social-btn"
                                                        data-id="{{ $social->id }}"
                                                        data-category="{{ $social->category }}"
                                                        data-businessUnit="{{ $social->businessUnit }}"
                                                        data-link="{{ $social->link }}"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#editSocialModal">
                                                        <i class="ri-edit-box-line"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-outline-danger btn-sm archive-btn" data-id="{{ $social->id }}">
                                                        <i class="ri-archive-line"></i>
                                                    </button>
                                                    <form id="archive-form-{{ $social->id }}" action="{{ route('social.destroy', $social->id) }}" method="POST" style="display: none;">
                                                        @csrf
                                                        @method('DELETE')
                                                    </form>
                                                </td>
                                            </tr>
                                        @empty
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="archive" role="tabpanel">
                        
                        </div> 
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="createSocialModal" tabindex="-1" aria-labelledby="createSocialModalLabel" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <form action="{{ route('social.store') }}" method="POST">
              @csrf
              <div class="modal-header">
                <h5 class="modal-title" id="createSocialModalLabel">Create New Social</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              
              <div class="modal-body">
                <div class="mb-3">
                  <label for="category" class="form-label">Category</label>
                    <select class="form-select" id="category" name="category" required>
                        <option value="" selected disabled>Please select</option>
                        <option value="youtube">Youtube</option>
                        <option value="instagram">Instagram</option>
                        <option value="tiktok">Tiktok</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="businessunit" class="form-label">Business Unit</label>
                    <select class="form-select" id="businessunit" name="businessunit" required>
                        <option value="" selected disabled>Please select</option>
                        <option value="KPN Corporation">KPN Corporation</option>
                        <option value="Cement">Cement</option>
                        <option value="Plantations">Plantations</option>
                        <option value="Downstream">Downstream</option>
                        <option value="Property">Property</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="link" class="form-label">Link</label>
                    <input type="text" class="form-control" id="link" name="link" required>
                </div>
              </div>
              
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Submit</button>
              </div>
              
            </form>
          </div>
        </div>
    </div>
    <div class="modal fade" id="editSocialModal" tabindex="-1" aria-labelledby="editSocialModalLabel" aria-hidden="true">
        <div class="modal-dialog">
          <form method="POST" action="" id="editSocialForm">
              @csrf
              @method('PUT')
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title" id="editSocialModalLabel">Edit Social</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="category" class="form-label">Category</label>
                        <select class="form-select" id="edit-category" name="category" required>
                            <option value="" selected disabled>Please select</option>
                            <option value="youtube">Youtube</option>
                            <option value="instagram">Instagram</option>
                            <option value="tiktok">Tiktok</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="businessunit" class="form-label">Business Unit</label>
                        <select class="form-select" id="edit-businessunit" name="businessunit" required>
                            <option value="" selected disabled>Please select</option>
                            <option value="KPN Corporation">KPN Corporation</option>
                            <option value="Cement">Cement</option>
                            <option value="Plantations">Plantations</option>
                            <option value="Downstream">Downstream</option>
                            <option value="Property">Property</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="link" class="form-label">Link</label>
                        <input type="text" class="form-control" id="edit-link" name="link" required>
                    </div>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                  <button type="submit" class="btn btn-primary">Update Social</button>
                </div>
              </div>
          </form>
        </div>
    </div>
</div>
@endsection