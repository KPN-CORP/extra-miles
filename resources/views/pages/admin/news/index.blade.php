@extends('layouts_.vertical', ['page_title' => __('News')])

@section('css')
    @include('layouts_.shared.admin-datatable-css')
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h4 class="page-title mb-0">{{ __('News') }}</h4>
        <a href="{{ route('news.create') }}" class="btn btn-primary">{{ __('Create News') }}</a>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="newsTable" class="table table-hover table-sm nowrap w-100 align-middle js-datatable">
                            <thead class="table-light">
                                <tr>
                                    <th class="no-sort">{{ __('No') }}</th>
                                    <th>{{ __('Category') }}</th>
                                    <th>{{ __('News Headline') }}</th>
                                    <th>{{ __('Views') }}</th>
                                    <th>{{ __('Likes') }}</th>
                                    <th>{{ __('Posted On') }}</th>
                                    <th>{{ __('Published Date') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th class="no-sort">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($news as $index => $row)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ __($row->category) }}</td>
                                        <td>{{ \Illuminate\Support\Str::limit($row->title, 35, '...') }}</td>
                                        <td>{{ $row->news_views_count }}</td>
                                        <td>{{ $row->news_likes_count }}</td>
                                        <td>{{ $row->created_at->translatedFormat('d M Y H:i:s') }}</td>
                                        <td>{{ \Carbon\Carbon::parse($row->publish_date)->translatedFormat('d M Y') }}</td>
                                        <td class="text-center"><span class="badge {{ $row->status == 'Publish' ? 'bg-info' : 'bg-secondary' }}">{{ __($row->status) }}</span></td>
                                        <td>
                                            <a href="{{ route('news.edit', $row->encrypted_id) }}" class="btn btn-outline-warning btn-sm"><i class="ri-edit-box-line"></i></a>
                                            <form action="{{ route('news.archive', $row->encrypted_id) }}" method="POST" style="display: inline-block;" onsubmit="return confirm({{ Js::from(__('Archive this news?')) }})">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-secondary btn-sm">
                                                    <i class="ri-archive-line"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
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
