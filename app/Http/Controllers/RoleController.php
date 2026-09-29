<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Employee;
use App\Models\Location;
use App\Models\ModelHasRole;
use App\Models\Permission;
use App\Models\Role;
use App\Models\RoleHasPermission;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\PermissionRegistrar;

class RoleController extends Controller
{
    protected $link;

    protected $userId;

    public function __construct()
    {
        $this->link = 'Roles';
        $this->userId = Auth()->user()->id;
    }

    public function index()
    {

        $parentLink = 'Settings';
        $link = $this->link;
        $active = '';

        return view('pages.admin.roles.app', compact('link', 'parentLink', 'active'));
    }

    public function assign()
    {
        $roles = Role::inCurrentDomain()->orderBy('name')->get();

        if (Auth()->user()->roles->first()->name != 'superadmin') {
            $roles = $roles->where('name', '!=', 'superadmin');
        }

        $parentLink = $this->link;
        $link = 'Assign';
        $active = 'assign';

        return view('pages.admin.roles.assign', compact('roles', 'link', 'parentLink', 'active'));
    }

    public function create()
    {
        $permissions = Permission::orderBy('group')->orderBy('label')->get();

        $locations = Employee::select('office_area', 'work_area_code', 'group_company')->orderBy('work_area_code')->distinct()->get();

        $groupCompanies = Employee::select('group_company')->orderBy('group_company')->distinct()->pluck('group_company');

        $companies = Company::select('contribution_level', 'contribution_level_code')->orderBy('contribution_level_code')->get();

        $parentLink = $this->link;
        $link = 'Create';
        $active = 'create';

        return view('pages.admin.roles.create', compact('link', 'parentLink', 'active', 'permissions', 'locations', 'groupCompanies', 'companies'));
    }

    public function manage()
    {

        $roles = Role::inCurrentDomain()->orderBy('name')->get();

        if (Auth()->user()->roles->first()->name != 'superadmin') {
            $roles = $roles->where('name', '!=', 'superadmin');
        }

        $parentLink = $this->link;
        $link = 'Manage';
        $active = 'manage';

        return view('pages.admin.roles.manage', compact('roles', 'link', 'parentLink', 'active'));
    }

    public function getAssignment(Request $request)
    {

        $roleId = $request->input('roleId');

        // $locations = Location::select('company_name', 'area', 'work_area')->orderBy('area')->get();

        // $groupCompanies = Location::select('company_name')->orderBy('company_name')->distinct()->pluck('company_name');

        $locations = Employee::select('office_area', 'work_area_code', 'group_company')->orderBy('work_area_code')->distinct()->get();

        $groupCompanies = Employee::select('group_company')->orderBy('group_company')->distinct()->pluck('group_company');

        $companies = Company::select('contribution_level', 'contribution_level_code')->orderBy('contribution_level_code')->get();

        // $roles = Role::with(['modelHasRole'])->where('id', $roleId)->get();
        $roles = ModelHasRole::with(['role'])->whereHas('role', function ($query) use ($roleId) {
            $query->where('id', $roleId);
        })->get();

        $users = Employee::select('id', 'fullname', 'employee_id', 'designation')->get();

        $parentLink = $this->link;
        $link = 'Manage';
        $active = 'manage';

        return view('pages.admin.roles.assignform', compact('roles', 'link', 'parentLink', 'active', 'users', 'roleId', 'locations', 'groupCompanies', 'companies'));
    }

    public function getPermission(Request $request)
    {

        $roleId = $request->input('roleId');

        $roles = Role::with(['permissions'])->where('id', $roleId)->get();

        // $locations = Location::select('company_name', 'area', 'work_area')->orderBy('area')->get();

        // $groupCompanies = Location::select('company_name')->orderBy('company_name')->distinct()->pluck('company_name');

        $locations = Employee::select('office_area', 'work_area_code', 'group_company')->orderBy('work_area_code')->distinct()->get();

        $groupCompanies = Employee::select('group_company')->orderBy('group_company')->distinct()->pluck('group_company');

        $companies = Company::select('contribution_level', 'contribution_level_code')->orderBy('contribution_level_code')->get();

        $permissions = Permission::orderBy('group')->orderBy('label')->get();

        $permissionNames = RoleHasPermission::where('role_id', $roleId)
            ->whereIn('permission_id', $permissions->pluck('id'))
            ->pluck('permission_id')
            ->toArray();

        $parentLink = $this->link;
        $link = 'Create';
        $active = 'create';

        return view('pages.admin.roles.manageform', compact('link', 'parentLink', 'active', 'roles', 'permissions', 'permissionNames', 'roleId', 'locations', 'groupCompanies', 'companies'));
    }

    public function assignUser(Request $request)
    {
        try {

            $roleId = $request->input('role_id');
            $selectedUserIds = $request->input('users_id', []);

            // Retrieve the previously saved user IDs for the given role
            $previouslySavedUserIds = ModelHasRole::where('role_id', $roleId)->pluck('model_id')->toArray();

            // Determine the user IDs that need to be deleted
            $userIdsToDelete = array_diff($previouslySavedUserIds, $selectedUserIds);

            // Perform deletion for the user IDs that need to be removed
            if (! empty($userIdsToDelete)) {
                ModelHasRole::where('role_id', $roleId)
                    ->whereIn('model_id', $userIdsToDelete)
                    ->delete();
            }

            // Now, you can loop through the selected user IDs and save them as needed
            foreach ($selectedUserIds as $userId) {
                // Save the user ID or perform any other action here
                // Check if the user ID is already associated with the role
                if (! in_array($userId, $previouslySavedUserIds)) {
                    // If not associated, save the association
                    ModelHasRole::create([
                        'role_id' => $roleId,
                        'model_type' => 'App\Models\User',
                        'model_id' => $userId,
                    ]);
                }
            }

            $role = Role::find($roleId);

            $userIds = json_encode($selectedUserIds);

            Log::info('Roles module: '.$this->userId.' Assigned user role to '.$userIds.' on RoleId '.$role->name);

            app()->make(PermissionRegistrar::class)->forgetCachedPermissions();

            // Optionally, you can redirect back to the form or another page after saving
            return redirect()->route('roles')->with('success', __('Users saved successfully!'));
        } catch (\Exception $e) {
            return redirect()->route('roles')->with('error', __('Users updated failed!'));
        }
    }

    public function store(Request $request): RedirectResponse
    {
        $roleName = $request->roleName;
        $guardName = 'web';

        $existingRole = Role::where('name', $roleName)->where('guard_name', $guardName)->first();

        if ($existingRole) {
            // Role with the same name already exists, handle accordingly (e.g., show error message)
            return redirect()->back()->with('error', __('Role with the same name already exists.'));
        }

        $role = new Role;
        $role->name = $roleName;
        $role->guard_name = $guardName;
        $role->fill($this->restrictionAttributes($request));
        $role->save();

        Log::info('Roles module: '.$this->userId.' Create Role & Permission '.$roleName.'. Restriction '.json_encode($role->only(['business_unit', 'company', 'location'])));

        foreach ($this->selectedPermissionIds($request) as $permissionId) {
            RoleHasPermission::create(['role_id' => $role->id, 'permission_id' => $permissionId]);

            Log::info('Roles module: '.$this->userId.' Add Permission '.$permissionId.' on Create Role & Permission '.$roleName);
        }

        app()->make(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->route('roles')->with('success', __('Role created successfully!'));
    }

    public function update(Request $request): RedirectResponse
    {
        try {
            // code...
            $roleId = $request->roleId;

            $role = Role::findOrFail($roleId);
            $role->fill($this->restrictionAttributes($request));

            Log::info('Roles module: '.$this->userId.' Updated Role & Permission '.$role->name.'. Restriction '.json_encode($role->only(['business_unit', 'company', 'location'])));

            $role->save();

            // The role may also hold other apps' permissions -- only replace ours.
            RoleHasPermission::where('role_id', $roleId)
                ->whereIn('permission_id', Permission::query()->select('id'))
                ->delete();

            foreach ($this->selectedPermissionIds($request) as $permissionId) {
                RoleHasPermission::create(['role_id' => $roleId, 'permission_id' => $permissionId]);
            }

            app()->make(PermissionRegistrar::class)->forgetCachedPermissions();

            return redirect()->route('roles')->with('success', __('Role updated successfully!'));
        } catch (\Exception $e) {
            return redirect()->route('roles')->with('error', __('Role updated failed!'));
        }
    }

    public function destroy($id): RedirectResponse
    {
        try {

            $role = Role::find($id);

            if ($role && $role->isSharedWithOtherDomains()) {
                // Other apps still use this role: drop only our permissions.
                RoleHasPermission::where('role_id', $id)
                    ->whereIn('permission_id', Permission::query()->select('id'))
                    ->delete();

                Log::info('Roles module: '.$this->userId.' Removed Extra Miles permissions from shared Role '.$role->name);

                app()->make(PermissionRegistrar::class)->forgetCachedPermissions();

                return redirect()->route('roles')->with('success', __('Role is also used by other apps, so only its permissions in this app were removed.'));
            }

            if ($role) {

                Log::info('Roles module: '.$this->userId.' Deleted Role '.$role->name);

                $role->delete();

                RoleHasPermission::where('role_id', $id)->delete();
                ModelHasRole::where('role_id', $id)->delete();

                app()->make(PermissionRegistrar::class)->forgetCachedPermissions();

                return redirect()->route('roles')->with('success', __('Role deleted successfully!'));
            }

            return redirect()->route('roles')->with('error', __('Role not found.'));
        } catch (\Exception $e) {
            return redirect()->route('roles')->with('error', __('Role deleted failed!'));
        }

    }

    /**
     * Restriction selects on the role form -> the role's data-access columns.
     */
    private function restrictionAttributes(Request $request): array
    {
        $businessUnit = array_values(array_filter((array) $request->input('group_company', [])));
        $company = array_values(array_filter((array) $request->input('contribution_level_code', [])));
        $location = array_values(array_filter((array) $request->input('work_area_code', [])));

        return [
            'business_unit' => $businessUnit ?: null,
            'company' => $company ?: null,
            'location' => $location ?: null,
            'is_data_access' => $businessUnit || $company || $location,
        ];
    }

    /**
     * Ids of this app's permissions whose checkbox is ticked. The ids come from
     * the database, never from the posted value, so another app's permission
     * cannot be attached.
     */
    private function selectedPermissionIds(Request $request): array
    {
        return Permission::pluck('id', 'name')
            ->filter(fn ($id, $name) => $request->filled($name))
            ->values()
            ->all();
    }
}
