<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class WellnessActivityType extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $appends = ['encrypted_id'];

    protected $fillable = [
        'name',
        'description',
        'is_active',
        'qr_token',
        'check_in_opens_minutes_before',
        'check_in_closes_minutes_after',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'check_in_opens_minutes_before' => 'integer',
        'check_in_closes_minutes_after' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $type) {
            $type->qr_token ??= (string) Str::uuid();
        });
    }

    public function getEncryptedIdAttribute(): string
    {
        return Crypt::encryptString($this->id);
    }

    /**
     * The effective scan window. A null column means "follow the config
     * default", so a type nobody has tuned tracks changes to that default.
     */
    public function checkInOpensMinutesBefore(): int
    {
        return $this->check_in_opens_minutes_before
            ?? (int) config('wellness.check_in.opens_minutes_before');
    }

    public function checkInClosesMinutesAfter(): int
    {
        return $this->check_in_closes_minutes_after
            ?? (int) config('wellness.check_in.closes_minutes_after');
    }

    public function activities()
    {
        return $this->hasMany(WellnessActivity::class, 'wellness_activity_type_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
