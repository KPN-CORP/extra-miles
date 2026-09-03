<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\DecryptsRouteId;
use App\Http\Requests\WellnessBlacklistRequest;
use App\Models\Employee;
use App\Models\WellnessBlacklist;
use Illuminate\Support\Facades\Auth;

class WellnessBlacklistController extends Controller
{
    use DecryptsRouteId;

    public function index()
    {
        $entries = WellnessBlacklist::orderByDesc('created_at')->get();

        return view('pages.admin.wellness.blacklist.index', [
            'parentLink' => 'Wellness',
            'link' => 'Blacklist',
            'active' => $entries->filter->isActive()->values(),
            'expired' => $entries->reject->isActive()->values(),
            'archived' => WellnessBlacklist::onlyTrashed()->orderByDesc('deleted_at')->get(),
        ]);
    }

    public function store(WellnessBlacklistRequest $request)
    {
        $employee = Employee::where('employee_id', $request->validated('employee_id'))->first();

        WellnessBlacklist::create($request->safe()->only(['employee_id', 'reason', 'end_date']) + [
            // Snapshot the name so the list stays readable without a cross-connection join.
            'fullname' => $employee?->fullname,
            'created_by' => Auth::id(),
        ]);

        return redirect()->back()->with('success', ($employee?->fullname ?: $request->validated('employee_id')).' has been added to the blacklist.');
    }

    public function update(WellnessBlacklistRequest $request, string $encryptedId)
    {
        $entry = WellnessBlacklist::findOrFail($this->decryptId($encryptedId));

        $employee = Employee::where('employee_id', $request->validated('employee_id'))->first();

        $entry->update($request->safe()->only(['employee_id', 'reason', 'end_date']) + [
            'fullname' => $employee?->fullname ?: $entry->fullname,
            'updated_by' => Auth::id(),
        ]);

        return redirect()->back()->with('success', 'Blacklist entry updated.');
    }

    /**
     * End a blacklist today rather than deleting it, so the history survives.
     */
    public function lift(string $encryptedId)
    {
        $entry = WellnessBlacklist::findOrFail($this->decryptId($encryptedId));

        $entry->update([
            'end_date' => now()->toDateString(),
            'updated_by' => Auth::id(),
        ]);

        return redirect()->back()->with('success', ($entry->fullname ?: $entry->employee_id).' is no longer blacklisted.');
    }

    public function archive(string $encryptedId)
    {
        $entry = WellnessBlacklist::findOrFail($this->decryptId($encryptedId));
        $entry->delete();

        return redirect()->back()->with('success', 'Blacklist entry archived.');
    }

    public function restore(string $encryptedId)
    {
        $entry = WellnessBlacklist::onlyTrashed()->findOrFail($this->decryptId($encryptedId));
        $entry->restore();

        return redirect()->back()->with('success', 'Blacklist entry restored.');
    }
}
