@extends('layouts_.vertical', ['page_title' => __('Events')])

@section('css')
    @include('layouts_.shared.admin-datatable-css')
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h4 class="page-title mb-1">{{ __('Events') }}</h4>
            <div class="text-muted small">
                <span class="me-3"><i class="ri-calendar-line me-1"></i>{{ date('l, d F Y') }}</span>
                <span><i class="ri-time-line me-1"></i><span id="currentTime"></span> {{ __('WIB') }}</span>
            </div>
        </div>
        <a href="{{ route('admin.events.create') }}" class="btn btn-primary">{{ __('Create Event') }}</a>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <ul class="nav nav-tabs mb-3" id="eventTab" role="tablist">
                        @foreach (['Open', 'Closed', 'Archive'] as $tab)
                            <li class="nav-item" role="presentation">
                                <button class="nav-link @if ($loop->first) active @endif"
                                    id="{{ strtolower(str_replace(' ', '-', $tab)) }}-tab" data-bs-toggle="tab"
                                    data-bs-target="#{{ strtolower(str_replace(' ', '-', $tab)) }}" type="button"
                                    role="tab">
                                    {{ __($tab) }}
                                </button>
                            </li>
                        @endforeach
                    </ul>
                    <div class="tab-content" id="eventTabContent">
                        {{-- =============================== Detail tabel Open =============================== --}}
                        <div class="tab-pane fade show active" id="open" role="tabpanel">
                            <div class="table-responsive">
                                <table id="eventsOpenTable" class="table table-hover table-sm nowrap w-100 align-middle js-datatable">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="no-sort">{{ __('No') }}</th>
                                            <th>{{ __('Category') }}</th>
                                            <th>{{ __('Created Date') }}</th>
                                            <th>{{ __('Title') }}</th>
                                            <th>{{ __('Total Register') }}</th>
                                            <th>{{ __('Status') }}</th>
                                            <th class="no-sort">{{ __('Barcode') }}</th>
                                            <th class="no-sort">{{ __('Action') }}</th>
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
                                                    {{ __($event->status) }}
                                                </span>
                                            </td>
                                            <td>
                                                {{-- Show QR --}}
                                                @if($event->status != 'Draft')
                                                    <a href="{{ route('event.qrpng', \Illuminate\Support\Facades\Crypt::encryptString($event->id)) }}" target="_blank" class="btn btn-outline-primary btn-sm">
                                                        {{ __('Print QR') }}
                                                    </a>
                                                @endif
                                            </td>
                                            <td>
                                                {{-- List Participants --}}
                                                @if($event->status != 'Draft')
                                                    <a href="{{ route('events.participants', \Illuminate\Support\Facades\Crypt::encryptString($event->id)) }}" class="btn btn-outline-info btn-sm" title="{{ __('List Participants') }}">
                                                        <i class="ri-eye-line"></i>
                                                    </a>
                                                @endif

                                                {{-- Close Registration --}}
                                                @if($event->status === 'Ongoing' || $event->status == 'Open Registration')
                                                    <form id="close-form-{{ $event->id }}" action="{{ route('events.toggle-status', $event->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="button" class="btn btn-outline-secondary btn-sm btn-close-reg"
                                                            data-id="{{ $event->id }}" data-action="close" title="{{ __('Close Registration') }}">
                                                            <i class="ri-close-line"></i>
                                                        </button>
                                                    </form>
                                                @elseif($event->status === 'Full Booked')
                                                    <form id="close-form-{{ $event->id }}" action="{{ route('events.toggle-status', $event->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="button" class="btn btn-outline-success btn-sm btn-close-reg"
                                                            data-id="{{ $event->id }}" data-action="open" title="{{ __('Open Registration') }}">
                                                            <i class="ri-checkbox-circle-line"></i>
                                                        </button>
                                                    </form>
                                                @endif

                                                @if($event->status === 'Draft' || $event->status === 'Full Booked' || $event->status == 'Open Registration')
                                                    {{-- Edit Event --}}
                                                    <a href="{{ route('events.edit', $event->id) }}" class="btn btn-outline-warning btn-sm" title="{{ __('Edit Event') }}">
                                                        <i class="ri-edit-box-line"></i>
                                                    </a>

                                                    {{-- Archive Event --}}
                                                    <form id="delete-form-{{ $event->id }}" action="{{ route('events.softDelete', $event->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="button" class="btn btn-outline-danger btn-sm btn-archive"
                                                            data-id="{{ $event->id }}" title="{{ __('Archive Event') }}">
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
                            <div class="table-responsive">
                                <table id="eventsClosedTable" class="table table-hover table-sm nowrap w-100 align-middle js-datatable">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="no-sort">{{ __('No') }}</th>
                                            <th>{{ __('Category') }}</th>
                                            <th>{{ __('Created Date') }}</th>
                                            <th>{{ __('Title') }}</th>
                                            <th>{{ __('Total Register') }}</th>
                                            <th>{{ __('Status') }}</th>
                                            <th class="no-sort">{{ __('Action') }}</th>
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
                                                    {{ __($event->status) }}
                                                </span>
                                            </td>
                                            <td>
                                                {{-- List Participants --}}
                                                @if($event->status != 'Draft'  && $event->participants_count>0)
                                                    <a href="{{ route('events.participants', \Illuminate\Support\Facades\Crypt::encryptString($event->id)) }}" class="btn btn-outline-info btn-sm" title="{{ __('List Participants') }}">
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
                            <div class="table-responsive">
                                <table id="eventsArchiveTable" class="table table-hover table-sm nowrap w-100 align-middle js-datatable">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="no-sort">{{ __('No') }}</th>
                                            <th>{{ __('Category') }}</th>
                                            <th>{{ __('Created Date') }}</th>
                                            <th>{{ __('Title') }}</th>
                                            <th>{{ __('Total Register') }}</th>
                                            <th>{{ __('Status') }}</th>
                                            <th class="no-sort">{{ __('Action') }}</th>
                                            <th>{{ __('Archive Date') }}</th>
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
                                                    {{ __($event->status) }}
                                                </span>
                                            </td>
                                            <td>
                                                {{-- List Participants --}}
                                                @if($event->status != 'Draft' && $event->participants_count>0)
                                                    <a href="{{ route('events.participants', \Illuminate\Support\Facades\Crypt::encryptString($event->id)) }}" class="btn btn-outline-info btn-sm" title="{{ __('List Participants') }}">
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
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal QR -->
    <div class="modal fade" id="qrModal" tabindex="-1" aria-labelledby="qrModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content p-3">
                <div class="modal-header">
                    <h5 class="modal-title w-100 text-center" id="qrModalLabel">{{ __('QR Code') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
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
@endsection

@push('scripts')
    @include('layouts_.shared.admin-datatable-js')
@endpush
