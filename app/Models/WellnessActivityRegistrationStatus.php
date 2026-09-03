<?php

namespace App\Models;

use App\Enums\WellnessRegistrationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Append-only audit trail of registration status transitions. Rows are written
 * by WellnessRegistrationService and never updated or deleted.
 */
class WellnessActivityRegistrationStatus extends Model
{
    use HasFactory;

    protected $table = 'wellness_activity_registration_statuses';

    protected $fillable = [
        'wellness_activity_registration_id',
        'from_status',
        'status',
        'changed_at',
        'remark',
        'created_by',
    ];

    protected $casts = [
        'from_status' => WellnessRegistrationStatus::class,
        'status' => WellnessRegistrationStatus::class,
        'changed_at' => 'datetime',
    ];

    public function registration()
    {
        return $this->belongsTo(WellnessActivityRegistration::class, 'wellness_activity_registration_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
}
