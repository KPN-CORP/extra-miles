@if ($roleId)
<form id="delete-role-form" action="{{ route('roles.delete', $roleId) }}" method="POST" style="display: none">
  @csrf
  @method('DELETE')
</form>
<form id="roleForm" action="{{ route('roles.update', [], true) }}" method="POST">
  @csrf
<div class="card">
  <div class="card-body">
    <div class="row">
      <div class="col">
        <input type="hidden" name="roleId" value="{{ $roleId }}">
        <div class="mb-4 text-end">
            <a href="javascript:void(0)" onclick="deleteRole();" class="btn btn-outline-danger px-4 me-2"><span class="spinner-border spinner-border-sm me-1 d-none" role="status" aria-hidden="true"></span>Delete</a>
          <button type="submit" id="submitButton" class="btn btn-primary px-4"><span class="spinner-border spinner-border-sm me-1 d-none" role="status" aria-hidden="true"></span>Update</button>
        </div>
      </div>
    </div>
      @foreach ($roles as $role)
      @php
          // Decode the JSON string
          $restriction = json_decode($role->restriction, true);
      @endphp
      @endforeach
      <div class="row mb-3">
        <div class="col-md-8">
          <div class="form-group">
            <label for="roleName">Restrict Group Company (Keeping blank means no restrictions)</label>
                <select class="form-control select2" name="group_company[]" multiple="multiple">
                  @foreach ($groupCompanies as $groupCompany)
                      <option value="{{ $groupCompany }}" {{ isset($restriction['group_company']) && in_array($groupCompany, $restriction['group_company']) ? 'selected' : '' }}>{{ $groupCompany }}</option>
                  @endforeach
                </select>
          </div>
        </div>
      </div>
      <div class="row mb-3">
        <div class="col-md-8">
          <div class="form-group">
            <label for="roleName">Restrict Company (Keeping blank means no restrictions)</label>
            <select class="form-control select2" name="contribution_level_code[]" multiple="multiple">
                @foreach ($companies as $company)
                    <option value="{{ $company->contribution_level_code }}" {{ isset($restriction['contribution_level_code']) && in_array($company->contribution_level_code, $restriction['contribution_level_code']) ? 'selected' : '' }}>{{ $company->contribution_level.' ('.$company->contribution_level_code.')' }}</option>
                @endforeach
            </select>
          </div>
        </div>
      </div>
      <div class="row mb-3">
        <div class="col-md-8">
          <div class="form-group">
            <label for="roleName">Restrict Location (Keeping blank means no restrictions)</label>
              <select class="form-control select2" name="work_area_code[]" multiple="multiple">
                @foreach ($locations as $location)
                    <option value="{{ $location->work_area_code }}" {{ isset($restriction['work_area_code']) && in_array($location->work_area, $restriction['work_area_code']) ? 'selected' : '' }}>{{ $location->office_area.' ('.$location->group_company.')' }}</option>
                @endforeach
              </select>
          </div>
        </div>
      </div>
    </div>
  </div>
  <hr class="mt-3 mb-3">
  <div class="row mb-4">
    <div class="col-md-3 mb-3">
      <div class="list-group" id="list-tab" role="tablist">
        {{-- <a class="list-group-item list-group-item-action" id="list-onBehalf-list" data-bs-toggle="list" href="#list-onBehalf" role="tab" aria-controls="onBehalf">On Behalfs</a>
        <a class="list-group-item list-group-item-action" id="list-report-list" data-bs-toggle="list" href="#list-report" role="tab" aria-controls="report">{{ __('Report') }}</a>
        <a class="list-group-item list-group-item-action" id="list-guide-list" data-bs-toggle="list" href="#list-guide" role="tab" aria-controls="guide">Guideline</a> --}}
        <a class="list-group-item list-group-item-action active" id="list-setting-list" data-bs-toggle="list" href="#list-setting" role="tab" aria-controls="setting">Settings</a>
        {{-- @if (auth()->user()->hasRole('superadmin') || auth()->user()->hasRole('Admin HCIS'))
          <a class="list-group-item list-group-item-action" id="list-adminhcis-list" data-bs-toggle="list" href="#list-adminhcis" role="tab" aria-controls="adminhcis">HCIS Report</a>
        @endif --}}
      </div>
    </div>
    <div class="col-md-9">
      <div class="card">
        <div class="card-body">
          <div class="row">
            <div class="tab-content" id="nav-tabContent">
              <div class="tab-pane fade active show" id="list-setting" role="tabpanel" aria-labelledby="list-setting-list">
                <ul class="nav">
                  <li class="nav-item">
                    <a class="nav-link" id="setting-accessibility" data-bs-toggle="list" href="#list-setting-accessibility" role="tab" aria-controls="setting">Accessibility</a>
                  </li>
                </ul>
                <div class="tab-pane fade p-3 active show" id="list-setting-accessibility" role="tabpanel" aria-labelledby="setting-accessibility">
                  @php
                      $onbehalfsPermissions = $permissions->filter(function($permission) {
                          return stripos($permission->group_name, 'extramile') !== false;
                      });
                      $previousGroup = null;
                  @endphp
                  @foreach($onbehalfsPermissions as $permission)
                      @if($previousGroup != $permission->group_name)
                          <div class="form-check mb-3 bg-dark-subtle">
                              <label class="form-check-label" for="{{ $permission->group_name }}">
                                  <strong>{{ explode('_', $permission->group_name)[1] }}</strong>
                              </label>
                          </div>
                          @php
                              $previousGroup = $permission->group_name;
                          @endphp
                      @endif
                      <div class="form-check mb-3">
                          <input class="form-check-input" type="checkbox" id="{{ $permission->name }}" value="{{ $permission->id }}" name="{{ $permission->name }}" @if(in_array($permission->id, $permissionNames)) checked @endif>
                          <label class="form-check-label" for="{{ $permission->name }}">
                              {{ $permission->display_name }} <i class="ri-information-line" title="{{ $permission->desc }}"></i>
                          </label>
                      </div>
                  @endforeach
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</form>
@endif
