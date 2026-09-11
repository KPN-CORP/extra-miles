@extends('layouts_.vertical', ['page_title' => __('Form Builder')])

@section('css')
    @include('layouts_.shared.admin-datatable-css')
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h4 class="page-title mb-0">{{ __('Form Builder') }}</h4>
        <a href="{{ route('form.create') }}" class="btn btn-primary">{{ __('Create Form') }}</a>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <ul class="nav nav-tabs mb-3" id="eventTab" role="tablist">
                        @foreach (['Active', 'Archive'] as $tab)
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
                        {{-- =============================== Detail tabel Active =============================== --}}
                        <div class="tab-pane fade show active" id="active" role="tabpanel">
                            <div class="table-responsive">
                                <table id="formActiveTable" class="table table-hover table-sm nowrap w-100 align-middle js-datatable">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="no-sort">{{ __('No') }}</th>
                                            <th>{{ __('Created Date') }}</th>
                                            <th>{{ __('Category') }}</th>
                                            <th>{{ __('Title') }}</th>
                                            <th class="no-sort">{{ __('Detail') }}</th>
                                            <th>{{ __('Status') }}</th>
                                            <th class="no-sort">{{ __('Action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($formTemplates as $form)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $form->created_at }}</td>
                                                <td>{{ $form->category }}</td>
                                                <td>{{ $form->title }}</td>
                                                <td>
                                                    <span class="badge bg-info text-white view-schema"
                                                          style="cursor:pointer"
                                                          data-id="{{ $form->id }}"
                                                          data-title="{{ $form->title }}"
                                                          data-schema='@json(json_decode($form->form_schema))'>
                                                          <i class="ri-search-line"></i> {{ __('View') }}
                                                    </span>
                                                </td>
                                                <td><span class="badge bg-success">{{ __('Active') }}</span></td>
                                                <td>
                                                    <a href="{{ route('formbuilder.edit', $form->id) }}"
                                                        class="btn btn-outline-warning btn-sm edit-quote-btn"
                                                        data-id="{{ $form->id }}">
                                                         <i class="ri-edit-box-line"></i>
                                                     </a>
                                                    <button type="button" class="btn btn-outline-danger btn-sm archive-btn" data-id="{{ $form->id }}">
                                                        <i class="ri-archive-line"></i>
                                                    </button>

                                                    <form id="archive-form-{{ $form->id }}" action="{{ route('formbuilder.archive', $form->id) }}" method="POST" style="display: none;">
                                                        @csrf
                                                        @method('DELETE')
                                                    </form>
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
                                <table id="formArchiveTable" class="table table-hover table-sm nowrap w-100 align-middle js-datatable">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="no-sort">{{ __('No') }}</th>
                                            <th>{{ __('Created Date') }}</th>
                                            <th>{{ __('Category') }}</th>
                                            <th>{{ __('Title') }}</th>
                                            <th class="no-sort">{{ __('Detail') }}</th>
                                            <th>{{ __('Status') }}</th>
                                            <th>{{ __('Deleted At') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($formTemplateArchive as $form)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $form->created_at }}</td>
                                                <td>{{ $form->category }}</td>
                                                <td>{{ $form->title }}</td>
                                                <td>
                                                    <span class="badge bg-info text-white view-schema"
                                                          style="cursor:pointer"
                                                          data-id="{{ $form->id }}"
                                                          data-title="{{ $form->title }}"
                                                          data-schema='@json(json_decode($form->form_schema))'>
                                                          <i class="ri-search-line"></i> {{ __('View') }}
                                                    </span>
                                                </td>
                                                <td><span class="badge bg-danger">{{ __('Archive') }}</span></td>
                                                <td>{{ $form->deleted_at }}</td>
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
<div class="modal fade" id="viewSchemaModal" tabindex="-1" aria-labelledby="schemaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="schemaModalLabel">{{ __('Form Schema') }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
        </div>
        <div class="modal-body" id="schemaFields">
            <!-- Dynamic content -->
        </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    @include('layouts_.shared.admin-datatable-js')
@endpush
