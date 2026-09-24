<?php

namespace App\Models;

use App\Enums\WellnessRegistrationSource;
use App\Enums\WellnessRegistrationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Crypt;

/**
 * No SoftDeletes on purpose -- see the migration. Cancelling is a status
 * transition, and the audit trail lives in the status history table.
 */
class WellnessActivityRegistration extends Model
{
    use HasFactory;

    protected $appends = ['encrypted_id'];

    protected $fillable = [
        'wellness_activity_schedule_id',
        'wellness_activity_id',
        'employee_id',
        'fullname',
        'business_unit',
        'unit',
        'job_level',
        'location',
        'status',
        'source',
        'registered_at',
        'confirm_due_at',
        'attended_at',
        'attendance_note',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'status' => WellnessRegistrationStatus::class,
        'source' => WellnessRegistrationSource::class,
        'registered_at' => 'datetime',
        'confirm_due_at' => 'datetime',
        'attended_at' => 'datetime',
    ];

    public function getEncryptedIdAttribute(): string
    {
        return Crypt::encryptString($this->id);
    }

    /**
     * Whether the employee can still accept this seat. A seat with no due date
     * was granted on a session that does not ask for confirmation.
     */
    public function canConfirm(?Carbon $at = null): bool
    {
        return $this->status->awaitsConfirmation()
            && $this->confirm_due_at !== null
            && ($at ?? now())->lte($this->confirm_due_at);
    }

    /**
     * Held a seat but let the window lapse. The sweep has not reached it yet.
     */
    public function confirmationExpired(?Carbon $at = null): bool
    {
        return $this->status->awaitsConfirmation()
            && $this->confirm_due_at !== null
            && ($at ?? now())->gt($this->confirm_due_at);
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
     * Lives in the `kpncorp` connection, so this can only ever be lazy/eager
     * loaded as a separate query -- it can never be joined in SQL.
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'employee_id');
    }

    public function statusHistories()
    {
        return $this->hasMany(WellnessActivityRegistrationStatus::class, 'wellness_activity_registration_id')
            ->orderBy('changed_at')
            ->orderBy('id');
    }

    /**
     * At most one feedback per registration -- resubmitting overwrites it.
     */
    public function feedback()
    {
        return $this->hasOne(WellnessActivityFeedback::class, 'wellness_activity_registration_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function hasAttended(): bool
    {
        return $this->attended_at !== null;
    }

    /**
     * Feedback is only for people who were actually there, and only once the
     * session is over -- attendance alone is not enough, because the QR opens
     * before the session starts.
     */
    public function canSubmitFeedback(?Carbon $at = null): bool
    {
        $at ??= now();

        return $this->attended_at !== null
            && $this->schedule !== null
            && $at->gte($this->schedule->end_at);
    }

    public function scopeHoldingSeat($query)
    {
        return $query->whereIn('status', WellnessRegistrationStatus::slotConsumingValues());
    }

    public function scopeForEmployee($query, string $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }
}
