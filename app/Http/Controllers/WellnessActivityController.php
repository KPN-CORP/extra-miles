<?php

namespace App\Http\Controllers;

use App\Enums\WellnessActivityStatus;
use App\Enums\WellnessRegistrationMethod;
use App\Http\Controllers\Concerns\DecryptsRouteId;
use App\Http\Requests\WellnessActivityRequest;
use App\Models\WellnessActivity;
use App\Models\WellnessActivityType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class WellnessActivityController extends Controller
{
    use DecryptsRouteId;

    public function index()
    {
        $activities = WellnessActivity::with('type')
            ->withCount('schedules')
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
        ]);
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
        ]);
    }

    public function store(WellnessActivityRequest $request)
    {
        $activity = WellnessActivity::create($request->safe()->only([
            'wellness_activity_type_id', 'registration_method', 'name', 'description', 'status',
        ]) + [
            'image' => $this->storeImage($request),
            'created_by' => Auth::id(),
        ]);

        return redirect()
            ->route('admin.wellness.schedules.index', $activity->encrypted_id)
            ->with('success', 'Activity created. Add its schedules below.');
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
        ]);
    }

    public function update(WellnessActivityRequest $request, string $encryptedId)
    {
        $activity = WellnessActivity::findOrFail($this->decryptId($encryptedId));

        $attributes = $request->safe()->only([
            'wellness_activity_type_id', 'registration_method', 'name', 'description', 'status',
        ]) + ['updated_by' => Auth::id()];

        if ($image = $this->storeImage($request)) {
            if ($activity->image) {
                Storage::disk('public')->delete($activity->image);
            }
            $attributes['image'] = $image;
        }

        $activity->update($attributes);

        return redirect()
            ->route('admin.wellness.activities.index')
            ->with('success', 'Activity updated.');
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
