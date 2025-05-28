<?php

use App\Console\Commands\UpdateAppVersion;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Artisan::command('version', function () {
    $this->comment(UpdateAppVersion::class);
});
// Artisan::call('app:version');
