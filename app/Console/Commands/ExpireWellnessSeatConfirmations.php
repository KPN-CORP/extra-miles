<?php

namespace App\Console\Commands;

use App\Services\WellnessRegistrationService;
use Illuminate\Console\Command;

/**
 * Revokes wellness seats whose confirmation window has closed, and hands each
 * freed seat to the next person in the queue.
 *
 * Runs often rather than once a day: the whole point of a deadline is that the
 * seat moves on promptly, and a queued employee promoted at 09:05 for a 10:00
 * session needs the time that is left.
 */
class ExpireWellnessSeatConfirmations extends Command
{
    protected $signature = 'wellness:expire-confirmations';

    protected $description = 'Revoke wellness seats not confirmed before their deadline and promote the queue';

    public function handle(WellnessRegistrationService $service): int
    {
        ['revoked' => $revoked, 'promoted' => $promoted] = $service->expireUnconfirmed();

        if ($revoked === 0) {
            $this->info('No expired seat confirmations.');

            return self::SUCCESS;
        }

        $this->info("Revoked {$revoked} unconfirmed seat(s); offered {$promoted} to the queue.");

        return self::SUCCESS;
    }
}
