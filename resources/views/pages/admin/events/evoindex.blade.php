@extends('layouts_.vertical', ['page_title' => 'EVO'])

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
                <span class="me-3 fs-5">
                    <i class="ri-calendar-line me-1"></i>{{ date('l, d F Y') }}
                </span>
                <span class="me-3">
                    <i class="ri-time-line me-1"></i><span id="currentTime"></span> WIB
                </span>
            </div>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('admin.evo.manage', $data->id) }}" class="btn btn-primary">
                Manage Event
            </a>

            {{-- 🔽 NEW: tombol buka modal export --}}
            <button type="button"
                    class="btn btn-outline-success"
                    data-bs-toggle="modal"
                    data-bs-target="#evoExportModal">
                <i class="ri-file-excel-2-line"></i> Export Report
            </button>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card shadow mb-4">
                <div class="card-body">
                    <ul class="nav nav-tabs mb-3" id="programTab" role="tablist">
                    @foreach ($options as $option)
                        @php $tabId = \Illuminate\Support\Str::slug($option, '-'); @endphp
                        <li class="nav-item" role="presentation">
                        <button
                            class="nav-link text-nowrap @if ($loop->first) active @endif"
                            id="{{ $tabId }}-tab"
                            data-bs-toggle="tab"
                            data-bs-target="#{{ $tabId }}"
                            type="button"
                            role="tab"
                        >
                            {{ $option }}
                        </button>
                        </li>
                    @endforeach
                    </ul>
                    <div class="tab-content" id="programTabContent">
                    @foreach ($options as $option)
                        @php
                        $tabId = \Illuminate\Support\Str::slug($option, '-');
                        $participants = $data->participants->filter(function ($p) use ($option) {
                            $formData = json_decode($p->form_data, true);

                            // Normalisasi untuk checkbox / radio
                            $selected = $formData['question_1'] ?? [];

                            // jika radio → jadikan array
                            $selected = is_array($selected) ? $selected : [$selected];

                            return in_array($option, $selected);
                        });

                        @endphp

                        <div class="tab-pane fade @if ($loop->first) show active @endif" id="{{ $tabId }}" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge badge-outline-secondary px-1">{{ $participants->count() }} Participants</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <div class="col">
                                <div class="row">
                                    <div class="col">
                                        <h5 class="fw-bold">{{ $option }}</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="col">
                                <div class="row d-flex justify-content-end align-items-center mb-3">
                                    <div class="col-md-6">
                                        <div class="input-group">
                                        <span class="input-group-text bg-white border-dark-subtle">
                                            <i class="ri-search-line"></i>
                                        </span>
                                        <input type="text" name="customsearch" id="customsearch"
                                                class="form-control border-start-0"
                                                placeholder="Search.." aria-label="search">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover table-sm align-middle" id="scheduleTable" width="100%">
                            <thead class="table-light">
                                <tr>
                                <th>No</th>
                                <th>Participant</th>
                                <th>Job Level</th>
                                <th>Department</th>
                                <th>BU</th>
                                <th>Location</th>
                                <th>Submitted At</th>
                                <th>WhatsApp</th>
                                {{-- <th>#</th> --}}
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($participants as $p)
                                @php
                                    $formData = json_decode($p->form_data, true);
                                    $country = $formData['countryCode'] ?? '+62';
                                    $number = preg_replace('/[^0-9]/', '', $formData['whatsapp_number'] ?? '');
                                    $phone = $country . $number;

                                    // ambil data bulan (question_2)
                                    $question = $formData['question_2'] ?? [];

                                    // ubah array jadi string (misalnya: "Januari, Februari")
                                    $questionList = is_array($question) ? implode(', ', $question) : $question;

                                    // pesan WhatsApp berdasarkan tab aktif
                                    $message = "Halo, perkenalkan saya $username dari tim Corporate Communication KPN Corp, ingin konfirmasi keikutsertaannya di program *$option*.";
                                    $encodedMessage = urlencode($message);
                                @endphp

                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $p->fullname .' ('.$p->employee_id.')' }}</td>
                                    <td>{{ $p->job_level }}</td>
                                    <td>{{ $p->unit }}</td>
                                    <td>{{ $p->business_unit }}</td>
                                    <td>{{ $p->location }}</td>
                                    <td>{{ $p->created_at->format('d M Y H:i') }}</td>
                                    <td>
                                    @if($number)
                                        <a href="https://wa.me/{{ ltrim($phone, '+') }}?text={{ $encodedMessage }}"
                                        target="_blank"
                                        class="text-success text-decoration-none">
                                        <i class="ri-whatsapp-line"></i> {{ $phone }}
                                        </a>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                    </td>
                                    {{-- <td>{{ $questionList ?: '-' }}</td> --}}
                                </tr>
                                @endforeach
                            </tbody>
                            </table>
                        </div>
                        </div>
                    @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
<!-- Modal Export EVO -->
<div class="modal fade" id="evoExportModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
          <form method="GET" action="{{ route('evo.export') }}">
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Pilih Program</label>
                    <select name="option" class="form-select mb-1" required>
                        <option value="all">All Programs</option>
                        @foreach($programs as $program)
                            <option value="{{ urlencode($program) }}">{{ $program }}</option>
                        @endforeach
                    </select>
                    <small class="text-muted">
                        * List berdasarkan seluruh program EVO, termasuk program lama.
                    </small>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>

                <button type="submit" class="btn btn-success">
                    <i class="ri-file-excel-2-line"></i> Export
                </button>
            </div>
        </form>
      </div>
  </div>
</div>
