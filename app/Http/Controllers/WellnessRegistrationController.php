<?php

namespace App\Http\Controllers;

use App\Enums\WellnessRegistrationSource;
use App\Enums\WellnessRegistrationStatus;
use App\Exceptions\WellnessRegistrationException;
use App\Exports\WellnessParticipantsExport;
use App\Http\Controllers\Concerns\DecryptsRouteId;
use App\Http\Requests\StoreWellnessRegistrationRequest;
use App\Models\Employee;
use App\Models\WellnessActivityRegistration;
use App\Models\WellnessActivitySchedule;
use App\Models\WellnessBlacklist;
use App\Services\WellnessRegistrationService;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Maatwebsite\Excel\Facades\Excel;

class WellnessRegistrationController extends Controller
{
    use DecryptsRouteId;

    public function __construct(protected WellnessRegistrationService $service) {}

    public function index(string $encryptedId)
    {
        $schedule = WellnessActivitySchedule::with('activity.type')
            ->findOrFail($this->decryptId($encryptedId));

        $method = $schedule->activity->registration_method;

        $registrations = $schedule->registrations()
            ->with('statusHistories.creator')
            ->orderBy('registered_at')
            ->orderBy('id')
            ->get();

        // Who is currently on the module-wide blacklist, so the list can flag
        // them even when this particular registration is not blacklisted.
        $blacklisted = WellnessBlacklist::query()
            ->active()
            ->whereIn('employee_id', $registrations->pluck('employee_id')->unique())
            ->pluck('employee_id')
            ->flip();

        // Tabs follow the method's own vocabulary: FIFO shows Registered,
        // Selection shows Waiting List. Neither shows the other's queue.
        $groups = [];
        foreach ($method->statuses() as $status) {
            $groups[$status->value] = $registrations->where('status', $status)->values();
        }

        // Read-only here: employees write feedback from the mobile app after
        // they have attended, the admin side only lists it.
        $feedback = $schedule->feedback()
            ->with('registration')
            ->orderByDesc('submitted_at')
            ->orderByDesc('id')
            ->get();

        return view('pages.admin.wellness.registrations.index', [
            'parentLink' => 'Wellness',
            'link' => 'Participants',
            'back' => 'admin.wellness.activities.index',
            'schedule' => $schedule,
            'method' => $method,
            'registrations' => $registrations,
            'groups' => $groups,
            'statuses' => $method->statuses(),
            'queueStatus' => $method->queueStatus(),
            'blacklisted' => $blacklisted,
            'feedback' => $feedback,
            'takenSeats' => $registrations->filter(fn ($r) => $r->status->consumesSlot())->count(),
            'queuedCount' => $registrations->filter(fn ($r) => $r->status->isQueued())->count(),
            'attendedCount' => $registrations->whereNotNull('attended_at')->count(),
        ]);
    }

    /**
     * Admin adds an employee. The seat is granted straight away -- an admin
     * putting someone on the list is the decision -- though on a session with a
     * confirmation deadline the employee still has to accept it.
     */
    public function store(StoreWellnessRegistrationRequest $request, string $encryptedId)
    {
        $schedule = WellnessActivitySchedule::findOrFail($this->decryptId($encryptedId));

        $employee = Employee::where('employee_id', $request->validated('employee_id'))->first();

        if (! $employee) {
            return redirect()->back()->with('error', __('Employee not found in the HR database.'));
        }

        try {
            $this->service->register(
                schedule: $schedule,
                employee: [
                    'employee_id' => $employee->employee_id,
                    'fullname' => $employee->fullname,
                    'business_unit' => $employee->group_company,
                    'unit' => $employee->unit,
                    'job_level' => $employee->job_level,
                    'location' => $employee->office_area,
                ],
                source: WellnessRegistrationSource::Admin,
                actorId: Auth::id(),
                remark: $request->validated('remark'),
                allowOverQuota: (bool) $request->validated('allow_over_quota'),
            );
        } catch (WellnessRegistrationException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        $schedule->refresh();

        return redirect()->back()->with('success', $schedule->requiresConfirmation()
            ? __(':name has been registered and asked to confirm.', ['name' => $employee->fullname])
            : __(':name has been registered and confirmed.', ['name' => $employee->fullname]));
    }

    /**
     * Give the employee the seat. On a session with a confirmation deadline
     * this only offers it -- the employee still has to accept before it lapses.
     */
    public function confirm(Request $request, string $encryptedId)
    {
        $registration = WellnessActivityRegistration::with('schedule.activity')
            ->findOrFail($this->decryptId($encryptedId));

        $request->validate(['remark' => ['nullable', 'string', 'max:500']]);

        try {
            $this->service->grantSeat($registration, $request->input('remark'), Auth::id());
        } catch (WellnessRegistrationException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        $name = ['name' => $this->nameFor($registration)];

        return redirect()->back()->with('success', $registration->status->awaitsConfirmation()
            ? __(':name has been given a seat and asked to confirm.', $name)
            : __(':name has been confirmed.', $name));
    }

    public function cancel(Request $request, string $encryptedId)
    {
        return $this->transition($request, $encryptedId, WellnessRegistrationStatus::Cancelled, 'cancelled');
    }

    /**
     * Take a seat back. The freed seat is offered to the next person in the
     * FIFO queue automatically; under Selection it returns to the admin.
     */
    public function revoke(Request $request, string $encryptedId)
    {
        $registration = WellnessActivityRegistration::with('schedule.activity')
            ->findOrFail($this->decryptId($encryptedId));

        $validated = $request->validate(['remark' => ['nullable', 'string', 'max:500']]);

        try {
            $this->service->transitionTo(
                $registration,
                WellnessRegistrationStatus::Cancelled,
                $validated['remark'] ?? 'Seat revoked by an administrator.',
                Auth::id(),
            );
        } catch (WellnessRegistrationException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->back()->with('success', __(
            'The seat held by :name has been revoked.',
            ['name' => $this->nameFor($registration)]
        ));
    }

    /**
     * Send a registration back to its method's queue, undoing a confirmation
     * or lifting a blacklist for this session.
     */
    public function requeue(Request $request, string $encryptedId)
    {
        $registration = WellnessActivityRegistration::with('schedule.activity')
            ->findOrFail($this->decryptId($encryptedId));

        $request->validate(['remark' => ['nullable', 'string', 'max:500']]);

        try {
            $this->service->requeue($registration, $request->input('remark'), Auth::id());
        } catch (WellnessRegistrationException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->back()->with('success', __(':name has been moved back to the queue.', ['name' => $this->nameFor($registration)]));
    }

    /**
     * Blacklist a participant for this session, and by default add them to the
     * module-wide blacklist so they are not auto-confirmed in future.
     */
    public function blacklist(Request $request, string $encryptedId)
    {
        $registration = WellnessActivityRegistration::with('schedule.activity')
            ->findOrFail($this->decryptId($encryptedId));

        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
            'end_date' => ['nullable', 'date', 'after_or_equal:today'],
            'add_to_master_list' => ['nullable', 'boolean'],
        ]);

        try {
            $this->service->blacklist(
                registration: $registration,
                reason: $validated['reason'],
                actorId: Auth::id(),
                endDate: $validated['end_date'] ?? null,
                addToMasterList: $request->boolean('add_to_master_list', true),
            );
        } catch (WellnessRegistrationException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->back()->with('success', __(':name has been blacklisted.', ['name' => $this->nameFor($registration)]));
    }

    public function bulkConfirm(Request $request)
    {
        $request->validate([
            'selected_ids' => ['required', 'array', 'min:1'],
            'selected_ids.*' => ['string'],
        ]);

        $confirmed = 0;
        $failures = [];

        foreach ($request->input('selected_ids') as $encryptedId) {
            // Encrypted ids here too -- the checkboxes must not expose raw keys.
            try {
                $id = (int) Crypt::decryptString($encryptedId);
            } catch (DecryptException) {
                continue;
            }

            $registration = WellnessActivityRegistration::find($id);

            if (! $registration) {
                continue;
            }

            try {
                $this->service->grantSeat($registration, 'Bulk confirmed.', Auth::id());
                $confirmed++;
            } catch (WellnessRegistrationException $e) {
                // Quota can run out partway through; report which ones missed
                // out rather than failing the whole batch.
                $failures[] = $this->nameFor($registration).': '.$e->getMessage();
            }
        }

        $message = __(':count registration(s) confirmed.', ['count' => $confirmed]);

        if ($failures) {
            return redirect()->back()
                ->with('success', $message)
                ->with('error', __('Skipped :count: :failures', [
                    'count' => count($failures),
                    'failures' => implode(' | ', array_slice($failures, 0, 3)),
                ]));
        }

        return redirect()->back()->with('success', $message);
    }

    public function export(string $encryptedId)
    {
        $schedule = WellnessActivitySchedule::with('activity')
            ->findOrFail($this->decryptId($encryptedId));

        $filename = 'wellness_participants_'.$schedule->id.'_'.now()->format('Ymd_His').'.xlsx';

        return Excel::download(new WellnessParticipantsExport($schedule->id), $filename);
    }

    /**
     * Typeahead for the "add employee" modal. Reads the `kpncorp` connection.
     */
    public function searchEmployees(Request $request)
    {
        $query = trim((string) $request->get('q'));

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $employees = Employee::query()
            ->where(function ($q) use ($query) {
                $q->where('fullname', 'like', "%{$query}%")
                    ->orWhere('employee_id', 'like', "%{$query}%");
            })
            ->whereNull('deleted_at')
            ->select('employee_id', 'fullname', 'group_company', 'unit', 'job_level', 'office_area')
            ->orderBy('fullname')
            ->limit(15)
            ->get();

        $blacklisted = WellnessBlacklist::query()
            ->active()
            ->whereIn('employee_id', $employees->pluck('employee_id'))
            ->pluck('employee_id')
            ->flip();

        return response()->json(
            $employees->map(fn ($employee) => [
                'employee_id' => $employee->employee_id,
                'fullname' => $employee->fullname,
                'group_company' => $employee->group_company,
                'unit' => $employee->unit,
                'job_level' => $employee->job_level,
                'office_area' => $employee->office_area,
                'blacklisted' => $blacklisted->has($employee->employee_id),
            ])
        );
    }

    protected function transition(Request $request, string $encryptedId, WellnessRegistrationStatus $target, string $verb)
    {
        $registration = WellnessActivityRegistration::with('schedule.activity')
            ->findOrFail($this->decryptId($encryptedId));

        $request->validate(['remark' => ['nullable', 'string', 'max:500']]);

        try {
            $this->service->transitionTo($registration, $target, $request->input('remark'), Auth::id());
        } catch (WellnessRegistrationException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        $name = ['name' => $this->nameFor($registration)];

        return redirect()->back()->with('success', match ($verb) {
            'confirmed' => __(':name has been confirmed.', $name),
            'cancelled' => __(':name has been cancelled.', $name),
            default => __(':name has been updated.', $name),
        });
    }

    protected function nameFor(WellnessActivityRegistration $registration): string
    {
        return $registration->fullname ?: $registration->employee_id;
    }
}
