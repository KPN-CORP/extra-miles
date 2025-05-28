<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Permission::create(['name'=>'menu-goals']);
        Permission::create(['name'=>'menu-report']);
        Permission::create(['name'=>'menu-admin']);

        Role::create(['name'=>'superadmin']);
        Role::create(['name'=>'admin']);
        Role::create(['name'=>'user']);

        $roleSuperadmin = Role::findByName('superadmin');
        $roleSuperadmin->givePermissionTo('menu-goals');
        $roleSuperadmin->givePermissionTo('menu-report');
        $roleSuperadmin->givePermissionTo('menu-admin');

        $roleAdmin = Role::findByName('admin');
        $roleAdmin->givePermissionTo('menu-goals');
        $roleAdmin->givePermissionTo('menu-report');
        $roleAdmin->givePermissionTo('menu-admin');

        $roleUser = Role::findByName('user');
        $roleUser->givePermissionTo('menu-goals');
        $roleUser->givePermissionTo('menu-report');
    }
}
