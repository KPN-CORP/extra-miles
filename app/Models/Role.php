<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Models\Role as SpatieRole;

/**
 * Roles have no domain_id of their own -- the same role can carry permissions
 * from several apps. $role->permissions only returns this app's permissions
 * because Permission is domain-scoped.
 */
class Role extends SpatieRole
{
    use HasFactory;

    protected $connection = 'sys_permission';

    protected $casts = [
        'business_unit' => 'array',
        'company' => 'array',
        'location' => 'array',
        'is_data_access' => 'boolean',
    ];

    public function modelHasRole()
    {
        return $this->hasMany(ModelHasRole::class, 'role_id', 'id');
    }

    public function rolehaspermission()
    {
        return $this->hasMany(RoleHasPermission::class, 'role_id', 'id');
    }

    /**
     * Roles that hold at least one of this app's permissions.
     */
    public function scopeInCurrentDomain(Builder $query): Builder
    {
        return $query->whereHas('permissions');
    }

    /**
     * Whether the role also carries permissions of another app.
     */
    public function isSharedWithOtherDomains(): bool
    {
        return RoleHasPermission::where('role_id', $this->id)
            ->whereNotIn('permission_id', Permission::query()->select('id'))
            ->exists();
    }
}
