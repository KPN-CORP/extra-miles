@extends('layouts_.vertical', ['page_title' => 'Survey/Voting'])

@section('css')
    <style>
        .nav-link.active {
            background-color: white !important;
            color: #ab2f2b !important;
            border: 2px solid #ab2f2b !important;
            border-radius: 0.5rem;
        }

        .nav-link {
            background-color: white !important;
            color: #212529; /* warna teks standar Bootstrap */
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .nav-link.active small {
            color: #ab2f2b !important;
        }
    </style>
@endsection

@section('content')
<div class="row">
  <div class="col-md-12">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <h5>Total Participant : <span class="text-danger">{{ $survey->survey_participant_count }}</span></h5>
          <div class="d-flex gap-2">
              {{-- <select id="statusFilter" class="form-select" style="width: 150px;">
                  <option value="All">All Status</option>
                  <option value="Submitted">Submitted</option>
                  <option value="Not Yet">Not Yet</option>
              </select> 
              <input type="text" id="searchInput" style="width: 200px;" class="form-control" placeholder="Search..." />--}}
              <a href="{{ route('survey.export', ['survey_id' => $survey->id]) }}" class="btn btn-outline-success" title="Download Report"><i class="ri-file-excel-line"></i></a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<div class="row">
  <!-- Sidebar Tabs -->
  <div class="col-md-12">
    <div class="card">
      <div class="card-body">
        {!! $survey->description !!}
        @php
            $schema = json_decode($survey->formTemplate->form_schema ?? '[]', true);
            $participants = $survey->participants ?? [];

            // Decode semua form_data peserta
            $responses = [];
            foreach ($listParticipants as $p) {
                $data = json_decode($p->form_data ?? '[]', true);
                if (is_array($data)) {
                    $responses[] = $data;
                }
            }
        @endphp

        @if (!empty($schema['fields']))
            @foreach ($schema['fields'] as $field)
                <div class="mb-4">
                    <p>{{ $field['label'] }}</p>

                    @if ($field['type'] === 'radio' && isset($field['options']))
                        @php
                            $counts = array_count_values(array_column($responses, $field['name']));
                            $total = count(array_filter(array_column($responses, $field['name'])));
                        @endphp
                        @foreach ($field['options'] as $option)
                            @php
                                $count = $counts[$option] ?? 0;
                                $percentage = $total > 0 ? round(($count / $total) * 100) : 0;
                            @endphp
                            <div class="d-flex align-items-center mb-2">
                              <div class="w-50">
                                <small><strong>{{ $option }}</strong></small>
                                <div class="progress">
                                    <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $percentage }}%;" aria-valuenow="{{ $percentage }}" aria-valuemin="0" aria-valuemax="100">
                                        {{ $percentage }}%
                                    </div>
                                </div>
                              </div>
                              <div class="ms-2 text-nowrap">
                                  <small>Vote Count : {{ $count }} </small>
                              </div>
                            </div>
                        @endforeach
                    @elseif ($field['type'] === 'textarea')
                        <ul>
                            @foreach ($responses as $response)
                                @if (!empty($response[$field['name']]))
                                    <li>{{ $response[$field['name']] }}</li>
                                @endif
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endforeach
        @else
            <p class="text-muted fst-italic">Form schema belum tersedia.</p>
        @endif
      </div>
    </div>
  </div>
</div>
@endsection