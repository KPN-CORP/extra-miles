<?php

namespace App\Enums;

/**
 * The full status vocabulary. Which of these a registration can actually hold
 * depends on the activity's registration method -- see
 * WellnessRegistrationMethod::statuses(). FIFO queues under `Registered`,
 * Selection by Admin queues under `WaitingList`; neither uses both.
 */
enum WellnessRegistrationStatus: string
{
    /** Has a seat, and has taken it. */
    case Confirmed = 'confirmed';

    /**
     * Holds a seat but has not accepted it yet. Counts against the quota just
     * like Confirmed -- the seat is spoken for -- but is revoked automatically
     * once `confirm_due_at` passes. Only ever used on schedules that carry a
     * confirmation deadline.
     */
    case AwaitingConfirmation = 'awaiting_confirmation';

    /** FIFO queue: registered, waiting for a seat to free up. */
    case Registered = 'registered';

    /** Selection by Admin queue: waiting for an admin to pick. */
    case WaitingList = 'waiting_list';

    /** Excluded from this session. See also the wellness_blacklists master list. */
    case Blacklisted = 'blacklisted';

    case Cancelled = 'cancelled';

    /**
     * A seat that is spoken for, whether or not the employee has accepted it.
     * An unconfirmed seat still holds quota -- releasing it early would let the
     * session be oversubscribed while its holder is still entitled to confirm.
     */
    public function consumesSlot(): bool
    {
        return in_array($this, [self::Confirmed, self::AwaitingConfirmation], true);
    }

    /**
     * @return array<int, string>
     */
    public static function slotConsumingValues(): array
    {
        return [self::Confirmed->value, self::AwaitingConfirmation->value];
    }

    /**
     * Holds a seat that still needs the employee's acceptance.
     */
    public function awaitsConfirmation(): bool
    {
        return $this === self::AwaitingConfirmation;
    }

    /**
     * Statuses that count as a live registration, so the employee cannot
     * register for the same session twice. Blacklisted and Cancelled are
     * deliberately excluded -- a blacklisted employee is still allowed to
     * register again, they just never auto-confirm.
     */
    public function isActive(): bool
    {
        return in_array($this, [
            self::Confirmed,
            self::AwaitingConfirmation,
            self::Registered,
            self::WaitingList,
        ], true);
    }

    /**
     * A registration waiting for a seat, under either method.
     */
    public function isQueued(): bool
    {
        return in_array($this, [self::Registered, self::WaitingList], true);
    }

    /**
     * @return array<int, string>
     */
    public static function queuedValues(): array
    {
        return [self::Registered->value, self::WaitingList->value];
    }

    /**
     * @return array<int, self>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Registered, self::WaitingList => [
                self::Confirmed, self::AwaitingConfirmation, self::Blacklisted, self::Cancelled,
            ],
            self::AwaitingConfirmation => [
                self::Confirmed, self::Blacklisted, self::Cancelled, self::Registered, self::WaitingList,
            ],
            self::Confirmed => [
                self::AwaitingConfirmation, self::Blacklisted, self::Cancelled, self::Registered, self::WaitingList,
            ],
            self::Blacklisted => [
                self::Confirmed, self::AwaitingConfirmation, self::Registered, self::WaitingList, self::Cancelled,
            ],
            self::Cancelled => [
                self::Confirmed, self::AwaitingConfirmation, self::Registered, self::WaitingList,
            ],
        };
    }

    public function canTransitionTo(self $status): bool
    {
        return in_array($status, $this->allowedTransitions(), true);
    }

    public function label(): string
    {
        return match ($this) {
            self::Confirmed => __('Confirmed'),
            self::AwaitingConfirmation => __('Awaiting Confirmation'),
            self::Registered => __('Registered'),
            self::WaitingList => __('Waiting List'),
            self::Blacklisted => __('Blacklisted'),
            self::Cancelled => __('Cancelled'),
        };
    }

    /**
     * Bootstrap 5 badge class, for the admin DataTables.
     */
    public function badgeClass(): string
    {
        return match ($this) {
            self::Confirmed => 'bg-success-subtle text-success',
            self::AwaitingConfirmation => 'bg-warning-subtle text-warning',
            self::Registered => 'bg-info-subtle text-info',
            self::WaitingList => 'bg-warning-subtle text-warning',
            self::Blacklisted => 'bg-dark-subtle text-dark',
            self::Cancelled => 'bg-secondary-subtle text-secondary',
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
