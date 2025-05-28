<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\ScheduleController;

class SendReminderEmailSchedules extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:reminderSchedules';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $controller = new ScheduleController();
        $controller->reminderDailySchedules();
        $this->info('Email reminder sent successfully.');
    }
}
