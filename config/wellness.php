<?php

return [

    /*
    |--------------------------------------------------------------------------
    | QR Check-in Window
    |--------------------------------------------------------------------------
    |
    | How many minutes before a schedule starts, and after it ends, the
    | attendance QR will accept a scan. Keeps a printed QR from being usable
    | days later, without forcing participants to scan at the exact minute.
    |
    */

    'check_in' => [
        'opens_minutes_before' => (int) env('WELLNESS_CHECKIN_BEFORE', 60),
        'closes_minutes_after' => (int) env('WELLNESS_CHECKIN_AFTER', 60),
    ],

    /*
    |--------------------------------------------------------------------------
    | Waitlist
    |--------------------------------------------------------------------------
    |
    | When a seat frees up, the head of the waitlist is promoted automatically.
    | Self-registrations are promoted to `pending` so an admin still makes the
    | final call; set this to false to require a manual promotion instead.
    |
    */

    'waitlist' => [
        'auto_promote' => (bool) env('WELLNESS_WAITLIST_AUTO_PROMOTE', true),
    ],

];
