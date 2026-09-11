<?php

namespace App\Models;

use App\Enums\WellnessRegistrationStatus;
use App\Enums\WellnessScheduleStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class WellnessActivitySchedule extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $appends = ['encrypted_id'];

    protected $fillable = [
        'wellness_activity_id',
        'start_at',
        'end_at',
        'location',
        'quota',
        'registration_start_at',
        'registration_end_at',
        'status',
        'qr_token',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'registration_start_at' => 'datetime',
        'registration_end_at' => 'datetime',
        'quota' => 'integer',
        'status' => WellnessScheduleStatus::class,
    ];

    protected static function booted(): void
    {
        static::creating(function (self $schedule) {
            $schedule->qr_token ??= (string) Str::uuid();
        });
    }

    public function getEncryptedIdAttribute(): string
    {
        return Crypt::encryptString($this->id);
    }

    /**
     * Adds taken_seats / queued_seats / attended_seats. One definition shared by
     * the schedule list, the activity list and the activity edit form, so the
     * three can never disagree about what counts as an occupied seat.
     */
    public function scopeWithSeatCounts(Builder $query): Builder
    {
        return $query->withCount([
            'registrations as taken_seats' => fn ($q) => $q->whereIn('status', WellnessRegistrationStatus::slotConsumingValues()),
            'registrations as queued_seats' => fn ($q) => $q->whereIn('status', WellnessRegistrationStatus::queuedValues()),
            'registrations as attended_seats' => fn ($q) => $q->whereNotNull('attended_at'),
        ]);
    }

    public function activity()
    {
        return $this->belongsTo(WellnessActivity::class, 'wellness_activity_id');
    }

    public function registrations()
    {
        return $this->hasMany(WellnessActivityRegistration::class, 'wellness_activity_schedule_id');
    }

    public function feedback()
    {
        return $this->hasMany(WellnessActivityFeedback::class, 'wellness_activity_schedule_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    /**
     * Seats currently held. Pending counts, so an admin never approves into a
     * room that is already full.
     */
    public function takenSeats(): int
    {
        return $this->registrations()
            ->whereIn('status', WellnessRegistrationStatus::slotConsumingValues())
            ->count();
    }

    public function remainingSeats(): ?int
    {
        if ($this->quota === null) {
            return null; // unlimited
        }

        return max(0, $this->quota - $this->takenSeats());
    }

    public function isFull(): bool
    {
        return $this->quota !== null && $this->takenSeats() >= $this->quota;
    }

    /**
     * Registrations queued for a seat, under either method (FIFO `registered`
     * or Selection `waiting_list`).
     */
    public function queueCount(): int
    {
        return $this->registrations()
            ->whereIn('status', WellnessRegistrationStatus::queuedValues())
            ->count();
    }

    /**
     * Whether the registration window is open right now. Null bounds mean the
     * window is unbounded on that side.
     */
    public function isRegistrationOpen(?Carbon $at = null): bool
    {
        $at ??= now();

        if (! $this->status->acceptsRegistration()) {
            return false;
        }

        if ($this->registration_start_at && $at->lt($this->registration_start_at)) {
            return false;
        }

        if ($this->registration_end_at && $at->gt($this->registration_end_at)) {
            return false;
        }

        // Never accept a registration for a session that has already ended.
        return $at->lte($this->end_at);
    }

    public function checkInOpensAt(): Carbon
    {
        return $this->start_at->copy()
            ->subMinutes(config('wellness.check_in.opens_minutes_before'));
    }

    public function checkInClosesAt(): Carbon
    {
        return $this->end_at->copy()
            ->addMinutes(config('wellness.check_in.closes_minutes_after'));
    }

    public function isCheckInOpen(?Carbon $at = null): bool
    {
        $at ??= now();

        return $this->status->allowsCheckIn()
            && $at->between($this->checkInOpensAt(), $this->checkInClosesAt());
    }

    public function scopeUpcoming($query)
    {
        return $query->where('start_at', '>=', now());
    }

    public function scopeOpen($query)
    {
        return $query->where('status', WellnessScheduleStatus::Open->value);
    }
}
