<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

/**
 * Idempotent: safe to re-run.
 *
 * Resolves the Role/Permission classes through config/permission.php rather
 * than importing Spatie's own -- this app maps them to App\Models\Role and
 * App\Models\Permission, which live on the `sys_permission` connection.
 * Permission stamps domain_id with this app's domain (DOMAIN_SYS_PERM) on
 * create, and the role settings screen groups permissions by `group`.
 */
class WellnessPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissionClass = app(PermissionRegistrar::class)->getPermissionClass();
        $roleClass = app(PermissionRegistrar::class)->getRoleClass();
        $guard = config('auth.defaults.guard');

        $permissions = [
            [
                'name' => 'viewmenuwellness',
                'group' => 'Wellness',
                'label' => 'Menu Wellness',
            ],
            [
                'name' => 'viewmenuwellnesstype',
                'group' => 'Wellness',
                'label' => 'Menu Wellness Type',
            ],
        ];

        foreach ($permissions as $permission) {
            $permissionClass::updateOrCreate(
                ['name' => $permission['name'], 'guard_name' => $guard],
                [
                    'group' => $permission['group'],
                    'label' => $permission['label'],
                ]
            );
        }

        $names = array_column($permissions, 'name');

        foreach (['superadmin', 'admin'] as $roleName) {
            $role = $roleClass::where('name', $roleName)->first();

            if ($role) {
                $role->givePermissionTo($names);
                $this->command?->info("Granted wellness permissions to role: {$roleName}");
            } else {
                $this->command?->warn("Role not found, skipped: {$roleName}");
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
