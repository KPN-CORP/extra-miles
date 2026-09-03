<?php

namespace App\Services;

use App\Enums\WellnessRegistrationMethod;
use App\Enums\WellnessRegistrationSource;
use App\Enums\WellnessRegistrationStatus;
use App\Exceptions\WellnessRegistrationException;
use App\Models\WellnessActivityRegistration;
use App\Models\WellnessActivityRegistrationStatus;
use App\Models\WellnessActivitySchedule;
use App\Models\WellnessBlacklist;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;

/**
 * The only place a wellness registration status is ever written.
 *
 * Two registration methods share this service (see WellnessRegistrationMethod):
 *
 *  - FIFO      confirms seats automatically in registration order while the
 *              quota lasts; everyone after that queues as `Registered`.
 *  - Selection everyone queues as `WaitingList` and an admin confirms seats.
 *
 * Only `Confirmed` consumes quota. Blacklisted employees may always register,
 * but are never confirmed automatically under either method.
 *
 * Every method that can take or free a seat runs inside a transaction holding a
 * row lock on the schedule, so quota checks, queue ordering and promotion can
 * never race each other.
 */
class WellnessRegistrationService
{
    /**
     * Register an employee. The landing status depends on the activity's
     * method, the remaining quota, and whether the employee is blacklisted:
     *
     *  FIFO      seat free + not blacklisted -> confirmed
     *            otherwise                   -> registered
     *  Selection always                      -> waiting_list
     *  Admin     seat free (or override)     -> confirmed
     *
     * @param  array{employee_id: string, fullname?: ?string, business_unit?: ?string, unit?: ?string, job_level?: ?string, location?: ?string}  $employee
     */
    public function register(
        WellnessActivitySchedule $schedule,
        array $employee,
        WellnessRegistrationSource $source,
        ?int $actorId = null,
        ?string $remark = null,
        bool $allowOverQuota = false,
    ): WellnessActivityRegistration {
        return DB::transaction(function () use ($schedule, $employee, $source, $actorId, $remark, $allowOverQuota) {
            $schedule = $this->lockSchedule($schedule);
            $method = $this->methodFor($schedule);

            // Admins may register outside the public window; employees may not.
            if ($source === WellnessRegistrationSource::SelfService && ! $schedule->isRegistrationOpen()) {
                throw WellnessRegistrationException::registrationClosed();
            }

            if ($source === WellnessRegistrationSource::Admin && ! $schedule->status->acceptsRegistration()) {
                throw WellnessRegistrationException::registrationClosed();
            }

            $registration = WellnessActivityRegistration::where('wellness_activity_schedule_id', $schedule->id)
                ->where('employee_id', $employee['employee_id'])
                ->lockForUpdate()
                ->first();

            if ($registration && $registration->status->isActive()) {
                throw WellnessRegistrationException::alreadyRegistered($registration->status);
            }

            $isFull = $schedule->isFull();
            $isBlacklisted = WellnessBlacklist::coversEmployee($employee['employee_id']);

            if ($source === WellnessRegistrationSource::Admin) {
                // An admin adding someone is itself the decision, so it confirms
                // even for a blacklisted employee -- noted in the remark below.
                if ($isFull && ! $allowOverQuota) {
                    throw WellnessRegistrationException::quotaExceeded();
                }

                $target = WellnessRegistrationStatus::Confirmed;

                if ($isBlacklisted) {
                    $remark = trim(($remark ? $remark.' ' : '').'(Employee is on the wellness blacklist.)');
                }
            } else {
                $target = $method->autoConfirms() && ! $isFull && ! $isBlacklisted
                    ? WellnessRegistrationStatus::Confirmed
                    : $method->queueStatus();

                if ($isBlacklisted && $method->autoConfirms()) {
                    $remark = $remark ?: 'On the wellness blacklist, so not confirmed automatically.';
                }
            }

            $attributes = [
                'wellness_activity_schedule_id' => $schedule->id,
                'wellness_activity_id' => $schedule->wellness_activity_id,
                'employee_id' => $employee['employee_id'],
                // HR snapshot: employees move between units, past reports must not.
                'fullname' => $employee['fullname'] ?? null,
                'business_unit' => $employee['business_unit'] ?? null,
                'unit' => $employee['unit'] ?? null,
                'job_level' => $employee['job_level'] ?? null,
                'location' => $employee['location'] ?? null,
                'source' => $source,
                // Registration order for FIFO. Re-registering after cancelling
                // resets this, which correctly sends them to the back of the queue.
                'registered_at' => now(),
                'attended_at' => null,
                'attendance_note' => null,
                'updated_by' => $actorId,
            ];

            if ($registration) {
                $previous = $registration->status;
                $registration->fill($attributes)->save();
            } else {
                $previous = null;
                $registration = WellnessActivityRegistration::create($attributes + [
                    'status' => $target,
                    'created_by' => $actorId,
                ]);
            }

            return $this->recordTransition($registration, $previous, $target, $remark, $actorId);
        });
    }

    /**
     * Move a registration to a new status, then backfill the queue if this
     * freed a seat.
     */
    public function transitionTo(
        WellnessActivityRegistration $registration,
        WellnessRegistrationStatus $target,
        ?string $remark = null,
        ?int $actorId = null,
    ): WellnessActivityRegistration {
        return DB::transaction(function () use ($registration, $target, $remark, $actorId) {
            $schedule = $this->lockSchedule($registration->schedule);
            $registration->refresh();

            $from = $registration->status;

            if ($from === $target) {
                return $registration;
            }

            if (! $from->canTransitionTo($target)) {
                throw WellnessRegistrationException::invalidTransition($from, $target);
            }

            // Taking a seat requires one to be free.
            if (! $from->consumesSlot() && $target->consumesSlot() && $schedule->isFull()) {
                throw WellnessRegistrationException::quotaExceeded();
            }

            $this->recordTransition($registration, $from, $target, $remark, $actorId);

            if ($from->consumesSlot() && ! $target->consumesSlot()) {
                $this->promoteQueue($schedule, $actorId);
            }

            return $registration;
        });
    }

    public function confirm(WellnessActivityRegistration $registration, ?string $remark = null, ?int $actorId = null): WellnessActivityRegistration
    {
        return $this->transitionTo($registration, WellnessRegistrationStatus::Confirmed, $remark, $actorId);
    }

    public function cancel(WellnessActivityRegistration $registration, ?string $remark = null, ?int $actorId = null): WellnessActivityRegistration
    {
        return $this->transitionTo($registration, WellnessRegistrationStatus::Cancelled, $remark, $actorId);
    }

    /**
     * Send a registration back to its method's queue -- undoing a confirmation
     * or lifting a blacklist for this session.
     */
    public function requeue(WellnessActivityRegistration $registration, ?string $remark = null, ?int $actorId = null): WellnessActivityRegistration
    {
        $method = $this->methodFor($registration->schedule);

        return $this->transitionTo($registration, $method->queueStatus(), $remark, $actorId);
    }

    /**
     * Blacklist a participant for this session, and optionally add them to the
     * module-wide blacklist so future registrations are not auto-confirmed.
     */
    public function blacklist(
        WellnessActivityRegistration $registration,
        string $reason,
        ?int $actorId = null,
        ?string $endDate = null,
        bool $addToMasterList = true,
    ): WellnessActivityRegistration {
        return DB::transaction(function () use ($registration, $reason, $actorId, $endDate, $addToMasterList) {
            if ($addToMasterList) {
                WellnessBlacklist::create([
                    'employee_id' => $registration->employee_id,
                    'fullname' => $registration->fullname,
                    'reason' => $reason,
                    'end_date' => $endDate,
                    'created_by' => $actorId,
                ]);
            }

            return $this->transitionTo($registration, WellnessRegistrationStatus::Blacklisted, $reason, $actorId);
        });
    }

    /**
     * Fill free seats from the FIFO queue, oldest registration first, skipping
     * blacklisted employees.
     *
     * Does nothing for Selection by Admin -- there, confirming is the admin's
     * call by definition.
     *
     * Assumes the caller already holds the schedule row lock.
     *
     * @return int number of registrations confirmed
     */
    public function promoteQueue(WellnessActivitySchedule $schedule, ?int $actorId = null): int
    {
        if (! config('wellness.waitlist.auto_promote')) {
            return 0;
        }

        if (! $this->methodFor($schedule)->autoConfirms()) {
            return 0;
        }

        $promoted = 0;

        while (true) {
            $remaining = $schedule->remainingSeats();

            if ($remaining !== null && $remaining <= 0) {
                break;
            }

            $next = $schedule->registrations()
                ->where('status', WellnessRegistrationStatus::Registered->value)
                ->whereNotExists($this->activeBlacklistFor('wellness_activity_registrations.employee_id'))
                ->orderBy('registered_at')
                ->orderBy('id')
                ->lockForUpdate()
                ->first();

            if (! $next) {
                break;
            }

            $this->recordTransition(
                $next,
                WellnessRegistrationStatus::Registered,
                WellnessRegistrationStatus::Confirmed,
                'Auto-confirmed from the FIFO queue.',
                $actorId
            );

            $promoted++;

            // Unlimited quota empties the whole queue in one pass; guard against
            // an unbounded loop regardless.
            if ($remaining === null && $promoted >= 1000) {
                break;
            }
        }

        return $promoted;
    }

    /**
     * Attendance check-in from a scanned session QR.
     *
     * Idempotent: a second scan returns the same registration rather than
     * moving attended_at or raising an error.
     */
    public function checkIn(string $qrToken, string $employeeId, ?string $note = null): WellnessActivityRegistration
    {
        $schedule = WellnessActivitySchedule::where('qr_token', $qrToken)->first();

        if (! $schedule) {
            throw WellnessRegistrationException::invalidQrToken();
        }

        if (! $schedule->isCheckInOpen()) {
            throw WellnessRegistrationException::checkInClosed();
        }

        $registration = WellnessActivityRegistration::where('wellness_activity_schedule_id', $schedule->id)
            ->where('employee_id', $employeeId)
            ->first();

        if (! $registration) {
            throw WellnessRegistrationException::notRegistered();
        }

        if ($registration->status !== WellnessRegistrationStatus::Confirmed) {
            throw WellnessRegistrationException::notConfirmed();
        }

        if ($registration->hasAttended()) {
            return $registration;
        }

        $registration->forceFill([
            'attended_at' => now(),
            'attendance_note' => $note,
        ])->save();

        return $registration;
    }

    /**
     * Subquery matching an employee who is currently on the blacklist.
     */
    protected function activeBlacklistFor(string $employeeIdColumn): \Closure
    {
        return function (QueryBuilder $query) use ($employeeIdColumn) {
            $query->select(DB::raw(1))
                ->from('wellness_blacklists')
                ->whereColumn('wellness_blacklists.employee_id', $employeeIdColumn)
                ->whereNull('wellness_blacklists.deleted_at')
                ->where(function (QueryBuilder $q) {
                    $q->whereNull('wellness_blacklists.end_date')
                        ->orWhereDate('wellness_blacklists.end_date', '>=', now()->toDateString());
                });
        };
    }

    protected function methodFor(WellnessActivitySchedule $schedule): WellnessRegistrationMethod
    {
        return $schedule->activity?->registration_method ?? WellnessRegistrationMethod::Fifo;
    }

    /**
     * Write the audit row and the mirrored status column together. Never call
     * this outside a transaction holding the schedule lock.
     */
    protected function recordTransition(
        WellnessActivityRegistration $registration,
        ?WellnessRegistrationStatus $from,
        WellnessRegistrationStatus $target,
        ?string $remark,
        ?int $actorId,
    ): WellnessActivityRegistration {
        WellnessActivityRegistrationStatus::create([
            'wellness_activity_registration_id' => $registration->id,
            'from_status' => $from,
            'status' => $target,
            'changed_at' => now(),
            'remark' => $remark,
            'created_by' => $actorId,
        ]);

        $registration->forceFill([
            'status' => $target,
            'updated_by' => $actorId,
        ])->save();

        return $registration;
    }

    protected function lockSchedule(WellnessActivitySchedule $schedule): WellnessActivitySchedule
    {
        return WellnessActivitySchedule::whereKey($schedule->getKey())
            ->lockForUpdate()
            ->firstOrFail();
    }
}
