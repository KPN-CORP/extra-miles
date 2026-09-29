<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use RuntimeException;

/**
 * An app that keeps its roles and permissions in the shared
 * `hcispanel_sys_permission` database. This app is the row named by
 * config('permission.domain').
 */
class Domain extends Model
{
    protected $connection = 'sys_permission';

    protected $fillable = ['name', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    private static ?int $currentId = null;

    public function permissions()
    {
        return $this->hasMany(Permission::class);
    }

    /**
     * Id of this app's domain, looked up once per request.
     */
    public static function currentId(): int
    {
        if (self::$currentId !== null) {
            return self::$currentId;
        }

        $name = config('permission.domain');

        if ($name === '') {
            throw new RuntimeException('DOMAIN_SYS_PERM is not set.');
        }

        $id = static::query()->where('name', $name)->where('is_active', true)->value('id');

        if ($id === null) {
            throw new RuntimeException("No active domain named [{$name}] in the sys_permission database.");
        }

        return self::$currentId = (int) $id;
    }
}
