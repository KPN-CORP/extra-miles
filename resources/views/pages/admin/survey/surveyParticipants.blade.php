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
          <h5>Total Participant : <span class="text-danger">{{ $survey->survey_participant_count }}</span> / {{ $survey->quota }}</h5>
          <div class="d-flex gap-2">
              <select id="statusFilter" class="form-select" style="width: 150px;">
                  <option value="All">All Status</option>
                  <option value="Submitted">Submitted</option>
                  <option value="Not Yet">Not Yet</option>
              </select>
              <input type="text" id="searchInput" class="form-control" placeholder="Search..." />
              <a href="{{ route('survey.export', ['survey_id' => $survey->id]) }}" class="btn btn-outline-success" title="Download Report"><i class="ri-file-excel-line"></i></a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<div class="row">
    <!-- Sidebar Tabs -->
    <div class="col-md-4">
      <div class="nav flex-column nav-pills gap-2" id="v-tabs" role="tablist" aria-orientation="vertical">
        @foreach ($listParticipants as $index => $participant)
            @php
                $formData = json_decode($participant->form_data, true);
                $isActive = $index === 0 ? 'active' : '';
                $tabId = 'tab' . $index;
            @endphp
            <button class="nav-link text-start border rounded p-3 {{ $isActive }}" id="{{ $tabId }}-tab"
                data-bs-toggle="pill" data-bs-target="#{{ $tabId }}" type="button" role="tab" data-status="{{ $participant->form_data ? 'Submitted' : 'Not Yet' }}"
                data-name="{{ strtolower($participant->fullname) }}">
                <div class="d-flex justify-content-between">
                    <strong>{{ $participant->fullname }}</strong>
                    <span class="badge {{ $participant->form_data ? 'bg-success' : 'bg-danger' }}">
                        {{ $participant->form_data ? 'Submitted' : 'Not Yet' }}
                    </span>
                </div>
                @if ($participant->form_data && $participant->created_at)
                    <small class="text-muted">{{ \Carbon\Carbon::parse($participant->created_at)->format('d M Y h:i A') }}</small><br>
                @endif
                <small class="text-muted">{{ $participant->business_unit }} | {{ $participant->location }}</small>
            </button>
        @endforeach
      </div>
    </div>
  
    <!-- Tab Content -->
    <div class="col-md-8">
      <div class="tab-content" id="v-tabsContent">
        @foreach ($listParticipants as $index => $participant)
        @php
            $formData = json_decode($participant->form_data, true);
            $formSchema = json_decode($participant->formTemplate->form_schema ?? '[]', true);
            $isActive = $index === 0 ? 'show active' : '';
            $tabId = 'tab' . $index;
        @endphp
        <div class="tab-pane fade {{ $isActive }}" id="{{ $tabId }}" role="tabpanel">
            <div class="card">
                <div class="card-body">
                  <div class="p-3 mb-3 bg-light rounded">
                    <h5 class="card-title">{{ $participant->fullname }}</h5>
                    <small class="text-muted">{{ $participant->business_unit }} | {{ $participant->location }}</small>
                  </div>
                  @if (!empty($formSchema['fields']))
                      @foreach ($formSchema['fields'] as $idx => $field)
                          @php
                              $questionNumber = $idx + 1;
                              $label = $field['label'] ?? $field['name'];
                              $answer = $formData[$field['name']] ?? '-';
                          @endphp
                          <p><strong>{{ $label }}</strong>
                            <br>@if (is_array($answer))
                                <ul>
                                    @foreach ($answer as $item)
                                        <li>{{ $item }}</li>
                                    @endforeach
                                </ul>
                            @else
                                {{ $answer }}
                            @endif
                          </p>
                      @endforeach
                  @else
                      <p class="text-muted fst-italic">Belum mengisi form.</p>
                  @endif
                </div>
            </div>
        </div>
    @endforeach
      </div>
    </div>
  </div>
@endsection