<?php

namespace App\Exceptions;

use App\Enums\WellnessRegistrationStatus;
use RuntimeException;

class WellnessRegistrationException extends RuntimeException
{
    /**
     * Machine-readable reason, so the API can answer with something the SPA can
     * branch on instead of matching on message text.
     */
    public string $reason;

    public static function make(string $reason, string $message): self
    {
        $exception = new self($message);
        $exception->reason = $reason;

        return $exception;
    }

    public static function registrationClosed(): self
    {
        return self::make('registration_closed', __('Registration for this schedule is not open.'));
    }

    public static function alreadyRegistered(WellnessRegistrationStatus $status): self
    {
        return self::make(
            'already_registered',
            __('This employee already has a registration for this schedule (:status).', ['status' => $status->label()])
        );
    }

    public static function quotaExceeded(): self
    {
        return self::make('quota_exceeded', __('This schedule is already full.'));
    }

    public static function invalidTransition(WellnessRegistrationStatus $from, WellnessRegistrationStatus $to): self
    {
        return self::make(
            'invalid_transition',
            __('Cannot change a registration from :from to :to.', ['from' => $from->label(), 'to' => $to->label()])
        );
    }

    public static function invalidQrToken(): self
    {
        return self::make('invalid_qr', __('This QR code is not valid.'));
    }

    public static function checkInClosed(): self
    {
        return self::make('check_in_closed', __('Check-in for this schedule is not open right now.'));
    }

    public static function notConfirmed(): self
    {
        return self::make('not_confirmed', __('Only a registration holding a seat can be checked in.'));
    }

    public static function nothingToConfirm(): self
    {
        return self::make('nothing_to_confirm', __('This registration is not waiting for your confirmation.'));
    }

    public static function confirmationClosed(): self
    {
        return self::make('confirmation_closed', __('The confirmation deadline for this session has passed.'));
    }

    public static function notRegistered(): self
    {
        return self::make('not_registered', __('No registration found for this employee on this schedule.'));
    }
}
