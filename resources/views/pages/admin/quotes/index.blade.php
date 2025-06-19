@extends('layouts_.vertical', ['page_title' => 'Quotes & Affirmation'])

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
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createQuoteModal">
            Create Quote
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
                        {{-- =============================== Detail tabel Active =============================== --}}
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
                                            <th>Created Date</th>
                                            <th>Author</th>
                                            <th>Quote</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($listQuotes as $Quote)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $Quote->created_at }}</td>
                                                <td>{{ $Quote->author }}</td>
                                                <td>{{ $Quote->quotes }}</td>
                                                <td><span class="badge bg-success">Active</span></td>
                                                <td> 
                                                    <a href="#" 
                                                        class="btn btn-outline-warning btn-sm edit-quote-btn"
                                                        data-id="{{ $Quote->id }}"
                                                        data-author="{{ $Quote->author }}"
                                                        data-quotes="{{ $Quote->quotes }}"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#editQuoteModal">
                                                        <i class="ri-edit-box-line"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-outline-danger btn-sm archive-btn" data-id="{{ $Quote->id }}">
                                                        <i class="ri-archive-line"></i>
                                                    </button>
                                                    
                                                    <form id="archive-form-{{ $Quote->id }}" action="{{ route('quotes.destroy', $Quote->id) }}" method="POST" style="display: none;">
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

                        {{-- =============================== Detail tabel Archive =============================== --}}
                        <div class="tab-pane fade" id="archive" role="tabpanel">
                            <div class="d-flex justify-content-between align-items-center">
                                <h3 class="card-title"></h3>
                                <div class="input-group" style="width: 30%;">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-white border-dark-subtle"><i class="ri-search-line"></i></span>
                                    </div>
                                    <input type="text" name="customsearch1" id="customsearch1" class="form-control w-  border-dark-subtle border-left-0" placeholder="Search.." aria-label="search" aria-describedby="search" >
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover table-sm dt-responsive nowrap mt-2" id="scheduleTable1" width="100%"
                                        cellspacing="0">
                                    <thead class="table-light">
                                        <tr class="text-center">
                                            <th>No</th>
                                            <th>Created Date</th>
                                            <th>Author</th>
                                            <th>Quote</th>
                                            <th>Status</th>
                                            <th>Archive At</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($quoteArchive as $Quote)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $Quote->created_at }}</td>
                                                <td>{{ $Quote->author }}</td>
                                                <td>{{ $Quote->quotes }}</td>
                                                <td><span class="badge bg-danger">Archive</span></td>
                                                <td>{{ $Quote->deleted_at }}</td>
                                            </tr>
                                            @empty
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="createQuoteModal" tabindex="-1" aria-labelledby="createQuoteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <form action="{{ route('quotes.store') }}" method="POST">
              @csrf
              <div class="modal-header">
                <h5 class="modal-title" id="createQuoteModalLabel">Create New Quote</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              
              <div class="modal-body">
                <div class="mb-3">
                  <label for="author" class="form-label">Author</label>
                  <input type="text" class="form-control" id="author" name="author" required>
                </div>
                <div class="mb-3">
                  <label for="quote" class="form-label">Quote</label>
                  <textarea class="form-control" id="quote" name="quote" rows="3" required></textarea>
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
    <div class="modal fade" id="editQuoteModal" tabindex="-1" aria-labelledby="editQuoteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
          <form method="POST" action="" id="editQuoteForm">
              @csrf
              @method('PUT')
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title" id="editQuoteModalLabel">Edit Quote</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit-author" class="form-label">Author</label>
                        <input type="text" name="author" class="form-control" id="edit-author" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit-quotes" class="form-label">Quote</label>
                        <textarea name="quotes" class="form-control" id="edit-quotes" rows="4" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                  <button type="submit" class="btn btn-primary">Update Quote</button>
                </div>
              </div>
          </form>
        </div>
    </div>
</div>
@endsection