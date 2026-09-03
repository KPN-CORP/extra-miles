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
        return self::make('registration_closed', 'Registration for this schedule is not open.');
    }

    public static function alreadyRegistered(WellnessRegistrationStatus $status): self
    {
        return self::make(
            'already_registered',
            'This employee already has a registration for this schedule ('.$status->label().').'
        );
    }

    public static function quotaExceeded(): self
    {
        return self::make('quota_exceeded', 'This schedule is already full.');
    }

    public static function invalidTransition(WellnessRegistrationStatus $from, WellnessRegistrationStatus $to): self
    {
        return self::make(
            'invalid_transition',
            'Cannot change a registration from '.$from->label().' to '.$to->label().'.'
        );
    }

    public static function invalidQrToken(): self
    {
        return self::make('invalid_qr', 'This QR code is not valid.');
    }

    public static function checkInClosed(): self
    {
        return self::make('check_in_closed', 'Check-in for this schedule is not open right now.');
    }

    public static function notConfirmed(): self
    {
        return self::make('not_confirmed', 'Only a confirmed registration can be checked in.');
    }

    public static function notRegistered(): self
    {
        return self::make('not_registered', 'No registration found for this employee on this schedule.');
    }
}
