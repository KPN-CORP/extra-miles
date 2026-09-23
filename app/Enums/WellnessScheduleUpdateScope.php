<?php

namespace App\Enums;

/**
 * Which occurrences an edit to a repeating schedule touches. Only meaningful
 * for a schedule that belongs to a recurrence group; a standalone schedule is
 * always edited as `This`.
 */
enum WellnessScheduleUpdateScope: string
{
    case This = 'this';
    case Following = 'following';

    public function includesFollowing(): bool
    {
        return $this === self::Following;
    }

    public function label(): string
    {
        return match ($this) {
            self::This => 'This schedule only',
            self::Following => 'This and the following schedules',
        };
    }
}
