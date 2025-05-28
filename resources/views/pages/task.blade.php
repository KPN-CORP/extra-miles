<x-app-layout>
    @section('title', 'Tasks')
    <x-slot name="content">
    <!-- Begin Page Content -->
    <div class="container-fluid">
        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
        </div>
        <!-- Content Row -->
        <div class="row">
          <div class="col-md-12">

            <div class="card shadow mb-4">
              <div class="card-body">
                  <div class="table-responsive">
                      <table class="table table-hover" id="taskTable" width="100%" cellspacing="0">
                          <thead class="thead-light">
                              <tr class="text-center">
                                  <th>Employees</th>
                                  <th>Category</th>
                                  <th>Trigger date</th>
                                  <th>Actions</th>
                              </tr>
                          </thead>
                          <tbody>
                              <tr>
                                  <td>Tiger Nixon</td>
                                  <td>KPI Setting</td>
                                  <td>Edinburgh</td>
                                  <td class="text-center"><button class="btn btn-sm btn-outline-primary px-4">Act</button></td>
                              </tr>
                              <tr>
                                  <td>Garrett Winters</td>
                                  <td>KPI Setting</td>
                                  <td>Tokyo</td>
                                  <td class="text-center"><button class="btn btn-sm btn-outline-primary px-4">Act</button></td>
                              </tr>
                              <tr>
                                  <td>Ashton Cox</td>
                                  <td>KPI Setting</td>
                                  <td>San Francisco</td>
                                  <td class="text-center"><button class="btn btn-sm btn-outline-primary px-4">Act</button></td>
                              </tr>
                              <tr>
                                  <td>Cedric Kelly</td>
                                  <td>KPI Setting</td>
                                  <td>Edinburgh</td>
                                  <td class="text-center"><button class="btn btn-sm btn-outline-primary px-4">Act</button></td>
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