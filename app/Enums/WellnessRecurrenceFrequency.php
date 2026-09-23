<?php

namespace App\Enums;

use Illuminate\Support\Carbon;

/**
 * How a repeating schedule steps from one occurrence to the next. A schedule
 * that does not repeat simply has a null frequency -- there is deliberately no
 * `None` case, so "does it repeat?" stays a null check rather than a comparison
 * that can be got wrong.
 */
enum WellnessRecurrenceFrequency: string
{
    case Daily = 'daily';
    case Weekly = 'weekly';
    case Monthly = 'monthly';

    /**
     * The $n-th occurrence counted from $base ($n = 0 being $base itself).
     *
     * Always measured from the base rather than from the previous occurrence:
     * stepping month by month from 31 Jan would clamp to the 28th in February
     * and then stay there, whereas counting from the base gives back 31 Mar.
     * Monthly uses the no-overflow variant so a short month clamps instead of
     * spilling into the month after.
     */
    public function nth(Carbon $base, int $n): Carbon
    {
        return match ($this) {
            self::Daily => $base->copy()->addDays($n),
            self::Weekly => $base->copy()->addWeeks($n),
            self::Monthly => $base->copy()->addMonthsNoOverflow($n),
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Daily => 'Daily',
            self::Weekly => 'Weekly',
            self::Monthly => 'Monthly',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $case) => [$case->value => $case->label()])
            ->all();
    }
}
