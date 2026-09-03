<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Crypt;

/**
 * Module-wide blacklist of employees. Being on it never blocks registration --
 * it only stops the system from confirming a seat automatically, so an admin
 * has to decide. See WellnessRegistrationService::register().
 */
class WellnessBlacklist extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'wellness_blacklists';

    protected $appends = ['encrypted_id'];

    protected $fillable = [
        'employee_id',
        'fullname',
        'reason',
        'end_date',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'end_date' => 'date',
    ];

    public function getEncryptedIdAttribute(): string
    {
        return Crypt::encryptString($this->id);
    }

    /**
     * Lives in the `kpncorp` connection, so it can only be loaded separately.
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'employee_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    /**
     * Still in force: no end date at all, or an end date that has not passed.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where(function (Builder $q) {
            $q->whereNull('end_date')->orWhereDate('end_date', '>=', now()->toDateString());
        });
    }

    public function scopeExpired(Builder $query): Builder
    {
        return $query->whereNotNull('end_date')->whereDate('end_date', '<', now()->toDateString());
    }

    public function isActive(): bool
    {
        return $this->end_date === null || $this->end_date->gte(now()->startOfDay());
    }

    /**
     * Whether this employee is currently blacklisted.
     */
    public static function coversEmployee(string $employeeId): bool
    {
        return static::query()->active()->where('employee_id', $employeeId)->exists();
    }
}
