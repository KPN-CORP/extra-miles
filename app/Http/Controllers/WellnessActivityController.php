<?php

namespace App\Http\Controllers;

use App\Enums\WellnessActivityStatus;
use App\Enums\WellnessRegistrationMethod;
use App\Enums\WellnessScheduleStatus;
use App\Http\Controllers\Concerns\DecryptsRouteId;
use App\Http\Requests\WellnessActivityRequest;
use App\Models\WellnessActivity;
use App\Models\WellnessActivityType;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class WellnessActivityController extends Controller
{
    use DecryptsRouteId;

    public function index(Request $request)
    {
        // Filtering runs in SQL rather than in the DataTable because the date
        // range asks a question about a *related* table -- "has a session in
        // this window" -- which the rendered rows cannot answer.
        $filters = [
            'type' => $request->query('type'),
            'method' => $request->query('method'),
            'from' => $request->query('from'),
            'to' => $request->query('to'),
        ];

        $method = WellnessRegistrationMethod::tryFrom((string) $filters['method']);
        $from = $this->parseFilterDate($filters['from']);
        $to = $this->parseFilterDate($filters['to']);

        // A backwards range would silently match nothing; read it as intended.
        if ($from && $to && $from->gt($to)) {
            [$from, $to] = [$to, $from];
            [$filters['from'], $filters['to']] = [$filters['to'], $filters['from']];
        }

        $activities = WellnessActivity::query()
            ->with([
                'type',
                // Loaded up front so the list's expandable Schedules row has its
                // sessions to hand -- clicking one must not cost a request.
                'schedules' => fn ($q) => $q->withSeatCounts()->orderBy('start_at'),
            ])
            ->withCount('schedules')
            ->when($filters['type'], fn ($q, $type) => $q->where('wellness_activity_type_id', $type))
            ->when($method, fn ($q) => $q->where('registration_method', $method->value))
            ->when($from || $to, fn ($q) => $q->whereHas('schedules', function ($schedule) use ($from, $to) {
                // Compared on the session's start date. A session that starts
                // inside the window counts, whatever time of day it runs.
                if ($from) {
                    $schedule->whereDate('start_at', '>=', $from);
                }

                if ($to) {
                    $schedule->whereDate('start_at', '<=', $to);
                }
            }))
            ->orderByDesc('created_at')
            ->get();

        $archived = WellnessActivity::onlyTrashed()
            ->with('type')
            ->orderByDesc('deleted_at')
            ->get();

        return view('pages.admin.wellness.activities.index', [
            'parentLink' => 'Wellness',
            'link' => 'Activities',
            'activities' => $activities,
            'archived' => $archived,
            'types' => WellnessActivityType::orderBy('name')->get(),
            'methods' => WellnessRegistrationMethod::options(),
            'filters' => $filters,
            'filtersActive' => array_filter($filters) !== [],
        ]);
    }

    /**
     * Filter dates arrive from a date input, but a hand-edited query string can
     * hold anything. Anything unparseable is treated as "no bound" rather than
     * blowing up the list.
     */
    protected function parseFilterDate(mixed $value): ?Carbon
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            return Carbon::parse($value)->startOfDay();
        } catch (\Throwable) {
            return null;
        }
    }

    public function create()
    {
        return view('pages.admin.wellness.activities.create', [
            'parentLink' => 'Wellness',
            'link' => 'Create Activity',
            'back' => 'admin.wellness.activities.index',
            'types' => WellnessActivityType::active()->orderBy('name')->get(),
            'statuses' => WellnessActivityStatus::options(),
            'methods' => WellnessRegistrationMethod::options(),
            'scheduleStatuses' => WellnessScheduleStatus::options(),
        ]);
    }

    public function store(WellnessActivityRequest $request)
    {
        $schedules = $request->safe()->array('schedules');
        $image = $this->storeImage($request);

        // The activity and the sessions entered on its Schedules tab are saved
        // together, so a bad session cannot leave a half-configured activity
        // behind. qr_token is filled in by the model's creating hook.
        $activity = DB::transaction(function () use ($request, $schedules, $image) {
            $activity = WellnessActivity::create($request->safe()->only([
                'wellness_activity_type_id', 'registration_method', 'name', 'description', 'status',
            ]) + [
                'image' => $image,
                'created_by' => Auth::id(),
            ]);

            foreach ($schedules as $schedule) {
                $activity->schedules()->create($schedule + ['created_by' => Auth::id()]);
            }

            return $activity;
        });

        $message = $schedules === []
            ? 'Activity created. Add its schedules below.'
            : 'Activity created with '.count($schedules).' schedule(s).';

        return redirect()
            ->route('admin.wellness.schedules.index', $activity->encrypted_id)
            ->with('success', $message);
    }

    public function edit(string $encryptedId)
    {
        $activity = WellnessActivity::findOrFail($this->decryptId($encryptedId));

        return view('pages.admin.wellness.activities.edit', [
            'parentLink' => 'Wellness',
            'link' => 'Edit Activity',
            'back' => 'admin.wellness.activities.index',
            'activity' => $activity,
            'types' => WellnessActivityType::active()->orderBy('name')->get(),
            'statuses' => WellnessActivityStatus::options(),
            'methods' => WellnessRegistrationMethod::options(),
            'scheduleStatuses' => WellnessScheduleStatus::options(),
            'schedules' => $activity->schedules()->withSeatCounts()->orderBy('start_at')->get(),
        ]);
    }

    public function update(WellnessActivityRequest $request, string $encryptedId)
    {
        $activity = WellnessActivity::findOrFail($this->decryptId($encryptedId));

        // The edit screen posts the whole session list, so work out which rows
        // are updates, which are new, and which the admin removed -- then check
        // the seat rules before writing anything.
        $existing = $activity->schedules()->withSeatCounts()->get()->keyBy('id');
        $submitted = $request->safe()->array('schedules');

        $plan = [];
        $errors = [];
        $seen = [];

        foreach ($submitted as $i => $row) {
            $id = $this->resolveScheduleId($row['id'] ?? null);
            unset($row['id']);

            if ($id === null) {
                $plan[] = ['schedule' => null, 'data' => $row];

                continue;
            }

            if ($id === false) {
                $errors["schedules.$i.id"] = 'This session could not be identified. Reload the page and try again.';

                continue;
            }

            $schedule = $existing->get($id);

            if (! $schedule) {
                // An id that is not one of this activity's own sessions -- stale
                // form or tampering. Refuse rather than quietly creating a row.
                $errors["schedules.$i.id"] = 'This session no longer exists. Reload the page and try again.';

                continue;
            }

            $seen[] = $id;

            // Shrinking the quota under the seats already held would silently put
            // the session over capacity; mirrors WellnessActivityScheduleController.
            if (($row['quota'] ?? null) !== null && $row['quota'] < $schedule->taken_seats) {
                $errors["schedules.$i.quota"] = 'Quota cannot be lower than the '.$schedule->taken_seats.' seat(s) already held.';

                continue;
            }

            $plan[] = ['schedule' => $schedule, 'data' => $row];
        }

        // Anything missing from the payload was removed in the form.
        $removed = $existing->except($seen);

        foreach ($removed as $schedule) {
            if ($schedule->taken_seats > 0) {
                $errors['schedules'] = 'The session on '.$schedule->start_at->format('d M Y H:i').' still has '
                    .$schedule->taken_seats.' active registration(s), so it cannot be removed. Cancel them first.';
            }
        }

        if ($errors !== []) {
            return redirect()->back()->withInput()->withErrors($errors);
        }

        $attributes = $request->safe()->only([
            'wellness_activity_type_id', 'registration_method', 'name', 'description', 'status',
        ]) + ['updated_by' => Auth::id()];

        if ($image = $this->storeImage($request)) {
            $previous = $activity->image;
            $attributes['image'] = $image;
        }

        DB::transaction(function () use ($activity, $attributes, $plan, $removed) {
            $activity->update($attributes);

            foreach ($plan as $entry) {
                if ($entry['schedule']) {
                    $entry['schedule']->update($entry['data'] + ['updated_by' => Auth::id()]);

                    continue;
                }

                $activity->schedules()->create($entry['data'] + ['created_by' => Auth::id()]);
            }

            // Soft delete, same as WellnessActivityScheduleController@archive --
            // registrations and their audit trail are never destroyed.
            foreach ($removed as $schedule) {
                $schedule->delete();
            }
        });

        // Only once the write succeeded, so a rollback cannot leave the activity
        // pointing at a file that is gone.
        if (isset($previous) && $previous) {
            Storage::disk('public')->delete($previous);
        }

        return redirect()
            ->route('admin.wellness.activities.index')
            ->with('success', 'Activity updated.');
    }

    /**
     * Schedule rows carry their encrypted id so primary keys stay out of the
     * markup.
     *
     * Returns null when the row has no id at all -- a new session. Returns false
     * when an id was sent but will not decrypt, which is a tampered or corrupted
     * form: the caller rejects it rather than treating it as new, which would
     * duplicate the session and drop the original.
     */
    protected function resolveScheduleId(mixed $encrypted): int|false|null
    {
        if (! is_string($encrypted) || $encrypted === '') {
            return null;
        }

        try {
            return (int) Crypt::decryptString($encrypted);
        } catch (DecryptException) {
            return false;
        }
    }

    public function archive(string $encryptedId)
    {
        $activity = WellnessActivity::findOrFail($this->decryptId($encryptedId));
        $activity->delete();

        return redirect()->back()->with('success', 'Activity archived.');
    }

    public function restore(string $encryptedId)
    {
        $activity = WellnessActivity::onlyTrashed()->findOrFail($this->decryptId($encryptedId));
        $activity->restore();

        return redirect()->back()->with('success', 'Activity restored.');
    }

    /**
     * Images are stored flat in storage/app/public so the existing
     * GET /images/{filename} route can serve them -- that route matches a
     * single path segment, so subdirectories would 404.
     */
    protected function storeImage(Request $request): ?string
    {
        if (! $request->hasFile('image')) {
            return null;
        }

        $file = $request->file('image');
        $name = 'wellness-'.Str::uuid().'.'.$file->getClientOriginalExtension();

        $file->storeAs('', $name, 'public');

        return $name;
    }
}
