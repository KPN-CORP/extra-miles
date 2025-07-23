<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    use HasFactory;

    protected $connection = 'kpncorp';

    public function modelHasRole()
    {
        return $this->hasMany(ModelHasRole::class, 'role_id', 'id');
    }
    public function rolehaspermission()
    {
        return $this->hasMany(RoleHasPermission::class, 'role_id', 'id');
    }

    // public function permissions()
    // {
    //     return $this->hasManyThrough(Permission::class, RoleHasPermission::class, 'role_id', 'id', 'id', 'permission_id');
    // }
}
