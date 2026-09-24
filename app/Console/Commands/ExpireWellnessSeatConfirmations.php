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

    protected $description = 'Revoke wellness seats not confirmed in time, release lapsed blacklists, and promote the queue';

    public function handle(WellnessRegistrationService $service): int
    {
        // Blacklists first: a registration released back into the queue this
        // minute should be eligible for any seat the expiry sweep then frees.
        ['restored' => $restored, 'promoted' => $afterRestore] = $service->restoreExpiredBlacklists();
        ['revoked' => $revoked, 'promoted' => $afterExpiry] = $service->expireUnconfirmed();

        if ($restored === 0 && $revoked === 0) {
            $this->info('Nothing to expire or restore.');

            return self::SUCCESS;
        }

        if ($restored > 0) {
            $this->info("Returned {$restored} registration(s) to the queue after their blacklist ended.");
        }

        if ($revoked > 0) {
            $this->info("Revoked {$revoked} unconfirmed seat(s).");
        }

        $this->info('Offered '.($afterRestore + $afterExpiry).' seat(s) to the queue.');

        return self::SUCCESS;
    }
}
