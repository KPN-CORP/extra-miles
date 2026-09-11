@extends('layouts_.vertical', ['page_title' => __('Quotes & Affirmation')])

@section('css')
    @include('layouts_.shared.admin-datatable-css')
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h4 class="page-title mb-0">{{ __('Quotes & Affirmation') }}</h4>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createQuoteModal">
            {{ __('Create Quote') }}
        </button>
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
                                <table id="quotesActiveTable" class="table table-hover table-sm nowrap w-100 align-middle js-datatable">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="no-sort">{{ __('No') }}</th>
                                            <th>{{ __('Created Date') }}</th>
                                            <th>{{ __('Author') }}</th>
                                            <th>{{ __('Quote') }}</th>
                                            <th>{{ __('Status') }}</th>
                                            <th class="no-sort">{{ __('Action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($listQuotes as $Quote)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $Quote->created_at }}</td>
                                                <td>{{ $Quote->author }}</td>
                                                <td>{{ $Quote->quotes }}</td>
                                                <td><span class="badge bg-success">{{ __('Active') }}</span></td>
                                                <td>
                                                    <a href="#"
                                                        class="btn btn-outline-warning btn-sm edit-quote-btn"
                                                        data-id="{{ $Quote->id }}"
                                                        data-author="{{ $Quote->author }}"
                                                        data-quotes="{{ $Quote->quotes }}"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#editQuoteModal">
                                                        <i class="ri-edit-box-line"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-outline-danger btn-sm archive-btn" data-id="{{ $Quote->id }}">
                                                        <i class="ri-archive-line"></i>
                                                    </button>

                                                    <form id="archive-form-{{ $Quote->id }}" action="{{ route('quotes.destroy', $Quote->id) }}" method="POST" style="display: none;">
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
                                <table id="quotesArchiveTable" class="table table-hover table-sm nowrap w-100 align-middle js-datatable">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="no-sort">{{ __('No') }}</th>
                                            <th>{{ __('Created Date') }}</th>
                                            <th>{{ __('Author') }}</th>
                                            <th>{{ __('Quote') }}</th>
                                            <th>{{ __('Status') }}</th>
                                            <th>{{ __('Archive At') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($quoteArchive as $Quote)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $Quote->created_at }}</td>
                                                <td>{{ $Quote->author }}</td>
                                                <td>{{ $Quote->quotes }}</td>
                                                <td><span class="badge bg-danger">{{ __('Archive') }}</span></td>
                                                <td>{{ $Quote->deleted_at }}</td>
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
    <div class="modal fade" id="createQuoteModal" tabindex="-1" aria-labelledby="createQuoteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <form action="{{ route('quotes.store') }}" method="POST">
              @csrf
              <div class="modal-header">
                <h5 class="modal-title" id="createQuoteModalLabel">{{ __('Create New Quote') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
              </div>

              <div class="modal-body">
                <div class="mb-3">
                  <label for="author" class="form-label">{{ __('Author') }}</label>
                  <input type="text" class="form-control" id="author" name="author" required>
                </div>
                <div class="mb-3">
                  <label for="quote" class="form-label">{{ __('Quote') }}</label>
                  <textarea class="form-control" id="quote" name="quote" rows="3" required></textarea>
                </div>
              </div>

              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                <button type="submit" class="btn btn-primary">{{ __('Submit') }}</button>
              </div>

            </form>
          </div>
        </div>
    </div>
    <div class="modal fade" id="editQuoteModal" tabindex="-1" aria-labelledby="editQuoteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
          <form method="POST" action="" id="editQuoteForm">
              @csrf
              @method('PUT')
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title" id="editQuoteModalLabel">{{ __('Edit Quote') }}</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit-author" class="form-label">{{ __('Author') }}</label>
                        <input type="text" name="author" class="form-control" id="edit-author" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit-quotes" class="form-label">{{ __('Quote') }}</label>
                        <textarea name="quotes" class="form-control" id="edit-quotes" rows="4" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                  <button type="submit" class="btn btn-primary">{{ __('Update Quote') }}</button>
                </div>
              </div>
          </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    @include('layouts_.shared.admin-datatable-js')
@endpush
