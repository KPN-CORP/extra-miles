<?php

namespace App\Http\Controllers;

use App\Enums\WellnessRecurrenceFrequency;
use App\Enums\WellnessScheduleStatus;
use App\Http\Controllers\Concerns\DecryptsRouteId;
use App\Http\Requests\WellnessActivityScheduleRequest;
use App\Models\WellnessActivity;
use App\Models\WellnessActivitySchedule;
use App\Services\WellnessScheduleRecurrenceService;
use Illuminate\Support\Facades\Auth;

class WellnessActivityScheduleController extends Controller
{
    use DecryptsRouteId;

    public function __construct(private readonly WellnessScheduleRecurrenceService $recurrence) {}

    public function index(string $encryptedId)
    {
        $activity = WellnessActivity::with('type')->findOrFail($this->decryptId($encryptedId));

        $schedules = $activity->schedules()
            ->withSeatCounts()
            ->orderBy('start_at')
            ->get();

        // A stable number per series, so the list can show "Repeat #2 of 6"
        // without the admin having to count rows themselves. Built as a plain
        // array keyed by id -- Collection::flatMap() collapses through
        // array_merge(), which would renumber the integer ids away.
        $seriesPositions = [];

        foreach ($schedules->filter->isRecurring()->groupBy('recurrence_group_id') as $group) {
            $total = $group->count();

            foreach ($group->values() as $index => $schedule) {
                $seriesPositions[$schedule->id] = ['position' => $index + 1, 'total' => $total];
            }
        }

        return view('pages.admin.wellness.schedules.index', [
            'parentLink' => 'Wellness',
            'link' => $activity->name,
            'back' => 'admin.wellness.activities.index',
            'activity' => $activity,
            'schedules' => $schedules,
            'statuses' => WellnessScheduleStatus::options(),
            'frequencies' => WellnessRecurrenceFrequency::options(),
            'seriesPositions' => $seriesPositions,
        ]);
    }

    public function store(WellnessActivityScheduleRequest $request, string $encryptedId)
    {
        $activity = WellnessActivity::findOrFail($this->decryptId($encryptedId));

        $created = $this->recurrence->createSeries(
            $activity,
            $request->scheduleAttributes(),
            $request->repeatFrequency(),
            $request->repeatUntil(),
            Auth::id(),
        );

        return redirect()->back()->with('success', $created->count() > 1
            ? __(':count schedules added.', ['count' => $created->count()])
            : __('Schedule added.'));
    }

    public function update(WellnessActivityScheduleRequest $request, string $encryptedId)
    {
        $schedule = WellnessActivitySchedule::withSeatCounts()
            ->findOrFail($this->decryptId($encryptedId));

        $attributes = $request->scheduleAttributes();
        $quota = $attributes['quota'] ?? null;

        // Shrinking the quota below the seats already held would silently put the
        // schedule over capacity; make the admin free those seats first.
        if ($quota !== null && $quota < $schedule->taken_seats) {
            return redirect()->back()->with('error', __('Quota cannot be lower than the :count seat(s) already held.', ['count' => $schedule->taken_seats]));
        }

        $applyToFollowing = $request->updateScope()->includesFollowing() && $schedule->isRecurring();

        if ($applyToFollowing && $quota !== null) {
            // Same guard, across the whole tail of the series -- an edit that
            // would overfill a later session is rejected before anything moves.
            $overfilled = $schedule->followingInSeries()
                ->withSeatCounts()
                ->get()
                ->first(fn (WellnessActivitySchedule $sibling) => $sibling->taken_seats > $quota);

            if ($overfilled) {
                return redirect()->back()->with('error', __('The session on :date already holds :count seat(s), so the quota cannot be lowered to :quota across the series.', [
                    'date' => $overfilled->start_at->translatedFormat('D, d M Y H:i'),
                    'count' => $overfilled->taken_seats,
                    'quota' => $quota,
                ]));
            }
        }

        $original = [
            'start_at' => $schedule->start_at->copy(),
            'end_at' => $schedule->end_at->copy(),
        ];

        $schedule->update($attributes + ['updated_by' => Auth::id()]);

        if (! $applyToFollowing) {
            return redirect()->back()->with('success', __('Schedule updated.'));
        }

        $updated = $this->recurrence->applyToFollowing($schedule, $original, Auth::id());

        return redirect()->back()->with('success', $updated > 0
            ? __('Schedule updated, along with :count following occurrence(s).', ['count' => $updated])
            : __('Schedule updated.'));
    }

    public function archive(string $encryptedId)
    {
        $schedule = WellnessActivitySchedule::withSeatCounts()
            ->findOrFail($this->decryptId($encryptedId));

        if ($schedule->taken_seats > 0) {
            return redirect()->back()->with('error', __('This schedule still has :count active registration(s). Cancel them first.', ['count' => $schedule->taken_seats]));
        }

        $schedule->delete();

        return redirect()->back()->with('success', __('Schedule archived.'));
    }
}
