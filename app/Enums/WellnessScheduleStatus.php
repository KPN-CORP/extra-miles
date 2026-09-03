<?php

namespace App\Enums;

enum WellnessScheduleStatus: string
{
    case Open = 'open';
    case Closed = 'closed';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    /**
     * Whether the schedule can take new registrations at all, before the
     * registration window and quota are even considered.
     */
    public function acceptsRegistration(): bool
    {
        return $this === self::Open;
    }

    public function allowsCheckIn(): bool
    {
        return in_array($this, [self::Open, self::Closed], true);
    }

    public function label(): string
    {
        return match ($this) {
            self::Open => 'Open',
            self::Closed => 'Closed',
            self::Completed => 'Completed',
            self::Cancelled => 'Cancelled',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Open => 'bg-success-subtle text-success',
            self::Closed => 'bg-warning-subtle text-warning',
            self::Completed => 'bg-primary-subtle text-primary',
            self::Cancelled => 'bg-danger-subtle text-danger',
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
