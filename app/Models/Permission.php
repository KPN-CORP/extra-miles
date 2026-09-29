<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Models\Permission as SpatiePermission;

/**
 * Scoped to this app's domain: every query -- including the ones Spatie runs
 * for $user->can() and @can -- only sees permissions whose domain_id is
 * Domain::currentId(), and new permissions are stamped with it.
 */
class Permission extends SpatiePermission
{
    use HasFactory;

    protected $connection = 'sys_permission';

    protected static function booted(): void
    {
        static::addGlobalScope('domain', function (Builder $query) {
            $query->where($query->qualifyColumn('domain_id'), Domain::currentId());
        });

        static::creating(function (Permission $permission) {
            $permission->domain_id ??= Domain::currentId();
        });
    }

    public function domain()
    {
        return $this->belongsTo(Domain::class);
    }
}
