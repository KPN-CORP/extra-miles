<?php

namespace App\Services;

use App\Enums\WellnessRecurrenceFrequency;
use App\Models\WellnessActivity;
use App\Models\WellnessActivitySchedule;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Owns the two multi-row operations on a repeating schedule: laying a series
 * down on create, and carrying an edit forward across the occurrences that come
 * after the one being edited.
 *
 * Both work on one rule: every field except the date is kept as an *offset from
 * the session start*, never as an absolute value. Shifting a session from 09:00
 * to 10:00 therefore drags its registration window along with it, and each
 * occurrence keeps its own date.
 */
class WellnessScheduleRecurrenceService
{
    /**
     * Creates the schedule, plus one row per further occurrence when a
     * frequency is given. Returns every row created, earliest first.
     *
     * @param  array<string, mixed>  $attributes  validated schedule fields
     */
    public function createSeries(
        WellnessActivity $activity,
        array $attributes,
        ?WellnessRecurrenceFrequency $frequency = null,
        ?Carbon $until = null,
        ?int $userId = null,
    ): Collection {
        $start = Carbon::parse($attributes['start_at']);
        $end = Carbon::parse($attributes['end_at']);

        $starts = $frequency && $until
            ? $this->occurrenceStarts($start, $frequency, $until)
            : [$start];

        // A one-off series is still a one-off schedule: no group, so the edit
        // form never offers to update occurrences that do not exist.
        $groupId = count($starts) > 1 ? (string) Str::uuid() : null;

        $shape = $this->shapeOf($start, $end, $attributes);

        $series = [
            'recurrence_group_id' => $groupId,
            'recurrence_frequency' => $groupId ? $frequency?->value : null,
            'recurrence_until' => $groupId ? $until?->toDateString() : null,
            'created_by' => $userId,
        ];

        return DB::transaction(function () use ($activity, $starts, $shape, $series) {
            $created = collect();

            foreach ($starts as $occurrenceStart) {
                $created->push(
                    $activity->schedules()->create($this->applyShape($shape, $occurrenceStart) + $series)
                );
            }

            return $created;
        });
    }

    /**
     * Applies an already-saved edit to every occurrence after $schedule in the
     * same series. $original is the schedule's state *before* the edit -- the
     * start shift is measured against it, so moving 09:00 to 10:00 moves every
     * later occurrence by the same hour while each keeps its own date.
     *
     * @param  array<string, mixed>  $original  start_at/end_at as they were
     * @return int number of occurrences updated
     */
    public function applyToFollowing(WellnessActivitySchedule $schedule, array $original, ?int $userId = null): int
    {
        $following = $schedule->followingInSeries()->get();

        if ($following->isEmpty()) {
            return 0;
        }

        $startShift = (int) Carbon::parse($original['start_at'])
            ->diffInSeconds($schedule->start_at, false);

        $shape = $this->shapeOf($schedule->start_at, $schedule->end_at, [
            'location' => $schedule->location,
            'quota' => $schedule->quota,
            'status' => $schedule->status->value,
            'registration_start_at' => $schedule->registration_start_at,
            'registration_end_at' => $schedule->registration_end_at,
            'confirmation_deadline' => $schedule->confirmation_deadline,
        ]);

        return DB::transaction(function () use ($following, $shape, $startShift, $userId) {
            foreach ($following as $sibling) {
                $sibling->forceFill($this->applyShape(
                    $shape,
                    $sibling->start_at->copy()->addSeconds($startShift),
                ) + ['updated_by' => $userId])->save();
            }

            return $following->count();
        });
    }

    /**
     * Every occurrence start from $start up to and including the last one that
     * falls on or before $until. Capped so a mistyped "until" cannot write tens
     * of thousands of rows.
     *
     * @return array<int, Carbon>
     */
    public function occurrenceStarts(Carbon $start, WellnessRecurrenceFrequency $frequency, Carbon $until): array
    {
        $limit = (int) config('wellness.recurrence.max_occurrences', 260);
        $lastMoment = $until->copy()->endOfDay();

        $starts = [];

        for ($n = 0; count($starts) < $limit; $n++) {
            $occurrence = $frequency->nth($start, $n);

            if ($n > 0 && $occurrence->gt($lastMoment)) {
                break;
            }

            $starts[] = $occurrence;
        }

        return $starts;
    }

    /**
     * Reduces a schedule to what stays the same across occurrences: its
     * duration, its registration window expressed as offsets from the start,
     * and the fields that are simply copied.
     *
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    private function shapeOf(Carbon $start, Carbon $end, array $attributes): array
    {
        $offset = function ($value) use ($start): ?int {
            if (blank($value)) {
                return null;
            }

            return (int) $start->diffInSeconds(Carbon::parse($value), false);
        };

        return [
            'duration' => (int) $start->diffInSeconds($end, false),
            'registration_start_offset' => $offset($attributes['registration_start_at'] ?? null),
            'registration_end_offset' => $offset($attributes['registration_end_at'] ?? null),
            'confirmation_deadline_offset' => $offset($attributes['confirmation_deadline'] ?? null),
            'location' => $attributes['location'] ?? null,
            'quota' => $attributes['quota'] ?? null,
            'status' => $attributes['status'],
        ];
    }

    /**
     * Turns a shape back into concrete columns for one occurrence date.
     *
     * @param  array<string, mixed>  $shape
     * @return array<string, mixed>
     */
    private function applyShape(array $shape, Carbon $start): array
    {
        return [
            'start_at' => $start->copy(),
            'end_at' => $start->copy()->addSeconds($shape['duration']),
            'registration_start_at' => $shape['registration_start_offset'] === null
                ? null
                : $start->copy()->addSeconds($shape['registration_start_offset']),
            'registration_end_at' => $shape['registration_end_offset'] === null
                ? null
                : $start->copy()->addSeconds($shape['registration_end_offset']),
            'confirmation_deadline' => $shape['confirmation_deadline_offset'] === null
                ? null
                : $start->copy()->addSeconds($shape['confirmation_deadline_offset']),
            'location' => $shape['location'],
            'quota' => $shape['quota'],
            'status' => $shape['status'],
        ];
    }
}
