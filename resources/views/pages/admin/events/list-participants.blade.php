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

        table.dataTable tbody tr>.dtfc-fixed-left,
        table.dataTable tbody tr>.dtfc-fixed-right {
            z-index: 3;
            background-color: white !important;
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="d-flex flex-wrap gap-1 mb-3">
            <h5><span class="badge bg-secondary fs-4 p-1">Request: {{ $countRequest }}</span></h5>
            <h5><span class="badge bg-danger fs-4 p-1">Waiting List: {{ $countWaitingList }}</span></h5>
            <h5><span class="badge bg-success fs-4 p-1">Approved: {{ $countApproved }}</span></h5>
            <h5><span class="badge bg-warning fs-4 p-1">Confirmation Needed: {{ $countConfirmation }}</span></h5>
            <h5><span class="badge bg-info fs-4 p-1">Attending: {{ $countAttending }}</span></h5>
            <h5><span class="badge bg-dark fs-4 p-1">Not Attending: {{ $countNotAttending }} | Canceled: {{ $countCanceled }}
                </span></h5>
            <form action="{{ route('participants.export', $event->id) }}" method="GET" class="align-items-center">
                <button type="submit" class="btn btn-success fs-4 p-1 align-middle" style="line-height: 1.2; height: 30px;">
                    <i class="ri-file-excel-2-line"></i> Export Participants
                </button>
            </form>
        </div>

        <!-- Tab Content -->
        <div class="row">
            <div class="col-md-12">
                <div class="card shadow mb-4">
                    <div class="card-body">
                        <ul class="nav nav-tabs mb-3" id="participantTab" role="tablist">
                            @foreach (['Request', 'Approved', 'Attendance'] as $tab)
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
                        <div class="tab-content" id="participantTabContent">
                            {{-- =============================== Detail tabel request =============================== --}}
                            <div class="tab-pane fade show active" id="request" role="tabpanel">
                                {{-- <p class="text-muted fs-14">Snow is a clean, flat toolbar theme.</p> --}}
                                <div class="d-flex justify-content-between align-items-center">
                                    <h4 class="card-title">
                                        Participants Approved: 
                                        <span style="color: {{ $countApproved >= $event->quota ? 'green' : 'red' }};">
                                            {{ $countApproved }}/{{ $event->quota }}
                                            @if($countApproved >= $event->quota)
                                                (Full)
                                            @else
                                                (Remaining {{ $event->quota - $countApproved }})
                                            @endif
                                        </span>
                                    </h4>
                                    <div class="col-12 col-md-6 col-lg-3">
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-white border-dark-subtle">
                                                    <i class="ri-search-line"></i>
                                                </span>
                                            </div>
                                            <input type="text" name="customsearch" id="customsearch"
                                                class="form-control border-dark-subtle border-start-0"
                                                placeholder="Search.." aria-label="search" aria-describedby="search">
                                        </div>
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-hover table-sm" id="scheduleTable" width="100%"
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
                                                    <td>
                                                        <span class="badge 
                                                            @if($p->status === 'Canceled')
                                                                bg-dark
                                                            @elseif($p->status === 'Registered')
                                                                bg-success
                                                            @elseif($p->status === 'Confirmation')
                                                                bg-warning
                                                            @else
                                                                bg-danger
                                                            @endif
                                                        ">{{ $p->status }}</span>
                                                    </td>
                                                    <td>
                                                        @if ($p->status == 'Waiting List')
                                                            <form action="{{ route('participants.approve', $p->id) }}"
                                                                method="POST" class="d-inline">
                                                                @csrf
                                                                {{-- <button type="submit"
                                                                    class="btn btn-outline-success btn-sm">Approve</button> --}}
                                                                <button type="submit"
                                                                    class="btn btn-sm {{ $countApproved >= $event->quota ? 'btn-secondary' : 'btn-outline-success' }}"
                                                                    {{ $countApproved >= $event->quota ? 'disabled' : '' }}
                                                                    title="{{ $countApproved >= $event->quota ? 'Full Quota' : 'Approve participant' }}">
                                                                    Approve
                                                                </button>
                                                            </form>
                                                            {{-- <form action="{{ route('participants.reject', $p->id) }}"
                                                                method="POST" class="d-inline">
                                                                @csrf
                                                                <button type="submit"
                                                                    class="btn btn-outline-danger btn-sm">Reject</button>
                                                            </form> --}}
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                            @endforelse
                                        </tbody>
                                    </table>
                                    {{-- <div class="mt-3">
                                    {{ $participants->links() }}
                                </div> --}}
                                </div>
                            </div>

                            {{-- =============================== Detail tabel approved =============================== --}}
                            <div class="tab-pane fade" id="approved" role="tabpanel">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h3 class="card-title"></h3>
                                    <div class="col-12 col-md-6 col-lg-3">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-white border-dark-subtle"><i
                                                    class="ri-search-line"></i></span>
                                        </div>
                                        <input type="text" name="customsearch2" id="customsearch2"
                                            class="form-control w-border-dark-subtle border-left-0" placeholder="Search.."
                                            aria-label="search" aria-describedby="search">
                                    </div>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-hover table-sm mt-2" id="scheduleTable1" width="100%"
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
                                            @forelse($approveparticipants as $index => $p)
                                                <tr>
                                                    <td style="width:2%">{{ $index + 1 }}</td>
                                                    <td>{{ $p->fullname }}</td>
                                                    <td>{{ $p->business_unit }}</td>
                                                    <td style="width:4%">{{ $p->job_level }}</td>
                                                    <td>{{ $p->location }}</td>
                                                    <td>
                                                        @if ($p->status === 'Registered')
                                                            <span class="badge bg-success">Registered</span>
                                                        @elseif($p->status === 'Confirmation')
                                                            <span class="badge bg-warning">Confirmation Needed</span>
                                                        @endif
                                                    </td>
                                                    <td style="width:18%">
                                                        @php
                                                        $formData = json_decode($p->form_data, true);
                                                        $waNumber = null;

                                                        if (!empty($formData['countryCode']) && !empty($formData['whatsapp_number'])) {
                                                            // Gabungkan kode negara dan nomor
                                                            $rawNumber = $formData['countryCode'] . $formData['whatsapp_number'];

                                                            // Hapus karakter non-digit
                                                            $waNumber = preg_replace('/[^0-9]/', '', $rawNumber);

                                                            // Normalisasi jika diawali 620 → jadi 62
                                                            if (substr($waNumber, 0, 3) === '620') {
                                                                $waNumber = '62' . substr($waNumber, 3);
                                                            }

                                                            // Buat pesan default
                                                            $defaultMessage = urlencode("Halo {$p->employee->fullname}, mohon konfirmasi kehadiran Anda untuk event {$event->title} di {$event->event_location} yang akan datang.");
                                                        }
                                                    @endphp

                                                    @if($waNumber)
                                                        <a href="https://wa.me/{{ $waNumber }}?text={{ $defaultMessage }}"
                                                        target="_blank"
                                                        class="btn btn-outline-success btn-sm">
                                                            <i class="bi bi-whatsapp"></i> Remind
                                                        </a>
                                                    @else
                                                        <button type="button"
                                                                class="btn btn-outline-secondary btn-sm" disabled>
                                                            No Number
                                                        </button>
                                                    @endif
                                                        <button type="button"
                                                            class="btn btn-outline-danger btn-sm"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#cancelModal{{ $p->id }}">
                                                            Canceled
                                                        </button>
                                                    </td>
                                                </tr>
                                                <!-- Modal -->
                                                <div class="modal fade" id="cancelModal{{ $p->id }}" tabindex="-1" aria-labelledby="cancelModalLabel{{ $p->id }}" aria-hidden="true">
                                                    <div class="modal-dialog">
                                                    <form action="{{ route('participants.reject', $p->id) }}" class="canceled-form" method="POST">
                                                        @csrf
                                                        <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="cancelModalLabel{{ $p->id }}">Cancel Reason</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="mb-3">
                                                            <label for="reason{{ $p->id }}" class="form-label">Please provide a reason:</label>
                                                            <textarea name="messages" id="reason{{ $p->id }}" class="form-control" rows="3" required></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                            <button type="submit" class="btn btn-danger">Submit Cancel</button>
                                                        </div>
                                                        </div>
                                                    </form>
                                                    </div>
                                                </div>
                                            @empty
                                                {{-- <tr>
                                            <td colspan="7" class="text-center">No data found.</td> --}}
                                                {{-- </tr> --}}
                                            @endforelse
                                        </tbody>
                                    </table>
                                    {{-- <div class="mt-3">
                                    {{ $approveparticipants->links() }}
                                </div> --}}
                                </div>
                            </div>

                            {{-- =============================== Detail tabel waiting list =============================== --}}
                            <div class="tab-pane fade" id="attendance" role="tabpanel">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h3 class="card-title"></h3>
                                    <div class="col-12 col-md-6 col-lg-3">
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-white border-dark-subtle"><i
                                                        class="ri-search-line"></i></span>
                                            </div>
                                            <input type="text" name="customsearch1" id="customsearch1"
                                                class="form-control w-border-dark-subtle border-left-0" placeholder="Search.."
                                                aria-label="search" aria-describedby="search">
                                        </div>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-hover table-sm mt-2" id="scheduleTable2" width="100%"
                                        cellspacing="0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>No</th>
                                                <th>Participant</th>
                                                <th>Business Unit</th>
                                                <th>Job Level</th>
                                                <th>Location</th>
                                                <th>Status</th>
                                                <th>Attending At</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($attending as $index => $p)
                                                <tr>
                                                    <td>{{ $index + 1 }}</td>
                                                    <td>{{ $p->fullname }}</td>
                                                    <td>{{ $p->business_unit }}</td>
                                                    <td>{{ $p->job_level }}</td>
                                                    <td>{{ $p->location }}</td>
                                                    <td>{{ $p->attending_status }}</td>
                                                    <td>{{ $p->attending_at }}</td>
                                                </tr>
                                            @empty
                                                {{-- <tr>
                                            <td colspan="7" class="text-center">No data found.</td> --}}
                                                {{-- </tr> --}}
                                            @endforelse
                                        </tbody>
                                    </table>
                                    {{-- <div class="mt-3">
                                    {{ $waitinglists->links() }}
                                </div> --}}
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection