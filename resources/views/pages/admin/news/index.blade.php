@extends('layouts_.vertical', ['page_title' => 'News'])

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0"></h3>
        <a href="{{ route('admin.events.create') }}" class="btn btn-primary">Create News</a>
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
                            <thead class="thead-light">
                                <tr class="text-center">
                                    <th>No</th>
                                    <th>Category</th>
                                    <th>Post Date</th>
                                    <th>News Title</th>
                                    <th>Like Count</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- Contoh data dummy --}}
                                <tr>
                                    <td>1</td>
                                    <td>Event/Comunity</td>
                                    <td>03 April 2025</td>
                                    <td>KPN Corp gelar buka puasa dan berbagi kepedulian serta kebahagiaan</td>
                                    <td>95</td>
                                    <td><span class="badge bg-success">Active</span></td>
                                    <td> 
                                        <a href="#" class="btn btn-outline-warning btn-sm"><i class="ri-edit-box-line"></i></a>
                                        <a href="#" class="btn btn-outline-secondary btn-sm"><i class="ri-archive-line"></i></a>
                                        <a href="#" class="btn btn-outline-danger btn-sm"><i class="ri-delete-bin-line"></i></a>
                                    </td>
                                </tr>
                                <tr>
                                    <td>2</td>
                                    <td>Event/Comunity</td>
                                    <td>02 April 2025</td>
                                    <td>Operasi pasar KPN Corp-Kemendag sediakan ribuan liter minyak</td>
                                    <td>12</td>
                                    <td><span class="badge bg-secondary">Archive</span></td>
                                    <td> 
                                        <a href="#" class="btn btn-outline-warning btn-sm"><i class="ri-edit-box-line"></i></a>
                                        <a href="#" class="btn btn-outline-secondary btn-sm"><i class="ri-archive-line"></i></a>
                                        <a href="#" class="btn btn-outline-danger btn-sm"><i class="ri-delete-bin-line"></i></a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection