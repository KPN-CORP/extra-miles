<?php

namespace App\Models;

use App\Enums\WellnessActivityStatus;
use App\Enums\WellnessRegistrationMethod;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Crypt;

class WellnessActivity extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $appends = ['encrypted_id'];

    protected $fillable = [
        'wellness_activity_type_id',
        'registration_method',
        'name',
        'description',
        'image',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'status' => WellnessActivityStatus::class,
        'registration_method' => WellnessRegistrationMethod::class,
    ];

    public function getEncryptedIdAttribute(): string
    {
        return Crypt::encryptString($this->id);
    }

    public function type()
    {
        return $this->belongsTo(WellnessActivityType::class, 'wellness_activity_type_id');
    }

    public function schedules()
    {
        return $this->hasMany(WellnessActivitySchedule::class, 'wellness_activity_id');
    }

    public function registrations()
    {
        return $this->hasMany(WellnessActivityRegistration::class, 'wellness_activity_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function scopeVisibleToEmployees($query)
    {
        return $query->where('status', WellnessActivityStatus::Active->value);
    }
}
