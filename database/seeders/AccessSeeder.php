<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Models\User;

class AccessSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // cari user pengguna
        $user = User::where('email', 'haiman.saputra@kpn-corp.com')->first();

        if ($user) {
            // Cari atau buat role "admin"
            $role = Role::firstOrCreate(['name' => 'superadmin']);
        
            // Tempelkan role ke pengguna
            $user->syncRoles($role);
        }
    }
}