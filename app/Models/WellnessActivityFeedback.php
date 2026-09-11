<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

/**
 * One employee's written feedback about one session they attended. Editable by
 * its author until they stop caring; the admin side only lists it.
 */
class WellnessActivityFeedback extends Model
{
    use HasFactory;

    // "feedback" is already plural, so the convention's plural table name is
    // the same word -- Eloquent would otherwise look for `wellness_activity_feedbacks`.
    protected $table = 'wellness_activity_feedback';

    protected $appends = ['encrypted_id'];

    protected $fillable = [
        'wellness_activity_registration_id',
        'wellness_activity_schedule_id',
        'wellness_activity_id',
        'employee_id',
        'fullname',
        'message',
        'submitted_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
    ];

    public function getEncryptedIdAttribute(): string
    {
        return Crypt::encryptString($this->id);
    }

    public function registration()
    {
        return $this->belongsTo(WellnessActivityRegistration::class, 'wellness_activity_registration_id');
    }

    public function schedule()
    {
        return $this->belongsTo(WellnessActivitySchedule::class, 'wellness_activity_schedule_id');
    }

    public function activity()
    {
        return $this->belongsTo(WellnessActivity::class, 'wellness_activity_id');
    }

    /**
     * Lives in the `kpncorp` connection, so it can only be loaded separately.
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'employee_id');
    }

    /**
     * Whether the employee has come back and changed what they wrote.
     */
    public function wasEdited(): bool
    {
        return $this->updated_at !== null
            && $this->created_at !== null
            && $this->updated_at->gt($this->created_at);
    }
}
