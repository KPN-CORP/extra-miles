<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Employee;
use App\Models\Schedule;

class DailyScheduleUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:daily-schedule-update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update employee access menu based on daily schedule';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Mendapatkan jadwal yang memiliki start_date di hari ini
        $today = now()->format('Y-m-d');
        $schedules = Schedule::where('start_date', $today)->get();

        // Looping melalui jadwal yang ditemukan
        foreach ($schedules as $schedule) {
            // Membuat kueri untuk mendapatkan karyawan yang memenuhi kriteria jadwal
            $employeesQuery = Employee::query()
                ->whereIn('work_area_code', explode(',', $schedule->location_filter))
                ->whereIn('contribution_level_code', explode(',', $schedule->company_filter))
                ->whereIn('group_company', explode(',', $schedule->bisnis_unit))
                ->whereIn('employee_type', explode(',', $schedule->employee_type))
                ->where('date_of_joining', '<=', $schedule->last_join_date);

            // Mendapatkan karyawan yang memenuhi kriteria
            $employeesToUpdate = $employeesQuery->get();

            // Mengupdate setiap karyawan yang ditemukan
            foreach ($employeesToUpdate as $employee) {
                // Mendapatkan nilai JSON yang ada dalam access_menu
                $accessMenuJson = json_decode($employee->access_menu, true);

                // Memeriksa apakah access_menu kosong
                if (empty($accessMenuJson) || $accessMenuJson === null) {
                    // Jika kosong, atur access_menu menjadi {{ goals:1 }}
                    $accessMenuJson = ['goals' => 1];
                } else {
                    // Jika tidak kosong, perbarui nilai khusus dalam objek JSON
                    $accessMenuJson['goals'] = 1;
                }

                // Mengonversi kembali objek JSON ke format string
                $updatedAccessMenu = json_encode($accessMenuJson);
                
                // Mengisi access_menu dengan nilai yang telah diperbarui
                $employee->access_menu = $updatedAccessMenu;
                $employee->save();
            }
        }

        $this->info('Daily schedule update completed successfully.');
    }
}
