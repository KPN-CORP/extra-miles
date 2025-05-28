<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\EmployeeController;

class FetchAndStoreEmployees extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fetch:employees';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch and store employees data from API';

    /**
     * Execute the console command.
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $controller = new EmployeeController();
        $controller->fetchAndStoreEmployees();
        $this->info('Employees data successfully fetched and stored.');
    }
}
