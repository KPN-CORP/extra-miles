@extends('layouts_.vertical', ['page_title' => 'Events'])

@section('css')
    <style>
        .nav-tabs .nav-link.active {
            background-color: #ab2f2b !important;  /* Bootstrap primary color */
            color: white !important;
            font-weight: bold;
            border-radius: 0.375rem;
        }
        .nav-tabs .nav-link {
            transition: background-color 0.3s ease, color 0.3s ease;
        }
    </style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <span class="badge bg-warning text-dark"><h5>REQUEST : {{ $countRequest }}</h5></span>
            <span class="badge bg-danger"><h5>WAITING LIST : {{ $countWaitingList }}</h5></span>
            <span class="badge bg-success"><h5>APPROVED : {{ $countApproved }}</h5></span>
            <span class="badge bg-warning text-dark"><h5>CONFIRMATION NEEDED : {{ $countConfirmation }}</h5></span>
            <span class="badge bg-primary"><h5>CONFIRMED : {{ $countConfirmed }}</h5></span>
            <span class="badge bg-info"><h5>ATTENDING : {{ $countAttending }}</h5></span>
            <span class="badge bg-dark"><h5>NOT ATTENDING : {{ $countNotAttending }}</h5></span>
        </div>
    </div>

    <!-- Tab Content -->
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow mb-4">
                <div class="card-body">
                    <ul class="nav nav-tabs mb-3" id="participantTab" role="tablist">
                        @foreach(['Request', 'Waiting List', 'Approved', 'Attending', 'Not Attending'] as $tab)
                            <li class="nav-item" role="presentation">
                                <button class="nav-link @if($loop->first) active @endif" id="{{ strtolower(str_replace(' ', '-', $tab)) }}-tab"
                                        data-bs-toggle="tab" data-bs-target="#{{ strtolower(str_replace(' ', '-', $tab)) }}"
                                        type="button" role="tab">
                                    {{ $tab }}
                                </button>
                            </li>
                        @endforeach
                    </ul>
                    <div class="tab-content" id="participantTabContent">
                        {{-- =============================== Detail tabel request =============================== --}}
                        <div class="tab-pane fade show active" id="request" role="tabpanel">
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
                                <table class="table table-hover table-sm dt-responsive nowrap mt-2" id="scheduleTable" width="100%"
                                    cellspacing="0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>No</th>
                                            <th>Participant</th>
                                            <th>Business Unit</th>
                                            <th>Job Level</th>
                                            <th>Location</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($participants as $index => $p)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $p->fullname }}</td>
                                            <td>{{ $p->business_unit }}</td>
                                            <td>{{ $p->job_level }}</td>
                                            <td>{{ $p->location }}</td>
                                            <td>{{ $p->status }}</td>
                                            <td>
                                                <form action="{{ route('participants.approve', $p->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-outline-success btn-sm">Approve</button>
                                                </form>
                                                <form action="{{ route('participants.reject', $p->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-outline-danger btn-sm">Reject</button>
                                                </form>
                                            </td>
                                        </tr>
                                        @empty
                                        {{-- <tr>
                                            <td colspan="7" class="text-center">No data found.</td> --}}
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                                <div class="mt-3">
                                    {{ $participants->links() }}
                                </div>
                            </div>
                        </div>

                        {{-- =============================== Detail tabel waiting list =============================== --}}
                        <div class="tab-pane fade" id="waiting-list" role="tabpanel">
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
                                <table class="table table-hover table-sm dt-responsive nowrap mt-2" id="scheduleTable1" width="100%"
                                    cellspacing="0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>No</th>
                                            <th>Participant</th>
                                            <th>Business Unit</th>
                                            <th>Job Level</th>
                                            <th>Location</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($waitinglists as $index => $p)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $p->fullname }}</td>
                                            <td>{{ $p->business_unit }}</td>
                                            <td>{{ $p->job_level }}</td>
                                            <td>{{ $p->location }}</td>
                                            <td>{{ $p->status }}</td>
                                            <td>
                                                <form action="{{ route('participants.reinvite', $p->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-outline-success btn-sm">Re-invice</button>
                                                </form>
                                            </td>
                                        </tr>
                                        @empty
                                        {{-- <tr>
                                            <td colspan="7" class="text-center">No data found.</td> --}}
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                                <div class="mt-3">
                                    {{ $participants->links() }}
                                </div>
                            </div>
                        </div>

                        {{-- =============================== Detail tabel approved =============================== --}}
                        <div class="tab-pane fade" id="approved" role="tabpanel">
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
                                <table class="table table-hover table-sm dt-responsive nowrap mt-2" id="scheduleTable2" width="100%"
                                    cellspacing="0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>No</th>
                                            <th>Participant</th>
                                            <th>Business Unit</th>
                                            <th>Job Level</th>
                                            <th>Location</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($approveparticipants as $index => $p)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $p->fullname }}</td>
                                            <td>{{ $p->business_unit }}</td>
                                            <td>{{ $p->job_level }}</td>
                                            <td>{{ $p->location }}</td>
                                            <td>{{ $p->status }}</td>
                                        </tr>
                                        @empty
                                        {{-- <tr>
                                            <td colspan="7" class="text-center">No data found.</td> --}}
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                                <div class="mt-3">
                                    {{ $participants->links() }}
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
@if(session('success'))
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: '{{ session("success") }}',
                confirmButtonColor: '#ab2f2b',
                confirmButtonText: 'OK'
            });
        });
    </script>
@endif

@if(session('error'))
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: '{{ session("error") }}',
                confirmButtonColor: '#ab2f2b',
                confirmButtonText: 'OK'
            });
        });
    </script>
@endif