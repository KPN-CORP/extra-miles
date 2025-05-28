<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\EmployeeController;

class UpdateEmployeeAccessMenu extends Command
{
    protected $signature = 'update:employee-access-menu';
    protected $description = 'Update employee access menu based on schedules';

    protected $employeeController;

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $employeeController = app(EmployeeController::class);
        $result = $employeeController->updateEmployeeAccessMenu();
        $this->info($result);
    }
}
