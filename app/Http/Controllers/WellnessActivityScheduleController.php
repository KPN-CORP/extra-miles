<?php

namespace App\Http\Controllers;

use App\Enums\WellnessRegistrationStatus;
use App\Enums\WellnessScheduleStatus;
use App\Http\Controllers\Concerns\DecryptsRouteId;
use App\Http\Requests\WellnessActivityScheduleRequest;
use App\Models\WellnessActivity;
use App\Models\WellnessActivitySchedule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class WellnessActivityScheduleController extends Controller
{
    use DecryptsRouteId;

    public function index(string $encryptedId)
    {
        $activity = WellnessActivity::with('type')->findOrFail($this->decryptId($encryptedId));

        $schedules = $activity->schedules()
            ->withCount($this->seatCounts())
            ->orderBy('start_at')
            ->get();

        return view('pages.admin.wellness.schedules.index', [
            'parentLink' => 'Wellness',
            'link' => $activity->name,
            'back' => 'admin.wellness.activities.index',
            'activity' => $activity,
            'schedules' => $schedules,
            'statuses' => WellnessScheduleStatus::options(),
        ]);
    }

    public function store(WellnessActivityScheduleRequest $request, string $encryptedId)
    {
        $activity = WellnessActivity::findOrFail($this->decryptId($encryptedId));

        $activity->schedules()->create($request->validated() + [
            'created_by' => Auth::id(),
        ]);

        return redirect()->back()->with('success', 'Schedule added.');
    }

    public function update(WellnessActivityScheduleRequest $request, string $encryptedId)
    {
        $schedule = WellnessActivitySchedule::withCount($this->seatCounts())
            ->findOrFail($this->decryptId($encryptedId));

        // Shrinking the quota below the seats already held would silently put the
        // schedule over capacity; make the admin free those seats first.
        $quota = $request->validated('quota');
        if ($quota !== null && $quota < $schedule->taken_seats) {
            return redirect()->back()->with('error', 'Quota cannot be lower than the '.$schedule->taken_seats.' seat(s) already held.');
        }

        $schedule->update($request->validated() + ['updated_by' => Auth::id()]);

        return redirect()->back()->with('success', 'Schedule updated.');
    }

    public function archive(string $encryptedId)
    {
        $schedule = WellnessActivitySchedule::withCount($this->seatCounts())
            ->findOrFail($this->decryptId($encryptedId));

        if ($schedule->taken_seats > 0) {
            return redirect()->back()->with('error', 'This schedule still has '.$schedule->taken_seats.' active registration(s). Cancel them first.');
        }

        $schedule->delete();

        return redirect()->back()->with('success', 'Schedule archived.');
    }

    /**
     * Rotate the attendance QR -- use when a printed code leaks.
     */
    public function rotateQr(string $encryptedId)
    {
        $schedule = WellnessActivitySchedule::findOrFail($this->decryptId($encryptedId));

        $schedule->update([
            'qr_token' => (string) Str::uuid(),
            'updated_by' => Auth::id(),
        ]);

        return redirect()->back()->with('success', 'A new QR code has been generated. Reprint it before the session.');
    }

    /**
     * Printable attendance QR for a single session.
     */
    public function qr(string $encryptedId)
    {
        $schedule = WellnessActivitySchedule::with('activity.type')
            ->findOrFail($this->decryptId($encryptedId));

        return view('pages.admin.wellness.schedules.qr', [
            'schedule' => $schedule,
        ]);
    }

    /**
     * @return array<string, \Closure>
     */
    protected function seatCounts(): array
    {
        return [
            'registrations as taken_seats' => fn ($q) => $q->whereIn('status', WellnessRegistrationStatus::slotConsumingValues()),
            'registrations as queued_seats' => fn ($q) => $q->whereIn('status', WellnessRegistrationStatus::queuedValues()),
            'registrations as attended_seats' => fn ($q) => $q->whereNotNull('attended_at'),
        ];
    }
}
