<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

/**
 * Idempotent: safe to re-run.
 *
 * Resolves the Role/Permission classes through config/permission.php rather
 * than importing Spatie's own -- this app maps them to App\Models\Role and
 * App\Models\Permission, which live on the `kpncorp` connection. Using
 * Spatie's classes directly would hit the default connection, where those
 * tables do not exist.
 *
 * `group_name`, `display_name` and `desc` are extra columns on that shared
 * permissions table. The role settings screen (pages/admin/roles/manageform)
 * lists only permissions whose group_name contains "extramile", renders
 * explode('_', group_name)[1] as the section heading, and shows `desc` as the
 * tooltip -- so a permission with a null group_name never appears there.
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
                'group_name' => 'extramile_Wellness',
                'display_name' => 'Menu Wellness',
                'desc' => 'Manage wellness activities, schedules, participants and attendance QR.',
            ],
            [
                'name' => 'viewmenuwellnesstype',
                'group_name' => 'extramile_Wellness',
                'display_name' => 'Menu Wellness Type',
                'desc' => 'Manage the wellness activity type master data.',
            ],
        ];

        foreach ($permissions as $permission) {
            $permissionClass::updateOrCreate(
                ['name' => $permission['name'], 'guard_name' => $guard],
                [
                    'group_name' => $permission['group_name'],
                    'display_name' => $permission['display_name'],
                    'desc' => $permission['desc'],
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
