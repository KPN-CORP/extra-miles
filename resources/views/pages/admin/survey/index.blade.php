@extends('layouts_.vertical', ['page_title' => 'Survey'])

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
        <div class="dropdown">
            <button class="btn btn-primary dropdown-toggle" type="button" id="createDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                Create
            </button>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="createDropdown">
                <li><a class="dropdown-item" href="{{ route('admin.survey.create', ['type' => 'survey']) }}">Survey Form</a></li>
                <li><a class="dropdown-item" href="{{ route('admin.survey.create', ['type' => 'vote']) }}">Voting Form</a></li>
            </ul>
        </div>
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
                                <div class="col-12 col-md-6 col-lg-3">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-white border-dark-subtle"><i class="ri-search-line"></i></span>
                                        </div>
                                        <input type="text" name="customsearch" id="customsearch" class="form-control w-  border-dark-subtle border-left-0" placeholder="Search.." aria-label="search" aria-describedby="search" >
                                    </div>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover table-sm dt-responsive nowrap mt-2" id="scheduleTable" width="100%"
                                        cellspacing="0">
                                    <thead class="table-light">
                                        <tr class="text-center">
                                            <th>No</th>
                                            <th>Created Date</th>
                                            <th>End Date</th>
                                            <th>Category</th>
                                            <th>Form Name</th>
                                            <th>Total Participant</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($surveyList as $survey)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $survey->created_at }}</td>
                                            <td>{{ $survey->end_date }}</td>
                                            <td>{{ $survey->category }}</td>
                                            <td>{{ $survey->title }}</td>
                                            <td style="text-align: center;">
                                                {{ $survey->survey_participant_count }}
                                            </td>
                                            <td><span class="badge 
                                                @if($survey->status === 'Ongoing')
                                                    bg-success
                                                @elseif($survey->status === 'Draft')
                                                    bg-secondary
                                                @else
                                                    text-bg-light
                                                @endif
                                            ">
                                                {{ $survey->status }}
                                            </span></td>
                                            <td> 
                                                @if($survey->status === 'Ongoing' || $survey->status == 'Draft')
                                                    <a href="{{ route('survey.edit', $survey->id) }}" class="btn btn-outline-warning btn-sm"><i class="ri-edit-box-line"></i></a>
                                                @endif
                                                @if($survey->status != 'Draft' && $survey->category === 'survey')
                                                    <a href="{{ route('survey.participants', \Illuminate\Support\Facades\Crypt::encryptString($survey->id)) }}" class="btn btn-outline-info btn-sm"><i class="ri-eye-line"></i></a>
                                                @elseif($survey->status != 'Draft' && $survey->category === 'vote')
                                                    <a href="{{ route('vote.participants', \Illuminate\Support\Facades\Crypt::encryptString($survey->id)) }}" class="btn btn-outline-info btn-sm"><i class="ri-eye-line"></i></a>
                                                @endif
                                                @if($survey->status === 'Draft')
                                                    <form action="{{ route('survey.archive', $survey->id) }}" method="POST" style="display: inline-block;" onsubmit="return confirm('Arsipkan survey ini?')">
                                                        @csrf
                                                        <button type="submit" class="btn btn-outline-danger btn-sm">
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
                                <div class="col-12 col-md-6 col-lg-3">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-white border-dark-subtle"><i class="ri-search-line"></i></span>
                                        </div>
                                        <input type="text" name="customsearch1" id="customsearch1" class="form-control w-  border-dark-subtle border-left-0" placeholder="Search.." aria-label="search" aria-describedby="search" >
                                    </div>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover table-sm dt-responsive nowrap mt-2" id="scheduleTable1" width="100%"
                                        cellspacing="0">
                                    <thead class="table-light">
                                        <tr class="text-center">
                                            <th>No</th>
                                            <th>Created Date</th>
                                            <th>End Date</th>
                                            <th>Category</th>
                                            <th>Form Name</th>
                                            <th>Total Participant</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($surveyClosed as $survey)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $survey->created_at }}</td>
                                            <td>{{ $survey->end_date }}</td>
                                            <td>{{ $survey->category }}</td>
                                            <td>{{ $survey->title }}</td>
                                            <td style="text-align: center;">
                                                {{ $survey->survey_participant_count }}
                                            </td>
                                            <td><span class="badge 
                                                @if($survey->status === 'Ongoing')
                                                    bg-success
                                                @elseif($survey->status === 'Draft')
                                                    bg-secondary
                                                @else
                                                    text-bg-light
                                                @endif
                                            ">
                                                {{ $survey->status }}
                                            </span></td>
                                            <td> 
                                                @if($survey->status === 'Ongoing' || $survey->status == 'Draft')
                                                    <a href="{{ route('survey.edit', $survey->id) }}" class="btn btn-outline-warning btn-sm"><i class="ri-edit-box-line"></i></a>
                                                @endif
                                                @if($survey->status != 'Draft' && $survey->category === 'survey')
                                                    <a href="{{ route('survey.participants', \Illuminate\Support\Facades\Crypt::encryptString($survey->id)) }}" class="btn btn-outline-info btn-sm"><i class="ri-eye-line"></i></a>
                                                @elseif($survey->status != 'Draft' && $survey->category === 'vote')
                                                    <a href="{{ route('vote.participants', \Illuminate\Support\Facades\Crypt::encryptString($survey->id)) }}" class="btn btn-outline-info btn-sm"><i class="ri-eye-line"></i></a>
                                                @endif
                                                @if($survey->status === 'Draft')
                                                    <a href="#" class="btn btn-outline-danger btn-sm"><i class="ri-archive-line"></i></a>
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
                                <div class="col-12 col-md-6 col-lg-3">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-white border-dark-subtle"><i class="ri-search-line"></i></span>
                                        </div>
                                        <input type="text" name="customsearch2" id="customsearch2" class="form-control w-  border-dark-subtle border-left-0" placeholder="Search.." aria-label="search" aria-describedby="search" >
                                    </div>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover table-sm dt-responsive nowrap mt-2" id="scheduleTable2" width="100%"
                                        cellspacing="0">
                                    <thead class="table-light">
                                        <tr class="text-center">
                                            <th>No</th>
                                            <th>Created Date</th>
                                            <th>End Date</th>
                                            <th>Category</th>
                                            <th>Form Name</th>
                                            <th>Total Participant</th>
                                            <th>Status</th>
                                            <th>Archive At</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($surveyArchive as $survey)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $survey->created_at }}</td>
                                            <td>{{ $survey->end_date }}</td>
                                            <td>{{ $survey->category }}</td>
                                            <td>{{ $survey->title }}</td>
                                            <td style="text-align: center;">
                                                {{ $survey->survey_participant_count }}
                                            </td>
                                            <td><span class="badge 
                                                @if($survey->status === 'Ongoing')
                                                    bg-success
                                                @elseif($survey->status === 'Draft')
                                                    bg-secondary
                                                @else
                                                    text-bg-light
                                                @endif
                                            ">
                                                {{ $survey->status }}
                                            </span></td>
                                            <td> 
                                                {{ $survey->deleted_at }}
                                            </td>
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
</div>
@endsection