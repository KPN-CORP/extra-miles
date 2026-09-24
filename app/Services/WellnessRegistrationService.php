<?php

namespace App\Services;

use App\Enums\WellnessRegistrationMethod;
use App\Enums\WellnessRegistrationSource;
use App\Enums\WellnessRegistrationStatus;
use App\Exceptions\WellnessRegistrationException;
use App\Models\WellnessActivityRegistration;
use App\Models\WellnessActivityRegistrationStatus;
use App\Models\WellnessActivitySchedule;
use App\Models\WellnessActivityType;
use App\Models\WellnessBlacklist;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Carbon;
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
 * A schedule may also carry a `confirmation_deadline`. Where it does, a seat is
 * granted as `AwaitingConfirmation` rather than `Confirmed`: it holds quota, but
 * the employee has to accept it before `confirm_due_at` or it is revoked and
 * passed down the queue. A schedule without a deadline skips that step entirely
 * and behaves exactly as it always did.
 *
 * Both `Confirmed` and `AwaitingConfirmation` consume quota. Blacklisted
 * employees may always register, but are never seated automatically under either
 * method.
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
     *  FIFO      seat free + not blacklisted -> seated
     *            otherwise                   -> registered
     *  Selection always                      -> waiting_list
     *  Admin     seat free (or override)     -> seated
     *
     * "Seated" is `confirmed`, or `awaiting_confirmation` when the schedule
     * carries a confirmation deadline -- see seatStatusFor().
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

                // The admin grants the seat; on a session with a deadline the
                // employee still has to accept it.
                $target = $this->seatStatusFor($schedule);

                if ($isBlacklisted) {
                    $remark = trim(($remark ? $remark.' ' : '').'(Employee is on the wellness blacklist.)');
                }
            } else {
                $target = $method->autoConfirms() && ! $isFull && ! $isBlacklisted
                    ? $this->seatStatusFor($schedule)
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

            return $this->recordTransition(
                $registration,
                $previous,
                $target,
                $remark,
                $actorId,
                $this->confirmDueFor($schedule, $target),
            );
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

            $this->recordTransition(
                $registration,
                $from,
                $target,
                $remark,
                $actorId,
                $this->confirmDueFor($schedule, $target),
            );

            if ($from->consumesSlot() && ! $target->consumesSlot()) {
                $this->promoteQueue($schedule, $actorId);
            }

            return $registration;
        });
    }

    /**
     * An admin gives someone the seat. On a session with a confirmation
     * deadline that lands them in `awaiting_confirmation`, and the employee
     * still has to accept it; otherwise it confirms outright.
     */
    public function grantSeat(WellnessActivityRegistration $registration, ?string $remark = null, ?int $actorId = null): WellnessActivityRegistration
    {
        return $this->transitionTo(
            $registration,
            $this->seatStatusFor($registration->schedule),
            $remark,
            $actorId,
        );
    }

    /**
     * The employee accepts a seat they were granted. Refuses once the window
     * has closed, so a stale button in a phone that has been asleep cannot
     * claim a seat that is already on its way to someone else.
     */
    public function confirmSeat(WellnessActivityRegistration $registration, ?string $remark = null, ?int $actorId = null): WellnessActivityRegistration
    {
        if ($registration->status === WellnessRegistrationStatus::Confirmed) {
            return $registration;
        }

        if (! $registration->status->awaitsConfirmation()) {
            throw WellnessRegistrationException::nothingToConfirm();
        }

        if (! $registration->canConfirm()) {
            throw WellnessRegistrationException::confirmationClosed();
        }

        return $this->transitionTo(
            $registration,
            WellnessRegistrationStatus::Confirmed,
            $remark ?: 'Confirmed by the employee.',
            $actorId,
        );
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
     * blacklisted employees. On a session with a confirmation deadline the seat
     * is only *offered* -- the employee still has to accept it, with a window of
     * their own running to the session start.
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

            $seated = $this->seatStatusFor($schedule);

            $this->recordTransition(
                $next,
                WellnessRegistrationStatus::Registered,
                $seated,
                $seated->awaitsConfirmation()
                    ? 'Seat offered from the FIFO queue; awaiting the employee.'
                    : 'Auto-confirmed from the FIFO queue.',
                $actorId,
                $this->confirmDueFor($schedule, $seated),
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
     * Revoke seats whose confirmation window has closed, and pass each freed
     * seat down the queue.
     *
     * Under FIFO the next person in line is offered it automatically, with a
     * window of their own. Under Selection nothing is promoted -- choosing is
     * the admin's call by definition -- so the seat simply returns to them.
     *
     * Grouped by schedule so each schedule is locked once and its queue is
     * backfilled after all of its expiries, not between them.
     *
     * @return array{revoked: int, promoted: int}
     */
    public function expireUnconfirmed(?Carbon $at = null, ?int $actorId = null): array
    {
        $at ??= now();

        $expired = WellnessActivityRegistration::query()
            ->where('status', WellnessRegistrationStatus::AwaitingConfirmation->value)
            ->whereNotNull('confirm_due_at')
            ->where('confirm_due_at', '<', $at)
            ->orderBy('wellness_activity_schedule_id')
            ->get()
            ->groupBy('wellness_activity_schedule_id');

        $revoked = 0;
        $promoted = 0;

        foreach ($expired as $scheduleId => $registrations) {
            $schedule = WellnessActivitySchedule::find($scheduleId);

            if (! $schedule) {
                continue;
            }

            [$scheduleRevoked, $schedulePromoted] = DB::transaction(function () use ($schedule, $registrations, $actorId) {
                $locked = $this->lockSchedule($schedule);
                $count = 0;

                foreach ($registrations as $registration) {
                    $registration->refresh();

                    // Someone may have confirmed between the read and the lock.
                    if (! $registration->status->awaitsConfirmation()) {
                        continue;
                    }

                    $this->recordTransition(
                        $registration,
                        $registration->status,
                        WellnessRegistrationStatus::Cancelled,
                        'Seat revoked: not confirmed before the deadline.',
                        $actorId,
                    );

                    $count++;
                }

                return [$count, $count > 0 ? $this->promoteQueue($locked, $actorId) : 0];
            });

            $revoked += $scheduleRevoked;
            $promoted += $schedulePromoted;
        }

        return ['revoked' => $revoked, 'promoted' => $promoted];
    }

    /**
     * Attendance check-in from a scanned activity-type QR.
     *
     * The code covers every session of the type, so the scan is matched to the
     * employee's registrations whose check-in window contains this moment, and
     * the earliest one not yet attended is marked. Back-to-back sessions work
     * because the first scan takes the 08:00 session and a later one falls
     * through to 10:00. Rows are locked so two frames decoded in quick
     * succession cannot mark two sessions.
     *
     * Idempotent: once every open session is attended, a further scan returns
     * the latest of them rather than moving attended_at or raising an error.
     */
    public function checkIn(string $qrToken, string $employeeId, ?string $note = null): WellnessActivityRegistration
    {
        $type = WellnessActivityType::where('qr_token', $qrToken)->first();

        if (! $type) {
            throw WellnessRegistrationException::invalidQrToken();
        }

        return DB::transaction(function () use ($type, $employeeId, $note) {
            $registrations = WellnessActivityRegistration::query()
                ->where('employee_id', $employeeId)
                ->whereHas('schedule.activity', fn ($q) => $q->where('wellness_activity_type_id', $type->id))
                ->with('schedule.activity')
                ->lockForUpdate()
                ->get();

            if ($registrations->isEmpty()) {
                throw WellnessRegistrationException::notRegistered();
            }

            $at = now();

            $open = $registrations
                ->each(fn ($r) => $r->schedule->activity->setRelation('type', $type))
                ->filter(fn ($r) => $r->schedule->isCheckInOpen($at))
                ->sortBy(fn ($r) => [$r->schedule->start_at, $r->schedule->id])
                ->values();

            if ($open->isEmpty()) {
                throw WellnessRegistrationException::checkInClosed();
            }

            // A seat still awaiting confirmation counts here: turning someone
            // away at the door because they never tapped Confirm would be worse
            // than taking the scan as the acceptance it plainly is.
            $seated = $open->filter(fn ($r) => $r->status->consumesSlot());

            if ($seated->isEmpty()) {
                throw WellnessRegistrationException::notConfirmed();
            }

            $registration = $seated->first(fn ($r) => ! $r->hasAttended());

            if (! $registration) {
                return $seated->last();
            }

            if ($registration->status->awaitsConfirmation()) {
                // Safe without the schedule lock: the seat is already held, so
                // this changes no quota -- it only settles who it belongs to.
                $this->recordTransition(
                    $registration,
                    $registration->status,
                    WellnessRegistrationStatus::Confirmed,
                    'Confirmed by checking in at the session.',
                    null,
                );
            }

            $registration->forceFill([
                'attended_at' => $at,
                'attendance_note' => $note,
            ])->save();

            return $registration;
        });
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

    /**
     * What "having a seat" means on this schedule: `awaiting_confirmation` when
     * it carries a deadline the employee can still meet, `confirmed` otherwise.
     *
     * Once the schedule's own deadline has passed, confirmDueFor() hands back
     * the session start instead, so a late arrival still gets a real window.
     * Only when that has passed too -- an admin adding someone to a session
     * already under way -- is there nothing left to wait for, and the seat is
     * confirmed outright rather than granted so it can expire a second later.
     */
    protected function seatStatusFor(?WellnessActivitySchedule $schedule): WellnessRegistrationStatus
    {
        $due = $schedule?->confirmDueFor();

        return $due && $due->isFuture()
            ? WellnessRegistrationStatus::AwaitingConfirmation
            : WellnessRegistrationStatus::Confirmed;
    }

    /**
     * The due date to store alongside a transition -- set only where the target
     * is a seat still waiting to be accepted.
     */
    protected function confirmDueFor(WellnessActivitySchedule $schedule, WellnessRegistrationStatus $target): ?Carbon
    {
        return $target->awaitsConfirmation() ? $schedule->confirmDueFor() : null;
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
        ?Carbon $confirmDueAt = null,
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
            // Cleared on every other target on purpose: a due date left behind on
            // a confirmed or cancelled row would be picked up by the expiry sweep.
            'confirm_due_at' => $confirmDueAt,
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
