<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoleHasPermission extends Model
{
    use HasFactory;
    protected $fillable = [
        'permission_id',
        'role_id',
    ];

    protected $connection = 'kpncorp';
    protected $table = 'role_has_permissions';
}
