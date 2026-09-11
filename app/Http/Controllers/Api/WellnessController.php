<?php

namespace App\Http\Controllers\Api;

use App\Enums\WellnessActivityStatus;
use App\Enums\WellnessRegistrationSource;
use App\Enums\WellnessRegistrationStatus;
use App\Enums\WellnessScheduleStatus;
use App\Exceptions\WellnessRegistrationException;
use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\WellnessActivity;
use App\Models\WellnessActivityFeedback;
use App\Models\WellnessActivityRegistration;
use App\Models\WellnessActivitySchedule;
use App\Services\WellnessRegistrationService;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Crypt;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

class WellnessController extends Controller
{
    public function __construct(protected WellnessRegistrationService $service) {}

    /**
     * Active activities, each with its upcoming open sessions.
     */
    public function getActivities(): JsonResponse
    {
        $employeeId = $this->employeeId();

        if ($employeeId instanceof JsonResponse) {
            return $employeeId;
        }

        $activities = WellnessActivity::with(['type'])
            ->visibleToEmployees()
            ->whereHas('schedules', fn ($q) => $q->open()->upcoming())
            ->get();

        $schedules = $this->schedulesFor($activities->pluck('id'), $employeeId);

        return response()->json(
            $activities->map(fn (WellnessActivity $activity) => [
                'id' => $activity->encrypted_id,
                'name' => $activity->name,
                'description' => $activity->description,
                'image' => $activity->image,
                'type' => $activity->type?->name,
                'upcoming_count' => $schedules->get($activity->id)?->count() ?? 0,
                'next_session' => $schedules->get($activity->id)?->first(),
            ])->values()
        );
    }

    /**
     * One activity with every upcoming session and this employee's standing on each.
     */
    public function getActivityDetails(string $id): JsonResponse
    {
        $employeeId = $this->employeeId();

        if ($employeeId instanceof JsonResponse) {
            return $employeeId;
        }

        try {
            $activityId = (int) Crypt::decryptString($id);
        } catch (DecryptException) {
            return response()->json(['error' => 'Activity not found'], 404);
        }

        $activity = WellnessActivity::with('type')
            ->where('status', WellnessActivityStatus::Active->value)
            ->find($activityId);

        if (! $activity) {
            return response()->json(['error' => 'Activity not found'], 404);
        }

        return response()->json([
            'id' => $activity->encrypted_id,
            'name' => $activity->name,
            'description' => $activity->description,
            'image' => $activity->image,
            'type' => $activity->type?->name,
            'registration_method' => $activity->registration_method->value,
            'registration_method_label' => $activity->registration_method->shortLabel(),
            'registration_method_note' => $activity->registration_method->description(),
            'schedules' => $this->schedulesFor(collect([$activity->id]), $employeeId)->get($activity->id, collect())->values(),
        ]);
    }

    /**
     * This employee's registrations, newest session first.
     */
    public function myRegistrations(): JsonResponse
    {
        $employeeId = $this->employeeId();

        if ($employeeId instanceof JsonResponse) {
            return $employeeId;
        }

        $registrations = WellnessActivityRegistration::with(['schedule.activity.type', 'feedback'])
            ->forEmployee($employeeId)
            ->whereIn('status', [
                WellnessRegistrationStatus::Confirmed->value,
                WellnessRegistrationStatus::Registered->value,
                WellnessRegistrationStatus::WaitingList->value,
            ])
            ->get()
            ->sortByDesc(fn ($registration) => $registration->schedule?->start_at)
            ->values();

        return response()->json(
            $registrations->map(fn (WellnessActivityRegistration $registration) => [
                'id' => $registration->encrypted_id,
                'status' => $registration->status->value,
                'status_label' => $registration->status->label(),
                'attended_at' => $registration->attended_at?->toDateTimeString(),
                'registered_at' => $registration->registered_at?->toDateTimeString(),
                'can_cancel' => $registration->status->canTransitionTo(WellnessRegistrationStatus::Cancelled),
                'can_check_in' => $registration->status === WellnessRegistrationStatus::Confirmed
                    && $registration->attended_at === null
                    && (bool) $registration->schedule?->isCheckInOpen(),
                'can_submit_feedback' => $registration->canSubmitFeedback(),
                'feedback' => $registration->feedback ? [
                    'message' => $registration->feedback->message,
                    'submitted_at' => $registration->feedback->submitted_at?->toDateTimeString(),
                ] : null,
                'activity' => [
                    'id' => $registration->schedule?->activity?->encrypted_id,
                    'name' => $registration->schedule?->activity?->name,
                    'type' => $registration->schedule?->activity?->type?->name,
                    'image' => $registration->schedule?->activity?->image,
                ],
                'schedule' => $registration->schedule ? $this->schedulePayload($registration->schedule) : null,
            ])
        );
    }

    /**
     * Self-registration. Lands on `pending`, or `waitlisted` when the session
     * is full -- the service decides, not the caller.
     */
    public function register(Request $request): JsonResponse
    {
        $employeeId = $this->employeeId();

        if ($employeeId instanceof JsonResponse) {
            return $employeeId;
        }

        $request->validate(['schedule_id' => 'required|string']);

        try {
            $scheduleId = (int) Crypt::decryptString($request->input('schedule_id'));
        } catch (DecryptException) {
            return response()->json(['error' => 'Schedule not found'], 404);
        }

        $schedule = WellnessActivitySchedule::find($scheduleId);

        if (! $schedule) {
            return response()->json(['error' => 'Schedule not found'], 404);
        }

        $employee = Employee::where('employee_id', $employeeId)->first();

        if (! $employee) {
            return response()->json(['error' => 'Employee not found'], 404);
        }

        try {
            $registration = $this->service->register(
                schedule: $schedule,
                employee: [
                    'employee_id' => $employee->employee_id,
                    'fullname' => $employee->fullname,
                    'business_unit' => $employee->group_company,
                    'unit' => $employee->unit,
                    'job_level' => $employee->job_level,
                    'location' => $employee->office_area,
                ],
                source: WellnessRegistrationSource::SelfService,
            );
        } catch (WellnessRegistrationException $e) {
            return response()->json(['error' => $e->getMessage(), 'reason' => $e->reason], 422);
        }

        return response()->json([
            'message' => match (true) {
                $registration->status === WellnessRegistrationStatus::Confirmed => 'Your seat is confirmed. See you there!',
                $registration->status === WellnessRegistrationStatus::Registered => 'This session is full, so you are in the queue. We will confirm you automatically if a seat frees up.',
                default => 'You are on the waiting list. An admin will confirm the participants.',
            },
            'status' => $registration->status->value,
            'status_label' => $registration->status->label(),
            'id' => $registration->encrypted_id,
        ]);
    }

    public function cancel(Request $request): JsonResponse
    {
        $employeeId = $this->employeeId();

        if ($employeeId instanceof JsonResponse) {
            return $employeeId;
        }

        $request->validate([
            'registration_id' => 'required|string',
            'remark' => 'nullable|string|max:500',
        ]);

        try {
            $registrationId = (int) Crypt::decryptString($request->input('registration_id'));
        } catch (DecryptException) {
            return response()->json(['error' => 'Registration not found'], 404);
        }

        $registration = WellnessActivityRegistration::find($registrationId);

        // An employee may only cancel their own registration.
        if (! $registration || $registration->employee_id !== $employeeId) {
            return response()->json(['error' => 'Registration not found'], 404);
        }

        try {
            $this->service->cancel($registration, $request->input('remark') ?: 'Cancelled by employee.');
        } catch (WellnessRegistrationException $e) {
            return response()->json(['error' => $e->getMessage(), 'reason' => $e->reason], 422);
        }

        return response()->json(['message' => 'Your registration has been cancelled.']);
    }

    /**
     * Attendance check-in from a scanned session QR.
     */
    public function checkIn(Request $request): JsonResponse
    {
        $employeeId = $this->employeeId();

        if ($employeeId instanceof JsonResponse) {
            return $employeeId;
        }

        $request->validate(['qrCode' => 'required|string']);

        try {
            $registration = $this->service->checkIn($request->input('qrCode'), $employeeId);
        } catch (WellnessRegistrationException $e) {
            return response()->json(['error' => $e->getMessage(), 'reason' => $e->reason], 422);
        }

        return response()->json([
            'message' => 'Your attendance has been recorded.',
            'attended_at' => $registration->attended_at?->toDateTimeString(),
            'activity' => $registration->schedule?->activity?->name,
        ]);
    }

    /**
     * Feedback about a session the employee attended. One per registration:
     * sending it again rewrites what they said rather than adding a second row.
     */
    public function submitFeedback(Request $request): JsonResponse
    {
        $employeeId = $this->employeeId();

        if ($employeeId instanceof JsonResponse) {
            return $employeeId;
        }

        $request->validate([
            'registration_id' => 'required|string',
            'message' => 'required|string|max:2000',
        ]);

        try {
            $registrationId = (int) Crypt::decryptString($request->input('registration_id'));
        } catch (DecryptException) {
            return response()->json(['error' => 'Registration not found'], 404);
        }

        $registration = WellnessActivityRegistration::with('schedule')->find($registrationId);

        // An employee may only leave feedback on their own registration.
        if (! $registration || $registration->employee_id !== $employeeId) {
            return response()->json(['error' => 'Registration not found'], 404);
        }

        if (! $registration->canSubmitFeedback()) {
            return response()->json([
                'error' => 'You can only send feedback for a session you attended, once it has finished.',
                'reason' => 'feedback_not_allowed',
            ], 422);
        }

        $feedback = WellnessActivityFeedback::updateOrCreate(
            ['wellness_activity_registration_id' => $registration->id],
            [
                'wellness_activity_schedule_id' => $registration->wellness_activity_schedule_id,
                'wellness_activity_id' => $registration->wellness_activity_id,
                'employee_id' => $registration->employee_id,
                'fullname' => $registration->fullname,
                'message' => $request->input('message'),
                'submitted_at' => now(),
            ]
        );

        return response()->json([
            'message' => 'Thank you! Your feedback has been sent.',
            'feedback' => [
                'message' => $feedback->message,
                'submitted_at' => $feedback->submitted_at?->toDateTimeString(),
            ],
        ]);
    }

    /**
     * Upcoming open sessions for the given activities, keyed by activity id,
     * each carrying this employee's own standing on that session.
     *
     * @param  Collection<int, int>  $activityIds
     */
    protected function schedulesFor($activityIds, string $employeeId)
    {
        if ($activityIds->isEmpty()) {
            return collect();
        }

        $schedules = WellnessActivitySchedule::whereIn('wellness_activity_id', $activityIds)
            ->where('status', WellnessScheduleStatus::Open->value)
            ->where('end_at', '>=', now())
            ->withCount([
                'registrations as taken_seats' => fn ($q) => $q->whereIn('status', WellnessRegistrationStatus::slotConsumingValues()),
            ])
            ->with(['registrations' => fn ($q) => $q->where('employee_id', $employeeId)])
            ->orderBy('start_at')
            ->get();

        return $schedules
            ->groupBy('wellness_activity_id')
            ->map(fn ($group) => $group->map(fn ($schedule) => $this->schedulePayload($schedule, true)));
    }

    /**
     * @return array<string, mixed>
     */
    protected function schedulePayload(WellnessActivitySchedule $schedule, bool $withSeats = false): array
    {
        $payload = [
            'id' => $schedule->encrypted_id,
            'start_at' => $schedule->start_at->toDateTimeString(),
            'end_at' => $schedule->end_at->toDateTimeString(),
            'location' => $schedule->location,
            'quota' => $schedule->quota,
            'registration_open' => $schedule->isRegistrationOpen(),
            'registration_start_at' => $schedule->registration_start_at?->toDateTimeString(),
            'registration_end_at' => $schedule->registration_end_at?->toDateTimeString(),
            'check_in_open' => $schedule->isCheckInOpen(),
        ];

        if (! $withSeats) {
            return $payload;
        }

        // taken_seats comes from withCount(); relations were constrained to this
        // employee, so `registrations` holds at most their own row.
        $taken = (int) ($schedule->taken_seats ?? 0);
        $mine = $schedule->relationLoaded('registrations') ? $schedule->registrations->first() : null;

        return $payload + [
            'taken_seats' => $taken,
            'remaining_seats' => $schedule->quota === null ? null : max(0, $schedule->quota - $taken),
            'is_full' => $schedule->quota !== null && $taken >= $schedule->quota,
            'my_status' => $mine?->status->value,
            'my_status_label' => $mine?->status->label(),
            'my_registration_id' => $mine?->encrypted_id,
        ];
    }

    /**
     * Employee id from the JWT, or a JSON error response the caller returns as-is.
     */
    protected function employeeId(): string|JsonResponse
    {
        try {
            $payload = JWTAuth::parseToken()->getPayload();
        } catch (TokenExpiredException) {
            return response()->json(['error' => 'Token expired'], 401);
        } catch (TokenInvalidException) {
            return response()->json(['error' => 'Token invalid'], 401);
        } catch (JWTException) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $employeeId = $payload->get('employee_id');

        if (! $employeeId) {
            return response()->json(['error' => 'Employee ID not found in token'], 400);
        }

        return (string) $employeeId;
    }
}
