<x-app-layout>
    @section('title', __('Assignment'))
    <x-slot name="content">
    <!-- Begin Page Content -->
    <div class="container-fluid">
        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-end mb-4">
            <button class="btn btn-primary px-4">{{ __('Create Assignment') }}</button>
        </div>
        <!-- Content Row -->
        <div class="row">
          <div class="col-md-12">

            <div class="card shadow mb-4">
              <div class="card-body">
                  <div class="table-responsive">
                      <table class="table table-hover" id="assignTable" width="100%" cellspacing="0">
                          <thead class="thead-light">
                              <tr class="text-center">
                                  <th>#</th>
                                  <th>{{ __('Assignment Name') }}</th>
                                  <th>{{ __('Assignment Code') }}</th>
                                  <th>{{ __('Created On') }}</th>
                                  <th>{{ __('Actions') }}</th>
                              </tr>
                          </thead>
                          <tbody>
                              <tr>
                                    <td>1</td>
                                    <td>KPN Head Office</td>
                                    <td>HO_0001</td>
                                    <td>25/05/2024</td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-circle btn-outline-primary" title="{{ __('Edit') }}"><i class="fas fa-edit"></i></button>
                                        <button class="btn btn-sm btn-circle btn-outline-danger" title="{{ __('Delete') }}"><i class="fas fa-trash-alt"></i></button>
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
    </x-slot>
</x-app-layout>