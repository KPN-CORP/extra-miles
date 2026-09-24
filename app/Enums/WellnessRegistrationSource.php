<?php

namespace App\Enums;

enum WellnessRegistrationSource: string
{
    /** The employee registered themselves from the mobile app. */
    case SelfService = 'self';

    /** An admin registered the employee from the back-office. */
    case Admin = 'admin';

    public function label(): string
    {
        return match ($this) {
            self::SelfService => __('Self Registration'),
            self::Admin => __('Registered by Admin'),
        };
    }
}
