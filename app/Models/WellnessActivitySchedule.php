<?php

namespace App\Models;

use App\Enums\WellnessRecurrenceFrequency;
use App\Enums\WellnessRegistrationStatus;
use App\Enums\WellnessScheduleStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Crypt;

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
        'confirmation_deadline',
        'status',
        'recurrence_group_id',
        'recurrence_frequency',
        'recurrence_until',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'registration_start_at' => 'datetime',
        'registration_end_at' => 'datetime',
        'confirmation_deadline' => 'datetime',
        'quota' => 'integer',
        'status' => WellnessScheduleStatus::class,
        'recurrence_frequency' => WellnessRecurrenceFrequency::class,
        'recurrence_until' => 'date',
    ];

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

    /**
     * Whether a seat on this session has to be accepted by the employee before
     * it is theirs. No deadline set means no extra step -- the session behaves
     * exactly as it did before confirmation deadlines existed.
     */
    public function requiresConfirmation(): bool
    {
        return $this->confirmation_deadline !== null;
    }

    /**
     * When an employee seated at $seatedAt has to confirm by.
     *
     * Someone seated while the deadline is still ahead gets that deadline.
     * Someone promoted off the queue after it has passed -- which only happens
     * because an earlier holder let their own window lapse -- gets their own,
     * running to the moment the session starts. Without this, a seat freed at
     * the deadline could never be taken by anyone.
     */
    public function confirmDueFor(?Carbon $seatedAt = null): ?Carbon
    {
        if (! $this->requiresConfirmation()) {
            return null;
        }

        $seatedAt ??= now();

        return $seatedAt->lte($this->confirmation_deadline)
            ? $this->confirmation_deadline->copy()
            : $this->start_at->copy();
    }

    /**
     * The attendance QR belongs to the activity type, and so does the window it
     * will accept a scan in. A session with no type reachable falls back to the
     * config default rather than refusing every scan.
     */
    public function activityType(): ?WellnessActivityType
    {
        return $this->activity?->type;
    }

    public function checkInOpensAt(): Carbon
    {
        $minutes = $this->activityType()?->checkInOpensMinutesBefore()
            ?? (int) config('wellness.check_in.opens_minutes_before');

        return $this->start_at->copy()->subMinutes($minutes);
    }

    public function checkInClosesAt(): Carbon
    {
        $minutes = $this->activityType()?->checkInClosesMinutesAfter()
            ?? (int) config('wellness.check_in.closes_minutes_after');

        return $this->end_at->copy()->addMinutes($minutes);
    }

    public function isCheckInOpen(?Carbon $at = null): bool
    {
        $at ??= now();

        return $this->status->allowsCheckIn()
            && $at->between($this->checkInOpensAt(), $this->checkInClosesAt());
    }

    /**
     * Whether this schedule was created as one occurrence of a repeating
     * series. Standalone schedules have no group, and are always edited alone.
     */
    public function isRecurring(): bool
    {
        return $this->recurrence_group_id !== null;
    }

    /**
     * The occurrences of the same series that come after this one. Ordered the
     * same way the list is, with the id breaking a tie between two sessions
     * that start at the same minute.
     */
    public function followingInSeries(): Builder
    {
        $query = static::query()
            ->where('recurrence_group_id', $this->recurrence_group_id)
            ->whereKeyNot($this->getKey());

        if (! $this->isRecurring()) {
            // No group means no siblings -- return a query that can never match
            // rather than one that would sweep up every standalone schedule.
            return $query->whereRaw('1 = 0');
        }

        return $query
            ->where(function (Builder $q) {
                $q->where('start_at', '>', $this->start_at)
                    ->orWhere(fn (Builder $tie) => $tie
                        ->where('start_at', $this->start_at)
                        ->where('id', '>', $this->id));
            })
            ->orderBy('start_at')
            ->orderBy('id');
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
