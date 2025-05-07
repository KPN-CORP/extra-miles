@extends('layouts_.vertical', ['page_title' => 'Events'])

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0"></h3>
        <a href="{{ route('events.create') }}" class="btn btn-primary">Create Event</a>
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
                                    <th>Created Date</th>
                                    <th>Title</th>
                                    <th>Total Register</th>
                                    <th>Status</th>
                                    <th>Barcode</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- Contoh data dummy --}}
                                <tr>
                                    <td>1</td>
                                    <td>Sport</td>
                                    <td>10 April 2025</td>
                                    <td>KPN Sports Festival 2025</td>
                                    <td>0</td>
                                    <td><span class="badge bg-primary">Archive</span></td>
                                    <td><span class="badge bg-secondary">Print Barcode</span></td>
                                    <td>
                                        <a href="#" class="btn btn-outline-info btn-sm"><i class="ri-eye-line"></i></a>
                                        <a href="#" class="btn btn-outline-secondary btn-sm"><i class="ri-close-line"></i></a>  
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