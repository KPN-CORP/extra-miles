@extends('layouts_.vertical', ['page_title' => 'News'])

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
        <h3 class="mb-0"></h3>
        <a href="{{ route('news.create') }}" class="btn btn-primary">Create News</a>
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
                            <input type="text" name="customsearch" id="customsearch" class="form-control w-  border-dark-subtle border-left-0" placeholder="Search.." aria-label="search" aria-describedby="search" >
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover table-sm dt-responsive nowrap mt-2" id="scheduleTable" width="100%"
                                cellspacing="0">
                            <thead class="table-light">
                                <tr class="text-center">
                                    <th>No</th>
                                    <th>Category</th>
                                    <th>News Headline</th>
                                    <th>Views</th>
                                    <th>Likes</th>
                                    <th>Posted On</th>
                                    <th>Published Date</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($news as $index => $row)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $row->category }}</td>
                                        <td>{{ \Illuminate\Support\Str::limit($row->title, 35, '...') }}</td>
                                        <td>{{ $row->news_views_count }}</td>
                                        <td>{{ $row->news_likes_count }}</td>
                                        <td>{{ $row->created_at->format('d M Y H:m:s') }}</td>
                                        <td>{{ \Carbon\Carbon::parse($row->publish_date)->format('d M Y') }}</td>
                                        <td class="text-center"><span class="badge {{ $row->status == 'Publish' ? 'bg-info' : 'bg-secondary' }}">{{ $row->status }}</span></td>
                                        <td> 
                                            <a href="{{ route('news.edit', $row->encrypted_id) }}" class="btn btn-outline-warning btn-sm"><i class="ri-edit-box-line"></i></a>
                                            <form action="{{ route('news.archive', $row->encrypted_id) }}" method="POST" style="display: inline-block;" onsubmit="return confirm('Archive this news?')">
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