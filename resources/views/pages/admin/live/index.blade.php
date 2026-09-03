@extends('layouts_.vertical', ['page_title' => 'Live Content'])

@section('css')
    @include('layouts_.shared.admin-datatable-css')
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h4 class="page-title mb-0">Live Content</h4>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createLiveModal">
            Create Live
        </button>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="card">
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
                            <div class="table-responsive">
                                <table id="liveActiveTable" class="table table-hover table-sm nowrap w-100 align-middle js-datatable">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="no-sort">No</th>
                                            <th>Title</th>
                                            <th>Content Link</th>
                                            <th>Created Date</th>
                                            <th>Status</th>
                                            <th class="no-sort">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($liveContents as $Live)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $Live->title }}</td>
                                                <td>{{ $Live->content_link }}</td>
                                                <td>{{ $Live->created_at }}</td>
                                                <td><span class="badge bg-success">Active</span></td>
                                                <td>
                                                    <button type="button" class="btn btn-outline-danger btn-sm archive-live-btn" data-id="{{ $Live->id }}">
                                                        <i class="ri-archive-line"></i>
                                                    </button>

                                                    <form id="archive-live-form-{{ $Live->id }}" action="{{ route('live.destroy', $Live->id) }}" method="POST" style="display: none;">
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
                            <div class="table-responsive">
                                <table id="liveArchiveTable" class="table table-hover table-sm nowrap w-100 align-middle js-datatable">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="no-sort">No</th>
                                            <th>Title</th>
                                            <th>Content Link</th>
                                            <th>Status</th>
                                            <th>Deleted At</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($liveArchive as $liveA)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $liveA->title }}</td>
                                                <td>{{ $liveA->content_link }}</td>
                                                <td><span class="badge bg-danger">Archive</span></td>
                                                <td>{{ $liveA->deleted_at }}</td>
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
    <div class="modal fade" id="createLiveModal" tabindex="-1" aria-labelledby="createLiveModalLabel" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <form action="{{ route('live.store') }}" method="POST">
              @csrf
              <div class="modal-header">
                <h5 class="modal-title" id="createLiveModalLabel">Create New Live Content</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>

              <div class="modal-body">
                <div class="mb-3">
                  <label for="title" class="form-label">Title</label>
                  <input type="text" class="form-control" id="title" name="title" required>
                </div>
                <div class="mb-3">
                  <label for="content_link" class="form-label">Content Link</label>
                  <textarea class="form-control" id="content_link" name="content_link" rows="3" required></textarea>
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
</div>
@endsection

@push('scripts')
    @include('layouts_.shared.admin-datatable-js')
@endpush
