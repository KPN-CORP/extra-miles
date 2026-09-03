<?php

namespace App\Enums;

enum WellnessRegistrationMethod: string
{
    /** First come, first served: seats are confirmed automatically while the quota lasts. */
    case Fifo = 'fifo';

    /** Everyone waits; an admin decides who gets a seat. */
    case SelectionByAdmin = 'selection';

    public function label(): string
    {
        return match ($this) {
            self::Fifo => 'FIFO (First Come, First Served)',
            self::SelectionByAdmin => 'Selection by Admin',
        };
    }

    public function shortLabel(): string
    {
        return match ($this) {
            self::Fifo => 'FIFO',
            self::SelectionByAdmin => 'Selection',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Fifo => 'Employees are confirmed automatically in registration order until the quota is full. The rest queue as Registered.',
            self::SelectionByAdmin => 'Every employee joins the Waiting List. An admin confirms who gets a seat.',
        };
    }

    /**
     * Where a new registration lands when it cannot be confirmed.
     */
    public function queueStatus(): WellnessRegistrationStatus
    {
        return match ($this) {
            self::Fifo => WellnessRegistrationStatus::Registered,
            self::SelectionByAdmin => WellnessRegistrationStatus::WaitingList,
        };
    }

    /**
     * Whether the system may confirm a seat without an admin acting.
     */
    public function autoConfirms(): bool
    {
        return $this === self::Fifo;
    }

    /**
     * The statuses a registration can hold under this method. Drives the
     * participant tabs and keeps each method's vocabulary separate.
     *
     * @return array<int, WellnessRegistrationStatus>
     */
    public function statuses(): array
    {
        return [
            WellnessRegistrationStatus::Confirmed,
            $this->queueStatus(),
            WellnessRegistrationStatus::Blacklisted,
            WellnessRegistrationStatus::Cancelled,
        ];
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Fifo => 'bg-info-subtle text-info',
            self::SelectionByAdmin => 'bg-primary-subtle text-primary',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Fifo => 'ri-time-line',
            self::SelectionByAdmin => 'ri-user-star-line',
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
