<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class UpdateAppVersion extends Command
{
    protected $signature = 'app:version';

    protected $description = 'Update application version in .env file';

    public function handle()
    {
        $version = trim(exec('git describe --tags --abbrev=0'));

        if (empty($version)) {
            $version = trim(exec('git rev-parse --short HEAD'));
        }

        file_put_contents(base_path('.env'), str_replace(
            'APP_VERSION=' . config('app.version'),
            'APP_VERSION=' . $version,
            file_get_contents(base_path('.env'))
        ));

        $this->info('Application version updated to ' . $version);
    }
}
