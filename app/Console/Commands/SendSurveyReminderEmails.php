<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Survey;
use App\Models\EventParticipant;
use App\Models\Employee;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Carbon;

class SendSurveyReminderEmails extends Command
{
    protected $signature = 'reminder:survey';
    protected $description = 'Send survey reminders to event participants who haven\'t submitted yet';

    public function handle()
    {
        $today = Carbon::now()->startOfDay();
        $yesterday = $today->copy()->subDay();
        
        // Ambil survey yang dimulai kemarin, masih aktif, dan punya event_id
        $surveys = Survey::whereDate('start_date', '<', $today)
            ->whereDate('end_date', '>=', $today)
            ->whereNotNull('event_id')
            ->with(['eventParticipant', 'surveyParticipant'])
            ->get();

        foreach ($surveys as $survey) {
            $filledEmails = $survey->surveyParticipant->pluck('employee_id')->toArray(); // yang sudah isi

            $eventParticipants = $survey->eventParticipant;

            foreach ($eventParticipants as $participant) {
                // $employeeEmail = $participant->employee->email ?? null;
                $employeeEmail = "eriton.dewa@kpn-corp.com";

                if ($employeeEmail && !in_array($participant->employee_id, $filledEmails)) {

                    // Kirim email reminder
                    Mail::to($employeeEmail)->send(new \App\Mail\SurveyReminderMail($survey));
                    $this->info("Reminder sent to: $employeeEmail for survey ID {$survey->id}");
                }
            }
        }

        return 0;
    }
}
