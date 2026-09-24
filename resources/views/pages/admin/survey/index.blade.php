@extends('layouts_.vertical', ['page_title' => __('Survey')])

@section('css')
    @include('layouts_.shared.admin-datatable-css')
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h4 class="page-title mb-1">{{ __('Survey') }}</h4>
            <div class="text-muted small">
                <span class="me-3"><i class="ri-calendar-line me-1"></i>{{ now()->translatedFormat('l, d F Y') }}</span>
                <span><i class="ri-time-line me-1"></i><span id="currentTime"></span> {{ __('WIB') }}</span>
            </div>
        </div>
        <div class="dropdown">
            <button class="btn btn-primary dropdown-toggle" type="button" id="createDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                {{ __('Create') }}
            </button>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="createDropdown">
                <li><a class="dropdown-item" href="{{ route('admin.survey.create', ['type' => 'survey']) }}">{{ __('Survey Form') }}</a></li>
                <li><a class="dropdown-item" href="{{ route('admin.survey.create', ['type' => 'vote']) }}">{{ __('Voting Form') }}</a></li>
            </ul>
        </div>
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
                                <table id="surveyOpenTable" class="table table-hover table-sm nowrap w-100 align-middle js-datatable">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="no-sort">{{ __('No') }}</th>
                                            <th>{{ __('Created Date') }}</th>
                                            <th>{{ __('End Date') }}</th>
                                            <th>{{ __('Category') }}</th>
                                            <th>{{ __('Form Name') }}</th>
                                            <th>{{ __('Total Participant') }}</th>
                                            <th>{{ __('Status') }}</th>
                                            <th class="no-sort">{{ __('Action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($surveyList as $survey)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $survey->created_at }}</td>
                                            <td>{{ $survey->end_date }}</td>
                                            <td>{{ __($survey->category) }}</td>
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
                                                {{ __($survey->status) }}
                                            </span></td>
                                            <td>
                                                @if($survey->status === 'Ongoing' || $survey->status == 'Draft')
                                                    <a href="{{ route('survey.edit', $survey->id) }}" class="btn btn-outline-warning btn-sm"><i class="ri-edit-box-line"></i></a>
                                                @endif
                                                @if($survey->status != 'Draft' && $survey->category === 'survey')
                                                    @can('viewdetailsurvey')
                                                        <a href="{{ route('survey.participants', \Illuminate\Support\Facades\Crypt::encryptString($survey->id)) }}" class="btn btn-outline-info btn-sm"><i class="ri-eye-line"></i></a>
                                                    @endcan
                                                @elseif($survey->status != 'Draft' && $survey->category === 'vote')
                                                    <a href="{{ route('vote.participants', \Illuminate\Support\Facades\Crypt::encryptString($survey->id)) }}" class="btn btn-outline-info btn-sm"><i class="ri-eye-line"></i></a>
                                                @endif
                                                @if($survey->status === 'Draft')
                                                    <form action="{{ route('survey.archive', $survey->id) }}" method="POST" style="display: inline-block;" onsubmit="return confirm('{{ __('Archive this survey?') }}')">
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
                            <div class="table-responsive">
                                <table id="surveyClosedTable" class="table table-hover table-sm nowrap w-100 align-middle js-datatable">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="no-sort">{{ __('No') }}</th>
                                            <th>{{ __('Created Date') }}</th>
                                            <th>{{ __('End Date') }}</th>
                                            <th>{{ __('Category') }}</th>
                                            <th>{{ __('Form Name') }}</th>
                                            <th>{{ __('Total Participant') }}</th>
                                            <th>{{ __('Status') }}</th>
                                            <th class="no-sort">{{ __('Action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($surveyClosed as $survey)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $survey->created_at }}</td>
                                            <td>{{ $survey->end_date }}</td>
                                            <td>{{ __($survey->category) }}</td>
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
                                                {{ __($survey->status) }}
                                            </span></td>
                                            <td>
                                                @if($survey->status === 'Ongoing' || $survey->status == 'Draft')
                                                    <a href="{{ route('survey.edit', $survey->id) }}" class="btn btn-outline-warning btn-sm"><i class="ri-edit-box-line"></i></a>
                                                @endif
                                                @if($survey->status != 'Draft' && $survey->category === 'survey')
                                                    @can('viewdetailsurvey')
                                                        <a href="{{ route('survey.participants', \Illuminate\Support\Facades\Crypt::encryptString($survey->id)) }}" class="btn btn-outline-info btn-sm"><i class="ri-eye-line"></i></a>
                                                    @endcan
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
                            <div class="table-responsive">
                                <table id="surveyArchiveTable" class="table table-hover table-sm nowrap w-100 align-middle js-datatable">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="no-sort">{{ __('No') }}</th>
                                            <th>{{ __('Created Date') }}</th>
                                            <th>{{ __('End Date') }}</th>
                                            <th>{{ __('Category') }}</th>
                                            <th>{{ __('Form Name') }}</th>
                                            <th>{{ __('Total Participant') }}</th>
                                            <th>{{ __('Status') }}</th>
                                            <th>{{ __('Archive At') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($surveyArchive as $survey)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $survey->created_at }}</td>
                                            <td>{{ $survey->end_date }}</td>
                                            <td>{{ __($survey->category) }}</td>
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
                                                {{ __($survey->status) }}
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

@push('scripts')
    @include('layouts_.shared.admin-datatable-js')
@endpush
