@extends('layouts_.vertical', ['page_title' => 'Events'])

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
        <div>
            <div class="text-muted small">
                <span class="me-3 fs-5"><i
                        class="ri-calendar-line me-1"></i>{{ date('l, d F Y') }}</span>
                <span class="me-3"><i class="ri-time-line me-1"></i><span
                        id="currentTime"></span>
                    WIB</span>
            </div>
        </div>
        <a href="{{ route('admin.events.create') }}" class="btn btn-primary">Create Event</a>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow mb-4">
                <div class="card-body">
                    <ul class="nav nav-tabs mb-3" id="eventTab" role="tablist">
                        @foreach (['Open', 'Closed', 'Archive'] as $tab)
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
                        {{-- =============================== Detail tabel Open =============================== --}}
                        <div class="tab-pane fade show active" id="open" role="tabpanel">
                            <div class="d-flex justify-content-between align-items-center">
                                <h3 class="card-title"></h3>
                                <div class="input-group" style="width: 30%;">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-white border-dark-subtle"><i class="ri-search-line"></i></span>
                                    </div>
                                    <input type="text" name="customsearch" id="customsearch" class="form-control w-border-dark-subtle border-left-0" placeholder="Search.." aria-label="search" aria-describedby="search" >
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover table-sm" id="scheduleTable" width="100%"
                                        cellspacing="0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>No</th>
                                            <th>Category</th>
                                            <th>Created Date</th>
                                            <th>Title</th>
                                            <th>Total Register</th>
                                            <th>Status</th>
                                            <th>Barcode</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($events as $event)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $event->category }}</td>
                                            <td>{{ \Carbon\Carbon::parse($event->start_date)->format('d M Y') }}</td>
                                            <td>{{ $event->title }}</td>
                                            <td style="text-align: center;">
                                                <span style="color: {{ $event->participants_count >= $event->quota ? 'green' : 'red' }};">
                                                    {{ $event->participants_count }}/{{ $event->quota }}
                                                </span>
                                            </td>
                                            <td>
                                                {{-- View Status --}}
                                                <span class="badge 
                                                    @if($event->status === 'Ongoing' || $event->status === 'Open Registration')
                                                        bg-success
                                                    @elseif($event->status === 'Full Booked')
                                                        bg-primary
                                                    @elseif($event->status === 'Draft')
                                                        bg-secondary
                                                    @else
                                                        text-bg-light
                                                    @endif
                                                ">
                                                    {{ $event->status }}
                                                </span>
                                            </td>
                                            <td>
                                                {{-- Show QR --}}
                                                @if($event->status != 'Draft')
                                                    {{-- <button type="button" class="btn btn-outline-primary btn-sm" onclick="showQRModal('{{ \Illuminate\Support\Facades\Crypt::encryptString($event->id) }}')">
                                                        Print QR
                                                    </button> --}}
                                                
                                                    <a href="{{ route('event.qrpng', \Illuminate\Support\Facades\Crypt::encryptString($event->id)) }}" target="_blank" class="btn btn-outline-primary btn-sm">
                                                        Print QR
                                                    </a>
                                                @endif
                                            </td>
                                            <td>
                                                {{-- List Participants --}}
                                                @if($event->status != 'Draft')
                                                    <a href="{{ route('events.participants', \Illuminate\Support\Facades\Crypt::encryptString($event->id)) }}" class="btn btn-outline-info btn-sm" title="List Participants">
                                                        <i class="ri-eye-line"></i>
                                                    </a>
                                                @endif
        
                                                {{-- Close Registration --}}
                                                @if($event->status === 'Ongoing' || $event->status == 'Open Registration')
                                                    <form id="close-form-{{ $event->id }}" action="{{ route('events.close', $event->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="button" class="btn btn-outline-secondary btn-sm btn-close-reg"
                                                            data-id="{{ $event->id }}" data-action="close" title="Close Registration">
                                                            <i class="ri-close-line"></i>
                                                        </button>
                                                    </form>
                                                @elseif($event->status === 'Full Booked')
                                                    <form id="close-form-{{ $event->id }}" action="{{ route('events.close', $event->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="button" class="btn btn-outline-success btn-sm btn-close-reg"
                                                            data-id="{{ $event->id }}" data-action="open" title="Open Registration">
                                                            <i class="ri-checkbox-circle-line"></i>
                                                        </button>
                                                    </form>
                                                @endif
        
                                                @if($event->status === 'Draft' || $event->status === 'Full Booked' || $event->status == 'Open Registration')
                                                    {{-- Edit Event --}}
                                                    <a href="{{ route('events.edit', $event->id) }}" class="btn btn-outline-warning btn-sm" title="Edit Event">
                                                        <i class="ri-edit-box-line"></i>
                                                    </a>
                                                
                                                    {{-- Archive Event --}}
                                                    <form id="delete-form-{{ $event->id }}" action="{{ route('events.softDelete', $event->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="button" class="btn btn-outline-danger btn-sm btn-archive" 
                                                            data-id="{{ $event->id }}" title="Archive Event">
                                                            <i class="ri-archive-line"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>
                                        @empty
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- =============================== Detail tabel Closed =============================== --}}
                        <div class="tab-pane fade" id="closed" role="tabpanel">
                            <div class="d-flex justify-content-between align-items-center">
                                <h3 class="card-title"></h3>
                                <div class="input-group" style="width: 30%;">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-white border-dark-subtle"><i class="ri-search-line"></i></span>
                                    </div>
                                    <input type="text" name="customsearch1" id="customsearch1" class="form-control w-border-dark-subtle border-left-0" placeholder="Search.." aria-label="search" aria-describedby="search" >
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover table-sm" id="scheduleTable1" width="100%"
                                        cellspacing="0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>No</th>
                                            <th>Category</th>
                                            <th>Created Date</th>
                                            <th>Title</th>
                                            <th>Total Register</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($eventClosed as $event)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $event->category }}</td>
                                            <td>{{ \Carbon\Carbon::parse($event->start_date)->format('d M Y') }}</td>
                                            <td>{{ $event->title }}</td>
                                            <td style="text-align: center;">
                                                <span style="color: {{ $event->participants_count >= $event->quota ? 'green' : 'red' }};">
                                                    {{ $event->participants_count }}/{{ $event->quota }}
                                                </span>
                                            </td>
                                            <td>
                                                {{-- View Status --}}
                                                <span class="badge 
                                                    @if($event->status === 'Ongoing' || $event->status === 'Open Registration')
                                                        bg-success
                                                    @elseif($event->status === 'Full Booked')
                                                        bg-primary
                                                    @elseif($event->status === 'Draft')
                                                        bg-secondary
                                                    @else
                                                        text-bg-light
                                                    @endif
                                                ">
                                                    {{ $event->status }}
                                                </span>
                                            </td>
                                            <td>
                                                {{-- List Participants --}}
                                                @if($event->status != 'Draft'  && $event->participants_count>0)
                                                    <a href="{{ route('events.participants', \Illuminate\Support\Facades\Crypt::encryptString($event->id)) }}" class="btn btn-outline-info btn-sm" title="List Participants">
                                                        <i class="ri-eye-line"></i>
                                                    </a>
                                                @endif
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
                                    <input type="text" name="customsearch2" id="customsearch2" class="form-control w-border-dark-subtle border-left-0" placeholder="Search.." aria-label="search" aria-describedby="search" >
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover table-sm" id="scheduleTable2" width="100%"
                                        cellspacing="0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>No</th>
                                            <th>Category</th>
                                            <th>Created Date</th>
                                            <th>Title</th>
                                            <th>Total Register</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                            <th>Archive Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($eventArchive as $event)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $event->category }}</td>
                                            <td>{{ \Carbon\Carbon::parse($event->start_date)->format('d M Y') }}</td>
                                            <td>{{ $event->title }}</td>
                                            <td style="text-align: center;">
                                                <span style="color: {{ $event->participants_count >= $event->quota ? 'green' : 'red' }};">
                                                    {{ $event->participants_count }}/{{ $event->quota }}
                                                </span>
                                            </td>
                                            <td>
                                                {{-- View Status --}}
                                                <span class="badge 
                                                    @if($event->status === 'Ongoing' || $event->status === 'Open Registration')
                                                        bg-success
                                                    @elseif($event->status === 'Full Booked')
                                                        bg-primary
                                                    @elseif($event->status === 'Draft')
                                                        bg-secondary
                                                    @else
                                                        text-bg-light
                                                    @endif
                                                ">
                                                    {{ $event->status }}
                                                </span>
                                            </td>
                                            <td>
                                                {{-- List Participants --}}
                                                @if($event->status != 'Draft' && $event->participants_count>0)
                                                    <a href="{{ route('events.participants', \Illuminate\Support\Facades\Crypt::encryptString($event->id)) }}" class="btn btn-outline-info btn-sm" title="List Participants">
                                                        <i class="ri-eye-line"></i>
                                                    </a>
                                                @endif
                                            </td>
                                            <td>{{ $event->deleted_at }}</td>
                                        </tr>
                                        @empty
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Modal QR -->
                        <div class="modal fade" id="qrModal" tabindex="-1" aria-labelledby="qrModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content p-3">
                                    <div class="modal-header">
                                        <h5 class="modal-title w-100 text-center" id="qrModalLabel">QR Code</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body text-center">
                                        <div class="d-flex justify-content-center py-2">
                                            <div id="qrcode"></div>
                                        </div>
                                        <p class="mt-3 text-center">
                                            <a href="#" id="dummyLink" target="_blank" class="text-primary fw-bold"></a>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection