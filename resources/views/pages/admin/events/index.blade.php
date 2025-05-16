@extends('layouts_.vertical', ['page_title' => 'Events'])

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0"></h3>
        <a href="{{ route('admin.events.create') }}" class="btn btn-primary">Create Event</a>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow mb-4">
                <div class="card-body">
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
                            <thead class="thead-light">
                                <tr class="text-center">
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
                                    <td>{{ $event->quota }}</td>
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
                                                bg-light
                                            @endif
                                        ">
                                            {{ $event->status }}
                                        </span>
                                    </td>
                                    <td>
                                        {{-- Show QR --}}
                                        @if($event->status != 'Draft')
                                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="showQRModal('{{ $event->barcode_token }}')">
                                                Print QR
                                            </button>
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
                                                <button type="button" class="btn btn-outline-secondary btn-sm"
                                                    data-id="{{ $event->id }}" data-action="close" title="Close Registration">
                                                    <i class="ri-close-line"></i>
                                                </button>
                                            </form>
                                        @elseif($event->status === 'Full Booked')
                                            <form id="close-form-{{ $event->id }}" action="{{ route('events.close', $event->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="button" class="btn btn-outline-success btn-sm"
                                                    data-id="{{ $event->id }}" data-action="open" title="Open Registration">
                                                    <i class="ri-checkbox-circle-line"></i>
                                                </button>
                                            </form>
                                        @endif

                                        {{-- Edit Event --}}
                                        <a href="{{ route('events.edit', $event->id) }}" class="btn btn-outline-warning btn-sm" title="Edit Event">
                                            <i class="ri-edit-box-line"></i>
                                        </a>

                                        {{-- Archive Event --}}
                                        <form id="delete-form-{{ $event->id }}" action="{{ route('events.softDelete', $event->id) }}" method="POST" style="display:inline-block;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-outline-danger btn-sm" 
                                                data-id="{{ $event->id }}" title="Archive Event">
                                                <i class="ri-archive-line"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                {{-- <tr>
                                    <td colspan="8" class="text-center">
                                        No events found.
                                    </td>
                                </tr> --}}
                                @endforelse
                            </tbody>
                        </table>
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // script QR Code
    function showQRModal(token) {
        document.getElementById("qrcode").innerHTML = "";

        const dummyURL = `https://example.com/ticket/${token}`;
        
        new QRCode(document.getElementById("qrcode"), {
            text: dummyURL,
            width: 300,
            height: 300,
            colorDark: "#000000",
            colorLight: "#ffffff",
            correctLevel: QRCode.CorrectLevel.H
        });

        const linkEl = document.getElementById("dummyLink");
        linkEl.href = dummyURL;
        linkEl.textContent = dummyURL;

        const modal = new bootstrap.Modal(document.getElementById('qrModal'));
        modal.show();
    }

    // script Close Registration
    document.addEventListener("DOMContentLoaded", function () {
        document.querySelectorAll(".btn-close-reg").forEach(function (btn) {
            btn.addEventListener("click", function () {
                const eventId = this.getAttribute("data-id");

                Swal.fire({
                    title: 'Are you sure?',
                    text: "This will close registration and set the status to 'Full Booked'.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ab2f2b',
                    cancelButtonColor: '#aaa',
                    confirmButtonText: 'Yes, close it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById(`close-form-${eventId}`).submit();
                    }
                });
            });
        });
    });

    // script Archive Event
    document.addEventListener("DOMContentLoaded", function () {
        document.querySelectorAll(".btn-archive").forEach(function (button) {
            button.addEventListener("click", function () {
                const eventId = this.getAttribute("data-id");
    
                Swal.fire({
                    title: 'Are you sure?',
                    text: "Event will be archived.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ab2f2b',
                    cancelButtonColor: '#aaa',
                    confirmButtonText: 'Yes, archive it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById(`delete-form-${eventId}`).submit();
                    }
                });
            });
        });
    });

    document.addEventListener("DOMContentLoaded", function () {
        document.querySelectorAll(".btn-close-reg").forEach(function (btn) {
            btn.addEventListener("click", function () {
                const id = this.dataset.id;
                const action = this.dataset.action;
                const message = action === 'close' 
                    ? 'Close registration for this event?' 
                    : 'Reopen registration for this event?';

                Swal.fire({
                    title: 'Are you sure?',
                    text: message,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#ab2f2b',
                    cancelButtonColor: '#aaa',
                    confirmButtonText: 'Yes, continue'
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById(`close-form-${id}`).submit();
                    }
                });
            });
        });
    });
</script>