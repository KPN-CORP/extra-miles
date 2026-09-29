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

    protected $connection = 'sys_permission';

    // Composite-key pivot with no timestamp columns.
    public $incrementing = false;

    public $timestamps = false;

    protected $table = 'role_has_permissions';
}
