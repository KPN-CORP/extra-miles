@extends('layouts_.vertical', ['page_title' => 'Survey'])

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0"></h3>
        <div class="dropdown">
            <button class="btn btn-primary dropdown-toggle" type="button" id="createDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                Create
            </button>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="createDropdown">
                <li><a class="dropdown-item" href="{{ route('events.create') }}">Survey Form</a></li>
                <li><a class="dropdown-item" href="#">Voting Form</a></li>
            </ul>
        </div>
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
                                    <th>Created Date</th>
                                    <th>End Date</th>
                                    <th>Category</th>
                                    <th>Form Name</th>
                                    <th>Total Participant</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- Contoh data dummy --}}
                                <tr>
                                    <td>1</td>
                                    <td>10 April 2025</td>
                                    <td>15 April 2025</td>
                                    <td>Survey</td>
                                    <td>KPN Breakfasting 2025</td>
                                    <td>45</td>
                                    <td><span class="badge bg-success">Active</span></td>
                                    <td> 
                                        <a href="#" class="btn btn-outline-warning btn-sm"><i class="ri-edit-box-line"></i></a>
                                        <a href="#" class="btn btn-outline-info btn-sm"><i class="ri-eye-line"></i></a>
                                        <a href="#" class="btn btn-outline-secondary btn-sm"><i class="ri-archive-line"></i></a>
                                        <a href="#" class="btn btn-outline-danger btn-sm"><i class="ri-delete-bin-line"></i></a>
                                    </td>
                                </tr>
                                <tr>
                                    <td>2</td>
                                    <td>01 April 2025</td>
                                    <td>30 April 2025</td>
                                    <td>Voting</td>
                                    <td>KPN Got Talent 2025</td>
                                    <td>1008</td>
                                    <td><span class="badge bg-secondary">Archive</span></td>
                                    <td> 
                                        <a href="#" class="btn btn-outline-warning btn-sm"><i class="ri-edit-box-line"></i></a>
                                        <a href="#" class="btn btn-outline-info btn-sm"><i class="ri-eye-line"></i></a>
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