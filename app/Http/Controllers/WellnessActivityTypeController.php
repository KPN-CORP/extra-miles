<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\DecryptsRouteId;
use App\Http\Requests\WellnessActivityTypeRequest;
use App\Models\WellnessActivityType;
use Illuminate\Support\Facades\Auth;

class WellnessActivityTypeController extends Controller
{
    use DecryptsRouteId;

    public function index()
    {
        $types = WellnessActivityType::withCount('activities')
            ->orderBy('name')
            ->get();

        $archived = WellnessActivityType::onlyTrashed()
            ->orderByDesc('deleted_at')
            ->get();

        return view('pages.admin.wellness.types.index', [
            'parentLink' => 'Wellness',
            'link' => 'Activity Types',
            'types' => $types,
            'archived' => $archived,
        ]);
    }

    public function store(WellnessActivityTypeRequest $request)
    {
        WellnessActivityType::create($request->safe()->only(['name', 'description', 'is_active']) + [
            'created_by' => Auth::id(),
        ]);

        return redirect()->back()->with('success', 'Activity type created.');
    }

    public function update(WellnessActivityTypeRequest $request, string $encryptedId)
    {
        $type = WellnessActivityType::findOrFail($this->decryptId($encryptedId));

        $type->update($request->safe()->only(['name', 'description', 'is_active']) + [
            'updated_by' => Auth::id(),
        ]);

        return redirect()->back()->with('success', 'Activity type updated.');
    }

    public function archive(string $encryptedId)
    {
        $type = WellnessActivityType::withCount('activities')
            ->findOrFail($this->decryptId($encryptedId));

        // Activities reference the type with restrictOnDelete; archiving one that
        // is still in use would leave those activities pointing at a hidden type.
        if ($type->activities_count > 0) {
            return redirect()->back()->with('error', 'This type still has '.$type->activities_count.' activity(ies). Archive those first.');
        }

        $type->delete();

        return redirect()->back()->with('success', 'Activity type archived.');
    }

    public function restore(string $encryptedId)
    {
        $type = WellnessActivityType::onlyTrashed()->findOrFail($this->decryptId($encryptedId));
        $type->restore();

        return redirect()->back()->with('success', 'Activity type restored.');
    }
}
